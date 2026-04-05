@extends('layouts.admin')

@section('content')
    <div class="space-y-8 animate-in fade-in duration-700" x-data="{ 
        showAddCategoryModal: false, 
        showEditCategoryModal: false, 
        editCategory: { id: '', name: '', icon: '', bg_color: '', text_color: '' }, 
        showAddLevelModal: false, 
        showEditLevelModal: false, 
        editLevel: { id: '', name: '', min_spending: 0, badge_color: '' },
        showAddTagModal: false,
        showEditTagModal: false,
        editTag: { id: '', group_name: '', name: '' },
        showAddTemplateModal: false,
        showEditTemplateModal: false,
        editTemplate: { id: '', name: '', message: '', type: 'promotion' },
        addLevelMin: '',
        activeTab: 'solid',
        colorPresets: [
            { bg: '#ffedd5', text: '#9a3412', name: 'Orange' },
            { bg: '#f1f5f9', text: '#1e293b', name: 'Slate' },
            { bg: '#fef9c3', text: '#854d0e', name: 'Yellow' },
            { bg: '#dbeafe', text: '#1e40af', name: 'Blue' },
            { bg: '#dcfce7', text: '#166534', name: 'Emerald' },
            { bg: '#ffe4e6', text: '#9f1239', name: 'Rose' },
            { bg: '#e0e7ff', text: '#3730a3', name: 'Indigo' },
            { bg: '#cffafe', text: '#155e75', name: 'Cyan' },
            { bg: '#f3e8ff', text: '#6b21a8', name: 'Purple' },
        ],
        gradientPresets: [
            'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'linear-gradient(135deg, #5ee7df 0%, #b490ca 100%)',
            'linear-gradient(135deg, #c3cfe2 0%, #c3cfe2 100%)',
            'linear-gradient(135deg, #f6d365 0%, #fda085 100%)',
            'linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%)',
            'linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)',
            'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)'
        ],
        formatCurrency(val) {
            if (!val && val !== 0) return '';
            let s = val.toString().replace(/\D/g, '');
            return s.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },
        stripCurrency(val) {
            return val.toString().replace(/\D/g, '');
        },
        isTailwind(color) {
            return color && (color.includes('bg-') || color.includes('text-'));
        },
        getStyle(color, type = 'bg') {
            if (this.isTailwind(color)) return '';
            if (type === 'bg') return 'background: ' + color;
            return 'color: ' + color;
        },
        getContrastColor(hex) {
            if (!hex || this.isTailwind(hex)) return '';
            if (hex.includes('gradient')) return 'white';
            const color = hex.replace('#', '');
            if (color.length !== 6) return 'white';
            const r = parseInt(color.substr(0, 2), 16);
            const g = parseInt(color.substr(2, 2), 16);
            const b = parseInt(color.substr(4, 2), 16);
            const brightness = (r * 299 + g * 587 + b * 114) / 1000;
            return brightness > 155 ? '#1e293b' : 'white';
        }
    }">
        
        <!-- Header Section -->
        <div class="flex justify-between items-center bg-white rounded-md shadow-sm p-10 border border-stone-200">
            <div>
                <h2 class="text-2xl font-extrabold text-stone-900 tracking-widest uppercase flex items-center gap-3">
                    <div class="w-12 h-12 bg-stone-50 rounded-md flex items-center justify-center border border-stone-200">
                        <i data-lucide="database" class="w-6 h-6 text-brand-primary"></i>
                    </div>
                    Master Data
                </h2>
                <p class="text-[10px] font-extrabold text-stone-500 uppercase tracking-widest mt-2">Manage categories and tiering levels for customers.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- Categories -->
            <div class="bg-white rounded-md shadow-sm border border-stone-200 p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-lg font-extrabold text-stone-900 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="tags" class="w-5 h-5 text-brand-primary"></i>
                        Master Categories
                    </h3>
                    <button @click="showAddCategoryModal = true" class="bg-brand-primary hover:opacity-90 text-white px-5 py-2.5 rounded-md text-[10px] uppercase tracking-widest font-extrabold transition-all outline-none">
                        + Add Category
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-stone-400 text-[9px] font-extrabold uppercase tracking-widest border-b border-stone-100">
                                <th class="pb-5 px-4 text-left">Name</th>
                                <th class="pb-5 px-4 text-left">Preview</th>
                                <th class="pb-5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach($categories as $category)
                            <tr class="group hover:bg-stone-50 transition-colors">
                                <td class="py-5 px-4 whitespace-nowrap text-xs font-extrabold uppercase tracking-widest text-stone-800">
                                    {{ $category->name }}
                                </td>
                                <td class="py-5 px-4 whitespace-nowrap text-sm">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-sm border border-stone-200 shadow-sm"
                                         :class="isTailwind('{{ $category->bg_color }}') ? '{{ $category->bg_color }}' : ''"
                                         :style="!isTailwind('{{ $category->bg_color }}') ? 'background:{{ $category->bg_color }}; color:' + getContrastColor('{{ $category->bg_color }}') : ''">
                                        <i data-lucide="{{ $category->icon ?? 'tag' }}" 
                                           class="w-3.5 h-3.5"
                                           :class="isTailwind('{{ $category->text_color }}') ? '{{ $category->text_color }}' : ''"
                                           :style="!isTailwind('{{ $category->text_color }}') ? 'color:' + getContrastColor('{{ $category->bg_color }}') : ''"></i>
                                        <span class="text-[9px] font-extrabold uppercase tracking-widest"
                                              :class="isTailwind('{{ $category->text_color }}') ? '{{ $category->text_color }}' : ''"
                                              :style="!isTailwind('{{ $category->text_color }}') ? 'color:' + getContrastColor('{{ $category->bg_color }}') : ''">Preview</span>
                                    </div>
                                </td>
                                <td class="py-5 px-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" 
                                            @click="editCategory = { id: {{ $category->id }}, name: '{{ addslashes($category->name) }}', icon: '{{ $category->icon }}', bg_color: '{{ $category->bg_color }}', text_color: '{{ $category->text_color }}' }; showEditCategoryModal = true"
                                            class="text-blue-500 hover:text-blue-700 hover:bg-blue-50 bg-white border border-blue-200 p-2.5 rounded-md transition-colors outline-none">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('master.category.destroy', $category->id) }}" method="POST" class="inline-block" data-confirm="Are you sure you want to delete this category?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 bg-white border border-rose-200 p-2.5 rounded-md transition-colors outline-none">
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
            <div class="bg-white rounded-md shadow-sm border border-stone-200 p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-lg font-extrabold text-stone-900 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="bar-chart-2" class="w-5 h-5 text-brand-primary"></i>
                        Master Levels
                    </h3>
                    <button @click="showAddLevelModal = true" class="bg-brand-primary hover:opacity-90 text-white px-5 py-2.5 rounded-md text-[10px] uppercase tracking-widest font-extrabold transition-all outline-none">
                        + Add Level
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-stone-400 text-[9px] font-extrabold uppercase tracking-widest border-b border-stone-100">
                                <th class="pb-5 px-4 text-left">Level Name</th>
                                <th class="pb-5 px-4 text-left">Min. Spending</th>
                                <th class="pb-5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach($levels as $level)
                            <tr class="group hover:bg-stone-50 transition-colors">
                                <td class="py-5 px-4 whitespace-nowrap">
                                    <span class="px-3 py-1.5 text-[9px] shadow-sm font-extrabold uppercase tracking-widest rounded-sm border border-stone-200"
                                          :class="isTailwind('{{ $level->badge_color }}') ? '{{ $level->badge_color }}' : ''"
                                          :style="!isTailwind('{{ $level->badge_color }}') ? 'background:{{ $level->badge_color }}; color:' + getContrastColor('{{ $level->badge_color }}') : ''">
                                        <i data-lucide="star" class="w-3 h-3 inline-block mr-1 mb-0.5"></i>
                                        {{ $level->name }}
                                    </span>
                                </td>
                                <td class="py-5 px-4 whitespace-nowrap text-xs font-extrabold text-stone-600 tracking-widest uppercase">
                                    Rp {{ number_format($level->min_spending, 0, ',', '.') }}
                                </td>
                                <td class="py-5 px-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" 
                                            @click="editLevel = { id: {{ $level->id }}, name: '{{ addslashes($level->name) }}', min_spending: {{ $level->min_spending }}, badge_color: '{{ $level->badge_color }}' }; showEditLevelModal = true"
                                            class="text-blue-500 hover:text-blue-700 hover:bg-blue-50 bg-white border border-blue-200 p-2.5 rounded-md transition-colors outline-none">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('master.level.destroy', $level->id) }}" method="POST" class="inline-block" data-confirm="Are you sure you want to delete this level?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 bg-white border border-rose-200 p-2.5 rounded-md transition-colors outline-none">
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

            <!-- Tags -->
            <div class="bg-white rounded-md shadow-sm border border-stone-200 p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-lg font-extrabold text-stone-900 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="tag" class="w-5 h-5 text-brand-primary"></i>
                        Master Tags
                    </h3>
                    <button @click="showAddTagModal = true" class="bg-brand-primary hover:opacity-90 text-white px-5 py-2.5 rounded-md text-[10px] uppercase tracking-widest font-extrabold transition-all outline-none">
                        + Add Tag
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-stone-400 text-[9px] font-extrabold uppercase tracking-widest border-b border-stone-100">
                                <th class="pb-5 px-4 text-left">Group</th>
                                <th class="pb-5 px-4 text-left">Tag Name</th>
                                <th class="pb-5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse($tags as $tag)
                            <tr class="group hover:bg-stone-50 transition-colors">
                                <td class="py-5 px-4">
                                    <span class="text-[9px] font-black text-stone-400 uppercase tracking-widest">{{ $tag->group_name }}</span>
                                </td>
                                <td class="py-5 px-4">
                                    <span class="px-3 py-1 bg-stone-100 text-stone-700 text-[10px] font-extrabold tracking-widest rounded-sm border border-stone-200">
                                        {{ $tag->name }}
                                    </span>
                                </td>
                                <td class="py-5 px-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" 
                                            @click="editTag = { id: {{ $tag->id }}, group_name: '{{ addslashes($tag->group_name) }}', name: '{{ addslashes($tag->name) }}' }; showEditTagModal = true"
                                            class="text-blue-500 hover:text-blue-700 hover:bg-blue-50 bg-white border border-blue-200 p-2.5 rounded-md transition-colors outline-none">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('master.tag.destroy', $tag->id) }}" method="POST" class="inline-block" data-confirm="Are you sure you want to delete this tag?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 bg-white border border-rose-200 p-2.5 rounded-md transition-colors outline-none">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-10 text-center text-[10px] font-extrabold text-stone-400 uppercase tracking-widest">No tags found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ─── Broadcast Templates ────────────────────────────────────────── -->
            <div class="bg-white rounded-md shadow-sm border border-stone-200 p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-lg font-extrabold text-stone-900 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="messages-square" class="w-5 h-5 text-emerald-600"></i>
                        Broadcast Templates
                    </h3>
                    <button @click="showAddTemplateModal = true"
                            class="bg-emerald-600 hover:opacity-90 text-white px-5 py-2.5 rounded-md text-[10px] font-extrabold uppercase tracking-widest transition-all outline-none">
                        + Add Template
                    </button>
                </div>

                @if($templates->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-stone-400">
                        <i data-lucide="file-text" class="w-12 h-12 mb-4 text-stone-300"></i>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-stone-400 text-center">No templates yet. Create one to speed up your broadcasts.</p>
                    </div>
                @else
                <div class="grid grid-cols-1 gap-5">
                    @foreach($templates as $template)
                    <div class="group relative bg-stone-50 border border-stone-200 rounded-md p-5 hover:border-emerald-200 hover:bg-emerald-50 transition-all">
                        <!-- Type Badge -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-widest rounded-sm border border-stone-200
                                {{ $template->type === 'promotion' ? 'bg-brand-light text-brand-primary border-brand-primary/20' : ($template->type === 'info' ? 'bg-stone-200 text-stone-600 border-stone-300' : 'bg-emerald-50 border-emerald-200 text-emerald-700') }}">
                                {{ $template->type }}
                            </span>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button"
                                    @click="editTemplate = { id: {{ $template->id }}, name: '{{ addslashes($template->name) }}', message: `{{ addslashes($template->message) }}`, type: '{{ $template->type }}' }; showEditTemplateModal = true"
                                    class="p-2 bg-white text-blue-500 hover:text-blue-700 rounded-md border border-blue-200 transition-colors">
                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                </button>
                                <form action="{{ route('master.template.destroy', $template->id) }}" method="POST" data-confirm="Delete template '{{ $template->name }}'?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 bg-white text-rose-500 hover:text-rose-700 rounded-md border border-rose-200 transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <h4 class="font-extrabold text-stone-800 text-[10px] uppercase tracking-widest mb-2 mt-4">{{ $template->name }}</h4>
                        <p class="text-[10px] font-medium text-stone-500 leading-relaxed max-h-24 overflow-hidden mb-1">{{ $template->message }}</p>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>

        <!-- Modals (inside outer x-data scope) -->
    <div x-show="showAddTemplateModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showAddTemplateModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="showAddTemplateModal = false"></div>
            <div x-show="showAddTemplateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-md shadow-xl w-full max-w-lg p-10 text-left border border-stone-200">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-extrabold text-stone-900 tracking-widest uppercase flex items-center gap-3">
                        <span class="w-10 h-10 bg-emerald-50 rounded-md flex items-center justify-center border border-emerald-100">
                            <i data-lucide="messages-square" class="w-5 h-5 text-emerald-600"></i>
                        </span> Add Template
                    </h3>
                    <button @click="showAddTemplateModal = false" class="p-3 bg-stone-50 text-stone-400 border border-stone-200 hover:text-stone-600 rounded-md transition-colors outline-none"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form action="{{ route('master.template.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Template Name</label>
                        <input type="text" name="name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold uppercase tracking-widest focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all" placeholder="E.G. WEEKEND PROMO">
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Type</label>
                        <select name="type" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold uppercase tracking-widest focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all appearance-none cursor-pointer">
                            <option value="promotion">Promotion</option>
                            <option value="info">Info / Event</option>
                            <option value="greeting">Greeting</option>
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest">Message Body</label>
                            <span class="text-[9px] font-extrabold text-stone-500 bg-stone-100 border border-stone-200 px-2 py-1 rounded uppercase tracking-widest">Use <code class="text-emerald-600">{name}</code></span>
                        </div>
                        <textarea name="message" rows="5" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all resize-none" placeholder="Hello {name}, we have an exclusive offer for you..."></textarea>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 bg-emerald-600 hover:opacity-90 text-white rounded-md text-[10px] font-extrabold uppercase tracking-widest shadow-sm transition-all outline-none">Save Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ─── Edit Template Modal ──────────────────────────────────────────── -->
    <div x-show="showEditTemplateModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showEditTemplateModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="showEditTemplateModal = false"></div>
            <div x-show="showEditTemplateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-md shadow-xl w-full max-w-lg p-10 text-left border border-stone-200">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-extrabold text-stone-900 tracking-widest uppercase flex items-center gap-3">
                        <span class="w-10 h-10 bg-blue-50 rounded-md flex items-center justify-center border border-blue-100">
                            <i data-lucide="edit-3" class="w-5 h-5 text-blue-500"></i>
                        </span> Edit Template
                    </h3>
                    <button @click="showEditTemplateModal = false" class="p-3 bg-stone-50 text-stone-400 border border-stone-200 hover:text-stone-600 rounded-md transition-colors outline-none"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form :action="'/master/templates/' + editTemplate.id" method="POST" class="space-y-5">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Template Name</label>
                        <input type="text" name="name" x-model="editTemplate.name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold uppercase tracking-widest focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Type</label>
                        <select name="type" x-model="editTemplate.type" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold uppercase tracking-widest focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer">
                            <option value="promotion">Promotion</option>
                            <option value="info">Info / Event</option>
                            <option value="greeting">Greeting</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Message Body</label>
                        <textarea name="message" x-model="editTemplate.message" rows="5" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all resize-none"></textarea>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 bg-blue-600 hover:opacity-90 text-white rounded-md text-[10px] font-extrabold uppercase tracking-widest shadow-sm transition-all outline-none">Update Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ─── Add Category Modal ───────────────────────────────────────────── -->
    <div x-show="showAddCategoryModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showAddCategoryModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="showAddCategoryModal = false"></div>
            <div x-show="showAddCategoryModal" class="relative bg-white rounded-md border border-stone-200 shadow-xl w-full max-w-md p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-extrabold uppercase tracking-widest text-stone-900">Add Category</h3>
                    <button @click="showAddCategoryModal = false" class="p-2 hover:bg-stone-50 rounded-md text-stone-400 border border-stone-200 outline-none"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form action="{{ route('master.category.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Category Name</label>
                        <input type="text" name="name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest uppercase focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all" placeholder="E.G. VVIP">
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Icon (Lucide name)</label>
                        <input type="text" name="icon" class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest uppercase focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all" placeholder="E.G. CROWN, STAR, USER">
                    </div>
                    <div x-data="{ localActiveTab: 'solid', customBg: '#ffffff', customText: '#000000' }">
                        <div class="flex items-center gap-4 mb-3 border-b border-stone-100 pb-2">
                            <button type="button" @click="localActiveTab = 'solid'" :class="localActiveTab === 'solid' ? 'text-brand-primary border-b-2 border-brand-primary' : 'text-stone-400'" class="text-[10px] font-extrabold uppercase tracking-widest pb-2 transition-all">Solid</button>
                            <button type="button" @click="localActiveTab = 'gradient'" :class="localActiveTab === 'gradient' ? 'text-brand-primary border-b-2 border-brand-primary' : 'text-stone-400'" class="text-[10px] font-extrabold uppercase tracking-widest pb-2 transition-all">Gradient</button>
                        </div>

                        <div x-show="localActiveTab === 'solid'" class="space-y-4">
                            <div class="grid grid-cols-5 gap-2 bg-stone-50 p-3 rounded-md border border-stone-100">
                                <template x-for="c in colorPresets">
                                    <button type="button" @click="$refs.bgIn.value = c.bg; $refs.textIn.value = c.text"
                                            :style="'background:' + c.bg"
                                            class="w-6 h-6 rounded-full transition-all hover:scale-110 border border-black/5">
                                    </button>
                                </template>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <label class="text-[9px] font-extrabold text-stone-400 uppercase block mb-1">Custom BG</label>
                                    <input type="color" x-model="customBg" @input="$refs.bgIn.value = customBg" class="w-full h-10 rounded-md bg-stone-50 border border-stone-200 cursor-pointer">
                                </div>
                                <div class="flex-1">
                                    <label class="text-[9px] font-extrabold text-stone-400 uppercase block mb-1">Custom Text</label>
                                    <input type="color" x-model="customText" @input="$refs.textIn.value = customText" class="w-full h-10 rounded-md bg-stone-50 border border-stone-200 cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <div x-show="localActiveTab === 'gradient'" class="grid grid-cols-4 gap-3 bg-stone-50 p-4 rounded-md border border-stone-200">
                            <template x-for="g in gradientPresets">
                                <button type="button" @click="$refs.bgIn.value = g; $refs.textIn.value = '#ffffff'"
                                        :style="'background:' + g"
                                        class="aspect-square rounded-md transition-all hover:rotate-3 shadow-sm">
                                </button>
                            </template>
                        </div>

                        <input type="hidden" name="bg_color" x-ref="bgIn">
                        <input type="hidden" name="text_color" x-ref="textIn">
                    </div>
                     <button type="submit" class="w-full py-4 bg-brand-primary hover:opacity-90 text-white rounded-md text-[10px] font-extrabold uppercase tracking-widest shadow-sm outline-none transition-all">Save Category</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ─── Edit Category Modal ──────────────────────────────────────────── -->
    <div x-show="showEditCategoryModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showEditCategoryModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="showEditCategoryModal = false"></div>
            <div x-show="showEditCategoryModal" class="relative bg-white rounded-md border border-stone-200 shadow-xl w-full max-w-md p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-extrabold uppercase tracking-widest text-stone-900">Edit Category</h3>
                    <button @click="showEditCategoryModal = false" class="p-2 hover:bg-stone-50 rounded-md text-stone-400 border border-stone-200 outline-none"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form :action="'/master/categories/' + editCategory.id" method="POST" class="space-y-5">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Category Name</label>
                        <input type="text" name="name" x-model="editCategory.name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest uppercase focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Icon</label>
                        <input type="text" name="icon" x-model="editCategory.icon" class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest uppercase focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    </div>
                    <div x-data="{ localActiveTab: 'solid' }" x-init="$watch('showEditCategoryModal', value => { if(value) localActiveTab = editCategory.bg_color.includes('gradient') ? 'gradient' : 'solid' })">
                        <div class="flex items-center gap-4 mb-3 border-b border-stone-100 pb-2">
                            <button type="button" @click="localActiveTab = 'solid'" :class="localActiveTab === 'solid' ? 'text-brand-primary border-b-2 border-brand-primary' : 'text-stone-400'" class="text-[10px] font-extrabold uppercase tracking-widest pb-2 transition-all">Solid</button>
                            <button type="button" @click="localActiveTab = 'gradient'" :class="localActiveTab === 'gradient' ? 'text-brand-primary border-b-2 border-brand-primary' : 'text-stone-400'" class="text-[10px] font-extrabold uppercase tracking-widest pb-2 transition-all">Gradient</button>
                        </div>

                        <div x-show="localActiveTab === 'solid'" class="space-y-4">
                            <div class="grid grid-cols-5 gap-2 bg-stone-50 p-3 rounded-md border border-stone-200">
                                <template x-for="c in colorPresets">
                                    <button type="button" @click="editCategory.bg_color = c.bg; editCategory.text_color = c.text"
                                            :style="'background:' + c.bg"
                                            class="w-6 h-6 rounded-full transition-all hover:scale-110 border border-black/5 shadow-sm">
                                    </button>
                                </template>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <label class="text-[9px] font-extrabold text-stone-400 uppercase block mb-1 tracking-widest">Custom BG</label>
                                    <input type="color" x-model="editCategory.bg_color" class="w-full h-10 rounded-md bg-stone-50 border border-stone-200 cursor-pointer">
                                </div>
                                <div class="flex-1">
                                    <label class="text-[9px] font-extrabold text-stone-400 uppercase block mb-1 tracking-widest">Custom Text</label>
                                    <input type="color" x-model="editCategory.text_color" class="w-full h-10 rounded-md bg-stone-50 border border-stone-200 cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <div x-show="localActiveTab === 'gradient'" class="grid grid-cols-4 gap-3 bg-stone-50 p-4 rounded-md border border-stone-200">
                            <template x-for="g in gradientPresets">
                                <button type="button" @click="editCategory.bg_color = g; editCategory.text_color = '#ffffff'"
                                        :style="'background:' + g"
                                        class="aspect-square rounded-md transition-all hover:rotate-3 shadow-sm">
                                </button>
                            </template>
                        </div>

                        <input type="hidden" name="bg_color" x-model="editCategory.bg_color">
                        <input type="hidden" name="text_color" x-model="editCategory.text_color">
                    </div>
                     <button type="submit" class="w-full py-4 bg-brand-primary hover:opacity-90 text-white rounded-md text-[10px] uppercase tracking-widest font-extrabold shadow-sm outline-none transition-all">Update Category</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ─── Add Level Modal ──────────────────────────────────────────────── -->
    <div x-show="showAddLevelModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showAddLevelModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="showAddLevelModal = false"></div>
            <div x-show="showAddLevelModal" class="relative bg-white rounded-md border border-stone-200 shadow-xl w-full max-w-md p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-extrabold uppercase tracking-widest text-stone-900">Add Level</h3>
                    <button @click="showAddLevelModal = false" class="p-2 hover:bg-stone-50 rounded-md text-stone-400 border border-stone-200 outline-none"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form action="{{ route('master.level.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Level Name</label>
                        <input type="text" name="name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest uppercase focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all" placeholder="E.G. DIAMOND">
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Min. Spending</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-stone-400 font-extrabold text-[10px] tracking-widest">RP</span>
                            <input type="text" x-model="addLevelMin" @input="addLevelMin = formatCurrency($event.target.value)" required class="w-full pl-12 pr-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold text-stone-700 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all tabular-nums" placeholder="0">
                            <input type="hidden" name="min_spending" :value="stripCurrency(addLevelMin)">
                        </div>
                    </div>
                    <div x-data="{ localActiveTab: 'solid', customBg: '#ffffff' }">
                        <div class="flex items-center gap-4 mb-3 border-b border-stone-100 pb-2">
                            <button type="button" @click="localActiveTab = 'solid'" :class="localActiveTab === 'solid' ? 'text-brand-primary border-b-2 border-brand-primary' : 'text-stone-400'" class="text-[10px] font-extrabold uppercase tracking-widest pb-2 transition-all">Solid Color</button>
                            <button type="button" @click="localActiveTab = 'gradient'" :class="localActiveTab === 'gradient' ? 'text-brand-primary border-b-2 border-brand-primary' : 'text-stone-400'" class="text-[10px] font-extrabold uppercase tracking-widest pb-2 transition-all">Gradient</button>
                        </div>
                        
                        <div x-show="localActiveTab === 'solid'">
                            <div class="grid grid-cols-5 gap-2 bg-stone-50 p-3 rounded-md border border-stone-100 mb-4">
                                <template x-for="c in colorPresets">
                                    <button type="button" @click="$refs.levelBg.value = c.bg"
                                            :style="'background:' + c.bg"
                                            class="w-6 h-6 rounded-full transition-all hover:scale-110 border border-black/5">
                                    </button>
                                </template>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="color" x-model="customBg" @input="$refs.levelBg.value = customBg" class="w-10 h-10 rounded-md cursor-pointer">
                                <input type="text" x-model="customBg" class="flex-1 px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold font-mono uppercase tracking-widest" placeholder="#FFFFFF">
                            </div>
                        </div>

                        <div x-show="localActiveTab === 'gradient'" class="grid grid-cols-4 gap-3 bg-stone-50 p-4 rounded-md border border-stone-200">
                            <template x-for="g in gradientPresets">
                                <button type="button" @click="$refs.levelBg.value = g"
                                        :style="'background:' + g"
                                        class="aspect-square rounded-md transition-all hover:rotate-3 shadow-sm">
                                </button>
                            </template>
                        </div>

                        <input type="hidden" name="badge_color" x-ref="levelBg">
                    </div>
                     <button type="submit" class="w-full py-4 bg-brand-primary hover:opacity-90 text-white rounded-md text-[10px] uppercase tracking-widest font-extrabold shadow-sm outline-none transition-all">Save Level</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ─── Edit Level Modal ─────────────────────────────────────────────── -->
    <div x-show="showEditLevelModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showEditLevelModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="showEditLevelModal = false"></div>
            <div x-show="showEditLevelModal" class="relative bg-white rounded-md border border-stone-200 shadow-xl w-full max-w-md p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-extrabold uppercase tracking-widest text-stone-900">Edit Level</h3>
                    <button @click="showEditLevelModal = false" class="p-2 hover:bg-stone-50 rounded-md text-stone-400 border border-stone-200 outline-none"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form :action="'/master/levels/' + editLevel.id" method="POST" class="space-y-5" x-init="$watch('showEditLevelModal', value => { if(value) editLevel.min_spending = formatCurrency(editLevel.min_spending) })">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Level Name</label>
                        <input type="text" name="name" x-model="editLevel.name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest uppercase focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Min. Spending</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-stone-400 font-extrabold text-[10px] tracking-widest">RP</span>
                            <input type="text" x-model="editLevel.min_spending" @input="editLevel.min_spending = formatCurrency($event.target.value)" required class="w-full pl-12 pr-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold text-stone-700 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all tabular-nums">
                            <input type="hidden" name="min_spending" :value="stripCurrency(editLevel.min_spending)">
                        </div>
                    </div>
                    <div x-data="{ localActiveTab: 'solid' }" x-init="$watch('showEditLevelModal', value => { if(value) localActiveTab = editLevel.badge_color.includes('gradient') ? 'gradient' : 'solid' })">
                        <div class="flex items-center gap-4 mb-4 border-b border-stone-100 pb-2">
                            <button type="button" @click="localActiveTab = 'solid'" :class="localActiveTab === 'solid' ? 'text-brand-primary border-b-2 border-brand-primary' : 'text-stone-400'" class="text-[10px] font-extrabold uppercase tracking-widest pb-2 transition-all">Solid Color</button>
                            <button type="button" @click="localActiveTab = 'gradient'" :class="localActiveTab === 'gradient' ? 'text-brand-primary border-b-2 border-brand-primary' : 'text-stone-400'" class="text-[10px] font-extrabold uppercase tracking-widest pb-2 transition-all">Gradient</button>
                        </div>
                        
                        <div x-show="localActiveTab === 'solid'">
                            <div class="grid grid-cols-5 gap-2 bg-stone-50 p-3 rounded-md border border-stone-200 mb-4">
                                <template x-for="c in colorPresets">
                                    <button type="button" @click="editLevel.badge_color = c.bg"
                                            :style="'background:' + c.bg"
                                            class="w-6 h-6 rounded-full transition-all hover:scale-110 border border-black/5 shadow-sm">
                                    </button>
                                </template>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="color" x-model="editLevel.badge_color" class="w-10 h-10 rounded-md cursor-pointer">
                                <input type="text" x-model="editLevel.badge_color" class="flex-1 px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold font-mono uppercase tracking-widest" placeholder="#FFFFFF">
                            </div>
                        </div>

                        <div x-show="localActiveTab === 'gradient'" class="grid grid-cols-4 gap-3 bg-stone-50 p-4 rounded-md border border-stone-200">
                            <template x-for="g in gradientPresets">
                                <button type="button" @click="editLevel.badge_color = g"
                                        :style="'background:' + g"
                                        class="aspect-square rounded-md transition-all hover:rotate-3 shadow-sm">
                                </button>
                            </template>
                        </div>

                        <input type="hidden" name="badge_color" x-model="editLevel.badge_color">
                    </div>
                     <button type="submit" class="w-full py-4 bg-brand-primary hover:opacity-90 text-white rounded-md text-[10px] uppercase tracking-widest font-extrabold shadow-sm outline-none transition-all">Update Level</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ─── Add Tag Modal ──────────────────────────────────────────────── -->
    <div x-show="showAddTagModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showAddTagModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="showAddTagModal = false"></div>
            <div x-show="showAddTagModal" class="relative bg-white rounded-md border border-stone-200 shadow-xl w-full max-w-md p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-extrabold uppercase tracking-widest text-stone-900">Add Tag</h3>
                    <button @click="showAddTagModal = false" class="p-3 bg-stone-50 text-stone-400 border border-stone-200 hover:text-stone-600 rounded-md transition-colors outline-none"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form action="{{ route('master.tag.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Group Name</label>
                        <input type="text" name="group_name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all" placeholder="e.g. Interest, Hobby, Status">
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Tag Name</label>
                        <input type="text" name="name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all" placeholder="e.g. Whiskey, Cigar, Golf">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 bg-brand-primary hover:opacity-90 text-white rounded-md text-[10px] font-extrabold uppercase tracking-widest shadow-sm transition-all outline-none">Save Tag</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ─── Edit Tag Modal ───────────────────────────────────────────────── -->
    <div x-show="showEditTagModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showEditTagModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="showEditTagModal = false"></div>
            <div x-show="showEditTagModal" class="relative bg-white rounded-md border border-stone-200 shadow-xl w-full max-w-md p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-extrabold uppercase tracking-widest text-stone-900">Edit Tag</h3>
                    <button @click="showEditTagModal = false" class="p-3 bg-stone-50 text-stone-400 border border-stone-200 hover:text-stone-600 rounded-md transition-colors outline-none"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form :action="'/master/tags/' + editTag.id" method="POST" class="space-y-5">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Group Name</label>
                        <input type="text" name="group_name" x-model="editTag.group_name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Tag Name</label>
                        <input type="text" name="name" x-model="editTag.name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 bg-blue-600 hover:opacity-90 text-white rounded-md text-[10px] font-extrabold uppercase tracking-widest shadow-sm transition-all outline-none">Update Tag</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection


