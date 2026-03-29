@extends('layouts.admin')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700" 
     x-data="{ 
        showAddUserModal: false,
        searchQuery: ''
     }">
    
    <!-- Premium Header & Stats -->
    <div class="relative overflow-hidden bg-slate-900 rounded-[2.5rem] p-10 shadow-2xl shadow-slate-200">
        <!-- Abstract Background Shapes -->
        <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-500/10 rounded-xl mb-4 border border-indigo-500/20 backdrop-blur-sm">
                    <i data-lucide="shield-check" class="w-4 h-4 text-indigo-400"></i>
                    <span class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Security Center</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-white tracking-tighter mb-2">
                    User <span class="text-indigo-500">Management</span>
                </h1>
                <p class="text-slate-400 font-medium max-w-md">Control administrative access and manage staff credentials with ease.</p>
            </div>

            <!-- Stats Quick View -->
            <div class="flex gap-4">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-3xl min-w-[140px] text-center">
                    <p class="text-2xl font-black text-white leading-none mb-1">{{ $users->count() }}</p>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Total Accounts</p>
                </div>
                <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-3xl min-w-[140px] text-center">
                    <p class="text-2xl font-black text-indigo-400 leading-none mb-1">{{ $users->where('role', 'ADMIN')->count() }}</p>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Admins</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 px-2">
        <div class="relative w-full sm:w-96 group">
            <i data-lucide="search" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
            <input type="text" x-model="searchQuery" placeholder="Search by username..." 
                   class="w-full bg-white border border-slate-200 rounded-2xl pl-12 pr-4 py-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/50 outline-none transition-all shadow-sm">
        </div>
        
        <button @click="showAddUserModal = true" 
                class="w-full sm:w-auto flex items-center justify-center gap-3 bg-indigo-600 hover:bg-xl-700 text-white font-black text-xs uppercase tracking-widest py-4 px-8 rounded-2xl shadow-xl shadow-indigo-600/20 hover:shadow-indigo-600/30 transition-all hover:-translate-y-1 active:scale-95">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
            Create Account
        </button>
    </div>

    <!-- User List Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($users as $user)
        <div x-show="!searchQuery || '{{ strtolower($user->username) }}'.includes(searchQuery.toLowerCase())"
             class="group relative bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm hover:shadow-2xl hover:shadow-slate-200/50 transition-all duration-500">
            
            <!-- User Status Badge -->
            <div class="absolute top-8 right-8">
                <form action="{{ route('users.toggle', $user->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" 
                            class="flex items-center gap-2 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest transition-all {{ $user->is_active ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-600/20' : 'bg-slate-50 text-slate-400 ring-1 ring-slate-200' }}">
                        <div class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300' }}"></div>
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </button>
                </form>
            </div>

            <!-- Profile Info -->
            <div class="flex flex-col items-center text-center space-y-4">
                <div class="relative">
                    <div class="w-24 h-24 bg-gradient-to-br from-slate-100 to-slate-50 rounded-[2rem] flex items-center justify-center text-slate-400 ring-8 ring-slate-50 border border-slate-100 shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <i data-lucide="user" class="w-10 h-10"></i>
                    </div>
                </div>
                
                <div class="space-y-1">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ $user->username }}</h3>
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-100">
                        {{ $user->role }}
                    </span>
                </div>
            </div>

            <!-- Meta Data -->
            <div class="grid grid-cols-2 gap-4 mt-8 pt-8 border-t border-slate-50">
                <div class="text-center">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">ID Account</p>
                    <p class="text-sm font-bold text-slate-700">#{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</p>
                    <p class="text-sm font-bold {{ $user->is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $user->is_active ? 'Online' : 'Offline' }}</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex gap-3">
                <button class="flex-1 py-3 bg-slate-50 hover:bg-slate-100 text-slate-500 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    <i data-lucide="settings-2" class="w-4 h-4 mx-auto"></i>
                </button>
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="flex-1" data-confirm="Are you sure you want to delete this user? This action cannot be undone.">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-rose-50 hover:bg-rose-100 text-rose-500 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                        <i data-lucide="trash-2" class="w-4 h-4 mx-auto"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Create User Modal -->
    <div x-show="showAddUserModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
        
        <div class="bg-white rounded-[3rem] shadow-2xl w-full max-w-lg overflow-hidden border border-white"
             @click.away="showAddUserModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="p-10">
                <div class="flex justify-between items-start mb-10">
                    <div>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tighter">New Account</h2>
                        <p class="text-slate-400 font-bold text-sm mt-1">Configure staff access control</p>
                    </div>
                    <button @click="showAddUserModal = false" class="p-4 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-3xl transition-all">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Username</label>
                        <div class="relative">
                            <i data-lucide="user" class="w-5 h-5 absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="text" name="username" required placeholder="e.g. johndoe"
                                   class="w-full bg-slate-50 border-none rounded-[1.5rem] pl-14 pr-6 py-4 text-slate-800 font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Access Password</label>
                        <div class="relative">
                            <i data-lucide="lock" class="w-5 h-5 absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="password" name="password" required placeholder="••••••••"
                                   class="w-full bg-slate-50 border-none rounded-[1.5rem] pl-14 pr-6 py-4 text-slate-800 font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Account Role</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="role" value="STAFF" checked class="peer hidden">
                                <div class="p-5 rounded-[1.5rem] bg-slate-50 border-2 border-transparent peer-checked:border-indigo-500 peer-checked:bg-white transition-all text-center">
                                    <i data-lucide="users" class="w-6 h-6 mx-auto mb-2 text-slate-400 group-hover:text-indigo-500 transition-colors"></i>
                                    <p class="text-[10px] font-black uppercase tracking-widest">Staff</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="role" value="ADMIN" class="peer hidden">
                                <div class="p-5 rounded-[1.5rem] bg-slate-50 border-2 border-transparent peer-checked:border-rose-500 peer-checked:bg-white transition-all text-center">
                                    <i data-lucide="shield-check" class="w-6 h-6 mx-auto mb-2 text-slate-400 group-hover:text-rose-500 transition-colors"></i>
                                    <p class="text-[10px] font-black uppercase tracking-widest">Admin</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" 
                                class="w-full py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] text-xs font-black uppercase tracking-widest shadow-xl shadow-indigo-600/20 transition-all hover:scale-[1.02] active:scale-95">
                            Create Account Permanently
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
