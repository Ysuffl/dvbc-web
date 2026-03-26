@extends('layouts.admin')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700" 
     x-data="{ 
        showProgressModal: false, 
        selectedCust: {}, 
        levels: {{ $levels->toJson() }},
        get progressPercentage() {
            if (!this.selectedCust.total_spent) return 0;
            const spent = parseFloat(this.selectedCust.total_spent);
            const maxLevel = this.levels[this.levels.length - 1];
            if (spent >= maxLevel.min_spending) return 100;
            
            // Find current segment
            for(let i=0; i < this.levels.length - 1; i++) {
                const current = parseFloat(this.levels[i].min_spending);
                const next = parseFloat(this.levels[i+1].min_spending);
                if (spent >= current && spent < next) {
                    const segmentProgress = (spent - current) / (next - current);
                    return ((i + segmentProgress) / (this.levels.length - 1)) * 100;
                }
            }
            return 0;
        }
     }">
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
                            <th class="pb-5 px-4 text-left">Level</th>
                            <th class="pb-5 px-4 text-left">Gender</th>
                            <th class="pb-5 px-4 text-left">Last Visit</th>
                            <th class="pb-5 px-4 text-left">Frequent Tags</th>
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
                            $cat = strtoupper($c->masterLevel->name ?? 'BRONZE');
                            $catColor = 'bg-slate-50 text-slate-400';
                            $catIcon = 'award';
                            if ($cat === 'PLATINUM') { $catColor = 'bg-blue-100 text-blue-800'; $catIcon = 'crown'; }
                            elseif ($cat === 'GOLD') { $catColor = 'bg-yellow-200 text-yellow-800'; $catIcon = 'award'; }
                            elseif ($cat === 'SILVER') { $catColor = 'bg-slate-200 text-slate-800'; $catIcon = 'star'; }
                            elseif ($cat === 'BRONZE') { $catColor = 'bg-orange-100 text-orange-800'; $catIcon = 'shield'; }
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
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ $c->gender ?: 'Not Set' }} • {{ $c->age ? $c->age.' Thn' : '---' }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Level -->
                            <td class="py-6 px-4 bg-slate-50/50 group-hover:bg-white border-y border-slate-50 transition-colors">
                                <span class="px-3 py-1.5 {{ $catColor }} rounded-lg text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 w-fit">
                                    <i data-lucide="{{ $catIcon }}" class="w-3 h-3"></i>
                                    {{ $cat }}
                                </span>
                            </td>

                            <!-- Gender -->
                            <td class="py-6 px-4 bg-slate-50/50 group-hover:bg-white border-y border-slate-50 transition-colors">
                                <span class="text-xs font-bold text-slate-600">
                                    {{ $c->gender ?: '---' }}
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

                            <!-- Frequent Tags -->
                            <td class="py-6 px-4 bg-slate-50/50 group-hover:bg-white border-y border-slate-50 transition-colors">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $topTags = $c->topTags(3);
                                    @endphp
                                    @if(count($topTags) > 0)
                                        @foreach($topTags as $tag)
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-md text-[9px] font-black uppercase tracking-wider border border-blue-100/50">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-[10px] text-slate-300 font-bold uppercase tracking-widest italic">No Tags</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Total Visits -->
                            <td class="py-6 px-4 bg-slate-50/50 group-hover:bg-white border-y border-slate-50 transition-colors text-center">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-white rounded-lg border border-slate-100 shadow-sm">
                                    <span class="text-sm font-black text-slate-700 leading-none">{{ $c->visits_count ?? 0 }}</span>
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
                                    <button 
                                        @click="selectedCust = { 
                                            name: '{{ $c->name }}', 
                                            level: '{{ $cat }}', 
                                            total_spent: {{ $c->total_spent ?? 0 }}, 
                                            visits: {{ $c->visits_count ?? 0 }},
                                            phone: '{{ $c->phone }}',
                                            last_visit: '{{ $latest ? $latest->start_time->format('d M, Y') : 'Never' }}'
                                        }; showProgressModal = true; $nextTick(() => lucide.createIcons())"
                                        class="p-2.5 bg-white border border-slate-200 text-slate-400 hover:text-blue-500 hover:border-blue-200 rounded-xl transition-all group-hover:scale-110 active:scale-95">
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

    <!-- Progress Modal -->
    <div x-show="showProgressModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showProgressModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity" @click="showProgressModal = false">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

            <div x-show="showProgressModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                
                <div class="p-8 sm:p-12">
                    <div class="flex justify-between items-start mb-10">
                        <div>
                            <h3 class="text-3xl font-black text-slate-800 tracking-tighter" x-text="selectedCust.name"></h3>
                            <p class="text-slate-400 font-bold text-sm mt-1" x-text="selectedCust.phone"></p>
                        </div>
                        <button @click="showProgressModal = false" class="p-3 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-2xl transition-all">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <!-- Visual Progress Card -->
                    <div class="bg-white border border-slate-100 rounded-[2rem] p-8 shadow-[0_20px_50px_rgba(0,0,0,0.05)] relative overflow-hidden">
                        <!-- Header inside card -->
                        <div class="flex justify-between items-start mb-12 border-b border-slate-50 pb-8">
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status Anda</p>
                                <h4 class="text-2xl font-black text-slate-800 tracking-tight" x-text="'VIP ' + selectedCust.level"></h4>
                                <p class="text-blue-500 font-bold text-xs" x-text="'Terakhir berkunjung: ' + selectedCust.last_visit"></p>
                            </div>
                            <div class="text-right space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kunjungan</p>
                                <p class="text-3xl font-black text-slate-800 tracking-tight" x-text="selectedCust.visits"></p>
                                <p class="text-slate-400 font-bold text-[10px] uppercase">Kunjungan Selesai</p>
                            </div>
                        </div>

                        <!-- The Progress Roadmap -->
                        <div class="relative pt-12 pb-16 px-4">
                            <!-- Progress Label (mimicking '2 / 10 Kunjungan Selesai', but with spend) -->
                            <div class="absolute -top-2 left-1/2 -translate-x-1/2 flex flex-col items-center">
                                <span class="text-[11px] font-black text-slate-800 mb-1" 
                                      x-text="'Rp ' + (selectedCust.total_spent || 0).toLocaleString() + ' / ' + 
                                              (parseFloat(levels.find(l => parseFloat(l.min_spending) > parseFloat(selectedCust.total_spent))?.min_spending || levels[levels.length-1].min_spending)).toLocaleString() + ' Belanja Terkumpul'">
                                </span>
                            </div>

                            <!-- roadmap container -->
                            <div class="relative pt-10">
                                <!-- Background Bar (Segmented) -->
                                <div class="h-6 w-full bg-slate-100 rounded-lg flex gap-1 p-1 overflow-hidden">
                                    <template x-for="i in 10">
                                        <div class="flex-1 rounded-sm transition-all duration-700"
                                             :class="progressPercentage >= (i * 10) ? 'bg-amber-400' : 'bg-slate-200/50'"></div>
                                    </template>
                                </div>

                                <!-- Nodes on top of bar -->
                                <div class="absolute top-1/2 -translate-y-1/2 left-0 right-0 flex justify-between px-0">
                                    <template x-for="(lvl, index) in levels" :key="index">
                                        <div class="relative flex flex-col items-center group">
                                            <!-- Node Circle -->
                                            <div class="w-12 h-12 rounded-full border-[3px] flex items-center justify-center transition-all duration-500 z-10 shadow-lg"
                                                 :class="parseFloat(selectedCust.total_spent) >= parseFloat(lvl.min_spending) 
                                                         ? 'bg-slate-800 border-white text-white scale-110' 
                                                         : 'bg-white border-slate-200 text-slate-300'">
                                                <i data-lucide="star" class="w-5 h-5" :class="parseFloat(selectedCust.total_spent) >= parseFloat(lvl.min_spending) ? 'fill-amber-400 text-amber-400' : ''"></i>
                                            </div>
                                            <!-- Node Label -->
                                            <p class="absolute top-16 text-[10px] font-black uppercase tracking-widest whitespace-nowrap text-center"
                                               :class="parseFloat(selectedCust.total_spent) >= parseFloat(lvl.min_spending) ? 'text-slate-800' : 'text-slate-400'"
                                               x-text="(lvl.name === 'Bronze' ? '' : 'VIP ') + lvl.name"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Info -->
                        <div class="mt-8 pt-8 border-t border-slate-50 flex items-center justify-center gap-2">
                             <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                             <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Akun Terverifikasi</p>
                        </div>
                    </div>

                    <div class="mt-12">
                        <button @click="showProgressModal = false" class="w-full py-5 bg-slate-100 text-slate-500 rounded-3xl text-sm font-black hover:bg-slate-200 transition-all uppercase tracking-[0.2em] leading-none">
                            Tutup Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>
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