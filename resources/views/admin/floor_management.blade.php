@extends('layouts.admin')

@section('content')
<div x-data="floorManager()" x-init="init()">

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- HEADER --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-stone-900 flex items-center gap-3 uppercase tracking-tight">
            <div class="w-10 h-10 bg-brand-light rounded-lg flex items-center justify-center text-brand-primary">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            </div>
            Floor Management
        </h1>
        <p class="text-stone-500 text-[10px] font-bold mt-1 uppercase tracking-widest">Kelola area, meja, dan minimum cash secara real-time</p>
    </div>
    <div class="flex items-center gap-3">
        <button @click="showAddAreaModal = true"
            class="flex items-center gap-2 bg-brand-primary hover:opacity-90 active:scale-95 transition-all text-white font-extrabold py-2.5 px-5 rounded-lg shadow-lg text-[10px] uppercase tracking-widest">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Area
        </button>
        <button @click="showAddTableModal = true"
            class="flex items-center gap-2 bg-stone-900 hover:opacity-90 active:scale-95 transition-all text-white font-extrabold py-2.5 px-5 rounded-lg shadow-lg text-[10px] uppercase tracking-widest">
            <i data-lucide="table-2" class="w-4 h-4"></i> Add Table
        </button>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- AREA TABS --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2 border-b border-stone-200">
    @foreach($areas as $area)
    <a href="{{ route('floor.index', ['area' => $area->id]) }}"
        class="flex items-center gap-2 px-5 py-3 rounded-t-lg font-extrabold text-[10px] uppercase tracking-widest whitespace-nowrap transition-all border-b-2
               {{ $selectedAreaId == $area->id
                    ? 'border-brand-primary text-brand-primary bg-brand-light'
                    : 'border-transparent text-stone-500 hover:text-stone-900 hover:bg-stone-50' }}">
        <i data-lucide="layers" class="w-4 h-4"></i>
        {{ $area->name }}
        <span class="px-2 py-0.5 rounded text-[9px] {{ $selectedAreaId == $area->id ? 'bg-brand-primary text-white' : 'bg-stone-200 text-stone-600' }} ml-1">
            Lt.{{ $area->floor_number }}
        </span>
        {{-- Edit Area Button --}}
        <span @click.prevent="editArea({ id: {{ $area->id }}, name: '{{ addslashes($area->name) }}', description: '{{ addslashes($area->description ?? '') }}', floor_number: {{ $area->floor_number }} })"
              class="ml-1 rounded p-1 hover:bg-black/10 transition-colors cursor-pointer block">
            <i data-lucide="pencil" class="w-3 h-3"></i>
        </span>
    </a>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- TWO-PANEL LAYOUT: Canvas kiri, Table list kanan --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-col gap-6">

    {{-- ── CANVAS PANEL ─────────────────────────────────────────────── --}}
    <div class="w-full">
        <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 bg-stone-50/50">
                <span class="font-extrabold text-stone-900 text-xs flex items-center gap-2 uppercase tracking-widest">
                    <i data-lucide="move" class="w-4 h-4 text-brand-primary"></i>
                    Layout Canvas — <span class="text-brand-primary">{{ $selectedArea->name ?? 'No Area' }}</span>
                </span>
                <button id="saveLayoutBtn"
                    class="flex items-center gap-2 bg-stone-900 hover:opacity-90 active:scale-95 text-white text-[10px] font-extrabold py-2 px-4 rounded-lg transition-all uppercase tracking-widest">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i> Save Layout
                </button>
            </div>

            {{-- Canvas Container (Scrollable & Massive) --}}
            <div class="relative w-full h-[800px] bg-stone-50 overflow-auto border-t border-stone-100 rounded-b-xl shadow-inner scroll-smooth" id="canvas-container">
                {{-- Dotted Grid matches canvas size --}}
                <div class="absolute w-[3500px] h-[2500px] pointer-events-none opacity-30"
                     style="background-image: radial-gradient(#94a3b8 2px, transparent 2px); background-size: 30px 30px; z-index: 0;"></div>

                {{-- Canvas Area --}}
                <div class="relative w-[3500px] h-[2500px]" id="canvas">
                    @foreach($tables as $table)
                    <div class="draggable absolute flex flex-col items-center justify-center cursor-grab active:cursor-grabbing select-none group transition-all duration-200 shadow-md hover:shadow-xl hover:-translate-y-1"
                         id="table_{{ $table->id }}"
                         data-id="{{ $table->id }}"
                         @click="if(!isDragging) toggleSelect({{ $table->id }})"
                         :class="selectedTables.includes({{ $table->id }}) ? 'ring-4 ring-brand-primary/30 border-brand-primary' : ''"
                         style="left: {{ $table->x_pos }}px; top: {{ $table->y_pos }}px;
                                width: {{ $table->shape == 'circle' ? '120px' : '90px' }};
                                height: {{ $table->shape == 'circle' ? '120px' : '90px' }};
                                border-radius: {{ $table->shape == 'circle' ? '50%' : '8px' }};
                                background: {{ $table->status == 'available' ? 'linear-gradient(135deg, #ffffff 0%, #FAFAF9 100%)' : 'linear-gradient(135deg, #FFF8ED 0%, #FFEDD5 100%)' }};
                                border: 2px solid {{ $table->status == 'available' ? '#E7E5E4' : '#A68A56' }}; z-index: 1;">

                        {{-- Glow Effect on Hover --}}
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 rounded-[inherit] ring-4 ring-stone-900/10 transition-all pointer-events-none"></div>

                        {{-- Table Code --}}
                        <div class="text-[14px] font-extrabold tracking-tight {{ $table->status == 'available' ? 'text-stone-800' : 'text-brand-primary' }} leading-none drop-shadow-sm uppercase">
                            {{ $table->code }}
                        </div>
                        
                        {{-- Capacity --}}
                        <div class="flex items-center gap-1 mt-1 text-stone-400">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            <span class="text-[9px] font-extrabold">{{ $table->capacity }}</span>
                        </div>

                        {{-- Min Spending Badge --}}
                        @if($table->min_spending > 0)
                        <div class="absolute -bottom-3 px-2 py-0.5 rounded pl-1 pr-1 bg-stone-900 text-white text-[9px] font-extrabold whitespace-nowrap shadow-md z-10 transition-transform group-hover:scale-105 group-hover:-translate-y-1 block tracking-wider uppercase">
                            Rp {{ number_format($table->min_spending/1000, 0) }}k
                        </div>
                        @endif

                        {{-- Hover tooltip (Extended info) --}}
                        <div class="absolute -top-14 scale-0 group-hover:scale-100 transition-all origin-bottom bg-stone-900 text-white text-[10px] font-extrabold py-2 px-3 rounded-lg pointer-events-none whitespace-nowrap z-[100] shadow-xl uppercase tracking-widest">
                            <span class="text-brand-primary">{{ $table->code }}</span> • Cap: {{ $table->capacity }}
                            <div class="mt-1 text-stone-300 text-[9px]">Min: Rp{{ number_format($table->min_spending, 0, ',', '.') }}</div>
                            {{-- Tooltip caret --}}
                            <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 border-[5px] border-transparent border-t-stone-900"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── TABLE LIST (BOTTOM GRID) ───────────────────────────────────────── --}}
    <div class="w-full">
        <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden p-6">
            <h2 class="font-extrabold text-stone-900 text-sm mb-5 flex flex-wrap items-center justify-between gap-4 uppercase tracking-widest">
                <span class="flex items-center gap-2">
                    <i data-lucide="list-tree" class="w-5 h-5 text-brand-primary"></i>
                    Daftar Meja ({{ $tables->count() }})
                </span>

                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 px-3 py-1.5 bg-stone-50 border border-stone-200 rounded-lg cursor-pointer hover:bg-stone-100 transition-colors">
                        <input type="checkbox" x-model="allSelected" @change="toggleAll()" class="w-4 h-4 text-brand-primary border-stone-300 rounded focus:ring-brand-primary/20">
                        <span class="text-[10px] font-extrabold text-stone-600 uppercase tracking-widest">Select All</span>
                    </label>
                </div>
            </h2>

            @if($tables->isEmpty())
                <div class="p-10 text-center text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    <i data-lucide="table-2" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
                    <p class="font-bold text-sm">Belum ada meja di area ini</p>
                    <p class="text-xs mt-1">Klik "Add Table" di bagian atas untuk menambahkan meja baru.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                    @foreach($tables as $table)
                    <div class="group relative bg-white border border-stone-200 rounded-xl p-4 flex flex-col hover:border-brand-primary hover:shadow-lg transition-all">
                        {{-- Top Header --}}
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <label class="relative flex items-center justify-center cursor-pointer group/check">
                                    <input type="checkbox" :value="{{ $table->id }}" x-model="selectedTables"
                                           class="peer w-5 h-5 opacity-0 absolute">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-stone-50 border border-stone-200 peer-checked:bg-brand-primary peer-checked:border-brand-primary transition-all shrink-0">
                                        <i data-lucide="check" class="w-4 h-4 text-white hidden peer-checked:block"></i>
                                        <i data-lucide="{{ $table->shape == 'circle' ? 'circle' : 'square' }}"
                                           class="w-5 h-5 text-stone-300 peer-checked:hidden"></i>
                                    </div>
                                </label>
                                <div>
                                    <div class="font-extrabold text-stone-900 text-sm leading-tight uppercase tracking-wider">{{ $table->code }}</div>
                                    <div class="flex items-center gap-1 text-[10px] text-stone-400 font-extrabold mt-1 uppercase tracking-widest">
                                        <i data-lucide="users" class="w-3 h-3"></i> {{ $table->capacity }} kursi
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="editTable({{ $table->toJson() }})" class="p-1.5 rounded-md hover:bg-stone-100 text-stone-400 hover:text-brand-primary transition-colors">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                <form method="POST" action="{{ route('floor.table.destroy', $table->id) }}" data-confirm="Hapus meja {{ $table->code }}?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-md hover:bg-red-50 text-stone-400 hover:text-red-500 transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Bottom Quick Min Cash --}}
                        <div class="mt-auto pt-4 border-t border-stone-100">
                            <form method="POST" action="{{ route('floor.table.min_spending', $table->id) }}" class="flex items-center justify-between gap-2">
                                @csrf @method('PATCH')
                                <span class="text-[9px] text-stone-400 font-extrabold uppercase tracking-widest">Min Cash (Rp)</span>
                                <div class="flex items-center gap-1.5">
                                    <input type="hidden" name="min_spending" id="inline_min_{{ $table->id }}" value="{{ $table->min_spending }}">
                                    <input type="text" value="{{ $table->min_spending > 0 ? number_format($table->min_spending, 0, ',', '.') : '0' }}"
                                           oninput="let v = this.value.replace(/\D/g, ''); document.getElementById('inline_min_{{ $table->id }}').value = v || 0; this.value = v ? parseInt(v, 10).toLocaleString('id-ID') : '';"
                                           class="w-full max-w-[90px] text-[10px] text-right font-extrabold {{ $table->min_spending > 0 ? 'text-brand-primary bg-brand-light border-brand-primary/20' : 'text-stone-600 bg-stone-50 border-stone-200' }} border rounded text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 transition-colors tabular-nums">
                                    <button type="submit" class="p-1.5 bg-stone-900 text-white rounded hover:bg-brand-primary transition-colors shadow-sm" title="Update Minimum Cash">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div x-show="selectedTables.length > 0"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-y-20 opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-20 opacity-0"
     class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[60] w-full max-w-2xl px-4">
    <div class="bg-stone-900 shadow-2xl rounded-xl border border-stone-800 p-4 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-brand-primary rounded-lg flex items-center justify-center text-white font-black shadow-lg">
                <span x-text="selectedTables.length"></span>
            </div>
            <div>
                <div class="text-white font-extrabold text-xs tracking-widest uppercase">Meja Terpilih</div>
                <div class="text-stone-400 text-[10px] font-bold uppercase tracking-wider mt-1">Update harga secara massal</div>
            </div>
        </div>

        <form method="POST" action="{{ route('floor.table.bulk_min_spending') }}" class="flex items-center gap-3 w-full md:w-auto">
            @csrf @method('PATCH')
            <template x-for="id in selectedTables" :key="id">
                <input type="hidden" name="table_ids[]" :value="id">
            </template>
            
            <div class="relative flex-1 md:flex-none">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-extrabold text-stone-500 uppercase">Rp</span>
                <input type="hidden" name="min_spending" id="bulk_min_val">
                <input type="text" placeholder="SET MIN CASH" 
                       oninput="let v = this.value.replace(/\D/g, ''); document.getElementById('bulk_min_val').value = v || 0; this.value = v ? parseInt(v, 10).toLocaleString('id-ID') : '';"
                       class="bg-stone-800 text-white border-stone-700 rounded-lg pl-9 pr-3 py-2.5 text-xs font-extrabold w-full md:w-[160px] focus:ring-brand-primary focus:border-brand-primary outline-none transition-all uppercase tracking-widest tabular-nums placeholder:text-stone-500">
            </div>

            <button type="submit" class="bg-brand-primary hover:opacity-90 text-white font-extrabold text-[10px] py-3 px-6 rounded-lg transition-all shadow-lg whitespace-nowrap active:scale-95 uppercase tracking-widest">
                Apply Massal
            </button>
            <button type="button" @click="selectedTables = []; allSelected = false" class="text-stone-500 hover:text-white transition-colors p-2 bg-stone-800 rounded-lg ml-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: ADD / EDIT AREA --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div x-show="showAddAreaModal || showEditAreaModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-sm"
     x-transition x-cloak>
    <div class="bg-white rounded-2xl border border-stone-200 shadow-xl w-full max-w-md mx-4" @click.away="closeAreaModals()">
        <div class="px-6 py-5 border-b border-stone-100 flex items-center justify-between">
            <h3 class="font-extrabold text-stone-900 text-sm uppercase tracking-widest" x-text="showEditAreaModal ? 'Edit Area' : 'Tambah Area'"></h3>
            <button @click="closeAreaModals()" class="text-stone-400 hover:text-stone-600 transition-colors w-8 h-8 flex items-center justify-center bg-stone-50 rounded-lg">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form :action="showEditAreaModal ? '/floor/areas/' + editAreaData.id : '{{ route('floor.area.store') }}'"
              method="POST" class="p-6 space-y-4">
            @csrf
            <template x-if="showEditAreaModal"><input type="hidden" name="_method" value="PUT"></template>
            <div>
                <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Nama Area *</label>
                <input type="text" name="name" :value="editAreaData.name" required placeholder="VIP ROOM, OUTDOOR"
                       class="w-full border border-stone-200 rounded-lg px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary uppercase tracking-wider bg-stone-50">
            </div>
            <div>
                <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Deskripsi</label>
                <textarea name="description" :value="editAreaData.description" rows="2" placeholder="KETERANGAN OPSIONAL"
                          class="w-full border border-stone-200 rounded-lg px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary resize-none uppercase tracking-wider bg-stone-50"></textarea>
            </div>
            <div>
                <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Lantai *</label>
                <input type="number" name="floor_number" :value="editAreaData.floor_number || 1" min="1" max="99" required
                       class="w-full border border-stone-200 rounded-lg px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary uppercase tracking-wider bg-stone-50 tabular-nums">
            </div>
            <div class="flex flex-col gap-3 pt-2">
                <div class="flex gap-3">
                    <button type="button" @click="closeAreaModals()"
                            class="flex-1 py-3.5 rounded-lg border border-stone-200 text-xs font-extrabold text-stone-600 hover:bg-stone-50 transition-colors uppercase tracking-widest">Batal</button>
                    <button type="submit"
                            class="flex-1 py-3.5 rounded-lg bg-brand-primary text-white text-xs font-extrabold hover:opacity-90 transition-all shadow-lg uppercase tracking-widest">
                        <span x-text="showEditAreaModal ? 'Simpan' : 'Tambah'"></span>
                    </button>
                </div>
                
                <template x-if="showEditAreaModal">
                    <button type="button" 
                            @click="Swal.fire({
                                title: 'Hapus Area?',
                                text: 'Seluruh data layout area ini akan hilang permanen.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Hapus',
                                background: '#ffffff',
                                customClass: {
                                    confirmButton: 'bg-red-500 px-8 py-3 rounded-lg font-extrabold text-white mr-3 uppercase tracking-widest text-xs',
                                    cancelButton: 'bg-stone-100 px-8 py-3 rounded-lg font-extrabold text-stone-500 uppercase tracking-widest text-xs'
                                },
                                buttonsStyling: false
                            }).then((result) => { if (result.isConfirmed) $refs.deleteAreaForm.submit() })"
                            class="w-full py-3 rounded-lg border border-red-200 text-[10px] font-extrabold text-red-500 hover:bg-red-50 transition-colors flex items-center justify-center gap-2 mt-2 uppercase tracking-widest">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus Area
                    </button>
                </template>
            </div>
        </form>

        {{-- Hidden Delete Form --}}
        <form x-ref="deleteAreaForm" :action="'/floor/areas/' + editAreaData.id" method="POST" class="hidden">
            @csrf @method('DELETE')
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: ADD / EDIT TABLE --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div x-show="showAddTableModal || showEditTableModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/60 backdrop-blur-sm"
     x-transition x-cloak>
    <div class="bg-white rounded-2xl border border-stone-200 shadow-xl w-full max-w-lg mx-4" @click.away="closeTableModals()">
        <div class="px-6 py-5 border-b border-stone-100 flex items-center justify-between">
            <h3 class="font-extrabold text-stone-900 text-sm uppercase tracking-widest" x-text="showEditTableModal ? 'Edit Meja' : 'Tambah Meja'"></h3>
            <button @click="closeTableModals()" class="text-stone-400 hover:text-stone-600 transition-colors w-8 h-8 flex items-center justify-center bg-stone-50 rounded-lg">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form :action="showEditTableModal ? '/floor/tables/' + editTableData.id : '{{ route('floor.table.store') }}'"
              method="POST" class="p-6 space-y-4">
            @csrf
            <template x-if="showEditTableModal"><input type="hidden" name="_method" value="PUT"></template>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Kode Meja *</label>
                    <input type="text" name="code" :value="editTableData.code" required placeholder="T01, VIP-A"
                           class="w-full border border-stone-200 rounded-lg px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary bg-stone-50 uppercase tracking-wider">
                </div>
                <div>
                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Area *</label>
                    <select name="area_fk_id" required
                            class="w-full border border-stone-200 rounded-lg px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary bg-stone-50 uppercase tracking-wider">
                        @foreach($areas as $area)
                        <option value="{{ $area->id }}"
                            x-bind:selected="editTableData.area_fk_id == {{ $area->id }} || (!showEditTableModal && {{ $area->id }} == {{ $selectedAreaId ?? 0 }})">
                            {{ $area->name }} (Lt.{{ $area->floor_number }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Bentuk *</label>
                    <div class="flex gap-2">
                        <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer border rounded-lg px-2 py-3.5 text-[10px] font-extrabold uppercase tracking-widest transition-all"
                               :class="editTableData.shape != 'circle' ? 'border-brand-primary bg-brand-light text-brand-primary' : 'border-stone-200 text-stone-500 bg-stone-50 hover:bg-stone-100'">
                            <input type="radio" name="shape" value="rectangle" class="hidden"
                                   :checked="editTableData.shape != 'circle'" @change="editTableData.shape = 'rectangle'">
                            <i data-lucide="square" class="w-3 h-3"></i> Kotak
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer border rounded-lg px-2 py-3.5 text-[10px] font-extrabold uppercase tracking-widest transition-all"
                               :class="editTableData.shape == 'circle' ? 'border-brand-primary bg-brand-light text-brand-primary' : 'border-stone-200 text-stone-500 bg-stone-50 hover:bg-stone-100'">
                            <input type="radio" name="shape" value="circle" class="hidden"
                                   :checked="editTableData.shape == 'circle'" @change="editTableData.shape = 'circle'">
                            <i data-lucide="circle" class="w-3 h-3"></i> Bulat
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">Kapasitas *</label>
                    <input type="number" name="capacity" :value="editTableData.capacity || 4" min="1" max="99" required
                           class="w-full border border-stone-200 rounded-lg px-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary tabular-nums bg-stone-50">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-extrabold text-stone-400 uppercase tracking-widest mb-2.5">
                    Minimum Cash (Rp) *
                    <span class="ml-1 text-[8px] text-stone-300 normal-case font-bold">— tampil di aplikasi saat booking</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-extrabold text-stone-400 uppercase tracking-widest">Rp</span>
                    <input type="hidden" name="min_spending" :value="editTableData.min_spending">
                    <input type="text"
                           x-bind:value="editTableData.min_spending ? parseInt(editTableData.min_spending, 10).toLocaleString('id-ID') : '0'"
                           @input="let v = $event.target.value.replace(/\D/g, ''); editTableData.min_spending = v ? parseInt(v, 10) : 0; $event.target.value = v ? parseInt(v, 10).toLocaleString('id-ID') : '';"
                           placeholder="0" required
                           class="w-full border border-stone-200 rounded-lg pl-10 pr-4 py-3.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary bg-stone-50 tabular-nums">
                </div>
                <p class="text-[9px] font-bold uppercase tracking-widest text-stone-400 mt-2">Contoh: 500000 = Rp 500.000 | Set 0 untuk tanpa minimum</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="closeTableModals()"
                        class="flex-1 py-3.5 rounded-lg border border-stone-200 text-xs font-extrabold text-stone-600 hover:bg-stone-50 transition-colors uppercase tracking-widest">Batal</button>
                <button type="submit"
                        class="flex-1 py-3.5 rounded-lg bg-brand-primary text-white text-xs font-extrabold hover:opacity-90 transition-all shadow-lg uppercase tracking-widest">
                    <span x-text="showEditTableModal ? 'Simpan' : 'Tambah'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

</div>{{-- end x-data --}}
@endsection

@section('scripts')
<script>
function floorManager() {
    return {
        showAddAreaModal:  false,
        showEditAreaModal: false,
        showAddTableModal: false,
        showEditTableModal: false,
        selectedTables: [],
        allSelected: false,
        isDragging: false,
        dragTimeout: null,
        editAreaData:  { id: null, name: '', description: '', floor_number: 1 },
        editTableData: { id: null, code: '', area_fk_id: {{ $selectedAreaId ?? 0 }}, shape: 'rectangle', capacity: 4, min_spending: 0 },

        init() {
            this.initDragDrop();
            lucide.createIcons();

            // Watch selectedTables to sync allSelected
            this.$watch('selectedTables', value => {
                const tableCount = {{ $tables->count() }};
                if (value.length === 0) this.allSelected = false;
                else if (value.length === tableCount) this.allSelected = true;
            });

            // Keyboard Navigation
            window.addEventListener('keydown', (e) => this.handleKeyboard(e));
        },

        handleKeyboard(e) {
            // Only trigger if tables are selected and not typing in input
            if (this.selectedTables.length === 0) return;
            if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;

            const keys = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'];
            if (!keys.includes(e.key)) return;

            e.preventDefault();

            const GRID = 12;
            const canvas = document.getElementById('canvas');
            const rect = canvas.getBoundingClientRect();

            let dx = 0;
            let dy = 0;

            if (e.key === 'ArrowUp') dy = -GRID;
            if (e.key === 'ArrowDown') dy = GRID;
            if (e.key === 'ArrowLeft') dx = -GRID;
            if (e.key === 'ArrowRight') dx = GRID;

            this.selectedTables.forEach(id => {
                const el = document.getElementById('table_' + id);
                if (!el) return;

                let nx = parseInt(el.style.left) + dx;
                let ny = parseInt(el.style.top) + dy;

                // Boundary check
                nx = Math.max(10, Math.min(nx, 3500 - el.offsetWidth - 10));
                ny = Math.max(10, Math.min(ny, 2500 - el.offsetHeight - 10));

                el.style.left = nx + 'px';
                el.style.top = ny + 'px';
            });
        },

        toggleAll() {
            if (this.allSelected) {
                this.selectedTables = @json($tables->pluck('id'));
            } else {
                this.selectedTables = [];
            }
        },

        toggleSelect(id) {
            if (this.selectedTables.includes(id)) {
                this.selectedTables = this.selectedTables.filter(t => t !== id);
            } else {
                this.selectedTables.push(id);
            }
        },

        // ── Area modals ──
        editArea(area) {
            this.editAreaData = { ...area };
            this.showEditAreaModal = true;
            this.$nextTick(() => lucide.createIcons());
        },
        closeAreaModals() {
            this.showAddAreaModal  = false;
            this.showEditAreaModal = false;
            this.editAreaData = { id: null, name: '', description: '', floor_number: 1 };
        },

        // ── Table modals ──
        editTable(tableJson) {
            this.editTableData = typeof tableJson === 'string' ? JSON.parse(tableJson) : tableJson;
            this.showEditTableModal = true;
            this.$nextTick(() => lucide.createIcons());
        },
        closeTableModals() {
            this.showAddTableModal  = false;
            this.showEditTableModal = false;
            this.editTableData = { id: null, code: '', area_fk_id: {{ $selectedAreaId ?? 0 }}, shape: 'rectangle', capacity: 4, min_spending: 0 };
        },

        // ── Drag & drop layout ──
        initDragDrop() {
            const canvas   = document.getElementById('canvas');
            if (!canvas) return;
            const GRID     = 12;
            let el = null, ox = 0, oy = 0;
            this.isDragging = false;

            document.querySelectorAll('.draggable').forEach(node => {
                node.addEventListener('mousedown', e => {
                    if (e.target.closest('button, form, input, textarea')) return;
                    this.isDragging = false; // reset on every click
                    el = node;
                    ox = e.clientX - node.getBoundingClientRect().left;
                    oy = e.clientY - node.getBoundingClientRect().top;
                    
                    // Delay setting isDragging to allow for a pure click
                    this.dragTimeout = setTimeout(() => {
                        this.isDragging = true;
                        node.style.zIndex = 1000;
                        node.classList.add('scale-110', 'opacity-80', 'ring-4', 'ring-indigo-400/30');
                    }, 150);
                });
            });

            document.addEventListener('mousemove', e => {
                if (!el || !this.isDragging) return;
                const rect = canvas.getBoundingClientRect();
                let nx = Math.round((e.clientX - ox - rect.left) / GRID) * GRID;
                let ny = Math.round((e.clientY - oy - rect.top)  / GRID) * GRID;
                nx = Math.max(10, Math.min(nx, rect.width  - el.offsetWidth  - 10));
                ny = Math.max(10, Math.min(ny, rect.height - el.offsetHeight - 10));
                el.style.left = nx + 'px';
                el.style.top  = ny + 'px';
            });

            document.addEventListener('mouseup', () => {
                clearTimeout(this.dragTimeout);
                if (el) {
                    el.style.zIndex = 1;
                    el.classList.remove('scale-110', 'opacity-80', 'ring-4', 'ring-indigo-400/30');
                }
                // we don't reset this.isDragging immediately because we want the @click listener to see it
                setTimeout(() => { this.isDragging = false; }, 0);
                el = null;
            });

            // Save layout button
            document.getElementById('saveLayoutBtn')?.addEventListener('click', async (btn) => {
                const target = document.getElementById('saveLayoutBtn');
                const orig   = target.innerHTML;
                target.disabled = true;
                target.innerHTML = '<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin inline"></i> Saving…';
                lucide.createIcons();

                const payload = [];
                document.querySelectorAll('.draggable').forEach(node => {
                    payload.push({ id: +node.dataset.id, x_pos: parseInt(node.style.left), y_pos: parseInt(node.style.top) });
                });

                try {
                    const res = await fetch("{{ route('floor.layout.save') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        target.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5 inline"></i> Saved!';
                        target.classList.add('bg-emerald-600');
                        lucide.createIcons();
                        setTimeout(() => { target.innerHTML = orig; target.classList.remove('bg-emerald-600'); target.disabled = false; lucide.createIcons(); }, 2500);
                    }
                } catch(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menyimpan layout area.',
                        background: '#ffffff',
                    });
                    target.innerHTML = orig;
                    target.disabled = false;
                    lucide.createIcons();
                }
            });
        }
    };
}
</script>
@endsection
