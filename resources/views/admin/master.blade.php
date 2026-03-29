@extends('layouts.admin')

@section('content')
    <div class="space-y-8 animate-in fade-in duration-700" x-data="{ 
        showAddCategoryModal: false, 
        showEditCategoryModal: false, 
        editCategory: { id: '', name: '', icon: '', bg_color: '', text_color: '' }, 
        showAddLevelModal: false, 
        showEditLevelModal: false, 
        editLevel: { id: '', name: '', min_spending: 0, badge_color: '' },
        formatCurrency(val) {
            if (!val && val !== 0) return '';
            let s = val.toString().replace(/\D/g, '');
            return s.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    }">
        
        <!-- Header Section -->
        <div class="flex justify-between items-center bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-10 border border-gray-50/50">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                    <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center">
                        <i data-lucide="database" class="w-6 h-6 text-indigo-500"></i>
                    </div>
                    Master Data
                </h2>
                <p class="text-sm font-medium text-slate-500 mt-2">Manage categories and tiering levels for customers.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- Categories -->
            <div class="bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-50/50 p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="tags" class="w-5 h-5 text-indigo-500"></i>
                        Master Categories
                    </h3>
                    <button @click="showAddCategoryModal = true" class="bg-indigo-500 hover:bg-indigo-600 shadow-lg shadow-indigo-500/20 text-white px-5 py-2.5 rounded-2xl text-sm font-bold transition-all hover:scale-105 active:scale-95">
                        + Add Category
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-slate-400 text-[10px] font-black uppercase tracking-[0.15em] border-b border-slate-50">
                                <th class="pb-5 px-4 text-left">Name</th>
                                <th class="pb-5 px-4 text-left">Preview</th>
                                <th class="pb-5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($categories as $category)
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td class="py-5 px-4 whitespace-nowrap text-sm font-black text-slate-800 tracking-tight">
                                    {{ $category->name }}
                                </td>
                                <td class="py-5 px-4 whitespace-nowrap text-sm">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border {{ $category->bg_color ?? 'bg-slate-50' }} border-slate-100/50">
                                        <i data-lucide="{{ $category->icon ?? 'tag' }}" class="w-4 h-4 {{ $category->text_color ?? 'text-slate-500' }}"></i>
                                        <span class="text-[10px] font-black uppercase tracking-wider {{ $category->text_color ?? 'text-slate-500' }}">Icon</span>
                                    </div>
                                </td>
                                <td class="py-5 px-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" 
                                            @click="editCategory = { id: {{ $category->id }}, name: '{{ $category->name }}', icon: '{{ $category->icon }}', bg_color: '{{ $category->bg_color }}', text_color: '{{ $category->text_color }}' }; showEditCategoryModal = true"
                                            class="text-blue-500 hover:text-blue-700 hover:bg-blue-100 bg-blue-50 p-2.5 rounded-xl transition-colors">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('master.category.destroy', $category->id) }}" method="POST" class="inline-block" data-confirm="Are you sure you want to delete this category?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-100 bg-rose-50 p-2.5 rounded-xl transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Levels -->
            <div class="bg-white rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-50/50 p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="bar-chart-2" class="w-5 h-5 text-amber-500"></i>
                        Master Levels
                    </h3>
                    <button @click="showAddLevelModal = true" class="bg-amber-500 hover:bg-amber-600 shadow-lg shadow-amber-500/20 text-white px-5 py-2.5 rounded-2xl text-sm font-bold transition-all hover:scale-105 active:scale-95">
                        + Add Level
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-slate-400 text-[10px] font-black uppercase tracking-[0.15em] border-b border-slate-50">
                                <th class="pb-5 px-4 text-left">Level Name</th>
                                <th class="pb-5 px-4 text-left">Min. Spending</th>
                                <th class="pb-5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($levels as $level)
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td class="py-5 px-4 whitespace-nowrap">
                                    <span class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg {{ $level->badge_color }}">
                                        <i data-lucide="star" class="w-3 h-3 inline-block mr-1"></i>
                                        {{ $level->name }}
                                    </span>
                                </td>
                                <td class="py-5 px-4 whitespace-nowrap text-sm font-black text-slate-600 tracking-tight">
                                    Rp {{ number_format($level->min_spending, 0, ',', '.') }}
                                </td>
                                <td class="py-5 px-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" 
                                            @click="editLevel = { id: {{ $level->id }}, name: '{{ $level->name }}', min_spending: {{ $level->min_spending }}, badge_color: '{{ $level->badge_color }}' }; showEditLevelModal = true"
                                            class="text-blue-500 hover:text-blue-700 hover:bg-blue-100 bg-blue-50 p-2.5 rounded-xl transition-colors">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('master.level.destroy', $level->id) }}" method="POST" class="inline-block" data-confirm="Are you sure you want to delete this level?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-100 bg-rose-50 p-2.5 rounded-xl transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Add Category Modal -->
        <div x-show="showAddCategoryModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="showAddCategoryModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAddCategoryModal = false"></div>
                
                <div x-show="showAddCategoryModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-10 overflow-hidden text-left">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Add Category</h3>
                        <button @click="showAddCategoryModal = false" class="p-3 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-2xl transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
                    </div>
                    <form action="{{ route('master.category.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Category Name</label>
                            <input type="text" name="name" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="e.g. VIP">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Icon (Lucide)</label>
                            <input type="text" name="icon" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="e.g. star">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Bg Color</label>
                                <input type="text" name="bg_color" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="e.g. bg-red-50">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Text Color</label>
                                <input type="text" name="text_color" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="e.g. text-red-500">
                            </div>
                        </div>
                        <div class="pt-6">
                            <button type="submit" class="w-full py-5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-2xl text-base font-black shadow-[0_12px_30px_rgba(99,102,241,0.3)] transition-all hover:-translate-y-1">Save Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div x-show="showEditCategoryModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="showEditCategoryModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEditCategoryModal = false"></div>
                
                <div x-show="showEditCategoryModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-10 overflow-hidden text-left">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Edit Category</h3>
                        <button @click="showEditCategoryModal = false" class="p-3 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-2xl transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
                    </div>
                    <form x-bind:action="'/master/categories/' + editCategory.id" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Category Name</label>
                            <input type="text" name="name" x-model="editCategory.name" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Icon (Lucide)</label>
                            <input type="text" name="icon" x-model="editCategory.icon" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Bg Color</label>
                                <input type="text" name="bg_color" x-model="editCategory.bg_color" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Text Color</label>
                                <input type="text" name="text_color" x-model="editCategory.text_color" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                        </div>
                        <div class="pt-6">
                            <button type="submit" class="w-full py-5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-2xl text-base font-black shadow-[0_12px_30px_rgba(99,102,241,0.3)] transition-all hover:-translate-y-1">Update Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Level Modal -->
        <div x-show="showAddLevelModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="showAddLevelModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAddLevelModal = false"></div>
                
                <div x-show="showAddLevelModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-10 overflow-hidden text-left">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Add Level</h3>
                        <button @click="showAddLevelModal = false" class="p-3 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-2xl transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
                    </div>
                    <form action="{{ route('master.level.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Level Name</label>
                            <input type="text" name="name" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-amber-500/10 transition-all" placeholder="e.g. Diamond">
                        </div>
                        <div x-data="{ localMin: '' }">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Minimum Spending (Rp)</label>
                            <input type="text" x-model="localMin" @input="localMin = formatCurrency($event.target.value)" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-amber-500/10 transition-all" placeholder="e.g. 50.000.000">
                            <input type="hidden" name="min_spending" :value="localMin.replace(/\./g, '')">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Badge Color</label>
                            <input type="text" name="badge_color" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-amber-500/10 transition-all" placeholder="e.g. bg-blue-100 text-blue-800">
                        </div>
                        <div class="pt-6">
                            <button type="submit" class="w-full py-5 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl text-base font-black shadow-[0_12px_30px_rgba(245,158,11,0.3)] transition-all hover:-translate-y-1">Save Level</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Level Modal -->
        <div x-show="showEditLevelModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="showEditLevelModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEditLevelModal = false"></div>
                
                <div x-show="showEditLevelModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-10 overflow-hidden text-left">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Edit Level</h3>
                        <button @click="showEditLevelModal = false" class="p-3 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-2xl transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
                    </div>
                    <form x-bind:action="'/master/levels/' + editLevel.id" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Level Name</label>
                            <input type="text" name="name" x-model="editLevel.name" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-amber-500/10 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Minimum Spending (Rp)</label>
                            <input type="text" :value="formatCurrency(editLevel.min_spending)" @input="editLevel.min_spending = $event.target.value.replace(/\D/g, ''); $event.target.value = formatCurrency(editLevel.min_spending)" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-amber-500/10 transition-all">
                            <input type="hidden" name="min_spending" :value="editLevel.min_spending">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Badge Color</label>
                            <input type="text" name="badge_color" x-model="editLevel.badge_color" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-4 focus:ring-amber-500/10 transition-all">
                        </div>
                        <div class="pt-6">
                            <button type="submit" class="w-full py-5 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl text-base font-black shadow-[0_12px_30px_rgba(245,158,11,0.3)] transition-all hover:-translate-y-1">Update Level</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
