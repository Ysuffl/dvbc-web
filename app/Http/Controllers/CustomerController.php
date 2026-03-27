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
            ->selectRaw('*, (SELECT count(distinct start_time) FROM bookings WHERE customer_id = customers.id) + total_visits as total_combined_visits')
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
        $customers = Customer::withCount(['bookings as visits_count' => function($query) {
            $query->select(DB::raw('count(distinct start_time)'));
        }])->orderBy('name')->get();

        $filename = "customers_import_template_" . date('Y-m-d') . ".xls";
        $headers  = [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        // Format export according to user's image (name, phone, age, gender, total_spending, total_visits)
        return response()->view('admin.exports.customers', compact('customers'))->withHeaders($headers);
    }

    public function import(Request $request)
    {
        $data = $request->input('data', []);
        
        if (empty($data)) {
            return response()->json(['status' => 'error', 'message' => 'No data received.'], 400);
        }

        DB::beginTransaction();
        try {
            // Fetch levels once for calculation
            $levels = MasterLevel::orderBy('min_spending', 'desc')->get();

            foreach ($data as $row) {
                // Bersihkan baris dari spasi berlebih
                $row = array_map(fn($val) => is_string($val) ? trim($val) : $val, $row);
                
                if (empty($row['name'])) continue;

                $totalSpending = !empty($row['total_spending']) ? (float)$row['total_spending'] : 0;
                
                // Determine Level ID by Spending
                $levelId = 1; // Default Bronze
                foreach ($levels as $lv) {
                    if ($totalSpending >= $lv->min_spending) {
                        $levelId = $lv->id;
                        break;
                    }
                }

                // Update or Create logic (No more category/last_status in Customer)
                $customerData = [
                    'name'           => $row['name'],
                    'age'            => !empty($row['age']) ? (int)$row['age'] : null,
                    'gender'         => strtoupper($row['gender'] ?? 'MALE'),
                    'total_spending' => $totalSpending,
                    'total_visits'   => !empty($row['total_visits']) ? (int)$row['total_visits'] : 0,
                    'master_level_id'=> $levelId,
                    'last_visit'     => date('Y-m-d H:i:s'),
                ];

                if (!empty($row['phone'])) {
                    // Normalize phone (remove leading 0 if needed or handle as string)
                    $phone = (string)$row['phone'];
                    Customer::updateOrCreate(['phone' => $phone], $customerData);
                } else {
                    Customer::create($customerData);
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Data imported successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
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

