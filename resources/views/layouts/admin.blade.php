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
<body class="bg-[#FDFDFB] min-h-screen flex flex-col lg:flex-row text-[#1A1A1A] antialiased selection:bg-brand-primary/10" x-data="{ sidebarOpen: false }">
    <!-- Mobile Navigation Header -->
    <header class="lg:hidden sticky top-0 z-[60] glass-card px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-md flex items-center justify-center p-2 border border-stone-100 shadow-sm">
                <img src="{{ asset('images/dreamville.webp') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <div class="flex flex-col">
                <span class="font-black text-stone-900 tracking-tighter text-lg leading-none">DreamVille</span>
                <span class="text-[9px] font-black text-brand-primary uppercase tracking-[0.2em] mt-0.5">Management</span>
            </div>
        </div>
        <button @click="sidebarOpen = true" class="w-11 h-11 flex items-center justify-center bg-white border border-stone-100 rounded-md text-stone-600 hover:text-brand-primary hover:border-brand-soft transition-all shadow-sm active:scale-95">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
    </header>

    <!-- Sidebar Overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-stone-900/40 backdrop-blur-md z-[70] lg:hidden"
         x-cloak>
    </div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:sticky lg:top-0 h-screen w-80 bg-white border-r border-stone-100 flex flex-col z-[80] transition-transform duration-500 ease-in-out shadow-[10px_0_40px_rgba(0,0,0,0.02)]">
        
        <!-- Brand Section -->
        <div class="px-10 py-12">
            <div class="flex items-center gap-5 group cursor-pointer">
                <div class="relative">
                    <div class="absolute -inset-3 bg-brand-primary/5 rounded-lg blur-xl group-hover:bg-brand-primary/10 transition-all duration-500"></div>
                    <div class="relative w-14 h-14 bg-white rounded-md shadow-xl shadow-brand-primary/5 border border-stone-50 flex items-center justify-center p-2.5 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                        <img src="{{ asset('images/dreamville.webp') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                </div>
                <div class="flex flex-col">
                    <span class="font-black text-stone-900 tracking-tighter text-2xl leading-none">DreamVille</span>
                    <span class="text-[10px] font-black text-brand-primary uppercase tracking-[0.35em] mt-1.5 opacity-80">Management</span>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div id="sidebarMenu" class="flex-1 px-6 space-y-12 overflow-y-auto custom-scrollbar">
            <!-- Systems Group -->
            <div>
                <div class="px-4 mb-6 text-[10px] font-black text-stone-300 uppercase tracking-[0.4em] flex items-center gap-4">
                    <span>Operations</span>
                    <div class="h-px bg-stone-100 flex-1"></div>
                </div>
                <nav class="space-y-1.5">
                    @php
                        $navItems = [
                            ['route' => 'dashboard', 'icon' => 'layout-grid', 'label' => 'Overview', 'roles' => ['admin', 'cs', 'staff']],
                            ['route' => 'floor.index', 'icon' => 'layers', 'label' => 'Floor Mapping', 'roles' => ['admin']],
                            ['route' => 'customers', 'icon' => 'users-round', 'label' => 'Customer Hub', 'roles' => ['admin', 'cs', 'staff']],
                            ['route' => 'demographics', 'icon' => 'pie-chart', 'label' => 'Performance', 'roles' => ['admin']],
                            ['route' => 'broadcast.index', 'icon' => 'zap', 'label' => 'Broadcast HQ', 'roles' => ['admin', 'cs']],
                        ];
                    @endphp

                    @foreach($navItems as $item)
                        @if(in_array(auth()->user()->role, $item['roles']))
                        @php $isActive = request()->routeIs(str_replace('.index', '', $item['route']) . '*'); @endphp
                        <a href="{{ $isActive ? 'javascript:void(0)' : route($item['route']) }}" 
                           class="nav-link group overflow-hidden {{ $isActive ? 'bg-stone-900 text-white shadow-2xl shadow-stone-200' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' }}">
                            <div class="relative">
                                <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 {{ $isActive ? 'text-brand-primary' : 'group-hover:scale-110 transition-transform duration-300' }}"></i>
                                @if($isActive)
                                <div class="absolute -inset-2 bg-brand-primary/20 blur-lg rounded-full"></div>
                                @endif
                            </div>
                            <span class="relative z-10 font-bold {{ $isActive ? 'tracking-normal' : 'tracking-tight' }}">{{ $item['label'] }}</span>
                            @if($isActive)
                            <div class="ml-auto w-1.5 h-1.5 bg-brand-primary rounded-full"></div>
                            @endif
                        </a>
                        @endif
                    @endforeach
                </nav>
            </div>

            <!-- Administration Group -->
            @if(auth()->user()->role === 'admin')
            <div>
                <div class="px-4 mb-6 text-[10px] font-black text-stone-300 uppercase tracking-[0.4em] flex items-center gap-4">
                    <span>Enterprise</span>
                    <div class="h-px bg-stone-100 flex-1"></div>
                </div>
                <nav class="space-y-1.5">
                    <a href="{{ request()->routeIs('users.*') ? 'javascript:void(0)' : route('users.index') }}" 
                       class="nav-link group {{ request()->routeIs('users.*') ? 'bg-stone-900 text-white shadow-2xl shadow-stone-200' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' }}">
                        <i data-lucide="shield-check" class="w-5 h-5 {{ request()->routeIs('users.*') ? 'text-brand-primary' : '' }}"></i>
                        <span>Staff Directory</span>
                        @if(request()->routeIs('users.*'))<div class="ml-auto w-1.5 h-1.5 bg-brand-primary rounded-full"></div>@endif
                    </a>
                    <a href="{{ request()->routeIs('master.*') ? 'javascript:void(0)' : route('master.index') }}" 
                       class="nav-link group {{ request()->routeIs('master.*') ? 'bg-stone-900 text-white shadow-2xl shadow-stone-200' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' }}">
                        <i data-lucide="settings-2" class="w-5 h-5 {{ request()->routeIs('master.*') ? 'text-brand-primary' : '' }}"></i>
                        <span>Master Settings</span>
                        @if(request()->routeIs('master.*'))<div class="ml-auto w-1.5 h-1.5 bg-brand-primary rounded-full"></div>@endif
                    </a>
                </nav>
            </div>
            @endif
        </div>
        
        <!-- User Section Footer -->
        <div class="p-8 mt-auto border-t border-stone-50">

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center justify-center gap-3 w-full px-6 py-4 bg-stone-50 border border-stone-100 rounded-md text-stone-500 hover:text-red-500 hover:bg-red-50 hover:border-red-100 font-bold text-xs uppercase tracking-widest transition-all group overflow-hidden relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-500/0 via-red-500/0 to-red-500/5 group-hover:via-red-500/5 transition-all"></div>
                    <i data-lucide="log-out" class="w-4 h-4 group-hover:-translate-x-1 transition-transform relative z-10"></i>
                    <span class="relative z-10">Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Content Area -->
    <main class="flex-1 min-w-0 bg-[#FDFDFB]">
        <div class="w-full max-w-[1600px] mx-auto px-6 sm:px-8 lg:px-12 py-8 lg:py-12">
            @yield('content')
        </div>
    </main>
    
    <!-- Initialize Lucide Icons -->
    <script>
        // SweetAlert2 Global Configuration & Interceptors
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            } else if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
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
                        popup: 'rounded-md border border-stone-100 shadow-2xl',
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
                        popup: 'rounded-md border border-stone-100 shadow-2xl',
                        confirmButton: 'bg-stone-900 px-8 py-3 rounded-md font-bold text-sm text-white hover:bg-stone-800 transition-all focus:ring-4 focus:ring-brand-primary/20 outline-none'
                    }
                });
            @endif
            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: `<ul class="text-left space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="flex items-center gap-2 text-xs font-bold text-stone-600 uppercase">
                                <div class="w-1.5 h-1.5 bg-red-500 rounded-full"></div>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>`,
                    background: '#ffffff',
                    color: '#2D2D2D',
                    iconColor: '#ef4444',
                    customClass: {
                        popup: 'rounded-md border border-stone-100 shadow-2xl p-8',
                        confirmButton: 'bg-stone-900 px-8 py-3.5 rounded-md font-black text-xs uppercase tracking-widest text-white hover:bg-black transition-all outline-none'
                    },
                    buttonsStyling: false
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
                            popup: 'rounded-md border border-stone-100 shadow-2xl p-8',
                            confirmButton: 'bg-brand-primary px-8 py-3.5 rounded-md font-black text-xs uppercase tracking-widest text-white hover:opacity-90 transition-all focus:ring-4 focus:ring-brand-primary/20 outline-none ml-3',
                            cancelButton: 'bg-stone-100 px-8 py-3.5 rounded-md font-black text-xs uppercase tracking-widest text-stone-500 hover:bg-stone-200 transition-all outline-none'
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
                    popup: 'rounded-md border border-stone-100 shadow-2xl',
                    confirmButton: 'bg-brand-primary px-8 py-3 rounded-md font-bold text-sm text-white hover:opacity-90 transition-all focus:ring-4 focus:ring-brand-primary/20 outline-none'
                },
                buttonsStyling: false
            });
        };
    </script>
    @yield('scripts')
</body>
</html>
