@extends('layouts.admin')

@section('content')
@php
    $categories = [
        'REGULER' => ['icon' => 'user', 'color' => 'text-blue-500'],
        'PRIORITAS' => ['icon' => 'zap', 'color' => 'text-amber-500'],
        'EVENT' => ['icon' => 'calendar', 'color' => 'text-emerald-500'],
        'BIG_SPENDER' => ['icon' => 'gem', 'color' => 'text-rose-500'],
        'PARTY' => ['icon' => 'music', 'color' => 'text-purple-500'],
        'FAMILY' => ['icon' => 'users', 'color' => 'text-indigo-500'],
    ];

    $periodLabels = [
        'today' => 'Today', 
        'this_week' => 'Weekly', 
        'this_month' => 'Monthly', 
        'this_year' => 'Annual',
        'last_hour' => 'Last Hour',
        'last_30_mins' => 'Last 30 Min',
        'last_15_mins' => 'Last 15 Min',
        'next_hour' => 'Next Hour',
        'next_30_mins' => 'Next 30 Min',
        'next_15_mins' => 'Next 15 Min',
        'custom' => 'Custom Range'
    ];
    $periodLabel = $periodLabels[request('period', 'this_week')] ?? 'Weekly';
@endphp

<div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-1000"
    x-data="{ showBookingModal: false, showEventModal: false, showInfoModal: false, showEditBookingModal: false, selectedBooking: null, editBookingData: { customer: {} } }">
    
    <!-- Hero Section / Top Stats -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
        <!-- Table Status Hub -->
        <div class="xl:col-span-2 premium-card relative overflow-hidden group">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-brand-primary/5 rounded-full blur-3xl group-hover:bg-brand-primary/10 transition-all duration-700"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
                <div>
                    <h2 class="text-2xl font-black text-stone-900 tracking-tight uppercase flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-stone-900 text-white flex items-center justify-center">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        </span>
                        Table Status
                    </h2>
                    <p class="text-stone-400 font-bold text-xs mt-2 uppercase tracking-widest pl-11">Real-time floor occupancy</p>
                </div>

                <div class="flex items-center gap-3">
                    <form action="{{ route('dashboard') }}" method="GET" id="floorFilterForm">
                        <input type="hidden" name="floor" value="{{ request('floor', $selectedFloor) }}" id="floorInput">
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                class="btn-secondary flex items-center gap-3 px-6">
                                <i data-lucide="layers" class="w-4 h-4 opacity-50"></i>
                                <span class="text-[11px]">{{ $floors->contains(request('floor', $selectedFloor)) ? str_replace('_', ' ', strtoupper(request('floor', $selectedFloor))) : 'ALL AREA' }}</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 opacity-50 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                class="absolute right-0 mt-3 w-64 glass-card rounded-lg z-50 overflow-hidden p-2 border border-stone-100 shadow-2xl"
                                x-cloak>
                                <div class="space-y-1">
                                    <a href="javascript:void(0)" class="block px-5 py-3.5 text-[11px] font-black rounded-md transition-all {{ !request('floor') ? 'bg-stone-900 text-white shadow-lg' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' }} uppercase tracking-widest"
                                        onclick="document.getElementById('floorInput').value = ''; document.getElementById('floorFilterForm').submit()">ALL AREA</a>
                                    @foreach($floors as $f)
                                    <a href="javascript:void(0)" class="block px-5 py-3.5 text-[11px] font-black rounded-md transition-all {{ request('floor', $selectedFloor) == $f ? 'bg-stone-900 text-white shadow-lg' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' }} uppercase tracking-widest"
                                        onclick="document.getElementById('floorInput').value = '{{ $f }}'; document.getElementById('floorFilterForm').submit()">{{ str_replace('_', ' ', strtoupper($f)) }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Grid -->
            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 xl:grid-cols-11 gap-4 mb-12">
                @foreach($tables as $table)
                @php
                    $status = strtolower($table->status);
                    $baseStyles = "aspect-square relative flex items-center justify-center rounded-md border-2 text-sm font-black transition-all duration-300 hover:scale-110 active:scale-95 cursor-pointer shadow-sm";
                    
                    $statusConfig = [
                        'occupied'  => ['bg' => 'bg-[#FF4D4D]', 'border' => 'border-[#FF4D4D]', 'text' => 'text-white', 'shadow' => 'shadow-[#FF4D4D]/20'],
                        'arrived'   => ['bg' => 'bg-[#FF4D4D]', 'border' => 'border-[#FF4D4D]', 'text' => 'text-white', 'shadow' => 'shadow-[#FF4D4D]/20'],
                        'come'      => ['bg' => 'bg-[#FF4D4D]', 'border' => 'border-[#FF4D4D]', 'text' => 'text-white', 'shadow' => 'shadow-[#FF4D4D]/20'],
                        'confirmed' => ['bg' => 'bg-[#FFB347]', 'border' => 'border-[#FFB347]', 'text' => 'text-white', 'shadow' => 'shadow-[#FFB347]/20'],
                        'booked'    => ['bg' => 'bg-[#FFB347]', 'border' => 'border-[#FFB347]', 'text' => 'text-white', 'shadow' => 'shadow-[#FFB347]/20'],
                        'pending'   => ['bg' => 'bg-[#FFB347]', 'border' => 'border-[#FFB347]', 'text' => 'text-white', 'shadow' => 'shadow-[#FFB347]/20'],
                        'reserved'  => ['bg' => 'bg-[#FFB347]', 'border' => 'border-[#FFB347]', 'text' => 'text-white', 'shadow' => 'shadow-[#FFB347]/20'],
                        'billed'    => ['bg' => 'bg-[#2ECC71]', 'border' => 'border-[#2ECC71]', 'text' => 'text-white', 'shadow' => 'shadow-[#2ECC71]/20'],
                        'completed' => ['bg' => 'bg-[#2ECC71]', 'border' => 'border-[#2ECC71]', 'text' => 'text-white', 'shadow' => 'shadow-[#2ECC71]/20'],
                        'hold'      => ['bg' => 'bg-[#3498DB]', 'border' => 'border-[#3498DB]', 'text' => 'text-white', 'shadow' => 'shadow-[#3498DB]/20'],
                        'default'   => ['bg' => 'bg-white', 'border' => 'border-stone-100', 'text' => 'text-stone-400', 'shadow' => 'shadow-transparent']
                    ];

                    $cfg = $statusConfig[$status] ?? $statusConfig['default'];
                    $statusClass = "{$cfg['bg']} {$cfg['text']} {$cfg['border']} {$cfg['shadow']}";

                    $currentBooking = $table->bookings->first();
                    $category = $currentBooking && $currentBooking->customer ? strtolower($currentBooking->category) : null;
                    if (!$currentBooking && $status === 'hold' && $table->holdByCustomer) {
                        $category = strtolower($table->holdByCustomer->category);
                    }
                @endphp
                <div class="{{ $baseStyles }} {{ $statusClass }}"
                    @if($currentBooking) @click="selectedBooking = { 
                        id: '{{ $currentBooking->id }}',
                        status: '{{ $currentBooking->status }}', 
                        pax: '{{ $currentBooking->pax }}', 
                        start_time: '{{ $currentBooking->start_time->format('Y-m-d\TH:i') }}',
                        end_time: '{{ $currentBooking->end_time->format('Y-m-d\TH:i') }}',
                        notes: '{{ addslashes($currentBooking->notes) }}',
                        table_model: { code: '{{ $table->code }}' },
                        customer: { 
                            name: '{{ addslashes($currentBooking->customer->name ?? 'Unknown') }}', 
                            category: '{{ $currentBooking->category ?? 'REGULAR' }}', 
                            phone: '{{ $currentBooking->customer->phone ?? 'N/A' }}',
                            age: '{{ $currentBooking->customer->age }}',
                            gender: '{{ $currentBooking->customer->gender }}'
                        } 
                    }; showInfoModal = true" 
                    @elseif($status === 'hold' && $table->holdByCustomer) @click="selectedBooking = {
                        id: 'hold_{{ $table->id }}',
                        status: 'hold',
                        pax: '-',
                        start_time: '{{ $table->updated_at ? $table->updated_at->format('Y-m-d\TH:i') : '' }}',
                        end_time: '{{ $table->hold_until ? $table->hold_until->format('Y-m-d\TH:i') : '' }}',
                        notes: 'Hold until: {{ $table->hold_until ? $table->hold_until->format('H:i') : 'Unknown' }}',
                        table_model: { code: '{{ $table->code }}' },
                        customer: {
                            name: '{{ addslashes($table->holdByCustomer->name ?? 'Unknown') }}',
                            category: '{{ $table->holdByCustomer->category ?? 'REGULER' }}',
                            phone: '{{ $table->holdByCustomer->phone ?? 'N/A' }}',
                            age: '{{ $table->holdByCustomer->age ?? '' }}',
                            gender: '{{ $table->holdByCustomer->gender ?? '' }}'
                        }
                    }; showInfoModal = true" @endif>
                    {{ $table->code }}

                    @if($category)
                    <div class="absolute -top-1.5 -right-1.5 w-6 h-6 rounded-full flex items-center justify-center bg-white shadow-lg border border-stone-100 scale-90">
                        @php
                            $catIcon = match($category) {
                                'priority' => 'crown',
                                'event' => 'megaphone',
                                'big spender' => 'dollar-sign',
                                'drinker' => 'glass-water',
                                'party' => 'sparkles',
                                'dinner' => 'utensils-crossed',
                                'lunch' => 'utensils',
                                'family' => 'users',
                                'youngster' => 'smile',
                                default => 'user'
                            };
                            $catColor = match($category) {
                                'priority' => 'text-amber-500',
                                'event' => 'text-indigo-500',
                                'big spender' => 'text-emerald-500',
                                'drinker' => 'text-blue-500',
                                'party' => 'text-purple-500',
                                'dinner' => 'text-orange-500',
                                'lunch' => 'text-rose-500',
                                'family' => 'text-cyan-500',
                                'youngster' => 'text-pink-500',
                                default => 'text-stone-400'
                            };
                        @endphp
                        <i data-lucide="{{ $catIcon }}" class="w-3.5 h-3.5 {{ $catColor }}"></i>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Improved Legend -->
            <div class="flex flex-wrap items-center gap-x-8 gap-y-4 pt-10 border-t border-stone-50">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-[#FF4D4D] rounded-full ring-4 ring-rose-50"></div>
                    <span class="text-[10px] text-stone-500 font-black uppercase tracking-widest">Arrived</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-[#FFB347] rounded-full ring-4 ring-amber-50"></div>
                    <span class="text-[10px] text-stone-500 font-black uppercase tracking-widest">Pending</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-[#3498DB] rounded-full ring-4 ring-blue-50"></div>
                    <span class="text-[10px] text-stone-500 font-black uppercase tracking-widest">Hold</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-white border-2 border-stone-100 rounded-full"></div>
                    <span class="text-[10px] text-stone-500 font-black uppercase tracking-widest">Available</span>
                </div>
                
                <div class="h-4 w-px bg-stone-100 mx-4 hidden sm:block"></div>
                
                <div class="flex flex-wrap items-center gap-2">
                    @php
                        $segmentLegends = [
                            ['icon' => 'crown', 'color' => 'text-amber-500', 'label' => 'Priority'],
                            ['icon' => 'megaphone', 'color' => 'text-indigo-500', 'label' => 'Event'],
                            ['icon' => 'dollar-sign', 'color' => 'text-emerald-500', 'label' => 'VVIP'],
                            ['icon' => 'sparkles', 'color' => 'text-purple-500', 'label' => 'Party'],
                        ];
                    @endphp
                    @foreach($segmentLegends as $leg)
                    <div class="px-3 py-1.5 bg-stone-50 rounded-md flex items-center gap-2 border border-transparent hover:border-stone-100 transition-colors">
                        <i data-lucide="{{ $leg['icon'] }}" class="w-3 h-3 {{ $leg['color'] }}"></i>
                        <span class="text-[9px] text-stone-400 font-black uppercase tracking-tighter">{{ $leg['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Performance Stats Sidebar -->
        <div class="premium-card">
            <div class="flex items-center justify-between mb-10">
                <div x-data="{ openPeriod: false }" class="relative">
                    <button type="button" @click="openPeriod = !openPeriod"
                        class="btn-secondary flex items-center gap-3 px-5 py-3">
                        <i data-lucide="calendar" class="w-4 h-4 opacity-50"></i>
                        <span class="text-[10px]">
                            @php
                                $periodLabels = ['today' => 'Today', 'this_week' => 'Weekly', 'this_month' => 'Monthly', 'this_year' => 'Annual'];
                                echo $periodLabels[request('period', 'this_week')] ?? 'Weekly';
                            @endphp
                        </span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-40"></i>
                    </button>
                    <div x-show="openPeriod" @click.away="openPeriod = false"
                        class="absolute left-0 mt-3 w-48 glass-card rounded-lg z-50 overflow-hidden p-1.5 border border-stone-100 shadow-2xl"
                        x-cloak>
                        @foreach(['today', 'this_week', 'this_month', 'this_year'] as $p)
                        <a href="{{ request()->fullUrlWithQuery(['period' => $p]) }}"
                            class="block px-4 py-2.5 text-[10px] font-black rounded-md transition-all {{ request('period', 'this_week') == $p ? 'bg-stone-900 text-white shadow-lg' : 'text-stone-500 hover:bg-stone-50 hover:text-stone-900' }} uppercase tracking-widest">
                            {{ ucfirst(str_replace('_', ' ', $p)) }}
                        </a>
                        @endforeach
                    </div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-brand-light flex items-center justify-center text-brand-primary border border-brand-soft/30 animate-pulse">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="stat-card p-6">
                    <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-md flex items-center justify-center mb-6 border border-blue-100/50">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <p class="text-3xl font-black text-stone-900 tracking-tighter">{{ $stats['booked_rooms'] }}</p>
                    <p class="text-[10px] text-stone-400 font-extrabold uppercase tracking-widest mt-2">Booked</p>
                </div>

                <div class="stat-card p-6">
                    <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-md flex items-center justify-center mb-6 border border-amber-100/50">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <p class="text-3xl font-black text-stone-900 tracking-tighter">{{ $stats['pending'] }}</p>
                    <p class="text-[10px] text-stone-400 font-extrabold uppercase tracking-widest mt-2">Waitlist</p>
                </div>

                <div class="col-span-2 relative mt-2 group">
                    <div class="absolute inset-0 bg-gradient-to-br from-stone-900 to-black rounded-xl shadow-2xl shadow-stone-200"></div>
                    <div class="relative p-8 flex items-center gap-6">
                        <div class="w-16 h-16 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl flex items-center justify-center text-brand-primary transform group-hover:rotate-6 transition-transform duration-500">
                            <i data-lucide="wallet" class="w-8 h-8"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] text-stone-400 font-black uppercase tracking-[0.3em] mb-2 mb-2">Total Revenue</p>
                            <p class="text-2xl sm:text-3xl font-black text-white tracking-tighter truncate tabular-nums">
                                Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    <div class="absolute right-6 top-6 opacity-10">
                        <i data-lucide="trending-up" class="w-20 h-20 text-white"></i>
                    </div>
                </div>
            </div>

            <!-- Segments -->
            <div class="mt-12 space-y-6">
                <div class="flex items-center gap-4">
                    <span class="text-[10px] font-black text-stone-300 uppercase tracking-[0.4em]">Segmentation</span>
                    <div class="h-px bg-stone-100 flex-1"></div>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    @foreach($categories as $key => $style)
                    <div class="p-3.5 rounded-lg bg-stone-50 border border-stone-100 hover:border-brand-primary/20 hover:bg-white hover:shadow-xl hover:shadow-brand-primary/5 transition-all duration-300 group cursor-default">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-md bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform flex-shrink-0">
                                <i data-lucide="{{ $style['icon'] }}" class="w-4.5 h-4.5 {{ $style['color'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-stone-900 leading-none mb-1.5">{{ $categoryStats[$key] ?? 0 }}</p>
                                <p class="text-[9px] font-black text-stone-400 uppercase tracking-widest truncate">{{ str_replace('_', ' ', $key) }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Booking List Container -->
    <div class="premium-card !p-0 overflow-hidden relative">
        <div class="px-10 py-12 border-b border-stone-50">
            <form action="{{ route('dashboard') }}" method="GET" id="bookingFilterForm" class="space-y-10">
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-8">
                    <div>
                        <h2 class="text-2xl font-black text-stone-900 tracking-tight uppercase flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-stone-900 text-white flex items-center justify-center">
                                <i data-lucide="list" class="w-4 h-4"></i>
                            </span>
                            Live Bookings
                        </h2>
                        <p class="text-stone-400 font-bold text-[10px] mt-2 uppercase tracking-[0.2em] pl-11">Manage guest arrivals and reservations</p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto">
                        <button type="button" @click="showBookingModal = true" class="btn-secondary flex-1 xl:flex-none">
                            <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i> New Guest
                        </button>
                        <button type="button" @click="showEventModal = true" class="btn-primary flex-1 xl:flex-none">
                            <i data-lucide="sparkles" class="w-4 h-4 mr-2"></i> Create Event
                        </button>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1 relative group flex items-center">
                        <div class="absolute left-5 inset-y-0 flex items-center pointer-events-none">
                            <i data-lucide="search"
                                class="w-5 h-5 text-stone-300 group-focus-within:text-brand-primary transition-colors"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="SEARCH BY NAME, PHONE, OR TABLE..." onchange="this.form.submit()"
                            class="w-full pl-14 pr-6 py-4 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest placeholder:text-stone-300">
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <input type="hidden" name="period" value="{{ request('period', 'this_week') }}" id="periodInput">
                        <input type="hidden" name="status" value="{{ request('status') }}" id="statusInput">
                        <input type="hidden" name="category" value="{{ request('category') }}" id="categoryInput">
                        <input type="hidden" name="sort" value="{{ request('sort', 'desc') }}" id="sortInput">

                        <div class="relative" x-data="{ open: false, showCustom: {{ request('period') == 'custom' ? 'true' : 'false' }} }">
                            <button type="button" @click="open = !open"
                                class="btn-secondary px-6">
                                <i data-lucide="sliders-horizontal" class="w-4 h-4 opacity-50"></i>
                                <span class="text-[10px]">Refine Search</span>
                                
                                @php
                                    $activeFilters = 0;
                                    if(request('period') && request('period') != 'this_week') $activeFilters++;
                                    if(request('status')) $activeFilters++;
                                    if(request('category')) $activeFilters++;
                                @endphp
                                
                                @if($activeFilters > 0)
                                    <span class="ml-2 w-5 h-5 bg-brand-primary text-white text-[9px] rounded-lg flex items-center justify-center font-black animate-pulse">{{ $activeFilters }}</span>
                                @endif
                                <i data-lucide="chevron-down" class="w-4 h-4 opacity-40"></i>
                            </button>


                            <!-- Filter Modal -->
                            <div x-show="open" x-cloak
                                class="fixed inset-0 z-[100] flex items-center justify-center p-4 lg:p-8"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0">
                                
                                <!-- Backdrop -->
                                <div class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="open = false"></div>

                                 <!-- Modal Content -->
                                <div class="relative w-full max-w-5xl max-h-[90vh] bg-white rounded-md shadow-[0_40px_120px_-20px_rgba(0,0,0,0.25)] border border-white/20 overflow-hidden flex flex-col"
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-300 transform"
                                    x-transition:enter-start="opacity-0 scale-95 translate-y-12"
                                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200 transform"
                                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 scale-95 translate-y-12">
                                    
                                    <div class="px-6 py-8 md:p-12 lg:p-16 overflow-y-auto">
                                        <!-- Header within Modal -->
                                        <div class="flex items-center justify-between mb-8 md:mb-12">
                                            <div>
                                                <h2 class="text-lg md:text-xl font-extrabold text-stone-900 tracking-tight uppercase">Advanced Filters</h2>
                                                <p class="text-stone-500 font-bold text-[10px] md:text-xs mt-1">Refine your booking list exactly how you need it.</p>
                                            </div>
                                            <button type="button" @click="open = false" class="w-10 h-10 flex items-center justify-center rounded-lg bg-stone-50 text-stone-400 hover:bg-stone-100 hover:text-stone-900 transition-all shrink-0">
                                                <i data-lucide="x" class="w-5 h-5"></i>
                                            </button>
                                        </div>

                                            <div class="flex flex-col lg:flex-row gap-12">
                                                <!-- Column 1: Time Period Presets -->
                                                <div class="w-full lg:w-64 flex-none space-y-6">
                                                    <h3 class="text-[10px] font-extrabold text-stone-400 uppercase tracking-widest flex items-center gap-3">
                                                        <i data-lucide="clock-4" class="w-4 h-4 text-brand-primary"></i> Time Period
                                                    </h3>
                                                    <div class="grid grid-cols-2 lg:grid-cols-1 gap-2">
                                                        @foreach([
                                                            'last_15_mins' => 'Last 15m', 'last_30_mins' => 'Last 30m', 'last_hour' => 'Last 1h',
                                                            'next_15_mins' => 'Next 15m', 'next_30_mins' => 'Next 30m', 'next_hour' => 'Next 1h',
                                                            'today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month',
                                                            'this_year' => 'This Year', 'all' => 'All Time'
                                                        ] as $val => $label)
                                                            <button type="button" 
                                                                @click="document.getElementById('periodInput').value = '{{ $val }}'; document.getElementById('bookingFilterForm').submit()"
                                                                class="px-4 py-3 text-[10px] font-bold rounded-lg border-2 {{ request('period', 'this_week') == $val ? 'bg-brand-light border-brand-soft text-brand-primary' : 'bg-stone-50 border-transparent text-stone-500 hover:bg-stone-100 hover:text-stone-900' }} transition-all uppercase tracking-tight">
                                                                {{ $label }}
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" @click="showCustom = !showCustom"
                                                        class="w-full px-5 py-3.5 text-[11px] font-extrabold rounded-lg border-2 {{ request('period') == 'custom' ? 'bg-brand-light border-brand-soft text-brand-primary' : 'bg-stone-900 border-stone-stone-900 text-white shadow-lg' }} transition-all flex items-center justify-center gap-3 uppercase tracking-widest">
                                                        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                                                        Set Custom Range
                                                    </button>
                                                </div>

                                                <!-- Column 2: Deep Filters -->
                                                <div class="flex-1 space-y-10">
                                                    <div class="space-y-6">
                                                        <h3 class="text-[10px] font-extrabold text-stone-400 uppercase tracking-widest flex items-center gap-3">
                                                            <i data-lucide="activity" class="w-4 h-4 text-brand-primary"></i> Refine by Status
                                                        </h3>
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach(['' => 'All Bookings', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'occupied' => 'Occupied', 'billed' => 'Billed', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'hold' => 'Hold'] as $val => $label)
                                                                <button type="button" 
                                                                    @click="document.getElementById('statusInput').value = '{{ $val }}'; document.getElementById('bookingFilterForm').submit()"
                                                                    class="px-5 py-2.5 text-[10px] font-extrabold rounded-lg border-2 {{ request('status') == $val ? 'bg-brand-primary border-brand-primary text-white' : 'bg-white border-stone-100 text-stone-500 hover:border-stone-300 hover:text-stone-900' }} transition-all uppercase tracking-widest">
                                                                    {{ $label }}
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                                        <div class="space-y-4">
                                                            <h3 class="text-[10px] font-extrabold text-stone-400 uppercase tracking-widest flex items-center gap-3">
                                                                <i data-lucide="layers" class="w-4 h-4 text-brand-primary"></i> Guest Category
                                                            </h3>
                                                            <select onchange="document.getElementById('categoryInput').value = this.value; document.getElementById('bookingFilterForm').submit()"
                                                                class="w-full px-5 py-3 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all text-stone-800 uppercase tracking-tight">
                                                                <option value="">Filter by Category</option>
                                                                @foreach(['REGULAR', 'PRIORITY', 'EVENT', 'BIG SPENDER', 'DRINKER', 'PARTY', 'DINNER', 'LUNCH', 'FAMILY', 'YOUNGSTER'] as $cat)
                                                                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="space-y-4">
                                                            <h3 class="text-[10px] font-extrabold text-stone-400 uppercase tracking-widest flex items-center gap-3">
                                                                <i data-lucide="arrow-up-down" class="w-4 h-4 text-brand-primary"></i> Sort Order
                                                            </h3>
                                                            <button type="button" 
                                                                onclick="document.getElementById('sortInput').value = (document.getElementById('sortInput').value == 'asc' ? 'desc' : 'asc'); document.getElementById('bookingFilterForm').submit()"
                                                                class="w-full px-5 py-3 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold flex items-center justify-between gap-3 hover:bg-stone-100 transition-all text-stone-800 uppercase tracking-tight">
                                                                <span>{{ request('sort') == 'asc' ? 'Oldest First' : 'Newest First' }}</span>
                                                                <i data-lucide="chevron-right" class="w-4 h-4 text-stone-300"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                <!-- Custom Range Panel -->
                                                <div x-show="showCustom" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" class="p-8 bg-stone-50 rounded-lg border border-stone-200 space-y-6">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                        <div class="space-y-2">
                                                            <label class="text-[10px] font-extrabold text-stone-400 uppercase tracking-widest block text-center">Start Time & Date</label>
                                                            <input type="text" name="start_date" value="{{ request('start_date') }}" placeholder="Click to select start"
                                                                class="datetime-picker w-full px-5 py-4 bg-white border border-stone-200 rounded-md text-xs font-bold text-center outline-none focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all">
                                                        </div>
                                                        <div class="space-y-2">
                                                            <label class="text-[10px] font-extrabold text-stone-400 uppercase tracking-widest block text-center">End Time & Date</label>
                                                            <input type="text" name="end_date" value="{{ request('end_date') }}" placeholder="Click to select end"
                                                                class="datetime-picker w-full px-5 py-4 bg-white border border-stone-200 rounded-lg text-xs font-bold text-center outline-none focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all">
                                                        </div>
                                                    </div>
                                                    <button type="button" 
                                                        @click="document.getElementById('periodInput').value = 'custom'; document.getElementById('bookingFilterForm').submit()"
                                                        class="w-full py-4 bg-brand-primary text-white rounded-lg text-xs font-extrabold uppercase tracking-widest hover:opacity-90 transition-all shadow-lg">
                                                        Apply Custom Range
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer Section -->
                                        <div class="mt-16 pt-10 border-t-2 border-slate-50 flex flex-col md:flex-row items-center justify-between gap-8">
                                            <div class="flex items-center gap-5">
                                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                                    <i data-lucide="filter" class="w-5 h-5 text-slate-400"></i>
                                                </div>
                                                <div class="flex flex-wrap gap-2">
                                                    @if(request('period') && request('period') != 'this_week')
                                                        <span class="px-4 py-2 bg-orange-50 text-[#e85a2f] text-[11px] font-black rounded-md border-2 border-orange-100 flex items-center gap-2">
                                                            {{ $periodLabel }}
                                                        </span>
                                                    @endif
                                                    @if(request('status'))
                                                        <span class="px-4 py-2 bg-blue-50 text-blue-600 text-[11px] font-black rounded-md border-2 border-blue-100 flex items-center gap-2">
                                                            {{ ucfirst(request('status')) }}
                                                        </span>
                                                    @endif
                                                    @if(request('category'))
                                                        <span class="px-4 py-2 bg-emerald-50 text-emerald-600 text-[11px] font-black rounded-md border-2 border-emerald-100 flex items-center gap-2">
                                                            {{ request('category') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center gap-8">
                                                <a href="{{ route('dashboard') }}" class="text-[12px] font-black text-slate-400 uppercase tracking-widest hover:text-red-500 transition-colors">
                                                    Reset All Filters
                                                </a>
                                                <button type="button" @click="open = false" 
                                                    class="px-12 py-5 bg-slate-900 text-white rounded-md text-[12px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all shadow-2xl shadow-slate-200">
                                                    Return to Dashboard
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" onclick="exportToExcel()"
                            class="px-5 py-3 bg-white text-emerald-700 rounded-lg text-[11px] font-bold flex items-center gap-2.5 border border-emerald-200 shadow-sm hover:bg-emerald-50 hover:border-emerald-300 transition-all uppercase tracking-wider">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            <span>Export</span>
                        </button>
                    </div>
                </div>
            </form>


            <div class="overflow-x-auto relative">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-stone-50/50">
                                <th class="py-6 px-10 text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] border-b border-stone-100">Guest</th>
                                <th class="py-6 px-4 text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] border-b border-stone-100">Profile</th>
                                <th class="py-6 px-4 text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] border-b border-stone-100">Category</th>
                                <th class="py-6 px-4 text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] border-b border-stone-100">Table</th>
                                <th class="py-6 px-4 text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] border-b border-stone-100">Pax</th>
                                <th class="py-6 px-4 text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] border-b border-stone-100">Schedule</th>
                                <th class="py-6 px-4 text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] border-b border-stone-100 text-right">Revenue</th>
                                <th class="py-6 px-10 text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] border-b border-stone-100 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-50">
                            @forelse($recentBookings as $booking)
                            <tr class="group hover:bg-stone-50/50 transition-all duration-300">
                                <td class="py-8 px-10">
                                    <div class="flex items-center gap-5">
                                        <div class="w-12 h-12 rounded-lg bg-white shadow-sm border border-stone-100 flex items-center justify-center text-stone-400 group-hover:scale-110 transition-transform duration-500 overflow-hidden relative">
                                            @php
                                                $initial = substr($booking->customer->name ?? '?', 0, 1);
                                                $colors = ['bg-rose-50 text-rose-500', 'bg-blue-50 text-blue-500', 'bg-amber-50 text-amber-500', 'bg-emerald-50 text-emerald-500', 'bg-indigo-50 text-indigo-500'];
                                                $colorClass = $colors[ord($initial) % count($colors)];
                                            @endphp
                                            <div class="absolute inset-0 {{ $colorClass }} opacity-20"></div>
                                            <span class="text-base font-black relative z-10 {{ explode(' ', $colorClass)[1] }}">{{ strtoupper($initial) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-stone-900 group-hover:text-brand-primary transition-colors">{{ $booking->customer->name ?? 'Unknown Guest' }}</p>
                                            <p class="text-[10px] font-bold text-stone-400 mt-1 uppercase tracking-widest">{{ $booking->customer->phone ?? 'NO CONTACT' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-8 px-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[10px] font-black text-stone-500">{{ $booking->customer->age ?? 'N/A' }} YRS</span>
                                        <span class="text-[9px] font-bold text-stone-400 uppercase tracking-tighter">{{ $booking->customer->gender ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="py-8 px-4">
                                    @php
                                        $cat = strtoupper($booking->category ?? 'REGULER');
                                        $catDisplay = match ($cat) {
                                            'REGULER' => 'REGULAR',
                                            'PRIORITAS' => 'PRIORITY',
                                            'BIG_SPENDER' => 'BIG SPENDER',
                                            default => str_replace('_', ' ', $cat)
                                        };
                                        $catData = $categoryMap[$cat] ?? $categoryMap[$catDisplay] ?? null;
                                        $catIcon = $catData?->icon ?? 'tag';
                                        $catColor = match($cat) {
                                            'PRIORITY', 'PRIORITAS' => 'bg-amber-50 text-amber-600 border-amber-100',
                                            'BIG SPENDER', 'BIG_SPENDER' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'EVENT' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                            default => 'bg-stone-50 text-stone-400 border-stone-100'
                                        };
                                    @endphp
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 {{ $catColor }} rounded-md border text-[9px] font-black uppercase tracking-widest">
                                        <i data-lucide="{{ $catIcon }}" class="w-3 h-3"></i>
                                        {{ $catDisplay }}
                                    </div>
                                </td>
                                <td class="py-8 px-4">
                                    <span class="px-3 py-1.5 bg-stone-900 text-white rounded-md text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-stone-200">
                                        {{ $booking->tableModel->code ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="py-8 px-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="users" class="w-3.5 h-3.5 text-stone-300"></i>
                                        <span class="text-sm font-black text-stone-900">{{ $booking->pax }}</span>
                                    </div>
                                </td>
                                <td class="py-8 px-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 text-stone-500 mb-1">
                                            <i data-lucide="calendar-days" class="w-3.5 h-3.5 text-brand-primary opacity-50"></i>
                                            <span class="text-[10px] font-black uppercase tracking-widest">{{ $booking->start_time ? $booking->start_time->format('d M Y') : 'N/A' }}</span>
                                        </div>
                                        <div class="space-y-1 pl-1">
                                            <div class="flex items-center gap-2">
                                                <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></div>
                                                <span class="text-[10px] font-black text-stone-900 uppercase leading-none">{{ $booking->start_time ? $booking->start_time->format('H:i') : '--:--' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-1.5 h-1.5 bg-rose-400 rounded-full"></div>
                                                <span class="text-[10px] font-black text-stone-400 uppercase leading-none">{{ $booking->end_time ? $booking->end_time->format('H:i') : '--:--' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-8 px-4 text-right">
                                    <span class="text-sm font-black text-stone-900 tabular-nums">
                                        {{ number_format($booking->billed_price, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-8 px-10 text-right">
                                    @php
                                        $status = strtolower($booking->status);
                                        $sCfg = match($status) {
                                            'confirmed', 'completed', 'billed', 'ok', 'finished', 'done', 'paid' => ['bg' => 'bg-emerald-500', 'text' => 'CONFIRMED'],
                                            'booked', 'pending', 'arrived', 'occupied', 'come' => ['bg' => 'bg-amber-500', 'text' => strtoupper($status)],
                                            'hold' => ['bg' => 'bg-blue-500', 'text' => 'HOLD'],
                                            'cancelled' => ['bg' => 'bg-rose-500', 'text' => 'CANCELLED'],
                                            default => ['bg' => 'bg-stone-300', 'text' => strtoupper($status)]
                                        };
                                    @endphp
                                    <div class="inline-flex items-center gap-2">
                                        <span class="text-[9px] font-black text-stone-500 uppercase tracking-[0.2em]">{{ $sCfg['text'] }}</span>
                                        <div class="w-2 h-2 {{ $sCfg['bg'] }} rounded-full ring-4 {{ str_replace('500', '50', $sCfg['bg']) }}"></div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-24 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-20 h-20 rounded-md bg-stone-50 flex items-center justify-center text-stone-200">
                                            <i data-lucide="database" class="w-10 h-10"></i>
                                        </div>
                                        <p class="text-xs font-black text-stone-400 uppercase tracking-widest">No active bookings found</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Enhanced Footer Stats --}}
                <div class="mx-10 my-10 p-10 bg-stone-50/50 rounded-md border border-stone-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
                        <div class="flex items-center gap-6 group">
                            <div class="w-14 h-14 bg-white rounded-lg flex items-center justify-center text-stone-400 group-hover:text-brand-primary group-hover:scale-110 transition-all duration-300 shadow-sm border border-stone-100">
                                <i data-lucide="hash" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-stone-400 font-black uppercase tracking-widest mb-1">Total Entries</p>
                                <p class="text-xl font-black text-stone-900 tracking-tight">{{ number_format($listTotals['count']) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 group">
                            <div class="w-14 h-14 bg-white rounded-lg flex items-center justify-center text-stone-400 group-hover:text-brand-primary group-hover:scale-110 transition-all duration-300 shadow-sm border border-stone-100">
                                <i data-lucide="user-check" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-stone-400 font-black uppercase tracking-widest mb-1">Total Pax</p>
                                <p class="text-xl font-black text-stone-900 tracking-tight">{{ number_format($listTotals['guests']) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 group">
                            <div class="w-14 h-14 bg-brand-primary rounded-lg flex items-center justify-center text-white group-hover:scale-110 transition-all duration-300 shadow-xl shadow-brand-primary/20">
                                <i data-lucide="circle-dollar-sign" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-brand-primary font-black uppercase tracking-widest mb-1">Estimated Revenue</p>
                                <p class="text-xl font-black text-stone-900 tracking-tight">Rp {{ number_format($listTotals['amount'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-10 pb-12 mt-6">
                    {{ $recentBookings->appends(request()->query())->links('components.pagination') }}
                </div>
        </div>
        </div>

        <div x-show="showEventModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak></div>

    <!-- Add Booking Modal -->
    <div x-show="showBookingModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showBookingModal" @click="showBookingModal = false; resetForm()"
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-stone-900/60 backdrop-blur-md transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

            <div x-show="showBookingModal"
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white/90 backdrop-blur-2xl rounded-xl p-1 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-white/20">
                
                <div class="bg-white rounded-lg p-10" x-data="{ 
                     customerSearch: '', 
                     searchResults: [], 
                     selectedCustomer: null,
                     allCustomers: [],
                    allTables: {{ Js::from($allTables) }},
                    selectedTableId: '',
                    get selectedTable() { return this.allTables.find(t => t.id == this.selectedTableId) },
                    
                    filterCustomers() {
                        if (this.customerSearch.length < 2) {
                            this.searchResults = [];
                            return;
                        }
                        const q = this.customerSearch.toLowerCase();
                        this.searchResults = this.allCustomers.filter(c => 
                            (c.name && c.name.toLowerCase().includes(q)) || 
                            (c.phone && c.phone.includes(q))
                        ).slice(0, 8);
                    },

                    selectCustomer(c) {
                        this.selectedCustomer = c;
                        this.customerSearch = c.name;
                        this.searchResults = [];
                        if (this.$refs.phoneInput) this.$refs.phoneInput.value = c.phone || '';
                        if (this.$refs.ageInput) this.$refs.ageInput.value = c.age || '';
                        if (this.$refs.genderSelect) this.$refs.genderSelect.value = c.gender || '';
                        if (this.$refs.categorySelect && c.category) {
                            this.$refs.categorySelect.value = c.category.toUpperCase();
                        }
                    },

                    clearCustomer() {
                        if (this.selectedCustomer && this.customerSearch !== this.selectedCustomer.name) {
                            this.selectedCustomer = null;
                        }
                    },

                    resetForm() {
                        this.customerSearch = '';
                        this.searchResults = [];
                        this.selectedCustomer = null;
                        if (this.$refs.bookingForm) this.$refs.bookingForm.reset();
                    }
                }" x-init="allCustomers = {{ Js::from($customers) }}">
                    
                    <div class="flex justify-between items-start mb-12">
                        <div>
                            <h3 class="text-2xl font-black text-stone-900 tracking-tight uppercase flex items-center gap-3">
                                <span class="w-10 h-10 rounded-lg bg-stone-900 text-white flex items-center justify-center">
                                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                                </span>
                                New Booking
                            </h3>
                            <p class="text-stone-400 font-bold text-[10px] mt-2 uppercase tracking-[0.2em] pl-1 w-full border-t border-stone-50 pt-2">Register guest reservation</p>
                        </div>
                        <button @click="showBookingModal = false; resetForm()"
                            class="w-12 h-12 flex items-center justify-center bg-stone-50 text-stone-400 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-all">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('bookings.store') }}" method="POST" class="space-y-8" x-ref="bookingForm">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Guest Name Search -->
                            <div class="md:col-span-2 relative">
                                <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Guest Identification</label>
                                <div class="relative group flex items-center">
                                    <div class="absolute left-5 inset-y-0 flex items-center pointer-events-none">
                                        <i data-lucide="search" class="w-4 h-4 text-stone-300 group-focus-within:text-brand-primary transition-colors"></i>
                                    </div>
                                    <input type="text" name="customer_name" required placeholder="SEARCH NAME OR PHONE..."
                                        x-model="customerSearch"
                                        @input.debounce.200ms="filterCustomers(); clearCustomer()"
                                        autocomplete="off"
                                        class="w-full pl-14 pr-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest">
                                    
                                    <div x-show="searchResults.length > 0" 
                                        class="absolute z-[100] left-0 right-0 mt-3 bg-white/90 backdrop-blur-xl rounded-lg shadow-2xl border border-stone-100 overflow-hidden p-2"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                        x-cloak>
                                        <div class="max-h-[280px] overflow-y-auto space-y-1">
                                            <template x-for="c in searchResults" :key="c.id">
                                                <div @click="selectCustomer(c)" 
                                                    class="px-5 py-4 hover:bg-stone-50 cursor-pointer flex items-center justify-between group rounded-md transition-all">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 rounded-md bg-brand-primary/10 text-brand-primary flex items-center justify-center font-black text-lg">
                                                            <span x-text="c.name.substring(0,1).toUpperCase()"></span>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-black text-stone-800" x-text="c.name"></p>
                                                            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest mt-0.5" x-text="c.phone || '-'"></p>
                                                        </div>
                                                    </div>
                                                    <span class="text-[9px] font-black uppercase tracking-[0.2em] px-3 py-1.5 rounded-lg bg-stone-100 text-stone-500 group-hover:bg-brand-primary group-hover:text-white transition-all" x-text="c.master_level?.name || 'REGULAR'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Placement -->
                            <div x-data="{ localArea: '' }" class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Zone Area</label>
                                    <select x-model="localArea"
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                        <option value="">ALL FLOORS</option>
                                        @foreach($floors as $floor)
                                        <option value="{{ $floor }}">{{ str_replace('_', ' ', strtoupper($floor)) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Table Selection</label>
                                    <select name="table_id" required x-model="selectedTableId"
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                        <option value="">SELECT TABLE</option>
                                        @foreach($allTables as $table)
                                        <template x-if="localArea === '' || localArea === '{{ $table->area_id }}'">
                                            <option value="{{ $table->id }}">{{ $table->code }}</option>
                                        </template>
                                        @endforeach
                                    </select>

                                    <!-- Table Meta -->
                                    <template x-if="selectedTable">
                                        <div class="mt-4 grid grid-cols-2 gap-3 animate-in fade-in slide-in-from-top-2">
                                            <div class="p-5 bg-brand-light/50 rounded-md border border-brand-soft/30">
                                                <p class="text-[8px] font-black text-brand-primary uppercase tracking-widest mb-1">Min Spend</p>
                                                <p class="text-[11px] font-black text-stone-900" x-text="'Rp ' + (selectedTable.min_spending || 0).toLocaleString('id-ID')"></p>
                                            </div>
                                            <div class="p-5 bg-stone-50 rounded-md border border-stone-100">
                                                <p class="text-[8px] font-black text-stone-400 uppercase tracking-widest mb-1">Capacity</p>
                                                <p class="text-[11px] font-black text-stone-900" x-text="(selectedTable.capacity || 0) + ' PAX'"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Category</label>
                                    <select name="customer_category" required x-ref="categorySelect"
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                        <option value="REGULAR">REGULAR</option>
                                        <option value="PRIORITY">PRIORITY</option>
                                        <option value="EVENT">EVENT</option>
                                        <option value="BIG SPENDER">BIG SPENDER</option>
                                        <option value="DRINKER">DRINKER</option>
                                        <option value="PARTY">PARTY</option>
                                        <option value="DINNER">DINNER</option>
                                        <option value="LUNCH">LUNCH</option>
                                        <option value="FAMILY">FAMILY</option>
                                        <option value="YOUNGSTER">YOUNGSTER</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Guest Count (PAX)</label>
                                    <input type="number" name="pax" required min="1" placeholder="0"
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest">
                                </div>
                            </div>

                            <!-- Customer Bio -->
                            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Contact Phone</label>
                                    <input type="text" name="phone" placeholder="ENTER PHONE NUMBER..." x-ref="phoneInput"
                                        class="w-full px-6 py-4 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Age</label>
                                        <select name="age" x-ref="ageInput"
                                            class="w-full px-5 py-4 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                            <option value="">N/A</option>
                                            <option value="<18"><18</option>
                                            <option value="18-24">18-24</option>
                                            <option value="25-34">25-34</option>
                                            <option value="35-44">35-44</option>
                                            <option value="45-59">45-59</option>
                                            <option value=">59">>59</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Gender</label>
                                        <select name="gender" x-ref="genderSelect"
                                            class="w-full px-5 py-4 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                            <option value="">N/A</option>
                                            <option value="MALE">MALE</option>
                                            <option value="FEMALE">FEMALE</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Timing -->
                            <div class="relative group">
                                <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Schedule Arrival</label>
                                <div class="relative">
                                    <i data-lucide="calendar" class="w-4 h-4 absolute left-5 top-1/2 -translate-y-1/2 text-stone-300 group-focus-within:text-brand-primary z-10 transition-colors"></i>
                                    <input type="text" name="start_time" required placeholder="SELECT ARRIVAL TIME"
                                        class="datetime-picker w-full pl-14 pr-5 py-4 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:bg-white focus:border-brand-primary/30 focus:ring-4 focus:ring-brand-primary/5 transition-all outline-none uppercase tracking-widest">
                                </div>
                            </div>

                            <div class="relative group">
                                <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Schedule Departure</label>
                                <div class="relative">
                                    <i data-lucide="clock" class="w-4 h-4 absolute left-5 top-1/2 -translate-y-1/2 text-stone-300 group-focus-within:text-brand-primary z-10 transition-colors"></i>
                                    <input type="text" name="end_time" required placeholder="SELECT DEPARTURE TIME"
                                        class="datetime-picker w-full pl-14 pr-5 py-4 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:bg-white focus:border-brand-primary/30 focus:ring-4 focus:ring-brand-primary/5 transition-all outline-none uppercase tracking-widest">
                                </div>
                            </div>
                        </div>

                        <!-- Tags Selection -->
                        @foreach($tags as $group => $groupTags)
                        <div class="pt-8 border-t border-stone-50">
                            <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-5 ml-1">Guest Interests ({{ $group }})</label>
                            <div class="flex flex-wrap gap-2.5">
                                @foreach($groupTags as $tag)
                                    <label class="relative cursor-pointer group">
                                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="hidden peer">
                                        <div class="px-5 py-2.5 rounded-md border border-stone-100 bg-white text-[10px] font-black text-stone-500 hover:bg-stone-50 peer-checked:bg-brand-primary peer-checked:text-white peer-checked:border-brand-primary transition-all uppercase tracking-widest">
                                            {{ $tag->name }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <div>
                            <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Reservation Notes</label>
                            <textarea name="notes" rows="3" placeholder="ATTACH SPECIAL REQUESTS OR REQUIREMENTS..."
                                class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest placeholder:text-stone-300 resize-none"></textarea>
                        </div>

                        <div class="pt-10">
                            <button type="submit"
                                class="w-full py-6 bg-stone-900 text-white rounded-md text-sm font-black uppercase tracking-[0.3em] shadow-2xl shadow-stone-200 hover:bg-black hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-4">
                                Confirm Reservation
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>

        <!-- Add Event Booking Modal -->
        <div x-show="showEventModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEventModal" @click="showEventModal = false; resetForm()"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-stone-900/60 backdrop-blur-md transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

                <div x-show="showEventModal"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white/95 backdrop-blur-2xl rounded-[2.5rem] p-1 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-white/20">
                    
                    <div class="bg-white rounded-[2.3rem] p-10" x-data="{ 
                        customerSearch: '', 
                        searchResults: [], 
                        selectedCustomer: null,
                        allCustomers: [],
                        activeBookings: [],
                        lockedTableIds: [],
                        eventStartTime: '',
                        eventEndTime: '',
                        
                        filterCustomers() {
                            if (this.customerSearch.length < 2) {
                                this.searchResults = [];
                                return;
                            }
                            const q = this.customerSearch.toLowerCase();
                            this.searchResults = this.allCustomers.filter(c => 
                                (c.name && c.name.toLowerCase().includes(q)) || 
                                (c.phone && c.phone.includes(q))
                            ).slice(0, 8);
                        },

                        selectCustomer(c) {
                            this.selectedCustomer = c;
                            this.customerSearch = c.name;
                            this.searchResults = [];
                            if ($refs.phoneInput) $refs.phoneInput.value = c.phone || '';
                            if ($refs.ageInput) $refs.ageInput.value = c.age || '';
                        },

                        clearCustomer() {
                            if (this.selectedCustomer && this.customerSearch !== this.selectedCustomer.name) {
                                this.selectedCustomer = null;
                            }
                        },

                        checkAvailability() {
                            if (!this.eventStartTime || !this.eventEndTime) {
                                this.lockedTableIds = [];
                                return;
                            }
                            try {
                                const start = new Date(this.eventStartTime).getTime();
                                const end = new Date(this.eventEndTime).getTime();
                                if (isNaN(start) || isNaN(end)) {
                                    this.lockedTableIds = [];
                                    return;
                                }
                                const locked = new Set();
                                this.activeBookings.forEach(b => {
                                    const bStart = new Date(b.start_time).getTime();
                                    const bEnd = new Date(b.end_time).getTime();
                                    if (!isNaN(bStart) && !isNaN(bEnd) && bStart < end && bEnd > start) {
                                        locked.add(b.table_id);
                                    }
                                });
                                this.lockedTableIds = Array.from(locked);
                            } catch (e) {
                                console.error('Error checking availability:', e);
                                this.lockedTableIds = [];
                            }
                        },

                        resetForm() {
                            this.customerSearch = '';
                            this.searchResults = [];
                            this.selectedCustomer = null;
                            this.eventStartTime = '';
                            this.eventEndTime = '';
                            this.lockedTableIds = [];
                            if (this.$refs.eventForm) {
                                this.$refs.eventForm.reset();
                                const fpStart = this.$refs.eventForm.querySelector('input[name=\'start_time\']');
                                const fpEnd = this.$refs.eventForm.querySelector('input[name=\'end_time\']');
                                if (fpStart && fpStart._flatpickr) fpStart._flatpickr.clear();
                                if (fpEnd && fpEnd._flatpickr) fpEnd._flatpickr.clear();
                            }
                        }
                    }" x-init="allCustomers = {{ Js::from($customers) }}; activeBookings = {{ Js::from($allActiveBookings) }}">
                        
                        <div class="flex justify-between items-start mb-12">
                            <div>
                                <h3 class="text-2xl font-black text-stone-900 tracking-tight uppercase flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-lg bg-stone-900 text-white flex items-center justify-center">
                                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                                    </span>
                                    Host Event
                                </h3>
                                <p class="text-stone-400 font-bold text-[10px] mt-2 uppercase tracking-[0.2em] pl-1 w-full border-t border-stone-50 pt-2">Multiple table reservation</p>
                            </div>
                            <button @click="showEventModal = false; resetForm()"
                                class="w-12 h-12 flex items-center justify-center bg-stone-50 text-stone-400 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-all">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form action="{{ route('bookings.event_store') }}" method="POST" class="space-y-8" x-ref="eventForm">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Guest Search -->
                                <div class="md:col-span-2 relative">
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Event Organizer</label>
                                    <div class="relative group flex items-center">
                                        <div class="absolute left-5 inset-y-0 flex items-center pointer-events-none">
                                            <i data-lucide="search" class="w-4 h-4 text-stone-300 group-focus-within:text-brand-primary transition-colors"></i>
                                        </div>
                                        <input type="text" name="customer_name" required placeholder="SEARCH NAME OR PHONE..."
                                            x-model="customerSearch"
                                            @input.debounce.200ms="filterCustomers(); clearCustomer()"
                                            autocomplete="off"
                                            class="w-full pl-14 pr-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest">
                                        
                                        <div x-show="searchResults.length > 0" 
                                            class="absolute z-[100] left-0 right-0 mt-3 bg-white/90 backdrop-blur-xl rounded-lg shadow-2xl border border-stone-100 overflow-hidden p-2"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                            x-cloak>
                                            <div class="max-h-[280px] overflow-y-auto space-y-1">
                                                <template x-for="c in searchResults" :key="c.id">
                                                    <div @click="selectCustomer(c)" 
                                                        class="px-5 py-4 hover:bg-stone-50 cursor-pointer flex items-center justify-between group rounded-md transition-all border-stone-50">
                                                        <div class="flex items-center gap-4">
                                                            <div class="w-10 h-10 rounded-md bg-brand-primary/10 text-brand-primary flex items-center justify-center font-black text-lg">
                                                                <span x-text="c.name.substring(0,1).toUpperCase()"></span>
                                                            </div>
                                                            <div>
                                                                <p class="text-sm font-black text-stone-800" x-text="c.name"></p>
                                                                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest mt-0.5" x-text="c.phone || '-'"></p>
                                                            </div>
                                                        </div>
                                                        <span class="text-[9px] font-black uppercase tracking-[0.2em] px-3 py-1.5 rounded-sm bg-stone-100 text-stone-500 group-hover:bg-brand-primary group-hover:text-white transition-all" x-text="c.master_level?.name || 'REGULAR'"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Multi Table Selection -->
                                <div class="md:col-span-2" x-data="{ eventArea: '' }">
                                    <div class="flex justify-between items-center mb-5 ml-1">
                                        <label class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Table Assemblage</label>
                                        <span class="text-[9px] font-black text-brand-primary uppercase tracking-widest">Multi-Select Enabled</span>
                                    </div>

                                    <select x-model="eventArea"
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-2xl text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer mb-6">
                                        <option value="">ALL FLOORS</option>
                                        @foreach($floors as $floor)
                                        <option value="{{ $floor }}">{{ str_replace('_', ' ', strtoupper($floor)) }}</option>
                                        @endforeach
                                    </select>

                                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3 max-h-64 overflow-y-auto p-6 bg-stone-50 rounded-lg border border-stone-100 shadow-inner">
                                        @foreach($allTables as $table)
                                        <label x-show="eventArea === '' || eventArea === '{{ $table->area_id }}'"
                                            class="relative flex items-center justify-center aspect-square rounded-md border-2 border-transparent transition-all group overflow-hidden"
                                            :class="lockedTableIds.includes({{ $table->id }}) ? 'bg-stone-200/50 cursor-not-allowed opacity-50' : 'bg-white shadow-sm cursor-pointer hover:border-brand-primary/20 has-[:checked]:bg-stone-900 has-[:checked]:text-white has-[:checked]:shadow-xl'">
                                            <input type="checkbox" name="table_ids[]" value="{{ $table->id }}" class="hidden peer" :disabled="lockedTableIds.includes({{ $table->id }})">
                                            <span class="text-[11px] font-black uppercase tracking-widest">{{ $table->code }}</span>
                                            <div class="absolute inset-0 bg-brand-primary/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Event Details -->
                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Capacity Required</label>
                                    <input type="number" name="pax" required min="1" placeholder="0"
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Event Category</label>
                                    <select name="customer_category" required
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                        <option value="EVENT">OFFICIAL EVENT</option>
                                        <option value="PARTY">PRIVATE PARTY</option>
                                        <option value="DINNER">GALA DINNER</option>
                                        <option value="BIG SPENDER">VVIP LOUNGE</option>
                                    </select>
                                </div>

                                <!-- Biometrics -->
                                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Contact Terminal</label>
                                        <input type="text" name="phone" placeholder="ENTER PHONE NUMBER..." x-ref="phoneInput"
                                            class="w-full px-6 py-4 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Age Segment</label>
                                            <select name="age" x-ref="ageInput"
                                                class="w-full px-5 py-4 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                                <option value="">N/A</option>
                                                <option value="<18"><18</option>
                                                <option value="18-24">18-24</option>
                                                <option value="25-34">25-34</option>
                                                <option value="35-44">35-44</option>
                                                <option value="45-59">45-59</option>
                                                <option value=">59">>59</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Host Gender</label>
                                            <select name="gender"
                                                class="w-full px-5 py-4 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                                <option value="">N/A</option>
                                                <option value="MALE">MALE</option>
                                                <option value="FEMALE">FEMALE</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timing -->
                                <div class="relative group">
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Event Initiation</label>
                                    <div class="relative">
                                        <i data-lucide="play" class="w-4 h-4 absolute left-5 top-1/2 -translate-y-1/2 text-stone-300 group-focus-within:text-brand-primary z-10 transition-colors"></i>
                                        <input type="text" name="start_time" required placeholder="START TIME"
                                            x-model="eventStartTime" @input="checkAvailability()" @change="checkAvailability()"
                                            class="datetime-picker w-full pl-14 pr-5 py-4 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:bg-white focus:border-brand-primary/30 focus:ring-4 focus:ring-brand-primary/5 transition-all outline-none uppercase tracking-widest">
                                    </div>
                                </div>

                                <div class="relative group">
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Event Conclusion</label>
                                    <div class="relative">
                                        <i data-lucide="square" class="w-4 h-4 absolute left-5 top-1/2 -translate-y-1/2 text-stone-300 group-focus-within:text-brand-primary z-10 transition-colors"></i>
                                        <input type="text" name="end_time" required placeholder="END TIME"
                                            x-model="eventEndTime" @input="checkAvailability()" @change="checkAvailability()"
                                            class="datetime-picker w-full pl-14 pr-5 py-4 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:bg-white focus:border-brand-primary/30 focus:ring-4 focus:ring-brand-primary/5 transition-all outline-none uppercase tracking-widest">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Event Briefing</label>
                                <textarea name="notes" rows="3" placeholder="ATTACH EVENT DETAILS, TECHNICAL RIDERS, OR REQUIREMENTS..."
                                    class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest placeholder:text-stone-300 resize-none"></textarea>
                            </div>

                            <div class="pt-10">
                                <button type="submit"
                                    class="w-full py-6 bg-stone-900 text-white rounded-md text-sm font-black uppercase tracking-[0.3em] shadow-2xl shadow-stone-200 hover:bg-black hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-4">
                                    Deploy Event
                                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Info Modal -->
        <div x-show="showInfoModal" 
             class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showInfoModal" @click="showInfoModal = false; selectedBooking = null"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-stone-900/60 backdrop-blur-md transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

                <div x-show="showInfoModal"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white/95 backdrop-blur-2xl rounded-lg p-1 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-white/20">
                    
                    <div class="bg-white rounded-md p-10">
                        <div x-show="!selectedBooking" class="py-20 flex flex-col items-center justify-center space-y-6">
                            <div class="w-12 h-12 border-4 border-stone-100 border-t-stone-900 rounded-full animate-spin"></div>
                            <p class="text-[10px] font-black text-stone-400 uppercase tracking-[0.3em]">Gathering Intel...</p>
                        </div>

                        <template x-if="selectedBooking">
                            <div class="space-y-10">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-2xl font-black text-stone-900 tracking-tight uppercase" x-text="'Table ' + selectedBooking.table_model?.code"></h3>
                                        <p class="text-stone-400 font-bold text-[10px] mt-2 uppercase tracking-[0.2em] pl-1 border-stone-50 pt-1">Reservation Dossier</p>
                                    </div>
                                    <button @click="showInfoModal = false; selectedBooking = null"
                                        class="w-12 h-12 flex items-center justify-center bg-stone-50 text-stone-400 hover:text-stone-900 rounded-md transition-all">
                                        <i data-lucide="x" class="w-5 h-5"></i>
                                    </button>
                                </div>

                                <!-- Mini Card -->
                                <div class="p-8 bg-stone-50 rounded-lg border border-stone-100 relative overflow-hidden group">
                                    <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                                        <i data-lucide="user" class="w-24 h-24 text-stone-900"></i>
                                    </div>
                                    
                                    <div class="flex items-center gap-6 mb-8">
                                        <div class="w-16 h-16 rounded-md bg-stone-900 flex items-center justify-center text-white font-black text-2xl shadow-xl shadow-stone-200">
                                            <span x-text="selectedBooking.customer?.name?.substring(0,1).toUpperCase() || '?'"></span>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-3">
                                                <h4 class="text-xl font-black text-stone-900 uppercase tracking-tight" x-text="selectedBooking.customer?.name"></h4>
                                                <span class="px-3 py-1.5 bg-brand-primary text-white rounded-sm text-[9px] font-black uppercase tracking-widest shadow-lg shadow-brand-primary/20"
                                                    x-text="selectedBooking.customer?.master_level?.name || 'REGULAR'"></span>
                                            </div>
                                            <p class="text-xs font-bold text-stone-400 mt-1 uppercase tracking-widest" x-text="selectedBooking.customer?.phone || 'ANONYMOUS TERMINAL'"></p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="p-5 bg-white rounded-md border border-stone-100/50 shadow-sm">
                                            <label class="block text-[8px] font-black text-stone-300 uppercase tracking-widest mb-1.5">Check In</label>
                                            <p class="text-[11px] font-black text-stone-900" x-text="selectedBooking.start_time ? new Date(selectedBooking.start_time).toLocaleString('id-ID', {day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'}) : '-'"></p>
                                        </div>
                                        <div class="p-5 bg-white rounded-md border border-stone-100/50 shadow-sm">
                                            <label class="block text-[8px] font-black text-stone-300 uppercase tracking-widest mb-1.5">Check Out</label>
                                            <p class="text-[11px] font-black text-stone-900" x-text="selectedBooking.end_time ? new Date(selectedBooking.end_time).toLocaleString('id-ID', {day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'}) : '-'"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-4">
                                    <div class="p-6 bg-stone-50 rounded-md border border-stone-100 text-center">
                                        <p class="text-[8px] font-black text-stone-400 uppercase tracking-widest mb-1">Status</p>
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-2 h-2 rounded-full" :class="{
                                                'bg-amber-500 shadow-[0_0_12px_rgba(245,158,11,0.5)]': selectedBooking.status === 'confirmed',
                                                'bg-stone-300': selectedBooking.status === 'pending',
                                                'bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.5)]': ['occupied', 'arrived'].includes(selectedBooking.status),
                                                'bg-red-500 shadow-[0_0_12px_rgba(239,68,68,0.5)]': selectedBooking.status === 'cancelled'
                                            }"></div>
                                            <span class="text-[10px] font-black text-stone-900 uppercase tracking-widest" x-text="selectedBooking.status"></span>
                                        </div>
                                    </div>
                                    <div class="p-6 bg-stone-50 rounded-md border border-stone-100 text-center">
                                        <p class="text-[8px] font-black text-stone-400 uppercase tracking-widest mb-1">Capacity</p>
                                        <p class="text-[10px] font-black text-stone-900 uppercase tracking-widest" x-text="selectedBooking.pax + ' PAX'"></p>
                                    </div>
                                    <div class="p-6 bg-stone-50 rounded-md border border-stone-100 text-center">
                                        <p class="text-[8px] font-black text-stone-400 uppercase tracking-widest mb-1">Category</p>
                                        <p class="text-[10px] font-black text-stone-900 uppercase tracking-widest" x-text="selectedBooking.customer?.category || 'REGULAR'"></p>
                                    </div>
                                </div>

                                <div class="flex gap-4 pt-10">
                                    <button x-show="selectedBooking.status !== 'hold'" @click="showInfoModal = false; showEditBookingModal = true; editBookingData = JSON.parse(JSON.stringify(selectedBooking))"
                                            class="flex-1 py-5 bg-stone-900 text-white rounded-md text-[11px] font-black uppercase tracking-[0.25em] shadow-xl shadow-stone-200 hover:bg-black hover:scale-[1.02] active:scale-95 transition-all outline-none">
                                        Update Details
                                    </button>
                                    <button @click="showInfoModal = false; selectedBooking = null"
                                            class="px-8 py-5 bg-stone-100 text-stone-500 rounded-md text-[11px] font-black uppercase tracking-[0.25em] hover:bg-stone-200 transition-all outline-none">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Booking Modal -->
        <div x-show="showEditBookingModal" class="fixed inset-0 z-[110] overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEditBookingModal" @click="showEditBookingModal = false"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-stone-900/60 backdrop-blur-md transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

                <template x-if="showEditBookingModal">
                    <div class="inline-block align-bottom bg-white/95 backdrop-blur-2xl rounded-lg p-1 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-white/20">
                        <form :action="'/bookings/' + editBookingData.id" method="POST" class="bg-white rounded-md p-10">
                            @csrf
                            @method('PUT')
                            
                            <div class="flex justify-between items-start mb-12">
                                <div>
                                    <h3 class="text-2xl font-black text-stone-900 tracking-tight uppercase flex items-center gap-3">
                                        <span class="w-10 h-10 rounded-md bg-stone-900 text-white flex items-center justify-center">
                                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                                        </span>
                                        Recalibrate
                                    </h3>
                                    <p class="text-stone-400 font-bold text-[10px] mt-2 uppercase tracking-[0.2em] pl-1 w-full border-t border-stone-50 pt-2">Modify reservation parameters</p>
                                </div>
                                <button type="button" @click="showEditBookingModal = false"
                                    class="w-12 h-12 flex items-center justify-center bg-stone-50 text-stone-400 hover:text-stone-900 hover:bg-stone-100 rounded-md transition-all">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Guest Identity</label>
                                    <input type="text" name="customer_name" x-model="editBookingData.customer.name" required 
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Classification</label>
                                    <select name="customer_category" x-model="editBookingData.customer.category" required 
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                        @foreach(['REGULAR', 'PRIORITY', 'EVENT', 'BIG SPENDER', 'DRINKER', 'PARTY', 'DINNER', 'LUNCH', 'FAMILY', 'YOUNGSTER'] as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Operational Status</label>
                                    <select name="status" x-model="editBookingData.status" required 
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                        @foreach(['PENDING', 'CONFIRMED', 'HOLD', 'ARRIVED', 'OCCUPIED', 'BILLED', 'COMPLETED', 'CANCELLED'] as $st)
                                            <option value="{{ $st }}">{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Terminal Link</label>
                                    <input type="text" name="phone" x-model="editBookingData.customer.phone" 
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Age Tier</label>
                                        <select name="age" x-model="editBookingData.customer.age" 
                                            class="w-full px-5 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                            <option value="">N/A</option>
                                            <option value="<18"><18</option>
                                            <option value="18-24">18-24</option>
                                            <option value="25-34">25-34</option>
                                            <option value="35-44">35-44</option>
                                            <option value="45-59">45-59</option>
                                            <option value=">59">>59</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Gender</label>
                                        <select name="gender" x-model="editBookingData.customer.gender" 
                                            class="w-full px-5 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest appearance-none cursor-pointer">
                                            <option value="">NOT SET</option>
                                            <option value="MALE">MALE</option>
                                            <option value="FEMALE">FEMALE</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Entry Schedule</label>
                                    <input type="text" name="start_time" required x-model="editBookingData.start_time"
                                        class="datetime-picker w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Exit Schedule</label>
                                    <input type="text" name="end_time" required x-model="editBookingData.end_time"
                                        class="datetime-picker w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest">
                                </div>
                                
                                <div>
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Personnel Count</label>
                                    <input type="number" name="pax" x-model="editBookingData.pax" required 
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest">
                                </div>

                                <!-- Tags Section -->
                                @foreach($tags as $group => $groupTags)
                                <div class="md:col-span-2 border-t border-stone-50 pt-8">
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-6 ml-1">Interest Profiling ({{ $group }})</label>
                                    <div class="flex flex-wrap gap-2.5">
                                        @foreach($groupTags as $tag)
                                            <label class="relative cursor-pointer group">
                                                <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" 
                                                       :checked="(editBookingData.tags || []).some(t => t.id == {{ $tag->id }})"
                                                       class="hidden peer">
                                                <div class="px-5 py-2.5 rounded-md border border-stone-100 bg-white text-[10px] font-black text-stone-500 hover:bg-stone-50 peer-checked:bg-stone-900 peer-checked:text-white peer-checked:border-stone-900 transition-all uppercase tracking-widest">
                                                    {{ $tag->name }}
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-3 ml-1">Manual Intelligence (Notes)</label>
                                    <textarea name="notes" x-model="editBookingData.notes" rows="2" 
                                        class="w-full px-6 py-5 bg-stone-50 border border-stone-100 rounded-md text-xs font-black focus:ring-4 focus:ring-brand-primary/5 focus:bg-white focus:border-brand-primary/30 transition-all outline-none uppercase tracking-widest placeholder:text-stone-300 resize-none"></textarea>
                                </div>
                            </div>

                            <div class="pt-12">
                                <button type="submit" class="w-full py-6 bg-stone-900 text-white rounded-md text-sm font-black uppercase tracking-[0.3em] shadow-2xl shadow-stone-200 hover:bg-black hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-4">
                                    Commit Changes
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>
<!-- Hidden table for Export -->
<table id="export-table" style="display: none;">
    <thead>
        <tr>
            <th>name</th>
            <th>phone</th>
            <th>gender</th>
            <th>age_range</th>
            <th>total_spend</th>
            <th>total_visit</th>
            <th>date</th>
            <th>time_in</th>
            <th>time_out</th>
            <th>toal_pax</th>
            <th>pu_din</th>
            <th>pu_fam</th>
            <th>pu_lunch</th>
            <th>pu_party</th>
            <th>pu_celeb</th>
            <th>pu_comm</th>
            <th>pu_corp</th>
            <th>pr_reg</th>
            <th>pr_ayce</th>
            <th>pr_aycd</th>
            <th>pr_alc</th>
            <th>pr_buff</th>
            <th>pr_iftar</th>
            <th>time_wdd</th>
            <th>time_wdn</th>
            <th>time_wed</th>
            <th>time_wen</th>
            <th>status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($allFilteredBookings as $booking)
        @php
            $ownedTags = $booking->tags->pluck('name')->toArray();
            $hasTag = fn($tagName) => in_array($tagName, $ownedTags) ? 1 : 0;
            $customer = $booking->customer;
        @endphp
        <tr>
            <td>{{ $customer->name ?? 'N/A' }}</td>
            <td>{{ $customer->phone ?? 'N/A' }}</td>
            <td>{{ strtoupper($customer->gender ?: 'MALE') }}</td>
            <td>{{ $customer->age ?? '' }}</td>
            <td>{{ $booking->billed_price }}</td>
            <td>1</td>
            <td>{{ $booking->start_time ? $booking->start_time->format('Y-m-d') : '' }}</td>
            <td>{{ $booking->start_time ? $booking->start_time->format('H:i') : '' }}</td>
            <td>{{ $booking->end_time ? $booking->end_time->format('H:i') : '' }}</td>
            <td>{{ $booking->pax }}</td>
            <td>{{ $hasTag('Dining') }}</td>
            <td>{{ $hasTag('Family') }}</td>
            <td>{{ $hasTag('Lunch') }}</td>
            <td>{{ $hasTag('Party') }}</td>
            <td>{{ $hasTag('Celebration') }}</td>
            <td>{{ $hasTag('Community') }}</td>
            <td>{{ $hasTag('Corporate') }}</td>
            <td>{{ $hasTag('Regular F&B') }}</td>
            <td>{{ $hasTag('AYCE') }}</td>
            <td>{{ $hasTag('AYCD') }}</td>
            <td>{{ $hasTag('Alcohol') }}</td>
            <td>{{ $hasTag('Buffet') }}</td>
            <td>{{ $hasTag('Iftar Buffet') }}</td>
            <td>{{ $hasTag('Weekday Day') }}</td>
            <td>{{ $hasTag('Weekday Night') }}</td>
            <td>{{ $hasTag('Weekend Day') }}</td>
            <td>{{ $hasTag('Weekend Night') }}</td>
            <td>{{ $booking->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
@endsection

@section('scripts')

<script>
    function exportToExcel() {
        const table = document.getElementById("export-table");
        const wb = XLSX.utils.table_to_book(table, { sheet: "Bookings" });
        const date = new Date().toISOString().slice(0, 10);
        XLSX.writeFile(wb, `Bookings_Report_${date}.xlsx`);
    }

    document.addEventListener('alpine:init', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // Initialize Flatpickr for professional date/time selection
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".datetime-picker", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            minuteIncrement: 5,
            allowInput: true,
            disableMobile: "true", // Force custom UI on mobile too
            static: true, // Ensures it stays near the input in modals
            onChange: function(selectedDates, dateStr, instance) {
                // Ensure Alpine x-model gets the update by dispatching a native input event
                if (instance.element) {
                    instance.element.dispatchEvent(new Event('input', { bubbles: true }));
                    instance.element.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });
    });
</script>
@endsection