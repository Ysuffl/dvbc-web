@extends('layouts.admin')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
            <i data-lucide="map" class="w-8 h-8 text-blue-500"></i>
            Floor Plan Editor
        </h1>
        <p class="text-slate-500 text-sm mt-1">Drag and move tables to arrange the layout for VIP OTIC floor</p>
    </div>
    <div class="flex items-center gap-4">
        <!-- Status Legend -->
        <div class="bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4 text-xs font-bold text-slate-600 uppercase tracking-widest">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-slate-100 border border-slate-300"></div> Tersedia
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-blue-100 border border-blue-500"></div> Booked
            </div>
        </div>
        
        <button id="saveBtn" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 active:scale-95 transition-all text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-500/30">
            <i data-lucide="save" class="w-5 h-5"></i>
            Save Layout
        </button>
    </div>
</div>

<div class="relative w-full rounded-2xl overflow-hidden border border-slate-200 shadow-xl bg-slate-50 min-h-[850px]" id="canvas-container">
    <!-- Visual Grid Overlay -->
    <div class="absolute inset-0 pointer-events-none opacity-50" 
         style="background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
    
    <div class="relative w-full min-h-[850px]" id="canvas">
        @foreach($tables as $table)
        <div class="draggable absolute flex flex-col items-center justify-center font-black text-slate-800 shadow-xl cursor-grab active:cursor-grabbing select-none group transition-all duration-200 ease-out"
             id="table_{{ $table->id }}"
             data-id="{{ $table->id }}"
             style="left: {{ $table->x_pos }}px; top: {{ $table->y_pos }}px; 
                    width: {{ $table->shape == 'circle' ? '130px' : '100px' }}; 
                    height: {{ $table->shape == 'circle' ? '130px' : '100px' }}; 
                    border-radius: {{ $table->shape == 'circle' ? '50%' : '24px' }}; 
                    background-color: {{ $table->status == 'available' ? '#f8fafc' : '#dbeafe' }};
                    border: 4px solid {{ $table->status == 'available' ? '#e2e8f0' : '#3b82f6' }};">
            <div class="text-xl tracking-tighter">{{ $table->code }}</div>
            <div class="text-[80%] opacity-40 font-bold uppercase">{{ $table->category }}</div>
            
            <!-- Floating Label for Drag Handle -->
            <div class="absolute -top-10 scale-0 group-hover:scale-100 transition-transform bg-slate-800 text-white text-[10px] py-1 px-3 rounded-full pointer-events-none">
                {{ $table->shape == 'circle' ? 'Large Table' : 'Standard' }}
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script>
    const canvas = document.getElementById('canvas');
    let isDragging = false;
    let currentEl = null;
    let offsetX = 0, offsetY = 0;
    const GRID_SIZE = 12; // Grid snapping for precision

    document.querySelectorAll('.draggable').forEach(el => {
        el.addEventListener('mousedown', (e) => {
            isDragging = true;
            currentEl = el;
            offsetX = e.clientX - el.getBoundingClientRect().left;
            offsetY = e.clientY - el.getBoundingClientRect().top;
            
            el.style.zIndex = 1000;
            el.classList.add('rotate-2', 'scale-110', 'opacity-80', 'ring-4', 'ring-blue-500/20');
        });
    });

    document.addEventListener('mousemove', (e) => {
        if (!isDragging || !currentEl) return;
        
        const canvasRect = canvas.getBoundingClientRect();
        let nx = e.clientX - offsetX - canvasRect.left;
        let ny = e.clientY - offsetY - canvasRect.top;
        
        // Snap to Grid
        nx = Math.round(nx / GRID_SIZE) * GRID_SIZE;
        ny = Math.round(ny / GRID_SIZE) * GRID_SIZE;
        
        // Boundaries
        nx = Math.max(20, Math.min(nx, canvasRect.width - currentEl.offsetWidth - 20));
        ny = Math.max(20, Math.min(ny, canvasRect.height - currentEl.offsetHeight - 20));

        currentEl.style.left = nx + 'px';
        currentEl.style.top = ny + 'px';
    });

    document.addEventListener('mouseup', () => {
        if (currentEl) {
            currentEl.style.zIndex = 1;
            currentEl.classList.remove('rotate-2', 'scale-110', 'opacity-80', 'ring-4', 'ring-blue-500/20');
        }
        isDragging = false;
        currentEl = null;
    });

    // Save functionality
    document.getElementById('saveBtn').addEventListener('click', async function() {
        const btn = this;
        const originalHtml = btn.innerHTML;
        
        // Loading State
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="refresh-cw" class="w-5 h-5 animate-spin"></i> Saving...';
        lucide.createIcons();

        const payload = [];
        document.querySelectorAll('.draggable').forEach(el => {
            payload.push({
                id: el.getAttribute('data-id'),
                x_pos: parseInt(el.style.left),
                y_pos: parseInt(el.style.top)
            });
        });

        try {
            const res = await fetch("{{ route('update_coordinates') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                btn.classList.add('bg-green-600');
                btn.innerHTML = '<i data-lucide="check" class="w-5 h-5"></i> Layout Saved!';
                lucide.createIcons();
                
                setTimeout(() => {
                    btn.classList.remove('bg-green-600');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    lucide.createIcons();
                }, 3000);
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Data Not Saved',
                text: 'Gagal menyimpan perubahan. Periksa koneksi atau sesi login Anda.',
                background: '#ffffff',
            });
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            lucide.createIcons();
        }
    });

    // Re-trigger icons for late loaded elements
    lucide.createIcons();
</script>
@endsection
