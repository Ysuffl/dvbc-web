@extends('layouts.admin')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700"
    x-data="{ showBookingModal: false, showEventModal: false, showInfoModal: false, showEditBookingModal: false, selectedBooking: null, editBookingData: { customer: {} } }">
    <!-- Top Section: Room Status & Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Room Status -->
        <div
            class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-8 border border-stone-200 hover:border-brand-soft transition-all">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <h2 class="text-xl font-extrabold text-stone-900 tracking-tight uppercase">Table Status</h2>
                <div class="flex items-center gap-3">
                    <form action="{{ route('dashboard') }}" method="GET" id="floorFilterForm">
                        <input type="hidden" name="floor" value="{{ request('floor', $selectedFloor) }}" id="floorInput">
                        <div class="relative group" x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                class="px-5 py-2.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold text-stone-800 flex items-center gap-3 shadow-sm hover:border-brand-primary hover:text-brand-primary transition-all uppercase tracking-wider">
                                <i data-lucide="layers" class="w-4 h-4 text-stone-400 group-hover:text-brand-primary transition-colors"></i>
                                <span>{{ $floors->contains(request('floor', $selectedFloor)) ? str_replace('_', ' ', strtoupper(request('floor', $selectedFloor))) : 'ALL AREA' }}</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-stone-400 group-hover:text-brand-primary transition-colors"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-50 z-50 overflow-hidden"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2">
                                <div class="p-2 space-y-1">
                                    <a href="javascript:void(0)" class="block px-4 py-2.5 text-sm font-bold rounded-xl transition-colors {{ !request('floor') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50' }}"
                                        onclick="document.getElementById('floorInput').value = ''; document.getElementById('floorFilterForm').submit()">ALL AREA</a>
                                    @foreach($floors as $f)
                                    <a href="javascript:void(0)" class="block px-4 py-2.5 text-sm font-bold rounded-xl transition-colors {{ request('floor', $selectedFloor) == $f ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50' }}"
                                        onclick="document.getElementById('floorInput').value = '{{ $f }}'; document.getElementById('floorFilterForm').submit()">{{ str_replace('_', ' ', strtoupper($f)) }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Rooms Grid: Standard Grid Layout -->
            <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-11 gap-4 mb-10">
                @foreach($tables as $table)
                @php
                $statusClass = 'bg-slate-50 border-slate-100 text-slate-500';
                $status = strtolower($table->status);

                if (in_array($status, ['occupied', 'arrived', 'come'])) {
                    $statusClass = 'bg-[#f43f5e] text-white border-[#f43f5e] shadow-[0_4px_12px_rgba(244,63,94,0.25)]';
                } elseif (in_array($status, ['confirmed', 'booked', 'pending', 'reserved'])) {
                    $statusClass = 'bg-[#fbbf24] text-white border-[#fbbf24] shadow-[0_4px_12px_rgba(251,191,36,0.25)]';
                } elseif (in_array($status, ['billed', 'completed', 'ok', 'finished', 'done', 'paid'])) {
                    $statusClass = 'bg-[#10b981] text-white border-[#10b981] shadow-[0_4px_12px_rgba(16,185,129,0.25)]';
                } elseif ($status === 'hold') {
                    $statusClass = 'bg-[#3b82f6] text-white border-[#3b82f6] shadow-[0_4px_12px_rgba(59,130,246,0.25)]';
                }


                    $currentBooking = $table->bookings->first();
                    $category = $currentBooking && $currentBooking->customer ? strtolower($currentBooking->category) : null;
                    if (!$currentBooking && $status === 'hold' && $table->holdByCustomer) {
                        $category = strtolower($table->holdByCustomer->category);
                    }
                    $bData = null;
                    if ($currentBooking) {
                        $bData = [
                            'status' => $currentBooking->status,
                            'pax' => $currentBooking->pax,
                            'customer' => [
                                'name' => $currentBooking->customer->name ?? 'Unknown',
                                'category' => $currentBooking->category ?? 'REGULAR',
                                'phone' => $currentBooking->customer->phone ?? 'N/A'
                            ]
                        ];
                    }
                @endphp
                <div class="aspect-square relative flex items-center justify-center rounded-2xl border text-sm font-extrabold transition-all hover:scale-110 active:scale-95 cursor-pointer {{ $statusClass }}"
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
                    <div class="absolute -top-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center bg-white shadow-md border border-slate-100">
                        @if($category === 'priority')
                        <i data-lucide="crown" class="w-3.5 h-3.5 text-amber-500 fill-amber-500"></i>
                        @elseif($category === 'event')
                        <i data-lucide="megaphone" class="w-3.5 h-3.5 text-[#4f46e5]"></i>
                        @elseif($category === 'regular')
                        <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                        @elseif($category === 'big spender')
                        <i data-lucide="dollar-sign" class="w-3.5 h-3.5 text-emerald-500"></i>
                         @elseif($category === 'drinker')
                        <i data-lucide="glass-water" class="w-3.5 h-3.5 text-blue-500"></i>
                        @elseif($category === 'party')
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-500"></i>
                        @elseif($category === 'dinner')
                        <i data-lucide="utensils-crossed" class="w-3.5 h-3.5 text-orange-500"></i>
                        @elseif($category === 'lunch')
                        <i data-lucide="utensils" class="w-3.5 h-3.5 text-rose-500"></i>
                        @elseif($category === 'family')
                        <i data-lucide="users" class="w-3.5 h-3.5 text-cyan-500"></i>
                        @elseif($category === 'youngster')
                        <i data-lucide="smile" class="w-3.5 h-3.5 text-pink-500"></i>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Improved Legend -->
            <div class="flex flex-wrap items-center gap-x-10 gap-y-4 pt-8 border-t border-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 bg-[#f43f5e] rounded-full ring-4 ring-rose-50"></div>
                    <span class="text-sm text-slate-500 font-bold uppercase tracking-wider">Arrived</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 bg-[#fbbf24] rounded-full ring-4 ring-amber-50"></div>
                    <span class="text-sm text-slate-500 font-bold uppercase tracking-wider">Pending</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 bg-[#3b82f6] rounded-full ring-4 ring-blue-50"></div>
                    <span class="text-sm text-slate-500 font-bold uppercase tracking-wider">Hold</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 bg-white border border-slate-200 rounded-full"></div>
                    <span class="text-sm text-slate-500 font-bold uppercase tracking-wider">Available</span>
                </div>
                <!-- Sub-Legend for Categories -->
                <div class="h-4 w-px bg-slate-200 mx-2 hidden sm:block"></div>
                <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg">
                    <i data-lucide="crown" class="w-3.5 h-3.5 text-amber-500 fill-amber-500"></i>
                    <span class="text-[11px] text-slate-500 font-black uppercase">Priority</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg">
                    <i data-lucide="megaphone" class="w-3.5 h-3.5 text-[#4f46e5]"></i>
                    <span class="text-[11px] text-slate-500 font-black uppercase">Event</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-[11px] text-slate-500 font-black uppercase">Regular</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg">
                    <i data-lucide="dollar-sign" class="w-3.5 h-3.5 text-emerald-500"></i>
                    <span class="text-[11px] text-slate-500 font-black uppercase">Big Spender</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg">
                    <i data-lucide="glass-water" class="w-3.5 h-3.5 text-blue-500"></i>
                    <span class="text-[11px] text-slate-500 font-black uppercase">Drinker</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-500"></i>
                    <span class="text-[11px] text-slate-500 font-black uppercase">Party</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg">
                    <i data-lucide="utensils-crossed" class="w-3.5 h-3.5 text-orange-500"></i>
                    <span class="text-[11px] text-slate-500 font-black uppercase">Dinner</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg">
                    <i data-lucide="utensils" class="w-3.5 h-3.5 text-rose-500"></i>
                    <span class="text-[11px] text-slate-500 font-black uppercase">Lunch</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-cyan-500"></i>
                    <span class="text-[11px] text-slate-500 font-black uppercase">Family</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg">
                    <i data-lucide="smile" class="w-3.5 h-3.5 text-pink-500"></i>
                    <span class="text-[11px] text-slate-500 font-black uppercase">Youngster</span>
                </div>
            </div>
        </div>

        <!-- This Week Stats Card -->
        <div class="bg-white rounded-2xl shadow-sm p-8 border border-stone-200 hover:border-brand-soft transition-all">
            <div class="relative inline-block text-left mb-8" x-data="{ openPeriod: false }">
                <button type="button" @click="openPeriod = !openPeriod"
                    class="inline-flex items-center gap-2.5 px-4 py-2 bg-stone-50 rounded-lg border border-stone-200 hover:bg-white hover:border-brand-primary hover:text-brand-primary transition-colors uppercase tracking-wider group">
                    <i data-lucide="calendar-range" class="w-4 h-4 text-stone-400 group-hover:text-brand-primary"></i>
                    <span class="text-xs font-bold text-stone-600 group-hover:text-brand-primary uppercase tracking-tight">
                        @php
                            $periodLabels = [
                                'today' => 'Today',
                                'this_week' => 'This Week',
                                'this_month' => 'This Month',
                                'this_year' => 'This Year'
                            ];
                            echo $periodLabels[request('period', 'this_week')] ?? 'This Week';
                        @endphp
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-stone-400 group-hover:text-brand-primary"></i>
                </button>
                <div x-show="openPeriod" @click.away="openPeriod = false"
                    class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 z-50 overflow-hidden"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95">
                    <div class="p-2 space-y-1">
                        @foreach(['today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month', 'this_year' => 'This Year'] as $val => $label)
                        <a href="{{ request()->fullUrlWithQuery(['period' => $val]) }}"
                            class="block px-4 py-2 text-xs font-extrabold rounded-lg transition-colors {{ request('period', 'this_week') == $val ? 'bg-brand-light text-brand-primary' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }} uppercase tracking-tight">
                            {{ $label }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Booked Rooms -->
                <div class="bg-stone-50 p-6 rounded-xl border border-stone-200 hover:border-brand-soft transition-all group">
                    <div class="w-10 h-10 bg-white shadow-sm border border-stone-100 rounded-lg flex items-center justify-center mb-4 text-stone-500 group-hover:text-brand-primary transition-colors">
                        <i data-lucide="door-open" class="w-5 h-5"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-stone-900 tracking-tighter">{{ $stats['booked_rooms'] }}</p>
                    <p class="text-[10px] text-stone-500 font-bold uppercase tracking-widest mt-1">Booked Tables</p>
                </div>

                <!-- Pending -->
                <div class="bg-stone-50 p-6 rounded-xl border border-stone-200 hover:border-brand-soft transition-all group">
                    <div class="w-10 h-10 bg-white shadow-sm border border-stone-100 rounded-lg flex items-center justify-center mb-4 text-stone-500 group-hover:text-brand-primary transition-colors">
                        <i data-lucide="timer" class="w-5 h-5"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-stone-900 tracking-tighter">{{ $stats['pending'] }}</p>
                    <p class="text-[10px] text-stone-500 font-bold uppercase tracking-widest mt-1">Pending</p>
                </div>

                <!-- Canceled -->
                <div class="bg-stone-50 p-6 rounded-xl border border-stone-200 hover:border-brand-soft transition-all group">
                    <div class="w-10 h-10 bg-white shadow-sm border border-stone-100 rounded-lg flex items-center justify-center mb-4 text-stone-500 group-hover:text-brand-primary transition-colors">
                        <i data-lucide="ban" class="w-5 h-5"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-stone-900 tracking-tighter">{{ $stats['cancelled'] }}</p>
                    <p class="text-[10px] text-stone-500 font-bold uppercase tracking-widest mt-1">Canceled</p>
                </div>

                <!-- Revenue - Full Width to prevent overflow -->
                <div class="col-span-2 bg-brand-light p-6 rounded-xl border border-brand-soft hover:border-brand-primary transition-all group relative overflow-hidden">
                    <div class="flex items-center gap-6">
                        <div class="w-14 h-14 bg-white shadow-sm border border-brand-primary/20 rounded-xl flex-none flex items-center justify-center text-brand-primary">
                            <i data-lucide="banknote" class="w-7 h-7"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] text-brand-primary font-extrabold uppercase tracking-widest mb-1">Total Revenue</p>
                            <p class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tighter truncate tabular-nums">
                                Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    <!-- Decorative element for professional feel -->
                    <div class="absolute -right-4 -bottom-4 opacity-[0.03] text-brand-primary">
                        <i data-lucide="trending-up" class="w-24 h-24"></i>
                    </div>
                </div>
            </div>

            <!-- Category Breakdown -->
            <div class="mt-10 pt-8 border-t border-stone-100">
                <div class="flex items-center justify-between mb-6 px-2">
                    <h3 class="text-[11px] font-extrabold text-stone-900 uppercase tracking-[0.25em]">Customer Segmentation</h3>
                    <div class="flex items-center gap-1.5 px-3 py-1 bg-brand-light rounded-lg border border-brand-soft/30">
                        <span class="w-1.5 h-1.5 bg-brand-primary rounded-full animate-pulse"></span>
                        <span class="text-[9px] font-extrabold text-brand-primary uppercase tracking-widest">Live</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @php
                    $categories = [
                        'REGULER' => ['icon' => 'user', 'color' => 'text-stone-400', 'bg' => 'bg-stone-50'],
                        'EVENT' => ['icon' => 'megaphone', 'color' => 'text-indigo-500', 'bg' => 'bg-indigo-50/30'],
                        'PRIORITAS' => ['icon' => 'crown', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50/30'],
                        'BIG_SPENDER' => ['icon' => 'dollar-sign', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50/30'],
                        'DRINKER' => ['icon' => 'glass-water', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50/30'],
                        'PARTY' => ['icon' => 'sparkles', 'color' => 'text-purple-500', 'bg' => 'bg-purple-50/30'],
                        'DINNER' => ['icon' => 'utensils-crossed', 'color' => 'text-orange-500', 'bg' => 'bg-orange-50/30'],
                        'LUNCH' => ['icon' => 'utensils', 'color' => 'text-rose-500', 'bg' => 'bg-rose-50/30'],
                        'FAMILY' => ['icon' => 'users', 'color' => 'text-cyan-500', 'bg' => 'bg-cyan-50/30'],
                        'YOUNGSTER' => ['icon' => 'smile', 'color' => 'text-pink-500', 'bg' => 'bg-pink-50/30']
                    ];
                    @endphp

                    @foreach($categories as $key => $style)
                    <div class="{{ $style['bg'] }} p-3 rounded-xl border border-transparent hover:border-stone-200 transition-all group cursor-default">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-white shadow-sm flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                                <i data-lucide="{{ $style['icon'] }}" class="w-4.5 h-4.5 {{ $style['color'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-extrabold text-stone-800 leading-none mb-1">{{ $categoryStats[$key] ?? 0 }}</p>
                                <p class="text-[9px] font-bold text-stone-500 uppercase tracking-widest truncate">{{ str_replace('_', ' ', $key) }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Booking List Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden mt-8">
        <div class="p-8">
            <form action="{{ route('dashboard') }}" method="GET" id="bookingFilterForm"
                class="flex flex-col gap-6 mb-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <h2 class="text-xl font-extrabold text-stone-900 tracking-tight uppercase">Booking List</h2>
                    
                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <button type="button" @click="showBookingModal = true"
                            class="flex-1 md:flex-none px-5 py-2.5 bg-stone-50 text-stone-700 border border-stone-200 rounded-lg text-xs font-bold flex items-center justify-center gap-2 shadow-sm hover:bg-white hover:border-brand-primary hover:text-brand-primary transition-all uppercase tracking-wider">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Guest
                        </button>
                        <button type="button" @click="showEventModal = true"
                            class="flex-1 md:flex-none px-5 py-2.5 bg-brand-primary text-white rounded-lg text-xs font-bold flex items-center justify-center gap-2 shadow-sm hover:opacity-90 transition-all uppercase tracking-wider">
                            <i data-lucide="megaphone" class="w-4 h-4"></i> Add Event
                        </button>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex-1 relative group min-w-[300px] max-w-md">
                        <i data-lucide="search"
                            class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 group-focus-within:text-brand-primary transition-colors"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="SEARCH BY NAME, PHONE, OR TABLE..." onchange="this.form.submit()"
                            class="w-full pl-12 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all outline-none uppercase tracking-wider placeholder:text-stone-400">
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <input type="hidden" name="period" value="{{ request('period', 'this_week') }}" id="periodInput">
                        <input type="hidden" name="status" value="{{ request('status') }}" id="statusInput">
                        <input type="hidden" name="category" value="{{ request('category') }}" id="categoryInput">
                        <input type="hidden" name="sort" value="{{ request('sort', 'desc') }}" id="sortInput">

                        <div class="relative" x-data="{ open: false, showCustom: {{ request('period') == 'custom' ? 'true' : 'false' }} }">
                            <button type="button" @click="open = !open"
                                class="px-5 py-3 bg-white text-stone-800 rounded-lg text-[11px] font-bold flex items-center gap-2.5 border border-stone-200 shadow-sm hover:bg-stone-50 hover:text-brand-primary hover:border-brand-soft transition-all uppercase tracking-wider group">
                                <i data-lucide="sliders-horizontal" class="w-4 h-4 text-stone-400 group-hover:text-brand-primary"></i>
                                <span>Advanced Filters</span>
                                @php
                                    $activeFilters = 0;
                                    if(request('period') && request('period') != 'this_week') $activeFilters++;
                                    if(request('status')) $activeFilters++;
                                    if(request('category')) $activeFilters++;

                                    $p = request('period', 'this_week');
                                    $periodLabel = match($p) {
                                        'last_15_mins' => 'Last 15 Min',
                                        'last_30_mins' => 'Last 30 Min',
                                        'last_hour' => 'Last Hour',
                                        'next_15_mins' => 'Next 15 Min',
                                        'next_30_mins' => 'Next 30 Min',
                                        'next_hour' => 'Next Hour',
                                        'today' => 'Today',
                                        'this_week' => 'This Week',
                                        'this_month' => 'This Month',
                                        'this_year' => 'This Year',
                                        'all' => 'All Time',
                                        'custom' => 'Custom Range',
                                        default => 'This Week'
                                    };
                                @endphp
                                @if($activeFilters > 0)
                                    <span class="w-5 h-5 bg-brand-primary text-white text-[10px] rounded flex items-center justify-center font-black">{{ $activeFilters }}</span>
                                @endif
                                <i data-lucide="chevron-down" class="w-4 h-4 text-stone-400 group-hover:text-brand-primary"></i>
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
                                <div class="relative w-full max-w-5xl bg-white rounded-[3.5rem] shadow-[0_40px_120px_-20px_rgba(0,0,0,0.25)] border border-white/20 overflow-hidden"
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-300 transform"
                                    x-transition:enter-start="opacity-0 scale-95 translate-y-12"
                                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200 transform"
                                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 scale-95 translate-y-12">
                                    
                                    <div class="p-12 lg:p-16">
                                        <!-- Header within Modal -->
                                        <div class="flex items-center justify-between mb-12">
                                            <div>
                                                <h2 class="text-xl font-extrabold text-stone-900 tracking-tight uppercase">Advanced Filters</h2>
                                                <p class="text-stone-500 font-bold text-xs mt-1">Refine your booking list exactly how you need it.</p>
                                            </div>
                                            <button type="button" @click="open = false" class="w-10 h-10 flex items-center justify-center rounded-lg bg-stone-50 text-stone-400 hover:bg-stone-100 hover:text-stone-900 transition-all">
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
                                                <div x-show="showCustom" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" class="p-8 bg-stone-50 rounded-xl border border-stone-200 space-y-6">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                        <div class="space-y-2">
                                                            <label class="text-[10px] font-extrabold text-stone-400 uppercase tracking-widest block text-center">Start Time & Date</label>
                                                            <input type="text" name="start_date" value="{{ request('start_date') }}" placeholder="Click to select start"
                                                                class="datetime-picker w-full px-5 py-4 bg-white border border-stone-200 rounded-lg text-xs font-bold text-center outline-none focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all">
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
                                                        <span class="px-4 py-2 bg-orange-50 text-[#e85a2f] text-[11px] font-black rounded-xl border-2 border-orange-100 flex items-center gap-2">
                                                            {{ $periodLabel }}
                                                        </span>
                                                    @endif
                                                    @if(request('status'))
                                                        <span class="px-4 py-2 bg-blue-50 text-blue-600 text-[11px] font-black rounded-xl border-2 border-blue-100 flex items-center gap-2">
                                                            {{ ucfirst(request('status')) }}
                                                        </span>
                                                    @endif
                                                    @if(request('category'))
                                                        <span class="px-4 py-2 bg-emerald-50 text-emerald-600 text-[11px] font-black rounded-xl border-2 border-emerald-100 flex items-center gap-2">
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
                                                    class="px-12 py-5 bg-slate-900 text-white rounded-[2rem] text-[12px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all shadow-2xl shadow-slate-200">
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

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-stone-400 text-[10px] font-extrabold uppercase tracking-[0.2em] border-b border-stone-200">
                            <th class="pb-4 px-4 w-12 text-left">#</th>
                            <th class="pb-4 px-4 text-left">Guest Name</th>
                            <th class="pb-4 px-4 text-left text-nowrap">Category</th>
                            <th class="pb-4 px-4 text-left text-nowrap">Table Number</th>
                            <th class="pb-4 px-4 text-left">Total Guest</th>
                            <th class="pb-4 px-4 text-left">Check In</th>
                            <th class="pb-4 px-4 text-left">Check Out</th>
                            <th class="pb-4 px-4 text-left">Contact Number</th>
                            <th class="pb-4 px-4 text-right">Amount</th>
                            <th class="pb-4 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($recentBookings as $booking)
                        @php
                            $bRowData = [
                                'status' => $booking->status,
                                'pax' => $booking->pax,
                                'customer' => [
                                    'name' => $booking->customer->name ?? 'Unknown',
                                    'category' => $booking->category ?? 'REGULAR',
                                    'phone' => $booking->customer->phone ?? 'N/A'
                                ]
                            ];
                        @endphp
                        <tr class="group hover:bg-slate-50/50 transition-colors cursor-pointer"
                             @click="selectedBooking = { 
                                id: '{{ $booking->id }}',
                                status: '{{ $booking->status }}', 
                                pax: '{{ $booking->pax }}', 
                                start_time: '{{ $booking->start_time->format('Y-m-d\TH:i') }}',
                                end_time: '{{ $booking->end_time->format('Y-m-d\TH:i') }}',
                                notes: '{{ addslashes($booking->notes) }}',
                                tags: {{ $booking->tags->toJson() }},
                                table_model: { code: '{{ $booking->tableModel->code ?? 'N/A' }}' },
                                customer: { 
                                    name: '{{ addslashes($booking->customer->name ?? 'Unknown') }}', 
                                    category: '{{ $booking->category ?? 'REGULAR' }}', 
                                    phone: '{{ $booking->customer->phone ?? 'N/A' }}',
                                    age: '{{ $booking->customer->age }}',
                                    gender: '{{ $booking->customer->gender }}'
                                } 
                            }; showInfoModal = true">
                            <td class="py-6 px-4">
                                <span class="text-xs font-black text-slate-300 group-hover:text-blue-500 transition-colors">
                                    {{ $loop->iteration + ($recentBookings->firstItem() - 1) }}
                                </span>
                            </td>
                            <td class="py-6 px-4">
                                <span class="font-black text-slate-800 text-sm tracking-tight">{{
                                    $booking->customer->name ?? 'N/A' }}</span>
                            </td>
                            <td class="py-6 px-4">
                                @php
                                if (!function_exists('getContrast')) {
                                    function getContrast($hex) {
                                        if (!$hex || str_contains($hex, 'bg-')) return '';
                                        if (str_contains($hex, 'gradient')) return 'white';
                                        $hex = str_replace('#', '', $hex);
                                        if (strlen($hex) != 6) return 'white';
                                        $r = hexdec(substr($hex, 0, 2));
                                        $g = hexdec(substr($hex, 2, 2));
                                        $b = hexdec(substr($hex, 4, 2));
                                        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
                                        return $brightness > 155 ? '#1e293b' : 'white';
                                    }
                                }
                                $cat = strtoupper($booking->category ?? 'REGULER');
                                $catData = $categoryMap[$cat] ?? null;
                                $isTw = $catData && str_contains($catData->bg_color ?? '', 'bg-');
                                $catColor = $catData ? ($isTw ? $catData->bg_color . ' ' . $catData->text_color : '') : 'bg-slate-50 text-slate-400';
                                $txtCol = $catData ? getContrast($catData->bg_color) : '';
                                $catStyle = ($catData && !$isTw) ? "background: {$catData->bg_color}; color: {$txtCol}; text-shadow: 0 1px 1px rgba(0,0,0,0.1)" : "";
                                $catIcon  = $catData?->icon ?? 'tag';
                                @endphp
                                <span
                                    class="px-3 py-1.5 {{ $catColor }} rounded-lg text-[10px] font-black uppercase tracking-wider flex items-center gap-1"
                                    style="{{ $catStyle }}">
                                    <i data-lucide="{{ $catIcon }}" class="w-3 h-3"></i>
                                    {{ $cat }}
                                </span>
                            </td>
                            <td class="py-6 px-4">
                                <span
                                    class="px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-orange-100">
                                    {{ $booking->tableModel->code ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="py-6 px-4">
                                <span class="text-sm font-black text-slate-500">{{ $booking->pax }}</span>
                            </td>
                            <td class="py-6 px-4">
                                <div class="px-4 py-1.5 bg-slate-50 inline-block rounded-xl border border-slate-100/50">
                                    <span class="text-[11px] font-black text-slate-400">{{ $booking->start_time ?
                                        $booking->start_time->format('d M, Y') : '-' }}</span>
                                </div>
                            </td>
                            <td class="py-6 px-4">
                                <div class="px-4 py-1.5 bg-slate-50 inline-block rounded-xl border border-slate-100/50">
                                    <span class="text-[11px] font-black text-slate-400">{{ $booking->end_time ?
                                        $booking->end_time->format('d M, Y') : '-' }}</span>
                                </div>
                            </td>
                            <td class="py-6 px-4">
                                <span class="text-[11px] font-black text-slate-400">{{ $booking->customer->phone ??
                                    'N/A' }}</span>
                            </td>
                            <td class="py-6 px-4 text-right">
                                <span class="text-sm font-black text-emerald-600">Rp {{
                                    number_format($booking->billed_price, 0, ',', '.') }}</span>
                            </td>
                            <td class="py-6 px-4 text-right">
                                @php
                                $status = strtolower($booking->status);
                                $statusColor = 'bg-slate-50 text-slate-400';
                                if ($status === 'confirmed' || $status === 'completed' || $status === 'billed') $statusColor = 'bg-emerald-50 text-emerald-600';
                                elseif ($status === 'booked' || $status === 'pending') $statusColor = 'bg-amber-50 text-amber-600';
                                elseif ($status === 'hold') $statusColor = 'bg-blue-50 text-blue-600';
                                elseif ($status === 'cancelled') $statusColor = 'bg-rose-50 text-rose-600';
                                @endphp
                                <span
                                    class="px-3 py-1 rounded-full {{ $statusColor }} text-[9px] font-black uppercase tracking-widest">
                                    {{ $booking->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Totals Summary Row --}}
                <div class="mx-0 mt-4 p-4 md:p-6 bg-stone-50 border-t border-stone-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-center">
                        <div class="flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-stone-200 group">
                            <div class="w-10 h-10 bg-stone-50 rounded flex items-center justify-center text-stone-400 group-hover:text-brand-primary transition-colors">
                                <i data-lucide="hash" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-[9px] text-stone-500 font-extrabold uppercase tracking-widest leading-none mb-1">Total Bookings</p>
                                <p class="text-base font-extrabold text-stone-900 tracking-tight">{{ number_format($listTotals['count']) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-stone-200 group">
                            <div class="w-10 h-10 bg-stone-50 rounded flex items-center justify-center text-stone-400 group-hover:text-brand-primary transition-colors">
                                <i data-lucide="user-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-[9px] text-stone-500 font-extrabold uppercase tracking-widest leading-none mb-1">Total PAX</p>
                                <p class="text-base font-extrabold text-stone-900 tracking-tight">{{ number_format($listTotals['guests']) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-stone-200 group">
                            <div class="w-10 h-10 bg-brand-light rounded flex items-center justify-center text-brand-primary">
                                <i data-lucide="banknote" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-[9px] text-brand-primary font-extrabold uppercase tracking-widest leading-none mb-1">Total Amount</p>
                                <p class="text-base font-extrabold text-stone-900 tracking-tight">Rp {{ number_format($listTotals['amount'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-10 pb-10 mt-4">
                    {{ $recentBookings->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Booking Modal -->
    <div x-show="showBookingModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showBookingModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity"
                    @click="showBookingModal = false; resetForm()">
                    <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

                <div x-show="showBookingModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl border border-stone-200 transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white p-10" x-data="{ 
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
                            // Sync other fields
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
                        <div class="flex justify-between items-center mb-10">
                            <div>
                                <h3 class="text-xl font-extrabold text-stone-900 tracking-tight uppercase">Create New Booking</h3>
                                <p class="text-xs text-stone-500 font-bold mt-1">Fill in the details for the reservation</p>
                            </div>
                            <button @click="showBookingModal = false; resetForm()"
                                class="w-10 h-10 flex items-center justify-center bg-stone-50 text-stone-400 hover:text-stone-900 rounded-lg transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form action="{{ route('bookings.store') }}" method="POST" class="space-y-6" x-ref="bookingForm">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Guest Name -->
                                <div class="md:col-span-2 relative">
                                    <label
                                        class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-3 flex justify-between items-center">
                                        <span>Guest Name</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="customer_name" required placeholder="SEARCH NAME OR PHONE..."
                                            x-model="customerSearch"
                                            @input.debounce.200ms="filterCustomers(); clearCustomer()"
                                            autocomplete="off"
                                            class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-wider placeholder:text-stone-300">
                                        
                                        <!-- Search Results Dropdown -->
                                        <div x-show="searchResults.length > 0" 
                                            class="absolute z-[100] left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-2"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            x-cloak>
                                            <div class="max-h-[250px] overflow-y-auto">
                                                <template x-for="c in searchResults" :key="c.id">
                                                    <div @click="selectCustomer(c)" 
                                                        class="px-6 py-4 hover:bg-slate-50 cursor-pointer flex items-center justify-between group border-b border-slate-50 last:border-0">
                                                        <div class="flex items-center gap-4">
                                                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-lg"
                                                                :class="{
                                                                    'bg-amber-50 text-amber-500': c.master_level?.name?.toLowerCase() === 'gold',
                                                                    'bg-slate-100 text-slate-500': c.master_level?.name?.toLowerCase() === 'silver',
                                                                    'bg-orange-50 text-orange-600': c.master_level?.name?.toLowerCase() === 'bronze'
                                                                }">
                                                                <span x-text="c.name.substring(0,1).toUpperCase()"></span>
                                                            </div>
                                                            <div>
                                                                <p class="text-sm font-black text-slate-800" x-text="c.name"></p>
                                                                <p class="text-[10px] font-bold text-slate-400" x-text="c.phone || '-'"></p>
                                                            </div>
                                                        </div>
                                                        <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded bg-slate-100 text-slate-500"
                                                            :class="{
                                                                'bg-amber-100 text-amber-600': c.master_level?.name?.toLowerCase() === 'gold',
                                                                'bg-slate-100 text-slate-600': c.master_level?.name?.toLowerCase() === 'silver',
                                                                'bg-orange-100 text-orange-600': c.master_level?.name?.toLowerCase() === 'bronze'
                                                            }"
                                                            x-text="c.master_level?.name"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Area Selection -->
                                <div x-data="{ localArea: '' }">
                                    <label
                                        class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-3">Select Area</label>
                                    <select x-model="localArea"
                                        class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all cursor-pointer uppercase tracking-tight">
                                        <option value="">All Areas</option>
                                        @foreach($floors as $floor)
                                        <option value="{{ $floor }}">{{ str_replace('_', ' ', strtoupper($floor)) }}
                                        </option>
                                        @endforeach
                                    </select>

                                        <div class="mt-6">
                                            <label
                                                class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-3">Table</label>
                                            <select name="table_id" required x-model="selectedTableId"
                                                class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all cursor-pointer uppercase tracking-tight">
                                                <option value="">Select Table</option>
                                                @foreach($allTables as $table)
                                                <template x-if="localArea === '' || localArea === '{{ $table->area_id }}'">
                                                    <option value="{{ $table->id }}">{{ $table->code }}</option>
                                                </template>
                                                @endforeach
                                            </select>

                                            <!-- Table Details (Min Spend & Capacity) -->
                                            <template x-if="selectedTable">
                                                <div class="mt-4 flex gap-4 animate-in fade-in slide-in-from-top-2 duration-300">
                                                    <div class="flex-1 p-3 bg-brand-light rounded-lg border border-brand-soft/30">
                                                        <p class="text-[8px] font-extrabold text-brand-primary uppercase tracking-widest mb-1">Min. Spending</p>
                                                        <p class="text-xs font-extrabold text-stone-900" x-text="'Rp ' + (selectedTable.min_spending || 0).toLocaleString('id-ID')"></p>
                                                    </div>
                                                    <div class="flex-1 p-3 bg-stone-50 rounded-lg border border-stone-200">
                                                        <p class="text-[8px] font-extrabold text-stone-400 uppercase tracking-widest mb-1">Max Capacity</p>
                                                        <p class="text-xs font-extrabold text-stone-900" x-text="(selectedTable.capacity || 0) + ' PAX'"></p>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Category Selection -->
                                    <div>
                                        <label
                                            class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-3">Category</label>
                                        <select name="customer_category" required x-ref="categorySelect"
                                            class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all cursor-pointer uppercase tracking-tight">
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

                                    <!-- Pax -->
                                    <div>
                                        <label
                                            class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-3">Total Guests (PAX)</label>
                                        <input type="number" name="pax" required min="1" placeholder="0"
                                            class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-wider">
                                    </div>

                                    <!-- Phone -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-3">Contact Number</label>
                                            <input type="text" name="phone" placeholder="Enter phone number" x-ref="phoneInput"
                                                class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-wider">
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-3">Age</label>
                                                <input type="number" name="age" placeholder="Age" x-ref="ageInput"
                                                    class="w-full px-4 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-wider">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-3">Gender</label>
                                                <select name="gender" x-ref="genderSelect"
                                                    class="w-full px-4 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all cursor-pointer uppercase tracking-tight">
                                                    <option value="">N/A</option>
                                                    <option value="MALE">MALE</option>
                                                    <option value="FEMALE">FEMALE</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                <!-- Check In -->
                                <div class="relative group">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2.5 ml-1">Check In Time</label>
                                    <div class="relative">
                                        <i data-lucide="calendar" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors z-10"></i>
                                        <input type="text" name="start_time" required placeholder="Select check-in time"
                                            class="datetime-picker w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-transparent rounded-[1.25rem] text-sm font-bold text-slate-700 focus:bg-white focus:border-blue-500/20 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
                                    </div>
                                </div>

                                <!-- Check Out -->
                                <div class="relative group">
                                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5 ml-1">Check Out Time</label>
                                    <div class="relative">
                                        <i data-lucide="clock" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 group-focus-within:text-brand-primary transition-colors z-10"></i>
                                        <input type="text" name="end_time" required placeholder="SELECT CHECK-OUT TIME"
                                            class="datetime-picker w-full pl-12 pr-4 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold text-stone-800 focus:bg-white focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/10 transition-all outline-none uppercase tracking-wider placeholder:text-stone-300">
                                    </div>
                                </div>

                                <!-- Tags Section -->
                                @foreach($tags as $group => $groupTags)
                                <div class="md:col-span-2 mt-6 pt-6 border-t border-stone-100">
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-1.5 h-3.5 bg-brand-primary rounded-full"></div>
                                        <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest">Select {{ $group }}</label>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($groupTags as $tag)
                                            <label class="relative cursor-pointer group">
                                                <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="hidden peer">
                                                <div class="px-4 py-2 rounded-lg border border-stone-200 bg-white text-[10px] font-extrabold text-stone-500 
                                                            peer-checked:bg-brand-primary peer-checked:text-white peer-checked:border-brand-primary
                                                            hover:border-brand-soft hover:bg-stone-50 transition-all duration-200 uppercase tracking-wider">
                                                    {{ $tag->name }}
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach

                                <!-- Notes -->
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-3">Notes (Optional)</label>
                                    <textarea name="notes" rows="3" placeholder="ADD ANY SPECIAL REQUESTS..."
                                        class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold text-stone-800 focus:bg-white focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/10 transition-all outline-none uppercase tracking-wider placeholder:text-stone-300 resize-none"></textarea>
                                </div>
                            </div>

                            <div class="pt-6">
                                <button type="submit"
                                    class="w-full py-4 bg-brand-primary text-white rounded-lg text-sm font-extrabold flex items-center justify-center gap-3 shadow-lg hover:opacity-90 transition-all uppercase tracking-widest">
                                    Create Booking
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
                <div x-show="showEventModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity"
                    @click="showEventModal = false; resetForm()">
                    <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

                <div x-show="showEventModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl border border-stone-200 transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white p-8" x-data="{ 
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
                            // Sync other fields
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
                            const start = new Date(this.eventStartTime).getTime();
                            const end = new Date(this.eventEndTime).getTime();
                            const locked = new Set();
                            
                            this.activeBookings.forEach(b => {
                                const bStart = new Date(b.start_time).getTime();
                                const bEnd = new Date(b.end_time).getTime();
                                if (bStart < end && bEnd > start) {
                                    locked.add(b.table_id);
                                }
                            });
                            this.lockedTableIds = Array.from(locked);
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
                                // if flatpickr inputs need manual clear:
                                const fpStart = this.$refs.eventForm.querySelector('input[name=\'start_time\']');
                                const fpEnd = this.$refs.eventForm.querySelector('input[name=\'end_time\']');
                                if (fpStart && fpStart._flatpickr) fpStart._flatpickr.clear();
                                if (fpEnd && fpEnd._flatpickr) fpEnd._flatpickr.clear();
                            }
                        }
                    }" x-init="allCustomers = {{ Js::from($customers) }}; activeBookings = {{ Js::from($allActiveBookings) }}">
                        <div class="flex justify-between items-center mb-10">
                            <div>
                                <h3 class="text-xl font-extrabold text-stone-900 tracking-tight uppercase">Create Event Booking</h3>
                                <p class="text-xs text-stone-500 font-bold mt-1">Select multiple tables for your event</p>
                            </div>
                            <button @click="showEventModal = false; resetForm()"
                                class="w-10 h-10 flex items-center justify-center bg-stone-50 text-stone-400 hover:text-stone-900 rounded-lg transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form action="{{ route('bookings.event_store') }}" method="POST" class="space-y-6" x-ref="eventForm">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Guest Name -->
                                <div class="md:col-span-2 relative">
                                    <label
                                        class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-3 flex justify-between items-center">
                                        <span>Event / Guest Name</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="customer_name" required placeholder="SEARCH NAME OR PHONE..."
                                            x-model="customerSearch"
                                            @input.debounce.200ms="filterCustomers(); clearCustomer()"
                                            autocomplete="off"
                                            class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-wider placeholder:text-stone-300">
                                        
                                        <!-- Search Results Dropdown -->
                                        <div x-show="searchResults.length > 0" 
                                            class="absolute z-[100] left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-2"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            x-cloak>
                                            <div class="max-h-[250px] overflow-y-auto">
                                                <template x-for="c in searchResults" :key="c.id">
                                                    <div @click="selectCustomer(c)" 
                                                        class="px-6 py-4 hover:bg-slate-50 cursor-pointer flex items-center justify-between group border-b border-slate-50 last:border-0">
                                                        <div class="flex items-center gap-4">
                                                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-lg"
                                                                :class="{
                                                                    'bg-amber-50 text-amber-500': c.master_level?.name?.toLowerCase() === 'gold',
                                                                    'bg-slate-100 text-slate-500': c.master_level?.name?.toLowerCase() === 'silver',
                                                                    'bg-orange-50 text-orange-600': c.master_level?.name?.toLowerCase() === 'bronze'
                                                                }">
                                                                <span x-text="c.name.substring(0,1).toUpperCase()"></span>
                                                            </div>
                                                            <div>
                                                                <p class="text-sm font-black text-slate-800" x-text="c.name"></p>
                                                                <p class="text-[10px] font-bold text-slate-400" x-text="c.phone || '-'"></p>
                                                            </div>
                                                        </div>
                                                        <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded bg-slate-100 text-slate-500"
                                                            :class="{
                                                                'bg-amber-100 text-amber-600': c.master_level?.name?.toLowerCase() === 'gold',
                                                                'bg-slate-100 text-slate-600': c.master_level?.name?.toLowerCase() === 'silver',
                                                                'bg-orange-100 text-orange-600': c.master_level?.name?.toLowerCase() === 'bronze'
                                                            }"
                                                            x-text="c.master_level?.name"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Table Selection (Multi) with Area Filter -->
                                <div class="md:col-span-2" x-data="{ eventArea: '' }">
                                    <div class="flex justify-between items-center mb-3 text-[10px] font-extrabold uppercase tracking-widest">
                                        <label class="text-stone-400">Select Area</label>
                                        <label class="text-brand-primary">Select Multiple Tables</label>
                                    </div>

                                    <select x-model="eventArea"
                                        class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all cursor-pointer mb-6 uppercase tracking-tight">
                                        <option value="">All Areas</option>
                                        @foreach($floors as $floor)
                                        <option value="{{ $floor }}">{{ str_replace('_', ' ', strtoupper($floor)) }}
                                        </option>
                                        @endforeach
                                    </select>

                                    <div
                                        class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 max-h-48 overflow-y-auto p-4 bg-stone-50 rounded-xl border border-stone-200">
                                        @foreach($allTables as $table)
                                        <label x-show="eventArea === '' || eventArea === '{{ $table->area_id }}'"
                                            class="relative flex items-center justify-center aspect-square rounded-lg border-2 border-stone-200 overflow-hidden group transition-all"
                                            :class="lockedTableIds.includes({{ $table->id }}) ? 'opacity-50 cursor-not-allowed bg-stone-100' : 'cursor-pointer hover:border-brand-soft has-[:checked]:bg-brand-primary has-[:checked]:border-brand-primary has-[:checked]:text-white'">
                                            <input type="checkbox" name="table_ids[]" value="{{ $table->id }}"
                                                class="hidden peer"
                                                :disabled="lockedTableIds.includes({{ $table->id }})">
                                            <span class="text-xs font-extrabold uppercase tracking-widest">{{ $table->code }}</span>
                                            <div
                                                class="absolute top-1 right-1 opacity-0 group-has-[:checked]:opacity-100 transition-opacity">
                                                <i data-lucide="check-circle" class="w-3 h-3 text-white"></i>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Pax -->
                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Total
                                        Guests (PAX)</label>
                                    <input type="number" name="pax" required min="1" placeholder="0"
                                        class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/5 transition-all">
                                </div>

                                <!-- Phone -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Contact
                                            Number</label>
                                        <input type="text" name="phone" placeholder="Enter phone number" x-ref="phoneInput"
                                            class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/5 transition-all">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Age</label>
                                            <input type="number" name="age" placeholder="Age" x-ref="ageInput"
                                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/5 transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Gender</label>
                                            <select name="gender" x-ref="genderSelect"
                                                class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/5 transition-all cursor-pointer">
                                                <option value="">N/A</option>
                                                <option value="MALE">MALE</option>
                                                <option value="FEMALE">FEMALE</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Check In -->
                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Event
                                        Start</label>
                                    <input type="text" name="start_time" required placeholder="Select event start"
                                        x-model="eventStartTime" @input="checkAvailability()" @change="checkAvailability()"
                                        class="datetime-picker w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/5 transition-all">
                                </div>

                                <!-- Check Out -->
                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Event
                                        End</label>
                                    <input type="text" name="end_time" required placeholder="Select event end"
                                        x-model="eventEndTime" @input="checkAvailability()" @change="checkAvailability()"
                                        class="datetime-picker w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/5 transition-all">
                                </div>

                                <!-- Tags Section -->
                                @foreach($tags as $group => $groupTags)
                                <div class="md:col-span-2 border-t border-slate-100 pt-6 mt-4">
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Event {{ $group }}</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($groupTags as $tag)
                                            <label class="cursor-pointer group">
                                                <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="hidden peer">
                                                <div class="px-4 py-2 rounded-2xl border border-slate-100 bg-slate-50 text-[11px] font-black text-slate-500 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-all shadow-sm group-hover:bg-slate-100">
                                                    {{ $tag->name }}
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach

                                <!-- Notes -->
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Event
                                        Notes (Optional)</label>
                                    <textarea name="notes" rows="3"
                                        placeholder="Add any special requests for the event..."
                                        class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/5 transition-all resize-none"></textarea>
                                </div>
                            </div>

                            <div class="pt-6">
                                <button type="submit"
                                    class="w-full py-5 bg-blue-600 text-white rounded-2xl text-base font-black flex items-center justify-center gap-3 shadow-[0_12px_30px_rgba(37,99,235,0.3)] hover:bg-blue-700 transition-all hover:-translate-y-1 active:translate-y-0">
                                    Create Event
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Info Modal -->
        <div x-show="showInfoModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showInfoModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity"
                    @click="showInfoModal = false; selectedBooking = null">
                    <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

                <div x-show="showInfoModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl border border-stone-200 transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white p-8">
                        <div class="flex justify-between items-center mb-8">
                            <div>
                                <h3 class="text-xl font-extrabold text-stone-900 tracking-tight uppercase">Booking Details</h3>
                                <p class="text-xs text-stone-500 font-bold mt-1">Current reservation information</p>
                            </div>
                            <button @click="showInfoModal = false; selectedBooking = null"
                                class="w-10 h-10 flex items-center justify-center bg-stone-50 text-stone-400 hover:text-stone-900 rounded-lg transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <div x-show="!selectedBooking" class="py-12 flex flex-col items-center justify-center space-y-4">
                            <div class="w-10 h-10 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin"></div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Loading details...</p>
                        </div>
                        <template x-if="selectedBooking">
                            <div class="bg-white p-10">
                                <div class="flex justify-between items-center mb-8">
                                    <div>
                                        <h3 class="text-lg font-extrabold text-stone-900 tracking-tight uppercase" x-text="'Table ' + selectedBooking.table_model?.code"></h3>
                                        <p class="text-xs text-stone-500 font-bold mt-1">RESERVATION DETAILS</p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div class="flex items-center gap-5 p-4 bg-stone-50 rounded-xl border border-stone-100">
                                        <div class="w-14 h-14 rounded-lg bg-brand-primary flex items-center justify-center text-white font-black text-xl"
                                            :class="{
                                                'bg-[#A68A56]': selectedBooking.customer?.master_level?.name?.toLowerCase() === 'gold',
                                                'bg-stone-400': selectedBooking.customer?.master_level?.name?.toLowerCase() === 'silver',
                                                'bg-[#9F7549]': selectedBooking.customer?.master_level?.name?.toLowerCase() === 'bronze'
                                            }">
                                            <span x-text="selectedBooking.customer?.name?.substring(0,1).toUpperCase() || '?'"></span>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-3">
                                                <h4 class="text-base font-extrabold text-stone-900 uppercase tracking-tight" x-text="selectedBooking.customer?.name"></h4>
                                                <span class="px-2 py-0.5 bg-brand-light text-brand-primary rounded text-[9px] font-extrabold uppercase tracking-widest"
                                                    x-text="selectedBooking.customer?.master_level?.name || 'REGULER'"></span>
                                            </div>
                                            <p class="text-xs font-bold text-stone-500 mt-1" x-text="selectedBooking.customer?.phone || 'No phone number'"></p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="p-4 bg-stone-50 rounded-lg border border-stone-100">
                                            <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-1.5">Check In</label>
                                            <p class="text-xs font-extrabold text-stone-800" x-text="selectedBooking.start_time ? new Date(selectedBooking.start_time).toLocaleString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}) : '-'"></p>
                                        </div>
                                        <div class="p-4 bg-stone-50 rounded-lg border border-stone-100">
                                            <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-1.5">Check Out</label>
                                            <p class="text-xs font-extrabold text-stone-800" x-text="selectedBooking.end_time ? new Date(selectedBooking.end_time).toLocaleString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}) : '-'"></p>
                                        </div>
                                    </div>

                                    <div class="p-4 bg-stone-50 rounded-lg border border-stone-100">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-1.5">Status</label>
                                                <div class="flex items-center gap-2">
                                                    <div class="w-2 h-2 rounded-full" :class="{
                                                        'bg-amber-500': selectedBooking.status === 'confirmed',
                                                        'bg-stone-400': selectedBooking.status === 'pending',
                                                        'bg-emerald-500': ['occupied', 'arrived'].includes(selectedBooking.status),
                                                        'bg-red-500': selectedBooking.status === 'cancelled'
                                                    }"></div>
                                                    <span class="text-xs font-extrabold text-stone-800 uppercase" x-text="selectedBooking.status"></span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-1.5">Total Guests</label>
                                                <p class="text-xs font-extrabold text-stone-800" x-text="selectedBooking.pax + ' PAX'"></p>
                                            </div>
                                        </div>
                                    </div>

                                     <div class="flex gap-3 pt-4">
                                        <button x-show="selectedBooking.status !== 'hold'" @click="showInfoModal = false; showEditBookingModal = true; editBookingData = JSON.parse(JSON.stringify(selectedBooking))"
                                                class="flex-1 py-3.5 bg-brand-primary text-white rounded-lg text-xs font-extrabold uppercase tracking-widest shadow-lg hover:opacity-90 transition-all">
                                            Edit Booking
                                        </button>
                                        <button @click="showInfoModal = false; selectedBooking = null"
                                                class="flex-1 py-3.5 bg-stone-200 text-stone-700 rounded-lg text-xs font-extrabold uppercase tracking-widest hover:bg-stone-300 transition-all">
                                            Close
                                        </button>
                                    </div>
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
                <div x-show="showEditBookingModal" @click="showEditBookingModal = false" class="fixed inset-0 transition-opacity">
                    <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

                <template x-if="showEditBookingModal">
                    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl border border-stone-200 transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                        <form :action="'/bookings/' + editBookingData.id" method="POST" class="p-8">
                            @csrf
                            @method('PUT')
                            <div class="flex justify-between items-center mb-8">
                                <div>
                                    <h3 class="text-xl font-extrabold text-stone-900 tracking-tight uppercase">Edit Booking</h3>
                                    <p class="text-xs text-stone-500 font-bold mt-1">Update reservation details</p>
                                </div>
                                <button type="button" @click="showEditBookingModal = false" class="w-10 h-10 flex items-center justify-center bg-stone-50 text-stone-400 hover:text-stone-900 rounded-lg transition-colors">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Guest Name</label>
                                    <input type="text" name="customer_name" x-model="editBookingData.customer.name" required 
                                        class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-wider">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Category</label>
                                    <select name="customer_category" x-model="editBookingData.customer.category" required 
                                        class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-tight">
                                        @foreach(['REGULAR', 'PRIORITY', 'EVENT', 'BIG SPENDER', 'DRINKER', 'PARTY', 'DINNER', 'LUNCH', 'FAMILY', 'YOUNGSTER'] as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Status</label>
                                    <select name="status" x-model="editBookingData.status" required 
                                        class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-tight">
                                        @foreach(['PENDING', 'CONFIRMED', 'HOLD', 'ARRIVED', 'OCCUPIED', 'BILLED', 'COMPLETED', 'CANCELLED'] as $st)
                                            <option value="{{ $st }}">{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Contact Number</label>
                                    <input type="text" name="phone" x-model="editBookingData.customer.phone" 
                                        class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-wider">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Age</label>
                                        <input type="number" name="age" x-model="editBookingData.customer.age" 
                                            class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-wider">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Gender</label>
                                        <select name="gender" x-model="editBookingData.customer.gender" 
                                            class="w-full px-4 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-tight">
                                            <option value="">Not Set</option>
                                            <option value="MALE">Laki-laki</option>
                                            <option value="FEMALE">Perempuan</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Check In</label>
                                    <input type="text" name="start_time" required x-model="editBookingData.start_time"
                                        class="datetime-picker w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-wider">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Check Out</label>
                                    <input type="text" name="end_time" required x-model="editBookingData.end_time"
                                        class="datetime-picker w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-wider">
                                </div>
                                
                                <div>
                                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">PAX</label>
                                    <input type="number" name="pax" x-model="editBookingData.pax" required 
                                        class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-wider">
                                </div>

                                <!-- Tags Section -->
                                @foreach($tags as $group => $groupTags)
                                <div class="md:col-span-2 border-t border-stone-100 pt-6">
                                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-4">Edit {{ $group }}</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($groupTags as $tag)
                                            <label class="cursor-pointer group">
                                                <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" 
                                                       :checked="(editBookingData.tags || []).some(t => t.id == {{ $tag->id }})"
                                                       class="hidden peer">
                                                <div class="px-4 py-2 rounded-lg border border-stone-200 bg-white text-[10px] font-extrabold text-stone-500 peer-checked:bg-brand-primary peer-checked:text-white peer-checked:border-brand-primary transition-all uppercase tracking-widest">
                                                    {{ $tag->name }}
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Notes</label>
                                    <textarea name="notes" x-model="editBookingData.notes" rows="2" 
                                        class="w-full px-5 py-3.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold text-stone-800 focus:bg-white focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/10 transition-all uppercase tracking-widest placeholder:text-stone-300 resize-none"></textarea>
                                </div>
                            </div>

                            <div class="pt-8">
                                <button type="submit" class="w-full py-4 bg-brand-primary text-white rounded-lg text-sm font-extrabold uppercase tracking-widest shadow-lg hover:opacity-90 transition-all">
                                    Save Changes
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
            <th>Guest Name</th>
            <th>Category</th>
            <th>Table Number</th>
            <th>Total Guest</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Contact Number</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($allFilteredBookings as $booking)
        <tr>
            <td>{{ $booking->customer->name ?? 'N/A' }}</td>
            <td>{{ $booking->category ?? 'REGULAR' }}</td>
            <td>{{ $booking->tableModel->code ?? 'N/A' }}</td>
            <td>{{ $booking->pax }}</td>
            <td>{{ $booking->start_time ? $booking->start_time->format('d M, Y H:i') : '-' }}</td>
            <td>{{ $booking->end_time ? $booking->end_time->format('d M, Y H:i') : '-' }}</td>
            <td>{{ $booking->customer->phone ?? 'N/A' }}</td>
            <td>{{ $booking->billed_price }}</td>
            <td>{{ $booking->status }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">TOTAL</td>
            <td>{{ $listTotals['guests'] }}</td>
            <td colspan="3"></td>
            <td>{{ $listTotals['amount'] }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
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