@extends('layouts.admin')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700" 
     x-data="{ 
        showAddUserModal: false,
        searchQuery: ''
     }">
    
    <!-- Premium Header & Stats -->
    <div class="relative overflow-hidden bg-stone-900 rounded-md p-10 shadow-md">
        <!-- Abstract Background Shapes -->
        <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-brand-primary/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary/10 rounded-md mb-4 border border-brand-primary/20 backdrop-blur-sm">
                    <i data-lucide="shield-check" class="w-4 h-4 text-brand-primary"></i>
                    <span class="text-[10px] font-extrabold text-brand-primary uppercase tracking-[0.2em]">Security Center</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tighter mb-2 uppercase">
                    User <span class="text-brand-primary">Management</span>
                </h1>
                <p class="text-stone-400 font-extrabold text-[10px] uppercase tracking-widest max-w-md">Control administrative access and manage staff credentials with ease.</p>
            </div>

            <!-- Stats Quick View -->
            <div class="flex gap-4">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-md min-w-[140px] text-center">
                    <p class="text-2xl font-extrabold text-white leading-none mb-1 tabular-nums">{{ $users->count() }}</p>
                    <p class="text-[10px] font-extrabold text-stone-400 uppercase tracking-widest">Total Accounts</p>
                </div>
                <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-md min-w-[140px] text-center">
                    <p class="text-2xl font-extrabold text-brand-primary leading-none mb-1 tabular-nums">{{ $users->where('role', 'ADMIN')->count() }}</p>
                    <p class="text-[10px] font-extrabold text-stone-400 uppercase tracking-widest">Admins</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 px-2">
        <div class="relative w-full sm:w-96 group">
            <i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 group-focus-within:text-brand-primary transition-colors"></i>
            <input type="text" x-model="searchQuery" placeholder="SEARCH BY USERNAME..." 
                   class="w-full bg-white border border-stone-200 rounded-md pl-12 pr-4 py-4 text-[10px] font-extrabold uppercase tracking-widest focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all shadow-sm">
        </div>
        
        <button @click="showAddUserModal = true" 
                class="w-full sm:w-auto flex items-center justify-center gap-3 bg-brand-primary hover:opacity-90 text-white font-extrabold text-[10px] uppercase tracking-widest py-4 px-8 rounded-md shadow-md transition-all hover:-translate-y-0.5 active:translate-y-0 outline-none">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            Create Account
        </button>
    </div>

    <!-- User List Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($users as $user)
        <div x-show="!searchQuery || '{{ strtolower($user->username) }}'.includes(searchQuery.toLowerCase())"
             class="group relative bg-white border border-stone-200 rounded-md p-8 shadow-sm hover:shadow-lg hover:border-brand-primary/30 transition-all duration-300">
            
            <!-- User Status Badge -->
            <div class="absolute top-8 right-8">
                <form action="{{ route('users.toggle', $user->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" 
                            class="flex items-center gap-2 px-3 py-1.5 rounded-sm text-[9px] font-extrabold uppercase tracking-widest border transition-all {{ $user->is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-stone-50 text-stone-400 border-stone-200' }}">
                        <div class="w-1.5 h-1.5 rounded bg-current {{ $user->is_active ? 'animate-pulse' : '' }}"></div>
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </button>
                </form>
            </div>

            <!-- Profile Info -->
            <div class="flex flex-col items-center text-center space-y-4">
                <div class="relative">
                    <div class="w-24 h-24 bg-stone-50 border border-stone-200 rounded-md flex items-center justify-center text-stone-400 group-hover:text-brand-primary transition-colors duration-300">
                        <i data-lucide="user" class="w-10 h-10"></i>
                    </div>
                </div>
                
                <div class="space-y-1">
                    <h3 class="text-lg font-extrabold text-stone-900 tracking-widest uppercase">{{ $user->username }}</h3>
                    <span class="px-3 py-1 bg-brand-light border border-brand-primary/20 text-brand-primary rounded-sm text-[9px] font-extrabold uppercase tracking-widest inline-block">
                        {{ $user->role }}
                    </span>
                </div>
            </div>

            <!-- Meta Data -->
            <div class="grid grid-cols-2 gap-4 mt-8 pt-6 border-t border-stone-100">
                <div class="text-center">
                    <p class="text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-1">ID Account</p>
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-stone-700 tabular-nums">#{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-1">Status</p>
                    <p class="text-[10px] font-extrabold uppercase tracking-widest {{ $user->is_active ? 'text-emerald-600' : 'text-stone-400' }}">{{ $user->is_active ? 'Online' : 'Offline' }}</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex gap-3">
                <button class="flex-1 py-3 bg-stone-50 hover:bg-stone-100 border border-stone-200 text-stone-500 hover:text-brand-primary rounded-md text-[10px] font-extrabold uppercase tracking-widest transition-all">
                    <i data-lucide="settings-2" class="w-4 h-4 mx-auto"></i>
                </button>
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="flex-1" data-confirm="Are you sure you want to delete this user? This action cannot be undone.">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-white hover:bg-rose-50 border border-rose-200 text-rose-500 rounded-md text-[10px] font-extrabold uppercase tracking-widest transition-all">
                        <i data-lucide="trash-2" class="w-4 h-4 mx-auto"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Create User Modal -->
    <div x-show="showAddUserModal" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
        
        <div class="bg-white rounded-md shadow-xl w-full max-w-lg overflow-hidden border border-stone-200"
             @click.away="showAddUserModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="p-10">
                <div class="flex justify-between items-start mb-10">
                    <div>
                        <h2 class="text-xl font-extrabold text-stone-900 tracking-widest uppercase">New Account</h2>
                        <p class="text-stone-400 font-extrabold text-[10px] uppercase tracking-widest mt-1">Configure staff access control</p>
                    </div>
                    <button @click="showAddUserModal = false" class="p-3 bg-stone-50 text-stone-400 hover:text-stone-600 border border-stone-200 rounded-md transition-all outline-none">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-[9px] font-extrabold text-stone-400 uppercase tracking-widest ml-1">Username</label>
                        <div class="relative">
                            <i data-lucide="user" class="w-4 h-4 absolute left-5 top-1/2 -translate-y-1/2 text-stone-300"></i>
                            <input type="text" name="username" required placeholder="E.G. JOHNDOE"
                                   class="w-full bg-stone-50 border border-stone-200 rounded-md pl-12 pr-6 py-4 text-stone-900 font-extrabold text-[10px] uppercase tracking-widest focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all outline-none">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-extrabold text-stone-400 uppercase tracking-widest ml-1">Access Password</label>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 absolute left-5 top-1/2 -translate-y-1/2 text-stone-300"></i>
                            <input type="password" name="password" required placeholder="••••••••"
                                   class="w-full bg-stone-50 border border-stone-200 rounded-md pl-12 pr-6 py-4 text-stone-900 font-extrabold text-[10px] tracking-widest focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all outline-none">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-extrabold text-stone-400 uppercase tracking-widest ml-1">Account Role</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="role" value="STAFF" checked class="peer hidden">
                                <div class="p-4 rounded-md bg-stone-50 border border-stone-200 peer-checked:border-brand-primary peer-checked:bg-brand-light transition-all text-center">
                                    <i data-lucide="users" class="w-5 h-5 mx-auto mb-2 text-stone-400 group-hover:text-brand-primary peer-checked:text-brand-primary transition-colors"></i>
                                    <p class="text-[10px] font-extrabold text-stone-500 peer-checked:text-brand-primary uppercase tracking-widest">Staff</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="role" value="ADMIN" class="peer hidden">
                                <div class="p-4 rounded-md bg-stone-50 border border-stone-200 peer-checked:border-rose-500 peer-checked:bg-rose-50 transition-all text-center">
                                    <i data-lucide="shield-check" class="w-5 h-5 mx-auto mb-2 text-stone-400 group-hover:text-rose-500 peer-checked:text-rose-500 transition-colors"></i>
                                    <p class="text-[10px] font-extrabold text-stone-500 peer-checked:text-rose-600 uppercase tracking-widest">Admin</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" 
                                class="w-full py-4 bg-brand-primary hover:opacity-90 text-white rounded-md text-[10px] font-extrabold uppercase tracking-widest shadow-sm transition-all outline-none">
                            Create Account Permanently
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
