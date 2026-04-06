@extends('layouts.admin')

@section('content')
    <div class="space-y-8 animate-in fade-in duration-700" x-data="{ 
        showAddLevelModal: false, 
        showEditLevelModal: false, 
        editLevel: { id: '', name: '', min_spending: 0, badge_color: '' },
        showAddTagModal: false,
        showEditTagModal: false,
        showAddGroupModal: false,
        showEditGroupModal: false,
        showDeleteGroupConfirmModal: false,
        editTag: { id: '', master_tag_group_id: '', name: '', abbreviation: '' },
        editGroup: { id: '', name: '' },
        groupToDelete: { id: '', name: '', tagCount: 0 },
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
                <p class="text-[10px] font-extrabold text-stone-500 uppercase tracking-widest mt-2">Manage loyalty levels, tagging, and broadcast templates.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

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

            <!-- Tags Component -->
            <div class="bg-white rounded-md shadow-sm border border-stone-200 p-10">
                <div class="flex justify-between items-center mb-10">
                    <div>
                        <h3 class="text-xl font-extrabold text-stone-900 uppercase tracking-[0.2em] flex items-center gap-3">
                            <span class="w-10 h-10 bg-brand-primary/10 rounded-md flex items-center justify-center border border-brand-primary/20">
                                <i data-lucide="tag" class="w-5 h-5 text-brand-primary"></i>
                            </span>
                            Master Tags
                        </h3>
                        <p class="text-[10px] text-stone-400 font-extrabold uppercase mt-2 tracking-widest leading-relaxed">
                            Categorize your customer visits with <span class="text-stone-300">dynamic tagging</span>.
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="showAddGroupModal = true" class="group flex items-center gap-2 bg-stone-50 hover:bg-stone-100 text-stone-600 px-6 py-3 rounded-md text-[10px] uppercase font-black transition-all border border-stone-200 shadow-sm active:scale-95">
                            <i data-lucide="folder-plus" class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100 transition-opacity"></i>
                            New Group
                        </button>
                        <button @click="showAddTagModal = true" class="group flex items-center gap-2 bg-brand-primary hover:opacity-90 text-white px-6 py-3 rounded-md text-[10px] uppercase font-black transition-all shadow-md active:scale-95">
                            <i data-lucide="plus" class="w-3.5 h-3.5 opacity-80 group-hover:opacity-100 transition-opacity"></i>
                            Add Tag
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    @forelse($tagGroups as $group)
                    <div class="group/grp bg-stone-50/50 rounded-md border border-stone-100 overflow-hidden transition-all hover:border-stone-200">
                        <!-- Group Header -->
                        <div class="flex items-center justify-between px-6 py-4 bg-white border-b border-stone-100">
                            <div class="flex items-center gap-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-stone-900 uppercase tracking-widest flex items-center gap-2">
                                        {{ $group->name }}
                                        <span class="text-[8px] bg-stone-100 text-stone-400 py-0.5 px-1.5 rounded-full border border-stone-200/50">{{ count($group->tags) }} Tags</span>
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 opacity-0 group-hover/grp:opacity-100 transition-all">
                                <button @click="editGroup = { id: {{ $group->id }}, name: '{{ addslashes($group->name) }}' }; showEditGroupModal = true" 
                                        class="p-2 transition-colors text-stone-400 hover:text-blue-500 hover:bg-blue-50 bg-white border border-stone-100 rounded-md">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                                <button @click="groupToDelete = { id: {{ $group->id }}, name: '{{ addslashes($group->name) }}', tagCount: {{ count($group->tags) }} }; showDeleteGroupConfirmModal = true" 
                                        class="p-2 transition-colors text-stone-400 hover:text-rose-500 hover:bg-rose-50 bg-white border border-stone-100 rounded-md">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tag List in Group -->
                        <div class="p-6">
                            @if($group->tags->isEmpty())
                                <div class="py-4 text-center">
                                    <span class="text-[9px] font-extrabold text-stone-300 uppercase tracking-widest">No tags in this group yet</span>
                                </div>
                            @else
                                <div class="flex flex-wrap gap-3">
                                    @foreach($group->tags as $tag)
                                    <div class="group/item flex items-center gap-3 pl-4 pr-2 py-2.5 bg-white border border-stone-200 rounded-md hover:border-brand-primary/30 transition-all hover:shadow-sm">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-stone-700 tracking-wider">
                                                {{ $tag->name }}
                                            </span>
                                            @if($tag->abbreviation)
                                                <span class="text-[7px] font-black text-stone-300 font-mono tracking-widest uppercase">{{ $tag->abbreviation }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1 border-l border-stone-100 pl-2">
                                            <button @click="editTag = { id: {{ $tag->id }}, master_tag_group_id: '{{ $tag->master_tag_group_id }}', name: '{{ addslashes($tag->name) }}', abbreviation: '{{ addslashes($tag->abbreviation) }}' }; showEditTagModal = true" 
                                                    class="p-1.5 text-stone-300 hover:text-blue-500 transition-colors rounded-sm hover:bg-blue-50">
                                                <i data-lucide="edit-2" class="w-3 h-3"></i>
                                            </button>
                                            <form action="{{ route('master.tag.destroy', $tag->id) }}" method="POST" class="inline-block" data-confirm="Are you sure you want to delete this tag?">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 text-stone-300 hover:text-rose-500 transition-colors rounded-sm hover:bg-rose-50">
                                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="py-20 text-center border-2 border-dashed border-stone-100 rounded-md bg-stone-50/20">
                        <i data-lucide="tag" class="w-10 h-10 text-stone-200 mx-auto mb-4"></i>
                        <span class="text-[11px] font-black text-stone-300 uppercase tracking-[0.3em]">No master tags found</span>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- ─── Broadcast Templates ────────────────────────────────────────── -->
            <div class="bg-white rounded-md shadow-sm border border-stone-200 p-10 lg:col-span-2">
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
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
                        <select name="master_tag_group_id" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all appearance-none cursor-pointer uppercase">
                            <option value="">-- Select Group --</option>
                            @foreach($tagGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Tag Name</label>
                        <input type="text" name="name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all" placeholder="e.g. Whiskey, Cigar, Golf">
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Abbreviation (for Export)</label>
                        <input type="text" name="abbreviation" class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all" placeholder="e.g. pr_whiskey">
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
                        <select name="master_tag_group_id" x-model="editTag.master_tag_group_id" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer uppercase">
                            @foreach($tagGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Tag Name</label>
                        <input type="text" name="name" x-model="editTag.name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Abbreviation (for Export)</label>
                        <input type="text" name="abbreviation" x-model="editTag.abbreviation" class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 bg-blue-600 hover:opacity-90 text-white rounded-md text-[10px] font-extrabold uppercase tracking-widest shadow-sm transition-all outline-none">Update Tag</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ─── Add Group Modal ──────────────────────────────────────────────── -->
    <div x-show="showAddGroupModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showAddGroupModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="showAddGroupModal = false"></div>
            <div x-show="showAddGroupModal" class="relative bg-white rounded-md border border-stone-200 shadow-xl w-full max-w-md p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-extrabold uppercase tracking-widest text-stone-900">Add Tag Group</h3>
                    <button @click="showAddGroupModal = false" class="p-3 bg-stone-50 text-stone-400 border border-stone-200 hover:text-stone-600 rounded-md transition-colors outline-none"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form action="{{ route('master.tag_group.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Group Name</label>
                        <input type="text" name="name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all uppercase" placeholder="e.g. PURPOSE, PRODUCT, etc">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 bg-brand-primary hover:opacity-90 text-white rounded-md text-[10px] font-extrabold uppercase tracking-widest shadow-sm transition-all outline-none">Save Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ─── Edit Group Modal ─────────────────────────────────────────────── -->
    <div x-show="showEditGroupModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showEditGroupModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="showEditGroupModal = false"></div>
            <div x-show="showEditGroupModal" class="relative bg-white rounded-md border border-stone-200 shadow-xl w-full max-w-md p-10">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-extrabold uppercase tracking-widest text-stone-900">Edit Tag Group</h3>
                    <button @click="showEditGroupModal = false" class="p-3 bg-stone-50 text-stone-400 border border-stone-200 hover:text-stone-600 rounded-md transition-colors outline-none"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <form :action="'/master/tag-groups/' + editGroup.id" method="POST" class="space-y-5">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[9px] font-extrabold text-stone-400 uppercase tracking-widest mb-2">Group Name</label>
                        <input type="text" name="name" x-model="editGroup.name" required class="w-full px-5 py-4 bg-stone-50 border border-stone-200 rounded-md text-[10px] font-extrabold tracking-widest focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all uppercase">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 bg-blue-600 hover:opacity-90 text-white rounded-md text-[10px] font-extrabold uppercase tracking-widest shadow-sm transition-all outline-none">Update Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- ─── Delete Group Confirmation Modal ────────────────────────────── -->
    <div x-show="showDeleteGroupConfirmModal" class="fixed inset-0 z-[60] overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showDeleteGroupConfirmModal" class="fixed inset-0 bg-stone-900/80 backdrop-blur-md" @click="showDeleteGroupConfirmModal = false"></div>
            <div x-show="showDeleteGroupConfirmModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-md border border-stone-200 shadow-2xl w-full max-w-md p-10 text-center">
                
                <div class="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center border border-rose-100 mx-auto mb-6">
                    <i data-lucide="alert-triangle" class="w-10 h-10 text-rose-500"></i>
                </div>

                <h3 class="text-xl font-black uppercase tracking-widest text-stone-900 mb-2">Delete Group?</h3>
                <p class="text-xs text-stone-400 font-medium leading-relaxed mb-8">
                    You are about to delete <span class="text-stone-900 font-black" x-text="groupToDelete.name"></span>. 
                    This action will permanently remove <span class="text-rose-600 font-black" x-text="groupToDelete.tagCount"></span> tags associated with it.
                </p>

                <div class="flex flex-col gap-3">
                    <form :action="'/master/tag-groups/' + groupToDelete.id" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full py-4 bg-rose-600 hover:bg-rose-700 text-white rounded-md text-[10px] font-black uppercase tracking-[0.2em] shadow-lg transition-all active:scale-95">
                            Confirm Deletion
                        </button>
                    </form>
                    <button @click="showDeleteGroupConfirmModal = false" class="w-full py-4 bg-stone-100 hover:bg-stone-200 text-stone-600 rounded-md text-[10px] font-black uppercase tracking-[0.2em] transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


