@extends('layouts.admin')

@section('content')
<div x-data="floorManager()" x-init="init()">

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- HEADER --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
            <i data-lucide="layout-dashboard" class="w-8 h-8 text-indigo-500"></i>
            Floor Management
        </h1>
        <p class="text-slate-500 text-sm mt-1">Kelola area, meja, dan minimum cash secara real-time</p>
    </div>
    <div class="flex items-center gap-3">
        <button @click="showAddAreaModal = true"
            class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 transition-all text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-indigo-500/30 text-sm">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Area
        </button>
        <button @click="showAddTableModal = true"
            class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 transition-all text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-emerald-500/30 text-sm">
            <i data-lucide="table-2" class="w-4 h-4"></i> Add Table
        </button>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-semibold text-sm flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 font-semibold text-sm flex items-center gap-2">
    <i data-lucide="alert-circle" class="w-4 h-4"></i> {{ session('error') }}
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- AREA TABS --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
    @foreach($areas as $area)
    <a href="{{ route('floor.index', ['area' => $area->id]) }}"
        class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-sm whitespace-nowrap transition-all
               {{ $selectedAreaId == $area->id
                    ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30'
                    : 'bg-white text-slate-600 border border-slate-200 hover:border-indigo-300 hover:text-indigo-600' }}">
        <i data-lucide="layers" class="w-4 h-4"></i>
        {{ $area->name }}
        <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $selectedAreaId == $area->id ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
            Lt.{{ $area->floor_number }}
        </span>
        {{-- Edit Area Button --}}
        <span @click.prevent="editArea({ id: {{ $area->id }}, name: '{{ addslashes($area->name) }}', description: '{{ addslashes($area->description ?? '') }}', floor_number: {{ $area->floor_number }} })"
              class="ml-1 rounded-lg p-0.5 hover:bg-white/30 transition-colors cursor-pointer">
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
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
                <span class="font-bold text-slate-700 text-sm flex items-center gap-2">
                    <i data-lucide="move" class="w-4 h-4 text-indigo-400"></i>
                    Layout Canvas — <span class="text-indigo-600">{{ $selectedArea->name ?? 'No Area' }}</span>
                </span>
                <button id="saveLayoutBtn"
                    class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-bold py-2 px-4 rounded-lg transition-all">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i> Save Layout
                </button>
            </div>

            {{-- Canvas Container (Scrollable & Massive) --}}
            <div class="relative w-full h-[800px] bg-slate-50 overflow-auto border-t border-slate-100 rounded-b-2xl shadow-inner scroll-smooth" id="canvas-container">
                {{-- Dotted Grid matches canvas size --}}
                <div class="absolute w-[3500px] h-[2500px] pointer-events-none opacity-30"
                     style="background-image: radial-gradient(#94a3b8 2px, transparent 2px); background-size: 30px 30px; z-index: 0;"></div>

                {{-- Canvas Area --}}
                <div class="relative w-[3500px] h-[2500px]" id="canvas">
                    @foreach($tables as $table)
                    <div class="draggable absolute flex flex-col items-center justify-center cursor-grab active:cursor-grabbing select-none group transition-all duration-200 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] hover:shadow-[0_8px_30px_-4px_rgba(79,70,229,0.3)] hover:-translate-y-1"
                         id="table_{{ $table->id }}"
                         data-id="{{ $table->id }}"
                         style="left: {{ $table->x_pos }}px; top: {{ $table->y_pos }}px;
                                width: {{ $table->shape == 'circle' ? '140px' : '80px' }};
                                height: {{ $table->shape == 'circle' ? '140px' : '80px' }};
                                border-radius: {{ $table->shape == 'circle' ? '50%' : '12px' }};
                                background: {{ $table->status == 'available' ? 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)' : 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)' }};
                                border: 2px solid {{ $table->status == 'available' ? '#e2e8f0' : '#60a5fa' }}; z-index: 1;">

                        {{-- Glow Effect on Hover --}}
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 rounded-[inherit] ring-4 ring-indigo-500/20 transition-all pointer-events-none"></div>

                        {{-- Table Code --}}
                        <div class="text-[17px] font-black tracking-tight {{ $table->status == 'available' ? 'text-slate-800' : 'text-blue-700' }} leading-none drop-shadow-sm">
                            {{ $table->code }}
                        </div>
                        
                        {{-- Capacity --}}
                        <div class="flex items-center gap-1 mt-1 text-slate-400">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            <span class="text-[11px] font-extrabold">{{ $table->capacity }}</span>
                        </div>

                        {{-- Min Spending Badge --}}
                        @if($table->min_spending > 0)
                        <div class="absolute -bottom-3 px-2.5 py-0.5 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-800 text-[10px] font-black whitespace-nowrap shadow-sm z-10 transition-transform group-hover:scale-105 group-hover:-translate-y-1 drop-shadow-sm">
                            Rp {{ number_format($table->min_spending/1000, 0) }}k
                        </div>
                        @endif

                        {{-- Hover tooltip (Extended info) --}}
                        <div class="absolute -top-12 scale-0 group-hover:scale-100 transition-all origin-bottom bg-slate-800 text-white text-[11px] font-medium py-1.5 px-3 rounded-xl pointer-events-none whitespace-nowrap z-[100] shadow-xl">
                            <span class="font-bold text-indigo-300">{{ $table->code }}</span> • Cap: {{ $table->capacity }}
                            <div class="mt-0.5 text-emerald-300/90 text-[10px] font-bold">Min: Rp{{ number_format($table->min_spending, 0, ',', '.') }}</div>
                            {{-- Tooltip caret --}}
                            <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 border-[5px] border-transparent border-t-slate-800"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── TABLE LIST (BOTTOM GRID) ───────────────────────────────────────── --}}
    <div class="w-full">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6">
            <h2 class="font-black text-slate-800 text-lg mb-5 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i data-lucide="list-tree" class="w-5 h-5 text-indigo-500"></i>
                    Daftar Meja ({{ $tables->count() }})
                </span>
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
                    <div class="group relative bg-white border border-slate-200 rounded-2xl p-4 flex flex-col hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-500/10 transition-all">
                        {{-- Top Header --}}
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-100 {{ $table->shape == 'circle' ? 'rounded-full' : '' }} shrink-0">
                                    <i data-lucide="{{ $table->shape == 'circle' ? 'circle' : 'square' }}"
                                       class="w-5 h-5 {{ $table->status == 'available' ? 'text-slate-400' : 'text-blue-500' }}"></i>
                                </div>
                                <div>
                                    <div class="font-black text-slate-800 text-base leading-tight">{{ $table->code }}</div>
                                    <div class="flex items-center gap-1 text-[11px] text-slate-400 font-bold mt-0.5">
                                        <i data-lucide="users" class="w-3 h-3"></i> {{ $table->capacity }} kursi
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="editTable({{ $table->toJson() }})" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 transition-colors">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                <form method="POST" action="{{ route('floor.table.destroy', $table->id) }}" onsubmit="return confirm('Hapus meja {{ $table->code }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Bottom Quick Min Cash --}}
                        <div class="mt-auto pt-3 border-t border-slate-100">
                            <form method="POST" action="{{ route('floor.table.min_spending', $table->id) }}" class="flex items-center justify-between gap-2">
                                @csrf @method('PATCH')
                                <span class="text-[10px] text-slate-400 font-bold">Min Cash (Rp)</span>
                                <div class="flex items-center gap-1">
                                    <input type="hidden" name="min_spending" id="inline_min_{{ $table->id }}" value="{{ $table->min_spending }}">
                                    <input type="text" value="{{ $table->min_spending > 0 ? number_format($table->min_spending, 0, ',', '.') : '0' }}"
                                           oninput="let v = this.value.replace(/\D/g, ''); document.getElementById('inline_min_{{ $table->id }}').value = v || 0; this.value = v ? parseInt(v, 10).toLocaleString('id-ID') : '';"
                                           class="w-full max-w-[90px] text-[11px] text-right font-black {{ $table->min_spending > 0 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-slate-600 bg-slate-50 border-slate-200' }} border rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-colors">
                                    <button type="submit" class="p-1.5 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 transition-colors shadow-sm" title="Update Minimum Cash">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
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

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: ADD / EDIT AREA --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div x-show="showAddAreaModal || showEditAreaModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
     x-transition>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4" @click.away="closeAreaModals()">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-black text-slate-800 text-lg" x-text="showEditAreaModal ? 'Edit Area' : 'Tambah Area'"></h3>
            <button @click="closeAreaModals()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form :action="showEditAreaModal ? '/floor/areas/' + editAreaData.id : '{{ route('floor.area.store') }}'"
              method="POST" class="p-6 space-y-4">
            @csrf
            <template x-if="showEditAreaModal"><input type="hidden" name="_method" value="PUT"></template>
            <div>
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-1.5">Nama Area *</label>
                <input type="text" name="name" :value="editAreaData.name" required placeholder="e.g. VIP Room, Outdoor"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-1.5">Deskripsi</label>
                <textarea name="description" :value="editAreaData.description" rows="2" placeholder="Keterangan opsional"
                          class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-1.5">Lantai *</label>
                <input type="number" name="floor_number" :value="editAreaData.floor_number || 1" min="1" max="99" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="closeAreaModals()"
                        class="flex-1 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit"
                        class="flex-1 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                    <span x-text="showEditAreaModal ? 'Simpan Perubahan' : 'Tambah Area'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: ADD / EDIT TABLE --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div x-show="showAddTableModal || showEditTableModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
     x-transition>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4" @click.away="closeTableModals()">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-black text-slate-800 text-lg" x-text="showEditTableModal ? 'Edit Meja' : 'Tambah Meja'"></h3>
            <button @click="closeTableModals()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form :action="showEditTableModal ? '/floor/tables/' + editTableData.id : '{{ route('floor.table.store') }}'"
              method="POST" class="p-6 space-y-4">
            @csrf
            <template x-if="showEditTableModal"><input type="hidden" name="_method" value="PUT"></template>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-1.5">Kode Meja *</label>
                    <input type="text" name="code" :value="editTableData.code" required placeholder="T01, VIP-A"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400 uppercase">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-1.5">Area *</label>
                    <select name="area_fk_id" required
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400">
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
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-1.5">Bentuk *</label>
                    <div class="flex gap-2">
                        <label class="flex-1 flex items-center gap-2 cursor-pointer border rounded-xl px-3 py-3 text-sm font-bold transition-all"
                               :class="editTableData.shape != 'circle' ? 'border-emerald-400 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-500'">
                            <input type="radio" name="shape" value="rectangle" class="hidden"
                                   :checked="editTableData.shape != 'circle'" @change="editTableData.shape = 'rectangle'">
                            <i data-lucide="square" class="w-4 h-4"></i> Kotak
                        </label>
                        <label class="flex-1 flex items-center gap-2 cursor-pointer border rounded-xl px-3 py-3 text-sm font-bold transition-all"
                               :class="editTableData.shape == 'circle' ? 'border-emerald-400 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-500'">
                            <input type="radio" name="shape" value="circle" class="hidden"
                                   :checked="editTableData.shape == 'circle'" @change="editTableData.shape = 'circle'">
                            <i data-lucide="circle" class="w-4 h-4"></i> Bulat
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-1.5">Kapasitas (Kursi) *</label>
                    <input type="number" name="capacity" :value="editTableData.capacity || 4" min="1" max="99" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-1.5">
                    Minimum Cash (Rp) *
                    <span class="ml-1 text-[10px] text-slate-400 normal-case font-normal">— tampil di aplikasi saat booking</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-slate-400">Rp</span>
                    <input type="hidden" name="min_spending" :value="editTableData.min_spending">
                    <input type="text"
                           x-bind:value="editTableData.min_spending ? parseInt(editTableData.min_spending, 10).toLocaleString('id-ID') : '0'"
                           @input="let v = $event.target.value.replace(/\D/g, ''); editTableData.min_spending = v ? parseInt(v, 10) : 0; $event.target.value = v ? parseInt(v, 10).toLocaleString('id-ID') : '';"
                           placeholder="0" required
                           class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400">
                </div>
                <p class="text-[10px] text-slate-400 mt-1.5">Contoh: 500000 = Rp 500.000 | Set 0 untuk tanpa minimum</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="closeTableModals()"
                        class="flex-1 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit"
                        class="flex-1 py-3 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-500/30">
                    <span x-text="showEditTableModal ? 'Simpan Perubahan' : 'Tambah Meja'"></span>
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
        editAreaData:  { id: null, name: '', description: '', floor_number: 1 },
        editTableData: { id: null, code: '', area_fk_id: {{ $selectedAreaId ?? 0 }}, shape: 'rectangle', capacity: 4, min_spending: 0 },

        init() {
            this.initDragDrop();
            lucide.createIcons();
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
            let isDragging = false, el = null, ox = 0, oy = 0;

            document.querySelectorAll('.draggable').forEach(node => {
                node.addEventListener('mousedown', e => {
                    if (e.target.closest('button, form, input, textarea')) return;
                    isDragging = true;
                    el = node;
                    ox = e.clientX - node.getBoundingClientRect().left;
                    oy = e.clientY - node.getBoundingClientRect().top;
                    node.style.zIndex = 1000;
                    node.classList.add('scale-110', 'opacity-80', 'ring-4', 'ring-indigo-400/30');
                });
            });

            document.addEventListener('mousemove', e => {
                if (!isDragging || !el) return;
                const rect = canvas.getBoundingClientRect();
                let nx = Math.round((e.clientX - ox - rect.left) / GRID) * GRID;
                let ny = Math.round((e.clientY - oy - rect.top)  / GRID) * GRID;
                nx = Math.max(10, Math.min(nx, rect.width  - el.offsetWidth  - 10));
                ny = Math.max(10, Math.min(ny, rect.height - el.offsetHeight - 10));
                el.style.left = nx + 'px';
                el.style.top  = ny + 'px';
            });

            document.addEventListener('mouseup', () => {
                if (el) {
                    el.style.zIndex = 1;
                    el.classList.remove('scale-110', 'opacity-80', 'ring-4', 'ring-indigo-400/30');
                }
                isDragging = false;
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
                    alert('Gagal menyimpan layout.');
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
