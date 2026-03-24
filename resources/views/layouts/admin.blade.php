<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DreamVille Admin Dashboard</title>
    <!-- Use Breeze's Tailwind if available, or fallback to CDN -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .sidebar { min-height: 100vh; }
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex flex-col lg:flex-row text-gray-800" x-data="{ sidebarOpen: false }">
    <!-- Mobile Hamburger Bar -->
    <div class="lg:hidden bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-50 shrink-0">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/dreamville.png') }}" alt="Logo" class="w-8 h-8 object-contain">
            <span class="font-black text-slate-800 tracking-tight text-base">DreamVille</span>
        </div>
        <button @click="sidebarOpen = true" class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl text-slate-500 hover:text-blue-500 transition-all">
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
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] lg:hidden"
         style="display: none;">
    </div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:sticky lg:top-0 h-screen w-72 bg-[#f8fafc] border-r border-slate-200/60 flex flex-col z-[70] transition-transform duration-300 ease-in-out shrink-0">
        <!-- Brand Header -->
        <div class="px-6 py-8 flex items-center justify-between">
            <div class="bg-white rounded-[1.25rem] p-4 shadow-[0_2px_15px_rgba(0,0,0,0.02)] border border-slate-100 flex items-center gap-3 group cursor-pointer hover:border-blue-100 transition-all flex-1">
                <img src="{{ asset('images/dreamville.png') }}" alt="DreamVille Logo" class="w-10 h-10 object-contain rounded-lg">
                <span class="font-black text-slate-800 tracking-tight text-lg">DreamVille</span>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden ml-3 p-2 text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Menu -->
        <div class="flex-1 px-4 space-y-8 overflow-y-auto mt-4">
            <!-- Main Section -->
            <div>
                <div class="px-4 mb-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    Main Menu
                </div>
                <nav class="space-y-1.5">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 px-5 py-3.5 rounded-2xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-[#eef2ff] text-[#4f46e5]' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                        <i data-lucide="home" class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-[#4f46e5]' : '' }}"></i>
                        <span class="font-bold text-[13px]">Dashboard</span>
                    </a>
                    <a href="{{ route('floor_plan') }}" class="flex items-center gap-3.5 px-5 py-3.5 rounded-2xl transition-all duration-200 {{ request()->routeIs('floor_plan') ? 'bg-[#eef2ff] text-[#4f46e5]' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                        <i data-lucide="layout-grid" class="w-5 h-5 {{ request()->routeIs('floor_plan') ? 'text-[#4f46e5]' : '' }}"></i>
                        <span class="font-bold text-[13px]">Floor Plan</span>
                    </a>
                    <a href="{{ route('customers') }}" class="flex items-center gap-3.5 px-5 py-3.5 rounded-2xl transition-all duration-200 {{ request()->routeIs('customers') ? 'bg-[#eef2ff] text-[#4f46e5]' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                        <i data-lucide="contact" class="w-5 h-5 {{ request()->routeIs('customers') ? 'text-[#4f46e5]' : '' }}"></i>
                        <span class="font-bold text-[13px]">Customer CRM</span>
                    </a>
                </nav>
            </div>

            <!-- Management Section -->
            <div>
                <div class="px-4 mb-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    Administration
                </div>
                <nav class="space-y-1.5">
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3.5 px-5 py-3.5 rounded-2xl transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-[#eef2ff] text-[#4f46e5]' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                        <i data-lucide="shield-check" class="w-5 h-5 {{ request()->routeIs('users.*') ? 'text-[#4f46e5]' : '' }}"></i>
                        <span class="font-bold text-[13px]">User Management</span>
                    </a>
                    <a href="{{ route('master.index') }}" class="flex items-center gap-3.5 px-5 py-3.5 rounded-2xl transition-all duration-200 {{ request()->routeIs('master.*') ? 'bg-[#eef2ff] text-[#4f46e5]' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                        <i data-lucide="database" class="w-5 h-5 {{ request()->routeIs('master.*') ? 'text-[#4f46e5]' : '' }}"></i>
                        <span class="font-bold text-[13px]">Master Data</span>
                    </a>
                    <a href="#" class="flex items-center gap-3.5 px-5 py-3.5 rounded-2xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-all">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                        <span class="font-bold text-[13px]">Settings</span>
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- Bottom Logout Area -->
        <div class="p-6 mt-auto shrink-0">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center justify-center gap-3 w-full px-6 py-4 bg-white border border-slate-100 rounded-[1.25rem] text-slate-500 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-100 font-bold text-[13px] shadow-sm transition-all group">
                    <i data-lucide="log-out" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 min-w-0 bg-white">
        <div class="w-full px-4 sm:px-6 lg:px-10 py-6 sm:py-10">
            @yield('content')
        </div>
    </main>
    
    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
    @yield('scripts')
</body>
</html>
