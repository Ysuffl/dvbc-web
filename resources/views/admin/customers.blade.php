@extends('layouts.admin')

@section('content')
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
@endphp
<div class="space-y-8 animate-in fade-in duration-700" 
     x-data="{ 
        showProgressModal: false, 
        showEditModal: false,
        selectedCust: {}, 
        editCust: { id: '', name: '', phone: '', gender: '', age: '' },
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
    <div class="relative overflow-hidden bg-stone-900 rounded-xl p-10 shadow-2xl">
        <!-- Abstract Background Shapes -->
        <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-brand-primary/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary/10 rounded-lg mb-4 border border-brand-primary/20 backdrop-blur-sm">
                    <i data-lucide="shield-check" class="w-4 h-4 text-brand-primary"></i>
                    <span class="text-[10px] font-extrabold text-brand-primary uppercase tracking-widest">Verified CRM</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tighter mb-2 uppercase">
                    Customer <span class="text-brand-primary">Database</span>
                </h1>
                <p class="text-stone-400 font-bold max-w-md text-sm">Manage your guest relationships, track visit history, and optimize your service quality.</p>
            </div>

            <!-- Stats Quick View -->
            <div class="flex gap-4">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-xl min-w-[140px] text-center">
                    <p class="text-2xl font-extrabold text-white leading-none mb-2 tabular-nums">{{ $customers->total() }}</p>
                    <p class="text-[10px] font-extrabold text-stone-500 uppercase tracking-widest">Total Guests</p>
                </div>
                <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-xl min-w-[140px] text-center">
                    <p class="text-2xl font-extrabold text-brand-primary leading-none mb-2 tabular-nums">Rp {{ number_format($lifetimeRevenue / 1000000, 1) }}M</p>
                    <p class="text-[10px] font-extrabold text-stone-500 uppercase tracking-widest">Lifetime Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking List Card -->
    <div class="bg-white rounded-xl shadow-md border border-stone-200 overflow-hidden mt-6">
        <div class="p-8">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                <h2 class="text-xl font-extrabold text-stone-900 tracking-tight uppercase">Active Relationships</h2>
                
                <div class="flex items-center gap-3">
                    <!-- Export Button -->
                    <a href="{{ route('customers.export') }}" 
                       class="px-4 py-2.5 bg-brand-light text-brand-primary hover:opacity-90 rounded-lg text-[10px] font-extrabold uppercase tracking-widest flex items-center gap-2 transition-all border border-brand-primary/20 shadow-sm">
                        <i data-lucide="download-cloud" class="w-3.5 h-3.5"></i> Export XLSX
                    </a>

                    <!-- Import Button -->
                    <label class="px-4 py-2.5 bg-stone-900 text-white hover:opacity-90 rounded-lg text-[10px] font-extrabold uppercase tracking-widest flex items-center gap-2 transition-all shadow-sm cursor-pointer">
                        <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                        <span>Import XLSX</span>
                        <input type="file" id="importFile" class="hidden" accept=".xlsx, .xls, .csv" onchange="handleImport(event)">
                    </label>

                    <!-- Loading Indicator for Import -->
                    <div id="importLoading" class="hidden flex items-center gap-2 px-3 py-2 bg-stone-100 rounded-lg">
                        <div class="w-3 h-3 border-2 border-brand-primary border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-[10px] font-extrabold text-stone-500 uppercase tracking-widest">Processing...</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto overflow-y-visible pt-2">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[10px] font-extrabold text-stone-500 uppercase tracking-widest bg-stone-50">
                            <th class="py-3 px-4 text-left rounded-l-lg border-y border-l border-stone-200">Guest Name</th>
                            <th class="py-3 px-4 text-left border-y border-stone-200">Level</th>
                            <th class="py-3 px-4 text-left border-y border-stone-200">Gender</th>
                            <th class="py-3 px-4 text-left border-y border-stone-200">Last Visit</th>
                            <th class="py-3 px-4 text-left border-y border-stone-200">Tags</th>
                            <th class="py-3 px-4 text-center border-y border-stone-200">Visits</th>
                            <th class="py-3 px-4 text-right border-y border-stone-200">Total Spend</th>
                            <th class="py-3 px-4 text-left border-y border-stone-200">Contact</th>
                            <th class="py-3 px-4 text-right rounded-r-lg border-y border-r border-stone-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $c)
                        @php
                            $latest = $c->bookings->first();
                            // Level badge: ambil dari master_levels.badge_color (bukan hardcode)
                            $levelName  = $c->masterLevel->name ?? 'Bronze';
                            $levelColor = $c->masterLevel->badge_color ?? 'bg-orange-100 text-orange-800';
                            $catIconMap = ['Bronze'=>'shield','Silver'=>'star','Gold'=>'award','Platinum'=>'crown'];
                            $catIcon    = $catIconMap[$levelName] ?? 'award';
                        @endphp
                        <tr class="group hover:shadow-lg transition-all duration-300">
                            <!-- Guest Name -->
                            <td class="py-5 px-4 bg-stone-50/50 group-hover:bg-white rounded-l-xl border-y border-l border-stone-100 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-brand-light flex items-center justify-center rounded-lg text-brand-primary font-extrabold text-sm uppercase ring-2 ring-transparent group-hover:ring-brand-primary/20 transition-all shadow-sm">
                                        {{ substr($c->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h4 class="font-extrabold text-stone-900 text-sm tracking-tight uppercase">{{ $c->name }}</h4>
                                        <p class="text-[9px] text-stone-400 font-extrabold uppercase tracking-widest mt-1">{{ $c->gender ?: 'Not Set' }} • {{ $c->age ? $c->age.' Thn' : '---' }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Level -->
                            @php
                                $isTailwind = str_contains($levelColor, 'bg-');
                                $txtColor = getContrast($levelColor);
                                $style = $isTailwind ? '' : "background: {$levelColor}; color: {$txtColor}; text-shadow: 0 1px 2px rgba(0,0,0,.1)";
                            @endphp
                            <td class="py-5 px-4 bg-stone-50/50 group-hover:bg-white border-y border-stone-100 transition-colors">
                                <span class="px-2 py-1 {{ $isTailwind ? $levelColor : '' }} rounded text-[9px] font-extrabold uppercase tracking-widest flex items-center gap-1.5 w-fit"
                                      style="{{ $style }}">
                                    <i data-lucide="{{ $catIcon }}" class="w-3 h-3"></i>
                                    {{ $levelName }}
                                </span>
                            </td>

                            <!-- Gender -->
                            <td class="py-5 px-4 bg-stone-50/50 group-hover:bg-white border-y border-stone-100 transition-colors">
                                <span class="text-[10px] font-extrabold text-stone-600 uppercase tracking-widest">
                                    {{ $c->gender ?: '---' }}
                                </span>
                            </td>

                            <!-- Last Visit -->
                            <td class="py-5 px-4 bg-stone-50/50 group-hover:bg-white border-y border-stone-100 transition-colors">
                                @if($latest)
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-1.5 text-stone-900 font-extrabold text-[11px] tracking-widest uppercase">
                                            <i data-lucide="map-pin" class="w-3 h-3 text-brand-primary"></i>
                                            {{ $latest->tableModel->code ?? 'N/A' }}
                                        </div>
                                        <span class="text-[9px] text-stone-400 font-extrabold uppercase tracking-widest">{{ $latest->start_time->format('d M, Y') }}</span>
                                    </div>
                                @else
                                    <span class="text-[9px] text-stone-300 font-extrabold uppercase tracking-widest">Never visited</span>
                                @endif
                            </td>

                            <!-- Frequent Tags -->
                            <td class="py-5 px-4 bg-stone-50/50 group-hover:bg-white border-y border-stone-100 transition-colors">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        // Gunakan data dari bulk query (tidak ada N+1 lagi)
                                        $topTags = $rawTopTags[$c->id] ?? collect();
                                    @endphp
                                    @if($topTags->isNotEmpty())
                                        @foreach($topTags as $tag)
                                            <span class="px-2 py-0.5 bg-stone-100 text-stone-600 rounded text-[9px] font-extrabold uppercase tracking-widest border border-stone-200">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-[9px] text-stone-300 font-extrabold uppercase tracking-widest">No Tags</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Total Visits -->
                            <td class="py-5 px-4 bg-stone-50/50 group-hover:bg-white border-y border-stone-100 transition-colors text-center">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-white rounded-lg border border-stone-200 shadow-sm">
                                    <span class="text-xs font-extrabold text-stone-900 leading-none tabular-nums">{{ $c->total_combined_visits ?? 0 }}</span>
                                    <i data-lucide="award" class="w-3 h-3 text-brand-primary"></i>
                                </div>
                            </td>
                            <!-- Total Spend -->
                            <td class="py-5 px-4 bg-stone-50/50 group-hover:bg-white border-y border-stone-100 transition-colors text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-xs font-extrabold text-brand-primary tracking-widest tabular-nums uppercase">
                                        Rp {{ number_format(($c->total_spending ?? 0) + ($c->total_spent ?? 0), 0, ',', '.') }}
                                    </span>
                                    <span class="text-[9px] text-stone-400 font-extrabold uppercase tracking-widest mt-1">Aggregate</span>
                                </div>
                            </td>

                            <!-- Contact Info -->
                            <td class="py-5 px-4 bg-stone-50/50 group-hover:bg-white border-y border-stone-100 transition-colors">
                                <div class="flex items-center gap-2 text-stone-500 hover:text-stone-900 transition-colors font-extrabold text-[10px] tracking-widest">
                                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                    {{ $c->phone ?: '---' }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-5 px-4 bg-stone-50/50 group-hover:bg-white rounded-r-xl border-y border-r border-stone-100 transition-colors text-right">
                                <div class="flex items-center justify-end gap-2 text-stone-400">
                                    @if($c->phone)
                                        @php
                                            $waNumber = preg_replace('/[^0-9]/', '', $c->phone);
                                            if (substr($waNumber, 0, 1) === '0') {
                                                $waNumber = '62' . substr($waNumber, 1);
                                            }
                                            $waMsg = urlencode("Hello {$c->name}, this is DreamVille calling! We hope you had a great experience with us. Hope to see you soon!");
                                        @endphp
                                        <a href="https://wa.me/{{ $waNumber }}?text={{ $waMsg }}" target="_blank"
                                           class="p-2 bg-stone-900 text-brand-light hover:opacity-90 rounded-lg transition-all shadow-md group-hover:scale-105 active:scale-95">
                                            <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                                        </a>
                                    @endif
                                    <button 
                                        @click="editCust = { 
                                            id: '{{ $c->id }}',
                                            name: '{{ $c->name }}', 
                                            phone: '{{ $c->phone }}',
                                            gender: '{{ $c->gender }}',
                                            age: '{{ $c->age }}'
                                        }; showEditModal = true; $nextTick(() => lucide.createIcons())"
                                        class="p-2 bg-white border border-stone-200 text-stone-400 hover:text-stone-900 hover:border-stone-300 rounded-lg transition-all group-hover:scale-105 active:scale-95 shadow-sm">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <button 
                                        @click="selectedCust = { 
                                            name: '{{ $c->name }}', 
                                            level: '{{ $levelName }}', 
                                            total_spent: {{ $c->total_spent ?? 0 }}, 
                                            visits: {{ $c->visits_count ?? 0 }},
                                            phone: '{{ $c->phone }}',
                                            last_visit: '{{ $latest ? $latest->start_time->format('d M, Y') : 'Never' }}'
                                        }; showProgressModal = true; $nextTick(() => lucide.createIcons())"
                                        class="p-2 bg-white border border-stone-200 text-stone-400 hover:text-brand-primary hover:border-brand-primary rounded-lg transition-all group-hover:scale-105 active:scale-95 shadow-sm">
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
                <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

            <div x-show="showProgressModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-stone-200">
                
                <div class="p-8 sm:p-10">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h3 class="text-2xl font-extrabold text-stone-900 tracking-tight uppercase" x-text="selectedCust.name"></h3>
                            <p class="text-stone-400 font-extrabold text-[10px] uppercase tracking-widest mt-1" x-text="selectedCust.phone"></p>
                        </div>
                        <button @click="showProgressModal = false" class="p-2 bg-stone-50 text-stone-400 hover:text-stone-900 rounded-lg transition-all border border-stone-200">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Visual Progress Card -->
                    <div class="bg-stone-50 border border-stone-200 rounded-xl p-8 relative overflow-hidden">
                        <!-- Header inside card -->
                        <div class="flex justify-between items-start mb-10 border-b border-stone-200 pb-6">
                            <div class="space-y-1">
                                <p class="text-[9px] font-extrabold text-stone-500 uppercase tracking-widest">Status Anda</p>
                                <h4 class="text-lg font-extrabold text-brand-primary tracking-tight uppercase" x-text="'VIP ' + selectedCust.level"></h4>
                                <p class="text-stone-900 font-extrabold text-[10px] uppercase tracking-widest" x-text="'Terakhir berkunjung: ' + selectedCust.last_visit"></p>
                            </div>
                            <div class="text-right space-y-1">
                                <p class="text-[9px] font-extrabold text-stone-500 uppercase tracking-widest">Total</p>
                                <p class="text-2xl font-extrabold text-stone-900 tracking-tight tabular-nums" x-text="selectedCust.visits"></p>
                                <p class="text-stone-400 font-extrabold text-[9px] uppercase tracking-widest">Kunjungan</p>
                            </div>
                        </div>

                        <!-- The Progress Roadmap -->
                        <!-- Adjust colors -->
                        <div class="relative pt-6 pb-12 px-4">
                            <!-- Progress Label -->
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2 flex flex-col items-center">
                                <span class="text-[10px] font-extrabold text-stone-900 mb-1 uppercase tracking-widest bg-white px-3 py-1 rounded border border-stone-200 shadow-sm tabular-nums"
                                      x-text="'Rp ' + (selectedCust.total_spent || 0).toLocaleString() + ' / Rp ' + 
                                              (parseFloat(levels.find(l => parseFloat(l.min_spending) > parseFloat(selectedCust.total_spent))?.min_spending || levels[levels.length-1].min_spending)).toLocaleString()">
                                </span>
                            </div>

                            <!-- roadmap container -->
                            <div class="relative pt-8">
                                <div class="h-3 w-full bg-stone-200 rounded-full flex gap-0.5 overflow-hidden">
                                    <template x-for="i in 10">
                                        <div class="flex-1 rounded-sm transition-all duration-700"
                                             :class="progressPercentage >= (i * 10) ? 'bg-brand-primary' : 'bg-transparent'"></div>
                                    </template>
                                </div>
                                
                                <div class="absolute top-1/2 -translate-y-1/2 left-0 right-0 flex justify-between px-0">
                                    <template x-for="(lvl, index) in levels" :key="index">
                                        <div class="relative flex flex-col items-center group">
                                            <!-- Node Circle -->
                                            <div class="w-8 h-8 rounded-full border-[2px] flex items-center justify-center transition-all duration-500 z-10 shadow-sm"
                                                 :style="(parseFloat(selectedCust.total_spent) >= parseFloat(lvl.min_spending) && !lvl.badge_color.includes('bg-')) ? 'background:' + lvl.badge_color + '; border-color: white' : ''"
                                                 :class="parseFloat(selectedCust.total_spent) >= parseFloat(lvl.min_spending) 
                                                         ? (lvl.badge_color.includes('bg-') ? lvl.badge_color + ' border-white text-white' : 'text-white bg-brand-primary border-brand-primary') 
                                                         : 'bg-stone-50 border-stone-300 text-stone-300'">
                                                <i data-lucide="star" class="w-3.5 h-3.5" :class="parseFloat(selectedCust.total_spent) >= parseFloat(lvl.min_spending) ? 'fill-current' : ''"></i>
                                            </div>
                                            <!-- Node Label -->
                                            <p class="absolute top-10 text-[9px] font-extrabold uppercase tracking-widest whitespace-nowrap text-center"
                                               :class="parseFloat(selectedCust.total_spent) >= parseFloat(lvl.min_spending) ? 'text-stone-900' : 'text-stone-400'"
                                               x-text="(lvl.name === 'Bronze' ? '' : 'VIP ') + lvl.name"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Info -->
                        <div class="mt-8 pt-8 border-t border-stone-200 flex items-center justify-center gap-2">
                             <div class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></div>
                             <p class="text-[10px] font-extrabold text-brand-primary uppercase tracking-widest">Akun Terverifikasi</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button @click="showProgressModal = false" class="w-full py-4 bg-stone-100 text-stone-500 rounded-lg text-xs font-extrabold hover:bg-stone-200 transition-all uppercase tracking-widest leading-none border border-stone-200">
                            Tutup Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showEditModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity" @click="showEditModal = false">
                <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

            <div x-show="showEditModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-stone-200">
                
                <form :action="'/customers/' + editCust.id" method="POST" class="p-8 sm:p-10">
                    @csrf
                    @method('PUT')
                    
                    <div class="flex justify-between items-start mb-8 border-b border-stone-100 pb-4">
                        <div>
                            <h3 class="text-lg font-extrabold text-stone-900 tracking-widest uppercase">Edit Customer</h3>
                            <p class="text-stone-400 font-extrabold text-[10px] mt-1 tracking-widest uppercase">Update guest profile information</p>
                        </div>
                        <button type="button" @click="showEditModal = false" class="p-2 bg-stone-50 text-stone-400 hover:text-stone-900 rounded-lg border border-stone-200 transition-all">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="space-y-5">
                        <!-- Name -->
                        <div class="space-y-2">
                            <label class="text-[9px] font-extrabold text-stone-500 uppercase tracking-widest ml-1">Full Name</label>
                            <input type="text" name="name" x-model="editCust.name" required
                                   class="w-full bg-stone-50 border border-stone-200 rounded-lg px-4 py-3.5 text-stone-900 font-extrabold text-xs focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-widest">
                        </div>

                        <!-- Phone -->
                        <div class="space-y-2">
                            <label class="text-[9px] font-extrabold text-stone-500 uppercase tracking-widest ml-1">Phone Number</label>
                            <input type="text" name="phone" x-model="editCust.phone"
                                   class="w-full bg-stone-50 border border-stone-200 rounded-lg px-4 py-3.5 text-stone-900 font-extrabold text-xs focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-widest tabular-nums">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Gender -->
                            <div class="space-y-2">
                                <label class="text-[9px] font-extrabold text-stone-500 uppercase tracking-widest ml-1">Gender</label>
                                <select name="gender" x-model="editCust.gender"
                                        class="w-full bg-stone-50 border border-stone-200 rounded-lg px-4 py-3.5 text-stone-900 font-extrabold text-xs focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-widest">
                                    <option value="">Not Set</option>
                                    <option value="MALE">Laki-laki</option>
                                    <option value="FEMALE">Perempuan</option>
                                </select>
                            </div>

                            <!-- Age -->
                            <div class="space-y-2">
                                <label class="text-[9px] font-extrabold text-stone-500 uppercase tracking-widest ml-1">Age (Years)</label>
                                <input type="number" name="age" x-model="editCust.age"
                                       class="w-full bg-stone-50 border border-stone-200 rounded-lg px-4 py-3.5 text-stone-900 font-extrabold text-xs focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary transition-all uppercase tracking-widest tabular-nums">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button type="button" @click="showEditModal = false" 
                                class="flex-1 py-3 bg-stone-100 text-stone-500 border border-stone-200 rounded-lg text-[10px] font-extrabold hover:bg-stone-200 transition-all uppercase tracking-widest">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-[2] py-3 bg-brand-primary text-white rounded-lg text-[10px] font-extrabold hover:opacity-90 transition-all uppercase tracking-widest shadow-lg">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @section('scripts')

    <script>
        function handleImport(event) {
            const file = event.target.files[0];
            if (!file) return;

            const loading = document.getElementById('importLoading');
            loading.classList.remove('hidden');

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheet = workbook.SheetNames[0];
                    const jsonData = XLSX.utils.sheet_to_json(workbook.Sheets[firstSheet]);

                    // Send to server
                    fetch("{{ route('customers.import') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ data: jsonData })
                    })
                    .then(res => res.json())
                    .then(res => {
                        loading.classList.add('hidden');
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Import Success',
                                text: 'Data customer berhasil diimpor.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Import Failed',
                                text: res.message,
                                background: '#ffffff',
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        loading.classList.add('hidden');
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Terjadi kesalahan saat menghubungi server.',
                            background: '#ffffff',
                        });
                    });
                } catch (error) {
                    console.error('Parsing failed:', error);
                    loading.classList.add('hidden');
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File',
                        text: 'Gagal membaca file Excel. Pastikan format benar.',
                        background: '#ffffff',
                    });
                }
            };
            reader.readAsArrayBuffer(file);
        }
    </script>
    @endsection
@endsection