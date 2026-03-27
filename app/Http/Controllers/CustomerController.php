<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\MasterCategory;
use App\Models\MasterLevel;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::addSelect([
                'visits_count' => Booking::selectRaw('count(distinct start_time)')
                    ->whereColumn('customer_id', 'customers.id'),
            ])
            ->withSum('bookings as total_spent', 'billed_price')
            ->with([
                'masterLevel',
                'bookings' => fn($q) => $q->orderBy('start_time', 'desc')->with('tableModel'),
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // ── Bulk tag query (fix N+1) ─────────────────────────────────────
        // Satu query untuk mengambil top-3 tag per customer sekaligus,
        // bukan memanggil topTags() di setiap baris loop.
        $customerIds = $customers->pluck('id');

        $rawTopTags = DB::table('master_tags')
            ->join('booking_tags', 'master_tags.id', '=', 'booking_tags.tag_id')
            ->join('bookings', 'booking_tags.booking_id', '=', 'bookings.id')
            ->whereIn('bookings.customer_id', $customerIds)
            ->select(
                'bookings.customer_id',
                'master_tags.id',
                'master_tags.name',
                'master_tags.group_name',
                DB::raw('count(*) as tag_count')
            )
            ->groupBy('bookings.customer_id', 'master_tags.id', 'master_tags.name', 'master_tags.group_name')
            ->orderBy('bookings.customer_id')
            ->orderByDesc('tag_count')
            ->get()
            ->groupBy('customer_id')
            ->map(fn($tags) => $tags->take(3)); // Top 3 per customer

        // ── Category & Level config dari DB (bukan hardcode) ────────────
        $categoryMap = MasterCategory::all()
            ->keyBy(fn($c) => strtoupper($c->name));

        $levels = MasterLevel::orderBy('min_spending', 'asc')->get();

        $lifetimeRevenue = Booking::whereRaw(
            'LOWER(CAST(status AS TEXT)) IN (?, ?)', ['completed', 'billed']
        )->sum('billed_price');

        $allCustomersForExport = Customer::addSelect([
                'visits_count' => Booking::selectRaw('count(distinct start_time)')
                    ->whereColumn('customer_id', 'customers.id'),
            ])
            ->withSum('bookings as total_spent', 'billed_price')
            ->orderBy('name')
            ->get();

        return view('admin.customers', compact(
            'customers', 'lifetimeRevenue', 'allCustomersForExport',
            'levels', 'categoryMap', 'rawTopTags'
        ));
    }

    public function export()
    {
        $customers = Customer::withSum('bookings as total_spent', 'billed_price')
            ->addSelect([
                'visits_count' => Booking::selectRaw('count(distinct start_time)')
                    ->whereColumn('customer_id', 'customers.id'),
            ])
            ->orderBy('name')
            ->get();

        $filename = "customers_report_" . date('Y-m-d') . ".xls";
        $headers  = [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->view('admin.exports.customers', compact('customers'))->withHeaders($headers);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'phone'  => 'nullable|string|max:20',
            'gender' => 'nullable|string|max:20',
            'age'    => 'nullable|integer',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update($request->only(['name', 'phone', 'gender', 'age']));

        return back()->with('success', 'Customer updated successfully.');
    }
}

