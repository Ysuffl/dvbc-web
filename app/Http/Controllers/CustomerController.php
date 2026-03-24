<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Booking;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('bookings')
                        ->withSum('bookings as total_spent', 'billed_price')
                        ->with(['bookings' => function($query) {
                            $query->orderBy('start_time', 'desc')->with('tableModel');
                        }])
                        ->orderBy('created_at', 'desc');

        $customers = $query->paginate(15)->withQueryString();
        $allCustomersForExport = Customer::withCount('bookings')->withSum('bookings as total_spent', 'billed_price')->orderBy('name')->get();
        $lifetimeRevenue = Booking::whereRaw('LOWER(CAST(status AS TEXT)) IN (?, ?, ?, ?)', ['completed', 'billed', 'ok', 'paid'])->sum('billed_price');
        return view('admin.customers', compact('customers', 'lifetimeRevenue', 'allCustomersForExport'));
    }

    public function export()
    {
        $customers = Customer::withSum('bookings as total_spent', 'billed_price')
                            ->withCount('bookings')
                            ->orderBy('name')
                            ->get();

        $filename = "customers_report_" . date('Y-m-d') . ".xls";
        
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->view('admin.exports.customers', compact('customers'))->withHeaders($headers);
    }
}
