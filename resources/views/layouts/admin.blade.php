<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DreamVille Admin Dashboard</title>
    <!-- Local Bundled Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .sidebar { min-height: 100vh; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#FBFBF9] flex flex-col lg:flex-row text-[#2D2D2D] items-start" x-data="{ sidebarOpen: false }">
    <!-- Mobile Hamburger Bar -->
    <div class="lg:hidden bg-white/80 backdrop-blur-md border-b border-stone-200 px-6 py-4 flex items-center justify-between sticky top-0 z-50 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-brand-light rounded-lg flex items-center justify-center p-1.5 border border-brand-soft">
                <img src="{{ asset('images/dreamville.webp') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <span class="font-extrabold text-stone-800 tracking-tight text-base uppercase">DreamVille</span>
        </div>
        <button @click="sidebarOpen = true" class="p-2.5 bg-stone-50 border border-stone-100 rounded-xl text-stone-500 hover:text-brand-primary transition-all">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
    </div>

    <!-- Sidebar Overlay (Mobile) -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-stone-900/40 backdrop-blur-sm z-[60] lg:hidden"
         style="display: none;">
    </div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:sticky lg:top-0 h-screen w-72 bg-white border-r border-stone-200/80 flex flex-col z-[70] transition-transform duration-300 ease-in-out shrink-0 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
        
        <!-- Brand Header -->
        <div class="px-8 py-10">
            <div class="flex items-center gap-4 group cursor-pointer">
                <div class="relative">
                    <div class="absolute -inset-2 bg-brand-primary/5 rounded-2xl blur-lg group-hover:bg-brand-primary/10 transition-all"></div>
                    <div class="relative w-12 h-12 bg-white rounded-2xl shadow-sm border border-brand-soft flex items-center justify-center p-2 group-hover:scale-105 transition-all duration-300">
                        <img src="{{ asset('images/dreamville.webp') }}" alt="DreamVille Logo" class="w-full h-full object-contain">
                    </div>
                </div>
                <div class="flex flex-col">
                    <span class="font-extrabold text-stone-900 tracking-tighter text-xl leading-none">DreamVille</span>
                    <span class="text-[10px] font-bold text-brand-primary uppercase tracking-[0.2em] mt-1">Management</span>
                </div>
            </div>
        </div>

        <!-- Menu -->
        <div id="sidebarMenu" class="flex-1 px-4 space-y-10 overflow-y-auto">
            <!-- Main Section -->
            <div>
                <div class="px-4 mb-4 text-[10px] font-extrabold text-stone-400 uppercase tracking-[0.3em]">
                    Core Operations
                </div>
                <nav class="space-y-1">
                    <a href="{{ request()->routeIs('dashboard') ? 'javascript:void(0)' : route('dashboard') }}" class="group relative flex items-center gap-3.5 px-5 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20' : 'text-stone-500 hover:bg-brand-light hover:text-brand-primary' }}">
                        @if(request()->routeIs('dashboard'))
                        <div class="absolute inset-y-2.5 -left-1 w-1 bg-white rounded-full"></div>
                        @endif
                        <i data-lucide="layout-grid" class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-white' : 'group-hover:scale-110 transition-transform' }}"></i>
                        <span class="font-bold text-[13px]">Overview</span>
                    </a>
                    
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ request()->routeIs('floor.*') ? 'javascript:void(0)' : route('floor.index') }}" class="group relative flex items-center gap-3.5 px-5 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('floor.*') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20' : 'text-stone-500 hover:bg-brand-light hover:text-brand-primary' }}">
                        @if(request()->routeIs('floor.*'))
                        <div class="absolute inset-y-2.5 -left-1 w-1 bg-white rounded-full"></div>
                        @endif
                        <i data-lucide="layers" class="w-5 h-5 {{ request()->routeIs('floor.*') ? 'text-white' : 'group-hover:scale-110 transition-transform' }}"></i>
                        <span class="font-bold text-[13px]">Floor Mapping</span>
                    </a>
                    @endif

                    <a href="{{ request()->routeIs('customers') ? 'javascript:void(0)' : route('customers') }}" class="group relative flex items-center gap-3.5 px-5 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('customers') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20' : 'text-stone-500 hover:bg-brand-light hover:text-brand-primary' }}">
                        @if(request()->routeIs('customers'))
                        <div class="absolute inset-y-2.5 -left-1 w-1 bg-white rounded-full"></div>
                        @endif
                        <i data-lucide="users" class="w-5 h-5 {{ request()->routeIs('customers') ? 'text-white' : 'group-hover:scale-110 transition-transform' }}"></i>
                        <span class="font-bold text-[13px]">Customer Hub</span>
                    </a>

                    @if(auth()->user()->role === 'admin')
                    <a href="{{ request()->routeIs('demographics') ? 'javascript:void(0)' : route('demographics') }}" class="group relative flex items-center gap-3.5 px-5 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('demographics') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20' : 'text-stone-500 hover:bg-brand-light hover:text-brand-primary' }}">
                        @if(request()->routeIs('demographics'))
                        <div class="absolute inset-y-2.5 -left-1 w-1 bg-white rounded-full"></div>
                        @endif
                        <i data-lucide="bar-chart-3" class="w-5 h-5 {{ request()->routeIs('demographics') ? 'text-white' : 'group-hover:scale-110 transition-transform' }}"></i>
                        <span class="font-bold text-[13px]">Insights & Data</span>
                    </a>
                    <a href="{{ request()->routeIs('broadcast.*') ? 'javascript:void(0)' : route('broadcast.index') }}" class="group relative flex items-center gap-3.5 px-5 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('broadcast.*') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20' : 'text-stone-500 hover:bg-brand-light hover:text-brand-primary' }}">
                        @if(request()->routeIs('broadcast.*'))
                        <div class="absolute inset-y-2.5 -left-1 w-1 bg-white rounded-full"></div>
                        @endif
                        <i data-lucide="send" class="w-5 h-5 {{ request()->routeIs('broadcast.*') ? 'text-white' : 'group-hover:scale-110 transition-transform' }}"></i>
                        <span class="font-bold text-[13px]">Broadcast HQ</span>
                    </a>
                    @endif
                </nav>
            </div>

            @if(auth()->user()->role === 'admin')
            <!-- Management Section -->
            <div>
                <div class="px-4 mb-4 text-[10px] font-extrabold text-stone-400 uppercase tracking-[0.3em]">
                    Enterprise
                </div>
                <nav class="space-y-1">
                    <a href="{{ request()->routeIs('users.*') ? 'javascript:void(0)' : route('users.index') }}" class="group relative flex items-center gap-3.5 px-5 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('users.*') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20' : 'text-stone-500 hover:bg-brand-light hover:text-brand-primary' }}">
                        @if(request()->routeIs('users.*'))
                        <div class="absolute inset-y-2.5 -left-1 w-1 bg-white rounded-full"></div>
                        @endif
                        <i data-lucide="shield" class="w-5 h-5 {{ request()->routeIs('users.*') ? 'text-white' : 'group-hover:scale-110 transition-transform' }}"></i>
                        <span class="font-bold text-[13px]">Staff Access</span>
                    </a>
                    <a href="{{ request()->routeIs('master.*') ? 'javascript:void(0)' : route('master.index') }}" class="group relative flex items-center gap-3.5 px-5 py-3.5 rounded-xl transition-all duration-300 {{ request()->routeIs('master.*') ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20' : 'text-stone-500 hover:bg-brand-light hover:text-brand-primary' }}">
                        @if(request()->routeIs('master.*'))
                        <div class="absolute inset-y-2.5 -left-1 w-1 bg-white rounded-full"></div>
                        @endif
                        <i data-lucide="box" class="w-5 h-5 {{ request()->routeIs('master.*') ? 'text-white' : 'group-hover:scale-110 transition-transform' }}"></i>
                        <span class="font-bold text-[13px]">Master Settings</span>
                    </a>
                </nav>
            </div>
            @endif
        </div>
        
        <!-- Logout -->
        <div class="p-6 mt-auto">

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center justify-center gap-3 w-full px-6 py-3.5 bg-stone-900 rounded-xl text-stone-400 hover:text-white hover:bg-stone-800 font-bold text-[13px] shadow-sm transition-all group">
                    <i data-lucide="log-out" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 min-w-0 bg-[#FBFBF9]">
        <div class="w-full px-4 sm:px-6 lg:px-10 py-6 sm:py-10">
            @yield('content')
        </div>
    </main>
    
    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();

        // SweetAlert2 Global Configuration & Interceptors
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Handle Flash Messages from Laravel Session
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false,
                    background: '#ffffff',
                    color: '#2D2D2D',
                    iconColor: '#10b981',
                    customClass: {
                        popup: 'rounded-[1.5rem] border border-stone-100 shadow-2xl',
                    }
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error Occurred',
                    text: "{{ session('error') }}",
                    background: '#ffffff',
                    color: '#2D2D2D',
                    iconColor: '#ef4444',
                    customClass: {
                        popup: 'rounded-[1.5rem] border border-stone-100 shadow-2xl',
                        confirmButton: 'bg-stone-900 px-8 py-3 rounded-xl font-bold text-sm text-white hover:bg-stone-800 transition-all focus:ring-4 focus:ring-brand-primary/20 outline-none'
                    }
                });
            @endif

            // 2. Maintain Sidebar Scroll Position
            const sidebarMenu = document.getElementById('sidebarMenu');
            if (sidebarMenu) {
                const scrollPos = sessionStorage.getItem('sidebarScrollPos');
                if (scrollPos) sidebarMenu.scrollTop = parseInt(scrollPos);
                
                sidebarMenu.addEventListener('scroll', () => {
                    sessionStorage.setItem('sidebarScrollPos', sidebarMenu.scrollTop);
                });
            }

            // 3. Global Confirm Interceptor for forms with data-confirm
            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (form.hasAttribute('data-confirm')) {
                    e.preventDefault();
                    const message = form.getAttribute('data-confirm');
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, proceed',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        background: '#ffffff',
                        color: '#2D2D2D',
                        iconColor: '#A68A56',
                        customClass: {
                            popup: 'rounded-[2rem] border border-stone-100 shadow-2xl p-8',
                            confirmButton: 'bg-brand-primary px-8 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest text-white hover:opacity-90 transition-all focus:ring-4 focus:ring-brand-primary/20 outline-none ml-3',
                            cancelButton: 'bg-stone-100 px-8 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest text-stone-500 hover:bg-stone-200 transition-all outline-none'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.removeAttribute('data-confirm');
                            form.submit();
                        }
                    });
                }
            });
        });

        // 4. Helper for generic Alerts (replaces window.alert)
        window.alert = function(message) {
            Swal.fire({
                text: message,
                icon: 'info',
                confirmButtonText: 'Understand',
                background: '#ffffff',
                color: '#2D2D2D',
                customClass: {
                    popup: 'rounded-[1.5rem] border border-stone-100 shadow-2xl',
                    confirmButton: 'bg-brand-primary px-8 py-3 rounded-xl font-bold text-sm text-white hover:opacity-90 transition-all focus:ring-4 focus:ring-brand-primary/20 outline-none'
                },
                buttonsStyling: false
            });
        };
    </script>
    @yield('scripts')
</body>
</html>
