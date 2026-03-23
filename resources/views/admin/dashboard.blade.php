@extends('layouts.admin')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Top Section: Room Status & Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Room Status -->
        <div
            class="lg:col-span-2 bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-10 border border-gray-50/50">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Room Status</h2>
                <div class="flex items-center gap-3">
                    <form action="{{ route('dashboard') }}" method="GET" id="floorFilterForm">
                        <select name="floor" onchange="this.form.submit()" 
                            class="px-5 py-2.5 bg-slate-50 text-slate-600 rounded-2xl text-sm font-bold border border-slate-100/50 hover:bg-slate-100 transition-colors focus:ring-0 cursor-pointer">
                            @foreach($floors as $floor)
                                <option value="{{ $floor }}" {{ $selectedFloor == $floor ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', strtoupper($floor)) }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    <button type="button"
                        class="px-5 py-2.5 bg-slate-50 text-slate-600 rounded-2xl text-sm font-bold flex items-center gap-2 border border-slate-100/50 hover:bg-slate-100 transition-colors">
                        Today <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                    </button>
                    <button type="button"
                        class="p-2.5 bg-slate-50 text-slate-600 rounded-2xl border border-slate-100/50 hover:bg-slate-100 transition-colors">
                        <i data-lucide="maximize" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Rooms Grid: Exactly matching the image style -->
            <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-11 gap-4 mb-10">
                @foreach($tables as $table)
                @php
                    $statusClass = 'bg-slate-50 border-slate-100 text-slate-300';
                    $status = strtolower($table->status);

                    if ($status === 'occupied' || $status === 'booked' || $status === 'arrived') {
                        $statusClass = 'bg-[#10b981] text-white border-[#10b981] shadow-[0_4px_12px_rgba(16,185,129,0.25)]';
                    } elseif ($status === 'cancelled') {
                        $statusClass = 'bg-[#f43f5e] text-white border-[#f43f5e] shadow-[0_4px_12px_rgba(244,63,94,0.25)]';
                    } elseif ($status === 'pending' || $status === 'reserved') {
                        $statusClass = 'bg-[#ecfdf5] text-[#10b981] border-[#d1fae5]';
                    }

                    // Get currently active booking for indicator
                    $currentBooking = $table->bookings->first();
                    $category = $currentBooking && $currentBooking->customer ? strtolower($currentBooking->customer->category) : null;
                @endphp
                <div class="aspect-square relative flex items-center justify-center rounded-2xl border text-sm font-extrabold transition-all hover:scale-110 active:scale-95 cursor-pointer {{ $statusClass }}">
                    {{ $table->code }}

                    @if($category)
                    <div class="absolute -top-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center bg-white shadow-md border border-slate-100 group">
                        @if($category === 'priority')
                            <i data-lucide="crown" class="w-3.5 h-3.5 text-amber-500 fill-amber-500"></i>
                        @elseif($category === 'event')
                            <i data-lucide="megaphone" class="w-3.5 h-3.5 text-[#4f46e5]"></i>
                        @elseif($category === 'regular')
                            <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach

        </div>

        <!-- Improved Legend -->
        <div class="flex flex-wrap items-center gap-x-10 gap-y-4 pt-8 border-t border-slate-50">
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 bg-[#10b981] rounded-full ring-4 ring-emerald-50"></div>
                <span class="text-sm text-slate-500 font-bold">Booked</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 bg-[#f43f5e] rounded-full ring-4 ring-rose-50"></div>
                <span class="text-sm text-slate-500 font-bold">Canceled</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 bg-[#ecfdf5] border border-[#d1fae5] rounded-full ring-4 ring-slate-50"></div>
                <span class="text-sm text-slate-500 font-bold">Pending</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 bg-white border border-slate-200 rounded-full"></div>
                <span class="text-sm text-slate-500 font-bold">Not Booked</span>
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
        </div>
    </div>

    <!-- This Week Stats Card -->
    <div class="bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-10 border border-gray-50/50">
        <div
            class="inline-flex items-center gap-2.5 mb-10 px-5 py-2.5 bg-slate-50 rounded-2xl border border-slate-100/50">
            <i data-lucide="calendar-range" class="w-4 h-4 text-slate-500"></i>
            <span class="text-sm font-black text-slate-600">This Week</span>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <!-- Booked Rooms -->
            <div
                class="bg-[#f0fdf4]/50 p-8 rounded-[2rem] border border-[#dcfce7]/50 hover:shadow-lg hover:shadow-emerald-500/5 transition-all group">
                <div
                    class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <i data-lucide="door-open" class="w-6 h-6 text-emerald-600"></i>
                </div>
                <p class="text-3xl font-black text-slate-800 tracking-tighter">{{ $stats['booked_rooms'] }}</p>
                <p class="text-xs text-slate-400 font-black uppercase tracking-widest mt-1.5">Booked Rooms</p>
            </div>

            <!-- Pending -->
            <div
                class="bg-[#fdf2f8]/50 p-8 rounded-[2rem] border border-[#fce7f3]/50 hover:shadow-lg hover:shadow-pink-500/5 transition-all group">
                <div
                    class="w-12 h-12 bg-pink-100 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <i data-lucide="timer" class="w-6 h-6 text-pink-600"></i>
                </div>
                <p class="text-3xl font-black text-slate-800 tracking-tighter">{{ $stats['pending'] }}</p>
                <p class="text-xs text-slate-400 font-black uppercase tracking-widest mt-1.5">Pending</p>
            </div>

            <!-- Canceled -->
            <div
                class="bg-[#f5f3ff]/50 p-8 rounded-[2rem] border border-[#ede9fe]/50 hover:shadow-lg hover:shadow-violet-500/5 transition-all group">
                <div
                    class="w-12 h-12 bg-violet-100 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <i data-lucide="ban" class="w-6 h-6 text-violet-600"></i>
                </div>
                <p class="text-3xl font-black text-slate-800 tracking-tighter">{{ $stats['cancelled'] }}</p>
                <p class="text-xs text-slate-400 font-black uppercase tracking-widest mt-1.5">Canceled</p>
            </div>

            <!-- Revenue -->
            <div
                class="bg-[#fff7ed]/50 p-8 rounded-[2rem] border border-[#ffedd5]/50 hover:shadow-lg hover:shadow-orange-500/5 transition-all group">
                <div
                    class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <i data-lucide="banknote" class="w-6 h-6 text-orange-600"></i>
                </div>
                <p class="text-3xl font-black text-slate-800 tracking-tighter">${{ number_format($stats['total_revenue']) }}</p>
                <p class="text-xs text-slate-400 font-black uppercase tracking-widest mt-1.5">Total Revenue</p>
            </div>
        </div>
    </div>
</div>

<!-- Booking List Card -->
<div class="bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-50/50 overflow-hidden">
    <div class="p-10">
        <div class="flex flex-col xl:flex-row justify-between items-center mb-10 gap-6">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Booking List</h2>

            <div class="flex flex-1 max-w-xl relative group">
                <i data-lucide="search"
                    class="w-5 h-5 absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                <input type="text" placeholder="Search room, Guest Name..."
                    class="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-[1.25rem] text-sm font-medium focus:ring-4 focus:ring-blue-500/5 transition-all placeholder:text-slate-300">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    class="px-5 py-3 bg-slate-50 text-slate-600 rounded-2xl text-sm font-bold flex items-center gap-2 border border-slate-100/50 hover:bg-slate-100 transition-colors">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4 text-slate-400"></i> Filter
                </button>
                <button
                    class="px-5 py-3 bg-slate-50 text-slate-600 rounded-2xl text-sm font-bold flex items-center gap-2 border border-slate-100/50 hover:bg-slate-100 transition-colors text-nowrap">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i> This Week
                </button>
                <button
                    class="px-5 py-3 bg-slate-50 text-slate-600 rounded-2xl text-sm font-bold flex items-center gap-2 border border-slate-100/50 hover:bg-slate-100 transition-colors">
                    <i data-lucide="arrow-up-down" class="w-4 h-4 text-slate-400"></i> Sort
                </button>
                <button
                    class="px-8 py-3 bg-[#e85a2f] text-white rounded-2xl text-sm font-black flex items-center gap-2.5 shadow-[0_8px_20px_rgba(232,90,47,0.3)] hover:bg-[#d04a25] transition-all hover:-translate-y-0.5 active:translate-y-0">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i> Add Booking
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr
                        class="text-slate-400 text-[10px] font-black uppercase tracking-[0.15em] border-b border-slate-50">
                        <th class="pb-5 px-4 w-10"><input type="checkbox"
                                class="rounded-md border-slate-200 text-blue-500 focus:ring-blue-500 ring-offset-0">
                        </th>
                        <th class="pb-5 px-4 text-left">Guest Name</th>
                        <th class="pb-5 px-4 text-left">Room Number</th>
                        <th class="pb-5 px-4 text-left">Total Guest</th>
                        <th class="pb-5 px-4 text-left">Check In</th>
                        <th class="pb-5 px-4 text-left">Check Out</th>
                        <th class="pb-5 px-4 text-left">Contact Number</th>
                        <th class="pb-5 px-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($recentBookings as $booking)
                    <tr class="group hover:bg-slate-50/50 transition-colors">
                        <td class="py-6 px-4"><input type="checkbox"
                                class="rounded-md border-slate-200 text-blue-500 focus:ring-blue-500 ring-offset-0">
                        </td>
                        <td class="py-6 px-4">
                            <span class="font-black text-slate-800 text-sm tracking-tight">{{ $booking->customer_name }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="px-4 py-1.5 bg-orange-50 text-orange-600 rounded-xl text-xs font-black">{{ $booking->tableModel->code ?? 'N/A' }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-sm font-black text-slate-500">{{ $booking->pax }}</span>
                        </td>
                        <td class="py-6 px-4">
                            <div class="px-4 py-1.5 bg-slate-50 inline-block rounded-xl border border-slate-100/50">
                                <span class="text-[11px] font-black text-slate-400">{{ $booking->start_time ? $booking->start_time->format('d M, Y') : '-' }}</span>
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            <div class="px-4 py-1.5 bg-slate-50 inline-block rounded-xl border border-slate-100/50">
                                <span class="text-[11px] font-black text-slate-400">{{ $booking->end_time ? $booking->end_time->format('d M, Y') : '-' }}</span>
                            </div>
                        </td>
                        <td class="py-6 px-4">
                            <span class="text-[11px] font-black text-slate-400">{{ $booking->phone }}</span>
                        </td>
                        <td class="py-6 px-4 text-right">
                            @php
                                $status = strtolower($booking->status);
                                $color = 'bg-slate-100 text-slate-600';
                                if (in_array($status, ['confirmed', 'arrived', 'occupied'])) {
                                    $color = 'bg-emerald-100 text-emerald-700';
                                } elseif ($status === 'cancelled' || $status === 'no show') {
                                    $color = 'bg-rose-100 text-rose-700';
                                } elseif ($status === 'pending') {
                                    $color = 'bg-amber-100 text-amber-700';
                                }
                            @endphp
                            <span
                                class="px-4 py-2 {{ $color }} rounded-full text-[9px] font-black uppercase tracking-widest">{{ $booking->status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <i data-lucide="calendar-x" class="w-12 h-12 text-slate-200"></i>
                                <p class="text-slate-400 font-bold">No bookings found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection