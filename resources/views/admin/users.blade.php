@extends('layouts.admin')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
            <i data-lucide="shield" class="w-8 h-8 text-blue-500"></i>
            User Management
        </h1>
        <p class="text-slate-500 text-sm mt-1">Manage administrators and staff access</p>
    </div>
</div>

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- User List -->
    <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-slate-100 border-b border-slate-200 text-slate-600 text-left text-xs uppercase font-extrabold tracking-wider">
                    <th class="px-5 py-4 rounded-tl-lg">ID</th>
                    <th class="px-5 py-4">Username</th>
                    <th class="px-5 py-4 text-center">Role</th>
                    <th class="px-5 py-4 text-center rounded-tr-lg">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-b border-slate-100 bg-white text-sm hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-4 font-bold text-slate-500">#{{ $user->id }}</td>
                    <td class="px-5 py-4 font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                        {{ $user->username }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="px-3 py-1 bg-{{ $user->role == 'ADMIN' ? 'rose-50 text-rose-700 ring-1 ring-rose-600/20' : 'sky-50 text-sky-700 ring-1 ring-sky-600/20' }} rounded-full text-[10px] font-bold uppercase tracking-wider">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="px-3 py-1 bg-{{ $user->is_active ? 'teal-50 text-teal-700 ring-1 ring-teal-600/20' : 'slate-50 text-slate-600 ring-1 ring-slate-600/20' }} rounded-full text-[10px] font-bold uppercase tracking-wider inline-flex items-center gap-1">
                            @if($user->is_active) <i data-lucide="check-circle" class="w-3 h-3"></i> @else <i data-lucide="minus-circle" class="w-3 h-3"></i> @endif
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Create User Form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 h-fit">
        <h2 class="text-xl font-bold mb-4 text-slate-800 flex items-center gap-2">
            <i data-lucide="user-plus" class="w-5 h-5 text-indigo-500"></i>
            Add New User
        </h2>
        <form action="{{ route('users.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-slate-600 text-xs font-extrabold uppercase tracking-wider mb-2">Username</label>
                <div class="relative">
                    <i data-lucide="at-sign" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="username" placeholder="Masukkan username..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all text-sm" required>
                </div>
            </div>
            <div>
                <label class="block text-slate-600 text-xs font-extrabold uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <i data-lucide="lock" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="password" name="password" placeholder="••••••••" class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all text-sm" required>
                </div>
            </div>
            <div>
                <label class="block text-slate-600 text-xs font-extrabold uppercase tracking-wider mb-2">Role Akses</label>
                <select name="role" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all text-sm" required>
                    <option value="STAFF">STAFF (Operational)</option>
                    <option value="ADMIN">ADMIN (Full Access)</option>
                </select>
            </div>
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98]">
                <i data-lucide="save" class="w-5 h-5"></i>
                Simpan User Baru
            </button>
        </form>
    </div>
</div>
@endsection
