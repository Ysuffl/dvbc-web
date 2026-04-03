@extends('layouts.admin')

@section('content')
<div x-data="broadcastApp()" x-init="initApp()" class="max-w-7xl mx-auto space-y-6 lg:space-y-8">

    <!-- ─── Page Header ──────────────────────────────────────────────────── -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight uppercase">WhatsApp Broadcasting</h1>
            <p class="text-stone-500 text-[10px] uppercase tracking-widest mt-1.5 font-extrabold">Send targeted promotions directly to your customers.</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap" x-cloak>
            <div x-show="gatewayStatus === 'CONNECTED'" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg font-bold text-sm border border-emerald-200">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>Gateway Online
            </div>
            <div x-show="gatewayStatus === 'DISCONNECTED' || gatewayStatus === 'OFFLINE'" class="flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 rounded-lg font-bold text-sm border border-rose-200">
                <div class="w-2 h-2 rounded-full bg-rose-500"></div>Gateway Offline
            </div>
            <div x-show="gatewayStatus === 'WAITING_FOR_SCAN'" class="flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-600 rounded-lg font-bold text-sm border border-amber-200">
                <div class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></div>Waiting Scan
            </div>
            <div x-show="gatewayStatus === 'RECONNECTING'" class="flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-600 rounded-lg font-bold text-sm border border-purple-200">
                <div class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></div>Reconnecting...
            </div>
            <button x-show="gatewayStatus === 'CONNECTED'" @click="disconnectGateway"
                class="px-4 py-2 bg-white border border-rose-200 text-rose-600 font-extrabold text-[10px] uppercase tracking-widest rounded-lg hover:bg-rose-50 transition-colors shadow-sm">
                Disconnect
            </button>
        </div>
    </div>

    <!-- ─── ONBOARDING PANEL (not connected yet) ──────────────────────────── -->
    <div x-show="gatewayStatus !== 'CONNECTED' && gatewayStatus !== 'LOADING' && gatewayStatus !== 'RECONNECTING'" x-cloak
         class="bg-white rounded-xl p-10 lg:p-16 shadow-md border border-stone-200 flex flex-col items-center text-center max-w-xl mx-auto mt-8">

        <div class="w-16 h-16 bg-brand-light text-brand-primary rounded-lg flex items-center justify-center mb-6 shadow-sm border border-brand-primary/20">
            <i data-lucide="smartphone" class="w-8 h-8"></i>
        </div>
        <h2 class="text-xl font-extrabold text-stone-900 mb-3 uppercase tracking-widest">Connect WhatsApp Gateway</h2>
        <p class="text-stone-500 mb-8 text-[10px] font-extrabold uppercase tracking-widest leading-relaxed max-w-sm">
            Activate the self-hosted Baileys gateway to start sending bulk promotional messages to your customers.
        </p>

        <!-- DISCONNECTED state -->
        <div x-show="gatewayStatus === 'DISCONNECTED' || gatewayStatus === 'OFFLINE'" class="flex flex-col items-center gap-3">
            <button @click="startGateway" :disabled="isStarting"
                class="bg-brand-primary hover:opacity-90 text-white px-8 py-3.5 rounded-lg font-extrabold text-[10px] uppercase tracking-widest transition-all shadow-md disabled:opacity-50 flex items-center gap-2">
                <i data-lucide="power" class="w-4 h-4" x-show="!isStarting"></i>
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isStarting"></i>
                <span x-text="isStarting ? 'Initializing...' : 'Activate Gateway'"></span>
            </button>
            <p x-show="gatewayStatus === 'OFFLINE'" class="text-rose-500 text-[10px] font-extrabold uppercase tracking-widest mt-1">
                ⚠ Node.js server is unreachable. Make sure it's running.
            </p>
        </div>

        <!-- WAITING FOR SCAN state -->
        <div x-show="gatewayStatus === 'WAITING_FOR_SCAN'" class="flex flex-col items-center gap-4">
            <div class="bg-stone-50 p-4 rounded-xl border border-stone-200 shadow-inner relative">
                <div x-show="qrLoading" class="absolute inset-0 bg-white/80 rounded-xl flex items-center justify-center">
                    <i data-lucide="loader-2" class="w-8 h-8 text-brand-primary animate-spin"></i>
                </div>
                <img :src="qrCodeData" alt="WhatsApp QR Code" class="w-60 h-60 object-contain rounded-lg" x-show="qrCodeData">
                <div x-show="!qrCodeData && !qrLoading" class="w-60 h-60 flex flex-col items-center justify-center text-stone-400 bg-white rounded-lg gap-2 border border-stone-200">
                    <i data-lucide="qr-code" class="w-12 h-12"></i>
                    <span class="text-[10px] font-extrabold uppercase tracking-widest">Generating QR...</span>
                </div>
            </div>
            <p class="text-[10px] font-extrabold uppercase tracking-widest text-stone-900">Scan with WhatsApp on your phone</p>
            <p class="text-[9px] font-extrabold text-stone-500 uppercase tracking-widest">Open WhatsApp → Linked Devices → Link a Device</p>
        </div>
    </div>

    <!-- ─── MAIN BROADCAST DASHBOARD ─────────────────────────────────────── -->
    <div x-show="gatewayStatus === 'CONNECTED' || gatewayStatus === 'RECONNECTING'" x-cloak
         class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- LEFT: Compose -->
        <div class="xl:col-span-2 space-y-5">

            <!-- Compose Card -->
            <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
                <div class="px-7 py-5 border-b border-stone-100 flex items-center justify-between">
                    <h2 class="font-extrabold text-stone-900 text-sm uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="pencil-line" class="w-4 h-4 text-brand-primary"></i> Compose Message
                    </h2>
                    <div class="flex gap-2">
                        <span x-show="selectedCustomers.length > 0" class="px-3 py-1.5 bg-brand-light text-brand-primary text-[10px] font-extrabold uppercase tracking-widest rounded-lg tabular-nums">
                            <span x-text="selectedCustomers.length"></span> recipients
                        </span>
                    </div>
                </div>

                <div class="p-7 space-y-5">
                    <!-- Template Picker -->
                    <div x-data="{ open: false, search: '' }">
                        <label class="block text-[9px] font-extrabold text-stone-500 uppercase tracking-widest mb-2">Load from Template</label>
                        <div class="relative">
                            <div @click="open = !open"
                                 class="w-full flex items-center justify-between bg-stone-50 border border-stone-200 rounded-lg px-4 py-3.5 cursor-pointer hover:border-brand-primary/50 transition-all">
                                <span class="text-[10px] font-extrabold uppercase tracking-widest text-stone-700" x-text="selectedTemplateName || 'Select a template...'"></span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-stone-400 transition-transform" :class="open && 'rotate-180'"></i>
                            </div>
                            <!-- Dropdown -->
                            <div x-show="open" @click.outside="open = false"
                                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute top-full left-0 right-0 mt-2 bg-white border border-stone-200 rounded-lg shadow-xl z-30 overflow-hidden">
                                <div class="p-3 border-b border-stone-100">
                                    <input x-model="search" type="text" placeholder="SEARCH TEMPLATES..."
                                           class="w-full text-[10px] font-extrabold uppercase tracking-widest bg-stone-50 border border-stone-200 rounded-lg px-3 py-2 outline-none focus:border-brand-primary">
                                </div>
                                <div class="max-h-52 overflow-y-auto custom-scrollbar">
                                    @forelse($templates as $tmpl)
                                    <div x-show="search === '' || '{{ strtolower($tmpl->name) }}'.includes(search.toLowerCase())"
                                         @click="loadTemplate({{ $tmpl->id }}, '{{ addslashes($tmpl->name) }}', `{{ addslashes($tmpl->message) }}`); open = false"
                                         class="flex items-start gap-3 p-3 hover:bg-stone-50 cursor-pointer transition-colors group border-b border-stone-50 last:border-0">
                                        <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-widest rounded shrink-0 mt-0.5 border border-transparent
                                            {{ $tmpl->type === 'promotion' ? 'bg-brand-light text-brand-primary border-brand-primary/20' : ($tmpl->type === 'info' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                            {{ $tmpl->type }}
                                        </span>
                                        <div>
                                            <div class="text-[10px] font-extrabold uppercase tracking-widest text-stone-900 group-hover:text-brand-primary">{{ $tmpl->name }}</div>
                                            <div class="text-[9px] text-stone-500 mt-0.5 line-clamp-1 font-extrabold">{{ $tmpl->message }}</div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="p-6 text-center text-stone-400 text-[10px] font-extrabold uppercase tracking-widest">
                                        No templates. <a href="{{ route('master.index') }}" class="text-brand-primary hover:underline">Create one →</a>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Image Picker -->
                    <div x-data="{ dragging: false }">
                        <label class="block text-[9px] font-extrabold text-stone-500 uppercase tracking-widest mb-2">Attach Image (Optional)</label>
                        <div 
                            class="relative min-h-[120px] rounded-lg border-2 border-dashed transition-all flex flex-col items-center justify-center p-4 group"
                            :class="[
                                dragging ? 'border-brand-primary bg-brand-light' : 'border-stone-300 bg-stone-50 hover:border-brand-primary/50',
                                selectedImagePreview ? 'border-emerald-300 bg-emerald-50/10' : ''
                            ]"
                            @dragover.prevent="dragging = true"
                            @dragleave.prevent="dragging = false"
                            @drop.prevent="dragging = false; handleImageDrop($event)">
                            
                            <input type="file" @change="handleImageSelect" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" id="imageInput">
                            
                            <!-- State: No Image -->
                            <div x-show="!selectedImagePreview" class="flex flex-col items-center gap-2 pointer-events-none">
                                <div class="w-10 h-10 bg-white border border-stone-200 rounded-lg shadow-sm flex items-center justify-center text-stone-400 group-hover:text-brand-primary transition-colors">
                                    <i data-lucide="image-plus" class="w-5 h-5"></i>
                                </div>
                                <div class="text-[10px] font-extrabold text-stone-600 uppercase tracking-widest">Drop image here or click to upload</div>
                                <div class="text-[9px] font-extrabold text-stone-400 uppercase tracking-widest">JPG, PNG, WEBP (Max 5MB)</div>
                            </div>

                            <!-- State: Image Preview -->
                            <div x-show="selectedImagePreview" class="relative w-full flex items-center gap-4 py-2 px-2" x-cloak>
                                <div class="relative w-20 h-20 shrink-0">
                                    <img :src="selectedImagePreview" class="w-full h-full object-cover rounded-lg shadow-sm border border-stone-200">
                                    <button @click.stop="clearImage" class="absolute -top-2 -right-2 w-5 h-5 bg-stone-900 text-white rounded flex items-center justify-center shadow-lg hover:bg-rose-500 active:scale-95 transition-all z-20">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                    </button>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[10px] font-extrabold text-stone-900 uppercase tracking-widest truncate" x-text="selectedImageFile ? selectedImageFile.name : 'Selected Image'"></div>
                                    <div class="text-[9px] font-extrabold text-stone-400 uppercase tracking-widest tabular-nums mt-0.5" x-text="selectedImageFile ? (selectedImageFile.size / 1024 / 1024).toFixed(2) + ' MB' : ''"></div>
                                    <div class="mt-2 inline-flex items-center gap-1.5 px-2 py-0.5 bg-brand-light border border-brand-primary/20 text-brand-primary text-[9px] font-extrabold uppercase tracking-widest rounded">
                                        <i data-lucide="check" class="w-2.5 h-2.5"></i> Attached
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Textarea -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-[9px] font-extrabold text-stone-500 uppercase tracking-widest">Message Content</label>
                            <div class="flex gap-1.5">
                                <button @click="messageBody += '{name}'"
                                    class="text-[9px] font-extrabold px-2.5 py-1 bg-brand-light hover:opacity-80 text-brand-primary rounded border border-brand-primary/20 transition-all uppercase tracking-widest">
                                    + {name}
                                </button>
                            </div>
                        </div>
                        <textarea x-model="messageBody" rows="7"
                            class="w-full bg-stone-50 border border-stone-200 text-stone-900 text-sm rounded-lg focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary block p-4 transition-all outline-none resize-none placeholder-stone-400 font-extrabold leading-relaxed"
                            placeholder="Hello {name}, we have an exclusive promo just for you!&#10;&#10;💫 Special discount this weekend only..."></textarea>
                        <div class="flex justify-between mt-2">
                            <p class="text-[9px] text-stone-500 uppercase font-extrabold tracking-widest">Tip: Use <code class="text-brand-primary bg-brand-light px-1 rounded shadow-sm"> {name} </code> to personalise.</p>
                            <p class="text-[10px] font-extrabold uppercase tabular-nums" :class="messageBody.length > 900 ? 'text-rose-500' : 'text-stone-400'">
                                <span x-text="messageBody.length"></span>/1000
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Broadcast Results -->
            <div x-show="broadcastResults.length > 0" class="bg-white rounded-xl border border-stone-200 shadow-sm p-6 mt-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-extrabold text-stone-900 text-sm uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="activity" class="w-4 h-4 text-brand-primary"></i> Last Broadcast Results
                    </h3>
                    <div class="flex gap-2">
                        <span class="flex items-center gap-1 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded border border-emerald-100 uppercase tracking-widest tabular-nums">
                            <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                            <span x-text="broadcastResults.filter(r => r.status === 'success').length"></span> sent
                        </span>
                        <span class="flex items-center gap-1 text-[10px] font-extrabold text-rose-600 bg-rose-50 px-2.5 py-1 rounded border border-rose-100 uppercase tracking-widest tabular-nums">
                            <i data-lucide="x-circle" class="w-3 h-3"></i>
                            <span x-text="broadcastResults.filter(r => r.status === 'failed').length"></span> failed
                        </span>
                    </div>
                </div>
                <div class="max-h-52 overflow-y-auto space-y-1.5 pr-1 custom-scrollbar">
                    <template x-for="(res, i) in broadcastResults" :key="i">
                        <div class="flex items-center justify-between px-4 py-3 bg-stone-50 rounded-lg border border-stone-100">
                            <span class="font-bold text-[10px] text-stone-700 uppercase tracking-widest tabular-nums" x-text="res.to"></span>
                            <div x-show="res.status === 'success'" class="flex items-center gap-1 text-emerald-600 text-[10px] font-extrabold uppercase tracking-widest">
                                <i data-lucide="check" class="w-3 h-3"></i> Sent
                            </div>
                            <div x-show="res.status === 'failed'" class="text-[9px] font-extrabold text-rose-600 bg-rose-50 px-2.5 py-1 rounded border border-rose-100 uppercase tracking-widest truncate max-w-[200px]" x-text="res.error || 'Failed'"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- RIGHT: Audience Selector -->
        <div class="space-y-5">
            <!-- Mode Switcher -->
            <div class="bg-white rounded-xl border border-stone-200 shadow-sm p-6">
                <p class="text-[9px] font-extrabold text-stone-500 uppercase tracking-widest mb-3">Audience Mode</p>
                <div class="grid grid-cols-2 gap-2">
                    <button @click="audienceMode = 'manual'; selectedCustomers = []"
                        class="flex flex-col items-center gap-1.5 p-3 rounded-lg font-extrabold text-[10px] uppercase tracking-widest transition-all border-2 outline-none"
                        :class="audienceMode === 'manual' ? 'border-brand-primary bg-brand-light text-brand-primary shadow-sm' : 'border-stone-100 bg-stone-50 text-stone-500 hover:border-stone-300'">
                        <i data-lucide="list-checks" class="w-4 h-4"></i>
                        Manual Pick
                    </button>
                    <button @click="audienceMode = 'tag'; selectedCustomers = []; tagCustomers = []"
                        class="flex flex-col items-center gap-1.5 p-3 rounded-lg font-extrabold text-[10px] uppercase tracking-widest transition-all border-2 outline-none"
                        :class="audienceMode === 'tag' ? 'border-brand-primary bg-brand-light text-brand-primary shadow-sm' : 'border-stone-100 bg-stone-50 text-stone-500 hover:border-stone-300'">
                        <i data-lucide="tag" class="w-4 h-4"></i>
                        By Tag
                    </button>
                </div>
            </div>

            <!-- TAG MODE -->
            <div x-show="audienceMode === 'tag'" class="bg-white rounded-xl border border-stone-200 shadow-sm flex flex-col" style="max-height: 540px;">
                <div class="p-5 border-b border-stone-100 shrink-0">
                    <p class="text-[9px] font-extrabold text-stone-500 uppercase tracking-widest mb-3">Select Tag</p>
                    <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto custom-scrollbar">
                        @foreach($tags as $tag)
                        <button @click="selectTag({{ $tag->id }}, '{{ addslashes($tag->name) }}')"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[9px] font-extrabold uppercase tracking-widest transition-all border outline-none"
                            :class="selectedTagId === {{ $tag->id }}
                                ? 'bg-stone-900 text-brand-light border-stone-900 shadow-sm'
                                : 'bg-stone-50 text-stone-600 border-stone-200 hover:border-brand-primary/50 hover:text-brand-primary'">
                            {{ $tag->name }}
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black tabular-nums"
                                :class="selectedTagId === {{ $tag->id }} ? 'bg-white/20 text-white' : 'bg-stone-200 text-stone-500'">
                                {{ $tag->customer_count }}
                            </span>
                        </button>
                        @endforeach
                        @if($tags->isEmpty())
                        <p class="text-[10px] text-stone-400 font-extrabold uppercase tracking-widest">No tags with customer data found.</p>
                        @endif
                    </div>
                </div>

                <!-- Customer list for selected tag -->
                <div class="flex-1 overflow-y-auto p-4 space-y-1.5 custom-scrollbar">
                    <div x-show="tagLoading" class="flex items-center justify-center py-10 text-stone-400">
                        <i data-lucide="loader-2" class="w-6 h-6 animate-spin mr-2"></i>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest">Loading customers...</span>
                    </div>
                    <div x-show="!tagLoading && !selectedTagId" class="flex flex-col items-center justify-center py-10 text-stone-400 gap-2">
                        <i data-lucide="mouse-pointer-click" class="w-8 h-8 opacity-40"></i>
                        <p class="text-[10px] font-extrabold text-center uppercase tracking-widest max-w-[200px]">Select a tag above to see which customers should receive this broadcast.</p>
                    </div>
                    <div x-show="!tagLoading && selectedTagId && tagCustomers.length === 0" class="flex flex-col items-center py-10 text-stone-400 gap-2">
                        <i data-lucide="user-x" class="w-8 h-8 opacity-40"></i>
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-center max-w-[200px]">No customers with phone numbers found for this tag.</p>
                    </div>
                    <div x-show="!tagLoading && tagCustomers.length > 0" class="space-y-1">
                        <!-- Select all toggle for tag mode -->
                        <div @click="toggleSelectAllTagCustomers"
                             class="flex items-center p-3 bg-brand-light rounded-lg cursor-pointer border border-brand-primary/20 mb-2">
                            <div class="w-4 h-4 rounded border-2 flex items-center justify-center mr-2.5 shrink-0 transition-colors"
                                 :class="selectedCustomers.length === tagCustomers.length && tagCustomers.length > 0 ? 'bg-brand-primary border-brand-primary' : 'border-stone-300 bg-white'">
                                <svg x-show="selectedCustomers.length === tagCustomers.length && tagCustomers.length > 0" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="text-[10px] font-extrabold uppercase tracking-widest text-brand-primary tabular-nums">
                                Select All (<span x-text="tagCustomers.length"></span> customers)
                            </div>
                        </div>
                        <template x-for="(cust, idx) in tagCustomers" :key="cust.id">
                            <label class="flex items-center p-3 hover:bg-stone-50 rounded-lg transition-colors cursor-pointer border border-transparent hover:border-stone-100 group">
                                <input type="checkbox" :value="String(cust.id)" x-model="selectedCustomers"
                                    class="w-4 h-4 rounded border-stone-300 text-brand-primary focus:ring-brand-primary/20 mr-2.5 shrink-0 cursor-pointer">
                                <div class="flex-1 min-w-0">
                                    <div class="text-[10px] font-extrabold text-stone-900 uppercase tracking-widest truncate" x-text="cust.name"></div>
                                    <div class="text-[9px] text-stone-500 font-extrabold tracking-widest mt-0.5 tabular-nums" x-text="cust.phone"></div>
                                </div>
                                <div class="shrink-0 ml-2">
                                    <span class="px-2 py-0.5 bg-stone-100 text-stone-600 text-[9px] font-extrabold uppercase tracking-widest rounded border border-stone-200 tabular-nums">
                                        <span x-text="cust.tag_count"></span>x
                                    </span>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <!-- MANUAL MODE -->
            <div x-show="audienceMode === 'manual'" class="bg-white rounded-xl border border-stone-200 shadow-sm flex flex-col" style="max-height: 540px;">
                <div class="p-5 border-b border-stone-100 shrink-0">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[10px] font-extrabold text-stone-900 uppercase tracking-widest">All Customers</h3>
                        <button @click="toggleSelectAllManual"
                            class="text-[9px] font-extrabold text-brand-primary hover:opacity-80 transition-colors uppercase tracking-widest outline-none" x-text="selectedCustomers.length === eligibleCount ? 'Deselect All' : 'Select All'">
                        </button>
                    </div>
                    <!-- Search -->
                    <div class="relative mt-4">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input x-model="manualSearch" type="text" placeholder="SEARCH BY NAME OR PHONE..."
                               class="w-full pl-9 pr-3 py-2.5 bg-stone-50 border border-stone-200 rounded-lg text-[9px] font-extrabold uppercase tracking-widest focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary outline-none transition-all">
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-3 custom-scrollbar">
                    @foreach($customers as $customer)
                        @if($customer->phone)
                        <label x-show="manualSearch === '' || '{{ strtolower($customer->name . ' ' . $customer->phone) }}'.includes(manualSearch.toLowerCase())"
                               class="flex items-center p-3 hover:bg-stone-50 rounded-lg transition-colors cursor-pointer border border-transparent hover:border-stone-100 group">
                            <input type="checkbox" value="{{ $customer->id }}" x-model="selectedCustomers"
                                class="w-4 h-4 rounded border-stone-300 text-brand-primary focus:ring-brand-primary/20 mr-3 shrink-0 cursor-pointer">
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] font-extrabold text-stone-900 group-hover:text-brand-primary transition-colors truncate uppercase tracking-widest">{{ $customer->name }}</div>
                                <div class="text-[9px] text-stone-400 font-extrabold mt-0.5 tracking-widest tabular-nums">{{ $customer->phone }}</div>
                            </div>
                            <span class="px-2 py-1 bg-stone-100 text-stone-600 text-[9px] uppercase font-extrabold rounded border border-stone-200 shrink-0 ml-2 tracking-widest">
                                {{ Str::replace('VIP ', '', $customer->masterLevel->name ?? '—') }}
                            </span>
                        </label>
                        @else
                        <div x-show="manualSearch === '' || '{{ strtolower($customer->name) }}'.includes(manualSearch.toLowerCase())"
                             class="flex items-center p-3 opacity-40 rounded-lg cursor-not-allowed border border-transparent">
                            <input type="checkbox" disabled class="w-4 h-4 rounded border-stone-200 bg-stone-100 mr-3 shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] font-extrabold text-stone-500 truncate uppercase tracking-widest">{{ $customer->name }}</div>
                                <div class="text-[9px] text-rose-400 font-extrabold flex items-center gap-1.5 mt-0.5 uppercase tracking-widest">
                                    <i data-lucide="alert-circle" class="w-3 h-3"></i> No phone
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Send Button Panel -->
            <div class="bg-white rounded-xl border border-stone-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <span class="text-[10px] font-extrabold text-stone-500 uppercase tracking-widest">Recipients:</span>
                    <span class="text-2xl font-extrabold text-stone-900 tabular-nums" x-text="selectedCustomers.length"></span>
                </div>
                <button @click="sendBroadcast" :disabled="selectedCustomers.length === 0 || (!messageBody.trim() && !selectedImageFile) || isSending"
                    class="w-full bg-brand-primary hover:opacity-90 text-white px-6 py-4 rounded-lg font-extrabold text-[10px] uppercase tracking-widest transition-all shadow-md disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-2 outline-none">
                    <i data-lucide="send" class="w-4 h-4" x-show="!isSending"></i>
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isSending"></i>
                    <span x-text="isSending ? 'Broadcasting...' : 'Send Broadcast'"></span>
                </button>
                <p class="text-[9px] font-extrabold uppercase tracking-widest text-stone-400 text-center mt-4">Random delay between messages to protect from bans.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
@endsection

@section('scripts')
<script>
function broadcastApp() {
    return {
        // Gateway
        gatewayStatus: 'LOADING',
        isStarting: false,
        qrCodeData: null,
        qrLoading: false,
        pollInterval: null,

        // Compose
        messageBody: '',
        selectedTemplateName: '',

        // Audience
        audienceMode: 'manual',  // 'manual' | 'tag'
        selectedCustomers: [],
        manualSearch: '',
        eligibleCount: {{ $customers->whereNotNull('phone')->count() }},

        // Tag mode
        selectedTagId: null,
        selectedTagName: '',
        tagCustomers: [],
        tagLoading: false,

        // Results
        isSending: false,
        broadcastResults: [],

        // Image Attachment
        selectedImageFile: null,
        selectedImagePreview: null,

        // ── Init ──────────────────────────────────────────────────────────
        initApp() {
            this.checkStatus();
            this.$watch('gatewayStatus', () => this.refreshIcons());
            this.$watch('isSending', () => this.refreshIcons());
            this.$watch('audienceMode', () => this.refreshIcons());
        },

        refreshIcons() {
            setTimeout(() => typeof lucide !== 'undefined' && lucide.createIcons(), 60);
        },

        // ── Template ──────────────────────────────────────────────────────
        loadTemplate(id, name, message) {
            this.messageBody = message;
            this.selectedTemplateName = name;
        },

        // ── Image Handling ────────────────────────────────────────────────
        handleImageSelect(e) {
            const file = e.target.files[0];
            if (file) this.processImage(file);
        },

        handleImageDrop(e) {
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) this.processImage(file);
        },

        processImage(file) {
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({ icon: 'error', title: 'File too large', text: 'Maximum image size is 5MB' });
                return;
            }
            this.selectedImageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.selectedImagePreview = e.target.result;
                this.refreshIcons();
            };
            reader.readAsDataURL(file);
        },

        clearImage() {
            this.selectedImageFile = null;
            this.selectedImagePreview = null;
            const input = document.getElementById('imageInput');
            if (input) input.value = '';
            this.refreshIcons();
        },

        // ── Tag Audience ─────────────────────────────────────────────────
        async selectTag(tagId, tagName) {
            if (this.selectedTagId === tagId) {
                this.selectedTagId = null;
                this.selectedTagName = '';
                this.tagCustomers = [];
                this.selectedCustomers = [];
                return;
            }
            this.selectedTagId = tagId;
            this.selectedTagName = tagName;
            this.selectedCustomers = [];
            this.tagLoading = true;
            try {
                const res = await fetch(`{{ route('broadcast.customers_by_tag') }}?tag_id=${tagId}`);
                const data = await res.json();
                this.tagCustomers = data;
            } catch (e) {
                console.error('Tag customer fetch error:', e);
                this.tagCustomers = [];
            } finally {
                this.tagLoading = false;
                this.refreshIcons();
            }
        },

        toggleSelectAllTagCustomers() {
            if (this.selectedCustomers.length === this.tagCustomers.length && this.tagCustomers.length > 0) {
                this.selectedCustomers = [];
            } else {
                this.selectedCustomers = this.tagCustomers.map(c => String(c.id));
            }
        },

        toggleSelectAllManual() {
            const allBoxes = document.querySelectorAll('.manual-customer-checkbox:not(:disabled)');
            if (this.selectedCustomers.length === this.eligibleCount) {
                this.selectedCustomers = [];
            } else {
                this.selectedCustomers = Array.from(allBoxes).map(b => b.value);
            }
        },

        // ── Gateway ───────────────────────────────────────────────────────
        async checkStatus() {
            try {
                const res = await fetch("{{ route('broadcast.status') }}");
                if (!res.ok) throw new Error();
                const data = await res.json();
                const prev = this.gatewayStatus;
                this.gatewayStatus = data.status || 'OFFLINE';

                if (this.gatewayStatus === 'WAITING_FOR_SCAN' && !this.qrCodeData) this.fetchQrCode();
                if (this.gatewayStatus === 'CONNECTED') {
                    this.qrCodeData = null;
                    if (this.pollInterval) { clearInterval(this.pollInterval); this.pollInterval = null; }
                }
                if (this.gatewayStatus === 'WAITING_FOR_SCAN' && !this.pollInterval) {
                    this.pollInterval = setInterval(() => this.checkStatus(), 3000);
                }
            } catch {
                this.gatewayStatus = 'OFFLINE';
                if (this.pollInterval) { clearInterval(this.pollInterval); this.pollInterval = null; }
            }
        },

        async startGateway() {
            this.isStarting = true;
            try {
                const res = await fetch("{{ route('broadcast.start') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                });
                if (res.ok) {
                    this.pollInterval = setInterval(() => this.checkStatus(), 3000);
                } else {
                    const d = await res.json();
                    throw new Error(d.error || 'Failed to start');
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.message, customClass: { popup: 'rounded-xl border border-stone-200 shadow-xl' } });
            } finally {
                this.isStarting = false;
            }
        },

        async fetchQrCode() {
            if (this.qrLoading) return;
            this.qrLoading = true;
            try {
                const res = await fetch("{{ route('broadcast.qr') }}");
                if (res.ok) { const d = await res.json(); if (d.qr_base64) this.qrCodeData = d.qr_base64; }
            } catch {} finally { this.qrLoading = false; }
        },

        disconnectGateway() {
            Swal.fire({
                title: 'Disconnect Gateway?',
                text: 'You will need to scan QR code again to reconnect.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Disconnect',
                customClass: {
                    popup: 'rounded-xl border border-stone-200 shadow-xl',
                    confirmButton: 'bg-rose-600 px-6 py-2.5 rounded-lg font-extrabold text-[10px] uppercase tracking-widest text-white',
                    cancelButton: 'bg-stone-100 px-6 py-2.5 rounded-lg font-extrabold text-[10px] uppercase tracking-widest text-stone-600'
                }, buttonsStyling: false
            }).then(async (result) => {
                if (!result.isConfirmed) return;
                await fetch("{{ route('broadcast.disconnect') }}", {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                this.gatewayStatus = 'DISCONNECTED';
                this.qrCodeData = null;
            });
        },

        // ── Broadcast ─────────────────────────────────────────────────────
        sendBroadcast() {
            if (!this.selectedCustomers.length || (!this.messageBody.trim() && !this.selectedImageFile)) return;
            Swal.fire({
                title: 'Send Broadcast?',
                html: `Send to <strong>${this.selectedCustomers.length}</strong> recipient(s)?${this.selectedTagName ? `<br><small class="text-slate-500">Tag: <b>${this.selectedTagName}</b></small>` : ''}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Send Now',
                customClass: {
                    popup: 'rounded-xl border border-stone-200 shadow-xl',
                    confirmButton: 'bg-brand-primary px-6 py-2.5 rounded-lg font-extrabold text-[10px] uppercase tracking-widest text-white hover:opacity-90 transition-all',
                    cancelButton: 'bg-stone-100 px-6 py-2.5 rounded-lg font-extrabold text-[10px] uppercase tracking-widest text-stone-600 hover:bg-stone-200 transition-all'
                }, buttonsStyling: false
            }).then(async (result) => {
                if (!result.isConfirmed) return;
                this.isSending = true;
                this.broadcastResults = [];
                try {
                    const formData = new FormData();
                    formData.append('message', this.messageBody);
                    if (this.selectedImageFile) {
                        formData.append('image', this.selectedImageFile);
                    }
                    this.selectedCustomers.forEach(id => {
                        formData.append('customers[]', id);
                    });

                    const res = await fetch("{{ route('broadcast.send') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.broadcastResults = data.results || [];
                        Swal.fire({
                            icon: 'success',
                            title: 'Broadcast Completed!',
                            html: `✅ <b>${data.success}</b> sent &nbsp;|&nbsp; ❌ <b>${data.failed}</b> failed`,
                            customClass: {
                                popup: 'rounded-xl border border-stone-200 shadow-xl',
                                confirmButton: 'bg-brand-primary px-6 py-2.5 rounded-lg font-extrabold text-[10px] uppercase tracking-widest text-white hover:opacity-90 transition-all'
                            }, buttonsStyling: false
                        });
                    } else {
                        throw new Error(data.error || 'Failed to send broadcast');
                    }
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: e.message });
                } finally {
                    this.isSending = false;
                    this.refreshIcons();
                }
            });
        }
    };
}
</script>
@endsection
