<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Table;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Unique floors for filter
        $floors = Table::select('area_id')->distinct()->whereNotNull('area_id')->get()->pluck('area_id');
        
        $selectedFloor = $request->get('floor', $floors->first());

        // Tables for Room Status grid with current bookings for indicators
        // We filter out bookings that are completed/billed/cancelled to show the table as empty
        $tables = Table::where('area_id', $selectedFloor)
            ->with(['bookings' => function($query) {
                $query->whereDate('start_time', today())
                      ->whereNotIn(DB::raw('LOWER(CAST(status AS TEXT))'), 
                          ['cancelled', 'no show', 'completed', 'billed', 'ok', 'finished', 'done', 'paid'])
                      ->with('customer');
            }])
            ->orderBy('code')
            ->get();

        // Booking List with filters
        $recentBookingsQuery = Booking::with(['tableModel', 'customer']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $recentBookingsQuery->where(function($q) use ($search) {
                $q->whereHas('customer', function($sq) use ($search) {
                      $sq->where('name', 'like', "%$search%");
                  })
                  ->orWhereHas('tableModel', function($sq) use ($search) {
                      $sq->where('code', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('status')) {
            $statusFilter = strtolower($request->get('status'));
            $recentBookingsQuery->whereRaw('LOWER(CAST(status AS TEXT)) = ?', [$statusFilter]);
        }

        if ($request->filled('category')) {
            $category = strtolower($request->get('category'));
            
            // Map the frontend categories back to what the database holds
            $categoryMap = [
                'regular' => 'reguler',
                'priority' => 'prioritas',
                'big spender' => 'big_spender',
            ];
            
            $dbCategory = $categoryMap[$category] ?? str_replace(' ', '_', $category);

            $recentBookingsQuery->whereHas('customer', function($q) use ($dbCategory) {
                // Use CAST to TEXT to avoid PostgreSQL enum type comparison error
                $q->whereRaw('CAST(category AS TEXT) ILIKE ?', [$dbCategory]);
            });
        }

        $period = $request->get('period', 'this_week');
        if ($period == 'today') {
            $recentBookingsQuery->whereDate('start_time', today());
        } elseif ($period == 'this_week') {
            $recentBookingsQuery->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period == 'this_month') {
            $recentBookingsQuery->whereMonth('start_time', now()->month)
                                ->whereYear('start_time', now()->year);
        } elseif ($period == 'this_year') {
            $recentBookingsQuery->whereYear('start_time', now()->year);
        }

        $sort = $request->get('sort', 'desc');
        $recentBookingsQuery->orderBy('start_time', $sort);

        // Calculate totals for the filtered list before pagination
        // Fetch all filtered bookings first (used for export AND totals)
        $listQuery = clone $recentBookingsQuery;
        $allFilteredBookings = $listQuery->get();

        // Deduplicate by customer_id + start_time (PHP collection, avoids
        // PostgreSQL enum casting issues from raw SQL GROUP BY).
        // EVENT bookings span multiple tables but share the same customer_id
        // and start_time — we take the first row of each group.
        $uniqueGroups = $allFilteredBookings
            ->groupBy(function ($b) {
                $ts = $b->start_time ? $b->start_time->timestamp : 0;
                return $b->customer_id . '_' . $ts;
            })
            ->map(fn($group) => $group->first());

        $listTotals = [
            'count'  => $uniqueGroups->count(),
            'guests' => $uniqueGroups->sum('pax'),
            'amount' => $uniqueGroups->sum('billed_price'),
        ];

        $recentBookings = $recentBookingsQuery->paginate(10)->appends($request->query());

        // Dashboard Statistics for the center cards (Keep these for the today overview)
        $stats = [
            'total_bookings' => Booking::whereDate('start_time', today())->count(),
            'booked_rooms' => Booking::whereDate('start_time', today())
                            ->whereRaw('LOWER(CAST(status AS TEXT)) IN (?, ?, ?)', ['confirmed', 'booked', 'PENDING'])
                            ->count(),
            'pending' => Booking::whereDate('start_time', today())
                            ->whereRaw('LOWER(CAST(status AS TEXT)) = ?', ['pending'])
                            ->count(),
            'cancelled' => Booking::whereDate('start_time', today())
                            ->whereRaw('LOWER(CAST(status AS TEXT)) = ?', ['cancelled'])
                            ->count(),
            'total_revenue' => Booking::whereDate('start_time', today())
                                ->whereRaw('LOWER(CAST(status AS TEXT)) IN (?, ?, ?)', ['completed', 'billed', 'ok'])
                                ->sum('billed_price'),
        ];

        // Category breakdown for profesional UI
        $categoryStats = Booking::join('customers', 'bookings.customer_id', '=', 'customers.id')
            ->whereDate('bookings.start_time', today())
            ->select('customers.category', DB::raw('count(*) as count'))
            ->groupBy('customers.category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();

        $categoryStats = array_change_key_case($categoryStats, CASE_UPPER);

        $allTables = Table::orderBy('code')->get();

        return view('admin.dashboard', compact('tables', 'recentBookings', 'stats', 'floors', 'selectedFloor', 'allTables', 'categoryStats', 'listTotals', 'allFilteredBookings'));
    }

    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'customer_name' => 'required|string|max:255',
            'customer_category' => 'required|string',
            'phone' => 'nullable|string',
            'age' => 'nullable|integer',
            'pax' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        $categoryMap = [
            'REGULAR' => 'reguler',
            'PRIORITY' => 'prioritas',
            'BIG SPENDER' => 'big_spender',
        ];
        
        $dbCategory = $categoryMap[$validated['customer_category']] ?? str_replace(' ', '_', strtolower($validated['customer_category']));

        $customer = Customer::firstOrCreate(
            ['phone' => $validated['phone'] ?? ''],
            [
                'name' => $validated['customer_name'],
                'category' => $dbCategory,
                'age' => $validated['age'] ?? null
            ]
        );

        $updates = [];
        if ($customer->category !== $dbCategory) {
            $updates['category'] = $dbCategory;
        }
        if (isset($validated['age']) && $customer->age !== (int)$validated['age']) {
            $updates['age'] = (int)$validated['age'];
        }
        if (!empty($updates)) {
            $customer->update($updates);
        }

        $booking = Booking::create([
            'table_id' => $validated['table_id'],
            'customer_id' => $customer->id,
            'pax' => $validated['pax'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'notes' => $validated['notes'],
            'status' => 'PENDING'
        ]);

        return redirect()->back()->with('success', 'Booking added successfully');
    }

    public function storeEventBooking(Request $request)
    {
        $validated = $request->validate([
            'table_ids' => 'required|array',
            'table_ids.*' => 'exists:tables,id',
            'customer_name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'age' => 'nullable|integer',
            'pax' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::firstOrCreate(
            ['phone' => $validated['phone'] ?? ''],
            [
                'name' => $validated['customer_name'],
                'category' => 'EVENT',
                'age' => $validated['age'] ?? null
            ]
        );

        $updates = [];
        if ($customer->category !== 'EVENT') {
            $updates['category'] = 'EVENT';
        }
        if (isset($validated['age']) && $customer->age !== (int)$validated['age']) {
            $updates['age'] = (int)$validated['age'];
        }
        if (!empty($updates)) {
            $customer->update($updates);
        }

        foreach ($request->table_ids as $table_id) {
            Booking::create([
                'table_id' => $table_id,
                'customer_id' => $customer->id,
                'pax' => $validated['pax'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'notes' => $validated['notes'],
                'status' => 'PENDING'
            ]);
        }

        return redirect()->back()->with('success', 'Event Booking added successfully');
    }

    public function floorPlan()
    {
        $tables = Table::all();
        return view('admin.floor_plan', compact('tables'));
    }

    public function updateCoordinates(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'x' => 'required|numeric',
            'y' => 'required|numeric'
        ]);

        $table = Table::find($validated['table_id']);
        $table->update([
            'x' => $validated['x'],
            'y' => $validated['y']
        ]);

        return response()->json(['success' => true]);
    }
}
