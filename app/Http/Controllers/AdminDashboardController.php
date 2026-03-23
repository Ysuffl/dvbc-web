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
        $tables = Table::where('area_id', $selectedFloor)
            ->with(['bookings' => function($query) {
                $query->whereDate('start_time', today())
                      ->whereRaw('LOWER(CAST(status AS TEXT)) NOT IN (?, ?)', ['cancelled', 'no show'])
                      ->with('customer');
            }])
            ->orderBy('code')
            ->get();

        // Recent Bookings for the list
        $recentBookings = Booking::with(['tableModel', 'customer'])
            ->orderBy('start_time', 'desc')
            ->limit(10)
            ->get();

        $thisWeekStart = now()->startOfWeek();
        $thisWeekEnd = now()->endOfWeek();

        $bookedRoomsCount = Booking::whereBetween('start_time', [$thisWeekStart, $thisWeekEnd])
            ->whereHas('tableModel', function($query) use ($selectedFloor) {
                $query->where('area_id', $selectedFloor);
            })
            ->whereRaw('LOWER(CAST(status AS TEXT)) IN (?, ?, ?)', ['confirmed', 'arrived', 'occupied'])
            ->count();

        $pendingCount = Booking::whereBetween('start_time', [$thisWeekStart, $thisWeekEnd])
            ->whereHas('tableModel', function($query) use ($selectedFloor) {
                $query->where('area_id', $selectedFloor);
            })
            ->whereRaw('LOWER(CAST(status AS TEXT)) = ?', ['pending'])
            ->count();

        $cancelledCount = Booking::whereBetween('start_time', [$thisWeekStart, $thisWeekEnd])
            ->whereHas('tableModel', function($query) use ($selectedFloor) {
                $query->where('area_id', $selectedFloor);
            })
            ->whereRaw('LOWER(CAST(status AS TEXT)) = ?', ['cancelled'])
            ->count();

        // Total Revenue
        $totalRevenue = Booking::whereBetween('start_time', [$thisWeekStart, $thisWeekEnd])
            ->whereHas('tableModel', function($query) use ($selectedFloor) {
                $query->where('area_id', $selectedFloor);
            })
            ->whereRaw('LOWER(CAST(status AS TEXT)) IN (?, ?, ?)', ['confirmed', 'arrived', 'occupied'])
            ->sum('pax') * 25; 

        $stats = [
            'booked_rooms' => $bookedRoomsCount,
            'pending' => $pendingCount,
            'cancelled' => $cancelledCount,
            'total_revenue' => $totalRevenue,
        ];

        return view('admin.dashboard', compact('tables', 'recentBookings', 'stats', 'floors', 'selectedFloor'));
    }

    public function floorPlan()
    {
        $tables = Table::all();
        return view('admin.floor_plan', compact('tables'));
    }

    public function updateCoordinates(Request $request)
    {
        // Expected payload: json array [{id: 1, x_pos: 100, y_pos: 200}, ...]
        $tablesData = $request->json()->all();
        foreach ($tablesData as $data) {
            if (isset($data['id'])) {
                Table::where('id', $data['id'])->update([
                    'x_pos' => $data['x_pos'],
                    'y_pos' => $data['y_pos']
                ]);
            }
        }
        return response()->json(['status' => 'success']);
    }
}
