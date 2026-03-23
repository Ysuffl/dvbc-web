<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('bookings')
                        ->with(['bookings' => function($query) {
                            $query->orderBy('start_time', 'desc')->with('tableModel');
                        }])
                        ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        $customers = $query->paginate(15)->withQueryString();
        return view('admin.customers', compact('customers'));
    }
}
