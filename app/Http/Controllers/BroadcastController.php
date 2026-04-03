<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\MasterTag;
use App\Models\BroadcastTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BroadcastController extends Controller
{
    private string $gatewayUrl;
    private string $gatewaySecret;
    private int $timeout;

    public function __construct()
    {
        // Gunakan config() — bukan env() langsung — agar aman saat config di-cache
        $this->gatewayUrl    = config('whatsapp.gateway_url');
        $this->gatewaySecret = config('whatsapp.gateway_secret');
        $this->timeout       = config('whatsapp.timeout', 30);
    }

    public function index()
    {
        // ── PERBAIKAN: Paginate agar tidak load semua data sekaligus ──────────
        $customers = Customer::orderBy('name')->paginate(50);
        $templates = BroadcastTemplate::orderBy('name')->get();

        // Tags + jumlah customer unik per tag
        $tags = MasterTag::select('master_tags.*', DB::raw('COUNT(DISTINCT bookings.customer_id) as customer_count'))
            ->join('booking_tags', 'master_tags.id', '=', 'booking_tags.tag_id')
            ->join('bookings', 'booking_tags.booking_id', '=', 'bookings.id')
            ->groupBy(
                'master_tags.id',
                'master_tags.name',
                'master_tags.group_name',
                'master_tags.created_at',
                'master_tags.updated_at'
            )
            ->orderByDesc('customer_count')
            ->get();

        return view('admin.broadcast.index', compact('customers', 'templates', 'tags'));
    }

    // ── Gateway Status ──────────────────────────────────────────────────────
    public function getStatus()
    {
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->gatewaySecret])
                ->timeout(5)
                ->get($this->gatewayUrl . '/status');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            // ── PERBAIKAN: Jangan biarkan catch kosong; log & kembalikan error ──
            Log::channel('broadcast')->warning('Gateway status check returned non-2xx', [
                'status' => $response->status(),
            ]);
        } catch (\Exception $e) {
            Log::channel('broadcast')->error('Gateway status check failed', [
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json(['status' => 'OFFLINE', 'qrUrl' => null]);
    }

    public function startConnection()
    {
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->gatewaySecret])
                ->timeout($this->timeout)
                ->post($this->gatewayUrl . '/start');
            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::channel('broadcast')->error('Gateway start failed', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to connect to gateway', 'message' => $e->getMessage()], 500);
        }
    }

    public function getQrCode()
    {
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->gatewaySecret])
                ->timeout($this->timeout)
                ->get($this->gatewayUrl . '/qr');
            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::channel('broadcast')->error('Gateway QR fetch failed', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to retrieve QR'], 500);
        }
    }

    public function disconnect()
    {
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->gatewaySecret])
                ->timeout($this->timeout)
                ->post($this->gatewayUrl . '/disconnect');
            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::channel('broadcast')->error('Gateway disconnect failed', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    // ── Customer berdasarkan Tag ─────────────────────────────────────────────
    public function customersByTag(Request $request)
    {
        $tagId = $request->input('tag_id');

        if (!$tagId) {
            return response()->json(['error' => 'tag_id is required'], 422);
        }

        $customers = Customer::select('customers.*', DB::raw('COUNT(booking_tags.id) as tag_count'))
            ->join('bookings', 'customers.id', '=', 'bookings.customer_id')
            ->join('booking_tags', 'bookings.id', '=', 'booking_tags.booking_id')
            ->where('booking_tags.tag_id', $tagId)
            ->whereNotNull('customers.phone')
            ->groupBy(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.age',
                'customers.gender',
                'customers.total_spending',
                'customers.master_level_id',
                'customers.created_at',
                'customers.last_visit',
                'customers.total_visits'
            )
            ->orderByDesc('tag_count')
            ->get();

        return response()->json($customers);
    }

    // ── Kirim Broadcast ─────────────────────────────────────────────────────
    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'message'   => 'nullable|string|max:4096',
            // ── PERBAIKAN: Tambahkan MIME whitelist, bukan hanya 'image' ────
            'image'     => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'customers' => 'required|array|min:1',
            'customers.*' => 'integer|exists:customers,id',
        ]);

        if (!$request->message && !$request->hasFile('image')) {
            return response()->json(['error' => 'Harap berikan pesan atau gambar.'], 422);
        }

        $customers = Customer::whereIn('id', $request->customers)
            ->whereNotNull('phone')
            ->get();

        if ($customers->isEmpty()) {
            return response()->json(['error' => 'Tidak ada nomor telepon valid yang ditemukan.'], 400);
        }

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('broadcasts', 'public');
            // ── PERBAIKAN: Gunakan URL publik, bukan path absolut server ────
            $imageUrl = Storage::url($path);
        }

        $messages = $customers->map(fn($c) => [
            'to'    => $c->phone,
            'text'  => str_replace('{name}', $c->name, $request->message ?? ''),
            'image' => $imageUrl,
        ])->values()->toArray();

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->gatewaySecret,
                'Content-Type'  => 'application/json',
            ])
            // ── PERBAIKAN: Timeout terbatas (30 detik default) ──────────────
            ->timeout($this->timeout)
            ->post($this->gatewayUrl . '/broadcast', ['messages' => $messages]);

            $data = $response->json();

            if (!$response->successful()) {
                // ── PERBAIKAN: Log hanya statistik, bukan payload penuh (PII) ─
                Log::channel('broadcast')->error('Gateway HTTP Error', [
                    'status'     => $response->status(),
                    'response'   => $data,
                    'total_sent' => count($messages),
                ]);
                return response()->json($data, $response->status());
            }

            if (isset($data['failed']) && $data['failed'] > 0) {
                Log::channel('broadcast')->warning('Broadcast selesai dengan sebagian gagal', [
                    'total'   => $data['total']   ?? 0,
                    'success' => $data['success'] ?? 0,
                    'failed'  => $data['failed']  ?? 0,
                    // Jangan log nomor telepon atau isi pesan (PII)
                ]);
            }

            return response()->json($data);

        } catch (\Exception $e) {
            Log::channel('broadcast')->error('Gateway connection exception', [
                'message'    => $e->getMessage(),
                'total_sent' => count($messages),
                // ── PERBAIKAN: Tidak log trace penuh jika mengandung PII ────
            ]);
            return response()->json(['error' => 'Gateway error', 'message' => $e->getMessage()], 500);
        }
    }
}
