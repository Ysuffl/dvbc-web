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
            ->with(['bookings' => function ($query) {
            $query->whereDate('start_time', today())
                ->whereNotIn(DB::raw('LOWER(CAST(status AS TEXT))'),
                ['cancelled', 'no show', 'completed', 'billed', 'ok', 'finished', 'done', 'paid'])
                ->with('customer');
        }])
            ->orderBy('code')
            ->get();

        // Auto-clean UI logic (Sync with App): 
        // If a table has no valid active/upcoming bookings, force it to 'available' 
        // even if the database status is stale.
        $tables->each(function ($table) {
            $now = now();
            $validBookings = $table->bookings->filter(function ($b) use ($now) {
                    $status = strtolower($b->status);
                    // Keep if they are already at the table
                    if (in_array($status, ['occupied', 'arrived']))
                        return true;

                    // For pending/confirmed, check if time has passed (give 5 min grace)
                    $endTime = \Carbon\Carbon::parse($b->end_time);
                    if ($endTime->addMinutes(5)->isBefore($now))
                        return false;

                    return true;
                }
                );

                // Re-assign the filtered bookings collection back to the table model
                // this ensures the Blade only sees 'valid' current bookings
                $table->setRelation('bookings', $validBookings);

                $status = strtolower($table->status);

                if ($validBookings->isEmpty()) {
                    // Check if table is on HOLD and not expired
                    if ($status === 'hold' && $table->hold_until && $table->hold_until->isAfter($now)) {
                        $table->status = 'hold';
                    } else {
                        $table->status = 'available';
                    }
                } else {
                    // Direct status from the first valid booking
                    $first = $validBookings->first();
                    $bStatus = strtolower($first->status);
                    
                    if (in_array($bStatus, ['occupied', 'arrived'])) {
                        $table->status = 'occupied';
                    } else {
                        $table->status = 'booked';
                    }
                }
            });

        // Booking List with filters
        $recentBookingsQuery = Booking::with(['tableModel', 'customer']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $recentBookingsQuery->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($sq) use ($search) {
                        $sq->where('name', 'ilike', "%$search%");
                    }
                    )
                        ->orWhereHas('tableModel', function ($sq) use ($search) {
                    $sq->where('code', 'ilike', "%$search%");
                }
                );
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

            $recentBookingsQuery->whereHas('customer', function ($q) use ($dbCategory) {
                // Use CAST to TEXT to avoid PostgreSQL enum type comparison error
                $q->whereRaw('CAST(category AS TEXT) ILIKE ?', [$dbCategory]);
            });
        }

        $period = $request->get('period', 'this_week');
        if ($period == 'today') {
            $recentBookingsQuery->whereDate('start_time', today());
        }
        elseif ($period == 'this_week') {
            $recentBookingsQuery->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()]);
        }
        elseif ($period == 'this_month') {
            $recentBookingsQuery->whereMonth('start_time', now()->month)
                ->whereYear('start_time', now()->year);
        }
        elseif ($period == 'this_year') {
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
            'count' => $uniqueGroups->count(),
            'guests' => $uniqueGroups->sum('pax'),
            'amount' => $uniqueGroups->sum('billed_price'),
        ];

        $recentBookings = $recentBookingsQuery->paginate(10)->appends($request->query());

        // --- DASHBOARD STATISTICS (Top Cards) ---
        // These now respect the active filters (Period, Search, etc.)
        // and deduplicate multi-table events to be counted as ONE booking.
        $statsBookings = $allFilteredBookings;

        $uniqueStatsGroups = $statsBookings
            ->groupBy(function ($b) {
            // Determine a unique key for the event (customer + start_time)
            // We use timestamp to ensure string/object compatibility in the key
            $ts = ($b->start_time instanceof \Carbon\Carbon) ? $b->start_time->timestamp : strtotime($b->start_time);
            return $b->customer_id . '_' . $ts;
        })
            ->map(function ($group) {
            return [
            'first' => $group->first(),
            'all' => $group
            ];
        });

        $stats = [
            'total_bookings' => $uniqueStatsGroups->count(),
            'booked_rooms' => $uniqueStatsGroups->filter(function ($g) {
            $status = strtolower($g['first']->status);
            return in_array($status, ['confirmed', 'booked', 'pending']);
        })->count(),
            'pending' => $uniqueStatsGroups->filter(function ($g) {
            return strtolower($g['first']->status) === 'pending';
        })->count(),
            'cancelled' => $uniqueStatsGroups->filter(function ($g) {
            return strtolower($g['first']->status) === 'cancelled';
        })->count(),
            'total_revenue' => $uniqueStatsGroups->filter(function ($g) {
            $status = strtolower($g['first']->status);
            return in_array($status, ['completed', 'billed', 'ok', 'finished', 'done', 'paid']);
        })->sum(function ($g) {
            return $g['first']->billed_price;
        }),
        ];

        // Category breakdown (filtered & unique)
        $categoryStats = [];
        foreach ($uniqueStatsGroups as $g) {
            $cat = strtoupper($g['first']->customer->category ?? 'REGULER');
            $categoryStats[$cat] = ($categoryStats[$cat] ?? 0) + 1;
        }

        $allTables = Table::orderBy('code')->get();

        $customers = Customer::orderBy('name')->get();

        return view('admin.dashboard', compact('tables', 'recentBookings', 'stats', 'floors', 'selectedFloor', 'allTables', 'categoryStats', 'listTotals', 'allFilteredBookings', 'customers'));
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