<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'asc')->get();
        return view('admin.users', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
            'role'     => 'required|in:ADMIN,STAFF'
        ]);

        User::create([
            'username' => $request->username,
            'hashed_password' => Hash::make($request->password), // Laravel defaults to bcrypt
            'role' => $request->role,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'User created successfully!');
    }
}
