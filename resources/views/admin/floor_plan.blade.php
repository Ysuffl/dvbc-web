@extends('layouts.admin')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-stone-900 flex items-center gap-3 uppercase tracking-tight">
            <div class="w-10 h-10 bg-brand-light rounded-lg flex items-center justify-center text-brand-primary">
                <i data-lucide="map" class="w-5 h-5"></i>
            </div>
            Floor Plan Editor
        </h1>
        <p class="text-stone-500 text-[10px] font-bold mt-1 uppercase tracking-widest">Drag and move tables to arrange the layout for VIP OTIC floor</p>
    </div>
    <div class="flex items-center gap-4">
        <!-- Status Legend -->
        <div class="bg-white px-4 py-3 rounded-lg border border-stone-200 shadow-sm flex items-center gap-5 text-[10px] font-extrabold text-stone-600 uppercase tracking-widest">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded bg-stone-100 border border-stone-300"></div> Tersedia
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded bg-brand-light border border-brand-primary/50"></div> Booked
            </div>
        </div>
        
        <button id="saveBtn" class="flex items-center gap-2 bg-brand-primary hover:opacity-90 active:scale-95 transition-all text-white font-extrabold py-3 px-8 rounded-lg shadow-lg text-[10px] uppercase tracking-widest">
            <i data-lucide="save" class="w-4 h-4"></i>
            Save Layout
        </button>
    </div>
</div>

<div class="relative w-full rounded-xl overflow-hidden border border-stone-200 shadow-xl bg-stone-50 min-h-[850px]" id="canvas-container">
    <!-- Visual Grid Overlay -->
    <div class="absolute inset-0 pointer-events-none opacity-50" 
         style="background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
    
    <div class="relative w-full min-h-[850px]" id="canvas">
        @foreach($tables as $table)
        <div class="draggable absolute flex flex-col items-center justify-center font-extrabold text-stone-800 shadow-md hover:shadow-xl cursor-grab active:cursor-grabbing select-none group transition-all duration-200 ease-out"
             id="table_{{ $table->id }}"
             data-id="{{ $table->id }}"
             style="left: {{ $table->x_pos }}px; top: {{ $table->y_pos }}px; 
                    width: {{ $table->shape == 'circle' ? '120px' : '90px' }}; 
                    height: {{ $table->shape == 'circle' ? '120px' : '90px' }}; 
                    border-radius: {{ $table->shape == 'circle' ? '50%' : '8px' }}; 
                    background-color: {{ $table->status == 'available' ? '#f5f5f4' : '#FFF8ED' }};
                    border: 2px solid {{ $table->status == 'available' ? '#e7e5e4' : '#A68A56' }};">
            <div class="text-[14px] font-extrabold tracking-tight uppercase leading-none {{ $table->status == 'available' ? 'text-stone-800' : 'text-brand-primary' }}">{{ $table->code }}</div>
            <div class="text-[9px] font-extrabold mt-1 uppercase tracking-widest {{ $table->status == 'available' ? 'text-stone-400' : 'text-brand-primary/70' }}">{{ $table->category }}</div>
            
            <!-- Floating Label for Drag Handle -->
            <div class="absolute -top-10 scale-0 group-hover:scale-100 transition-transform bg-stone-900 text-white text-[10px] font-extrabold uppercase tracking-widest py-1.5 px-3 rounded-lg pointer-events-none">
                {{ $table->shape == 'circle' ? 'Large Table' : 'Standard' }}
                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 border-[4px] border-transparent border-t-stone-900"></div>
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
            el.classList.add('rotate-2', 'scale-110', 'opacity-80', 'ring-4', 'ring-brand-primary/20');
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
            currentEl.classList.remove('rotate-2', 'scale-110', 'opacity-80', 'ring-4', 'ring-brand-primary/20');
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
                btn.classList.add('bg-stone-900', 'text-white');
                btn.classList.remove('bg-brand-primary');
                btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Layout Saved!';
                lucide.createIcons();
                
                setTimeout(() => {
                    btn.classList.remove('bg-stone-900');
                    btn.classList.add('bg-brand-primary');
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
