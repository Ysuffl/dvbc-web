@extends('layouts.admin')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Premium Header -->
    <div class="relative overflow-hidden bg-slate-900 rounded-[2.5rem] p-10 shadow-2xl shadow-slate-200">
        <!-- Abstract Background Shapes -->
        <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500/10 rounded-xl mb-4 border border-blue-500/20 backdrop-blur-sm">
                    <i data-lucide="shield-check" class="w-4 h-4 text-blue-400"></i>
                    <span class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em]">Verified CRM</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-white tracking-tighter mb-2">
                    Customer <span class="text-blue-500">Database</span>
                </h1>
                <p class="text-slate-400 font-medium max-w-md">Manage your guest relationships, track visit history, and optimize your service quality.</p>
            </div>

            <!-- Stats Quick View -->
            <div class="flex gap-4">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-3xl min-w-[140px] text-center">
                    <p class="text-2xl font-black text-white leading-none mb-1">{{ $customers->total() }}</p>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Total Guests</p>
                </div>
                <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-3xl min-w-[140px] text-center">
                    <p class="text-2xl font-black text-emerald-400 leading-none mb-1">Rp {{ number_format($lifetimeRevenue / 1000000, 1) }}M</p>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Lifetime Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking List Card -->
    <div class="bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-50/50 overflow-hidden">
        <div class="p-10">
            <div class="flex flex-col xl:flex-row justify-between items-center mb-10 gap-6 w-full">
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Active Relationships</h2>
                    <button onclick="exportToExcel()" 
                       class="px-4 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all border border-emerald-100 shadow-sm hover:-translate-y-0.5">
                        <i data-lucide="download-cloud" class="w-3.5 h-3.5"></i> Export XLSX
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto overflow-y-visible">
                <table class="w-full text-left border-separate border-spacing-y-4 -mt-4">
                    <thead>
                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                            <th class="pb-5 px-4 text-left">Guest Name</th>
                            <th class="pb-5 px-4 text-left">Category</th>
                            <th class="pb-5 px-4 text-left">Last Visit</th>
                            <th class="pb-5 px-4 text-center">Total Visits</th>
                            <th class="pb-5 px-4 text-right">Total Spend</th>
                            <th class="pb-5 px-4 text-left">Contact Info</th>
                            <th class="pb-5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $c)
                        @php
                            $latest = $c->bookings->first();
                            $cat = strtoupper($c->category ?? 'REGULAR');
                            $catColor = 'bg-slate-50 text-slate-400';
                            $catIcon = 'user';
                            if ($cat === 'PRIORITY') { $catColor = 'bg-amber-50 text-amber-600'; $catIcon = 'crown'; }
                            elseif ($cat === 'EVENT') { $catColor = 'bg-indigo-50 text-indigo-600'; $catIcon = 'megaphone'; }
                            elseif ($cat === 'BIG SPENDER') { $catColor = 'bg-emerald-50 text-emerald-600'; $catIcon = 'dollar-sign'; }
                            elseif ($cat === 'DRINKER') { $catColor = 'bg-blue-50 text-blue-600'; $catIcon = 'glass-water'; }
                            elseif ($cat === 'PARTY') { $catColor = 'bg-purple-50 text-purple-600'; $catIcon = 'sparkles'; }
                            elseif ($cat === 'DINNER') { $catColor = 'bg-orange-50 text-orange-600'; $catIcon = 'utensils-crossed'; }
                            elseif ($cat === 'LUNCH') { $catColor = 'bg-rose-50 text-rose-600'; $catIcon = 'utensils'; }
                            elseif ($cat === 'FAMILY') { $catColor = 'bg-cyan-50 text-cyan-600'; $catIcon = 'users'; }
                            elseif ($cat === 'YOUNGSTER') { $catColor = 'bg-pink-50 text-pink-600'; $catIcon = 'smile'; }
                        @endphp
                        <tr class="group hover:scale-[1.005] hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300">
                            <!-- Guest Name -->
                            <td class="py-6 px-4 bg-slate-50/50 group-hover:bg-white rounded-l-[1.25rem] border-y border-l border-slate-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center rounded-2xl text-slate-600 font-black text-sm uppercase ring-4 ring-white shadow-sm">
                                        {{ substr($c->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-800 text-sm tracking-tight capitalize">{{ $c->name }}</h4>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Verified Account</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="py-6 px-4 bg-slate-50/50 group-hover:bg-white border-y border-slate-50 transition-colors">
                                <span class="px-3 py-1.5 {{ $catColor }} rounded-lg text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 w-fit">
                                    <i data-lucide="{{ $catIcon }}" class="w-3 h-3"></i>
                                    {{ $cat }}
                                </span>
                            </td>

                            <!-- Last Visit -->
                            <td class="py-6 px-4 bg-slate-50/50 group-hover:bg-white border-y border-slate-50 transition-colors">
                                @if($latest)
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-1.5 text-slate-600 font-black text-sm tracking-tighter">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-blue-500"></i>
                                            {{ $latest->tableModel->code ?? 'N/A' }}
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-bold">{{ $latest->start_time->format('d M, Y') }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-300 italic font-medium">Never visited</span>
                                @endif
                            </td>

                            <!-- Total Visits -->
                            <td class="py-6 px-4 bg-slate-50/50 group-hover:bg-white border-y border-slate-50 transition-colors text-center">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-white rounded-lg border border-slate-100 shadow-sm">
                                    <span class="text-sm font-black text-slate-700 leading-none">{{ $c->bookings_count }}</span>
                                    <i data-lucide="award" class="w-3.5 h-3.5 text-blue-500"></i>
                                </div>
                            </td>
                            <!-- Total Spend -->
                            <td class="py-6 px-4 bg-slate-50/50 group-hover:bg-white border-y border-slate-50 transition-colors text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-sm font-black text-emerald-600 tracking-tighter">
                                        Rp {{ number_format($c->total_spent ?? 0, 0, ',', '.') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Aggregate</span>
                                </div>
                            </td>

                            <!-- Contact Info -->
                            <td class="py-6 px-4 bg-slate-50/50 group-hover:bg-white border-y border-slate-50 transition-colors">
                                <div class="flex items-center gap-2 text-slate-500 hover:text-blue-600 transition-colors font-bold text-xs ring-offset-2">
                                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                    {{ $c->phone ?: '---' }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-6 px-4 bg-slate-50/50 group-hover:bg-white rounded-r-[1.25rem] border-y border-r border-slate-50 transition-colors text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($c->phone)
                                        @php
                                            $waNumber = preg_replace('/[^0-9]/', '', $c->phone);
                                            if (substr($waNumber, 0, 1) === '0') {
                                                $waNumber = '62' . substr($waNumber, 1);
                                            }
                                            $waMsg = urlencode("Hello {$c->name}, this is DreamVille calling! We hope you had a great experience with us. Hope to see you soon!");
                                        @endphp
                                        <a href="https://wa.me/{{ $waNumber }}?text={{ $waMsg }}" target="_blank"
                                           class="p-2.5 bg-green-500 text-white hover:bg-green-600 rounded-xl transition-all shadow-lg shadow-green-200 group-hover:scale-110 active:scale-95">
                                            <i data-lucide="phone-call" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                    <button class="p-2.5 bg-white border border-slate-200 text-slate-400 hover:text-blue-500 hover:border-blue-200 rounded-xl transition-all group-hover:scale-110 active:scale-95">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $customers->links() }}
            </div>
        </div>
    </div>

    <!-- Hidden Export Table -->
    <div style="display: none;">
        <table id="all-customers-export">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Category</th>
                    <th>Total Visits</th>
                    <th>Total Spend (IDR)</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allCustomersForExport as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ strtoupper($customer->category ?? 'REGULER') }}</td>
                    <td>{{ $customer->bookings_count }}</td>
                    <td>{{ $customer->total_spent ?? 0 }}</td>
                    <td>{{ $customer->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @section('scripts')
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script>
        function exportToExcel() {
            try {
                const table = document.getElementById('all-customers-export');
                const wb = XLSX.utils.table_to_book(table, { sheet: "Verified Guests" });
                
                // Get current date for filename
                const date = new Date().toISOString().split('T')[0];
                const filename = `DreamVille_Guests_${date}.xlsx`;
                
                XLSX.writeFile(wb, filename);
            } catch (error) {
                console.error('Export failed:', error);
                alert('Failed to export XLSX. Please try again.');
            }
        }
    </script>
    @endsection
@endsection