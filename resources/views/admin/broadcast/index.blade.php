@extends('layouts.admin')

@section('content')
<div x-data="broadcastApp()" x-init="initApp()" class="max-w-7xl mx-auto space-y-6 lg:space-y-8">

    <!-- ─── Premium Page Header ─────────────────────────────────────────── -->
    <div class="relative overflow-hidden bg-stone-900 rounded-md p-10 shadow-2xl border border-white/5 mb-8">
        <!-- Abstract Background Decorative Orbs -->
        <div class="absolute top-0 right-0 -translate-y-1/3 translate-x-1/3 w-96 h-96 bg-brand-primary/10 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/4 w-64 h-64 bg-amber-500/10 rounded-full blur-[80px]"></div>
        
        <div class="relative flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary/10 rounded-md mb-4 border border-brand-primary/20 backdrop-blur-md">
                    <i data-lucide="zap" class="w-4 h-4 text-brand-primary animate-pulse"></i>
                    <span class="text-[10px] font-extrabold text-brand-primary uppercase tracking-widest">Broadcast HQ</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tighter mb-2 uppercase leading-none">
                    Campaign <span class="text-brand-primary">Manager</span>
                </h1>
                <p class="text-stone-400 font-bold max-w-md text-sm leading-relaxed">Direct engagement platform. Send targeted WhatsApp campaigns with precision and real-time tracking.</p>
            </div>

            <div class="flex items-center gap-4 flex-wrap justify-center shrink-0" x-cloak>
                <!-- Connected Status -->
                <div x-show="gatewayStatus === 'CONNECTED'" 
                     class="group flex items-center gap-3 px-6 py-4 bg-emerald-500/10 backdrop-blur-xl border border-emerald-500/30 rounded-md transition-all">
                    <div class="relative">
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <div class="absolute inset-0 w-3 h-3 rounded-full bg-emerald-500 animate-ping opacity-75"></div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-emerald-400 text-[10px] font-extrabold uppercase tracking-[0.2em] leading-none mb-1">Gateway Status</span>
                        <span class="text-white text-sm font-black uppercase">Online & Operational</span>
                    </div>
                </div>

                <!-- Error/Offline Status -->
                <div x-show="gatewayStatus === 'DISCONNECTED' || gatewayStatus === 'OFFLINE'" 
                     class="flex items-center gap-3 px-6 py-4 bg-rose-500/10 backdrop-blur-xl border border-rose-500/30 rounded-md">
                    <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                    <div class="flex flex-col">
                        <span class="text-rose-400 text-[10px] font-extrabold uppercase tracking-[0.2em] leading-none mb-1">Gateway Status</span>
                        <span class="text-white text-sm font-black uppercase">System Offline</span>
                    </div>
                </div>

                <!-- Scan Status -->
                <div x-show="gatewayStatus === 'WAITING_FOR_SCAN'" 
                     class="flex items-center gap-3 px-6 py-4 bg-amber-500/10 backdrop-blur-xl border border-amber-500/30 rounded-md">
                    <div class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></div>
                    <div class="flex flex-col">
                        <span class="text-amber-400 text-[10px] font-extrabold uppercase tracking-[0.2em] leading-none mb-1">Action Required</span>
                        <span class="text-white text-sm font-black uppercase">Scan QR Code</span>
                    </div>
                </div>

                <button x-show="gatewayStatus === 'CONNECTED'" @click="disconnectGateway"
                    class="px-5 py-4 bg-white/5 hover:bg-rose-500/20 border border-white/10 text-white font-extrabold text-[10px] uppercase tracking-widest rounded-md transition-all shadow-xl backdrop-blur-md group">
                    <i data-lucide="power" class="w-4 h-4 text-rose-500 group-hover:rotate-90 transition-transform inline-block mr-2"></i>
                    Disconnect
                </button>
            </div>
        </div>
    </div>

    <!-- ─── Premium ONBOARDING PANEL ─────────────────────────────────────── -->
    <template x-if="gatewayStatus !== 'CONNECTED' && gatewayStatus !== 'LOADING' && gatewayStatus !== 'RECONNECTING'">
        <div class="relative overflow-hidden bg-white rounded-md p-1 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:20px_20px]">
            <div class="bg-white/40 backdrop-blur-[2px] rounded border border-stone-200 shadow-2xl p-12 lg:p-20 flex flex-col items-center text-center max-w-2xl mx-auto my-12">
                <div class="relative mb-10">
                    <div class="absolute inset-0 bg-brand-primary/20 blur-2xl rounded-full animate-pulse"></div>
                    <div class="relative w-24 h-24 bg-stone-900 text-brand-primary rounded-md flex items-center justify-center shadow-2xl border border-white/10 transform -rotate-3 hover:rotate-0 transition-transform">
                        <i data-lucide="smartphone" class="w-10 h-10"></i>
                    </div>
                </div>
                
                <h2 class="text-3xl font-black text-stone-900 mb-4 uppercase tracking-tighter">Initialize <span class="text-brand-primary">Handshake</span></h2>
                <p class="text-stone-500 mb-10 text-xs font-bold uppercase tracking-widest leading-relaxed max-w-md">
                    Secure your connection via Baileys API to unlock high-volume broadcasting capabilities to your guest lists.
                </p>

                <!-- DISCONNECTED state -->
                <div x-show="gatewayStatus === 'DISCONNECTED' || gatewayStatus === 'OFFLINE'" class="w-full flex flex-col items-center gap-4">
                    <button @click="startGateway" :disabled="isStarting"
                        class="group relative bg-stone-900 hover:bg-stone-800 text-white px-12 py-5 rounded-md font-black text-[11px] uppercase tracking-[0.2em] transition-all shadow-2xl disabled:opacity-50 overflow-hidden">
                        <div class="absolute inset-0 bg-brand-primary/20 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-500"></div>
                        <div class="relative flex items-center gap-3">
                            <i data-lucide="zap" class="w-4 h-4 text-brand-primary" x-show="!isStarting"></i>
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isStarting"></i>
                            <span x-text="isStarting ? 'Synchronizing System...' : 'Establish Secure Connection'"></span>
                        </div>
                    </button>
                    <p x-show="gatewayStatus === 'OFFLINE'" class="flex items-center gap-2 text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 bg-rose-50 px-4 py-2 rounded-md border border-rose-100">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Node.js Gateway Server is currently Unreachable
                    </p>
                </div>

                <!-- WAITING FOR SCAN state -->
                <div x-show="gatewayStatus === 'WAITING_FOR_SCAN'" class="w-full flex flex-col items-center gap-8">
                    <div class="relative p-6 bg-stone-900 rounded-md shadow-2xl overflow-hidden group">
                        <!-- Scanning Animation Borders -->
                        <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-brand-primary"></div>
                        <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-brand-primary"></div>
                        <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-brand-primary"></div>
                        <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-brand-primary"></div>

                        <div x-show="qrLoading" class="absolute inset-0 bg-stone-900/90 rounded-md flex flex-col items-center justify-center z-20">
                            <div class="w-12 h-12 border-4 border-brand-primary border-t-transparent rounded-full animate-spin mb-4"></div>
                            <span class="text-white text-[10px] font-black uppercase tracking-widest">Generating Token</span>
                        </div>

                        <div class="relative bg-white p-2 rounded shadow-inner">
                            <img :src="qrCodeData" alt="WhatsApp QR Code" class="w-64 h-64 object-contain" x-show="qrCodeData">
                            <div x-show="!qrCodeData && !qrLoading" class="w-64 h-64 flex flex-col items-center justify-center text-stone-300 bg-stone-900 gap-4">
                                <i data-lucide="qr-code" class="w-16 h-16 opacity-20"></i>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-stone-500">Waiting for Gateway...</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-center gap-4">
                            <div class="w-8 h-[1px] bg-stone-200"></div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-stone-900">Secure Scan Protocol</span>
                            <div class="w-8 h-[1px] bg-stone-200"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-stone-50 p-4 rounded-md border border-stone-200 text-left">
                                <span class="block text-stone-400 text-[9px] font-black mb-1 uppercase tracking-widest">Step 01</span>
                                <p class="text-stone-900 text-[10px] font-black uppercase leading-tight">Open WhatsApp on your device</p>
                            </div>
                            <div class="bg-stone-50 p-4 rounded-md border border-stone-200 text-left">
                                <span class="block text-stone-400 text-[9px] font-black mb-1 uppercase tracking-widest">Step 02</span>
                                <p class="text-stone-900 text-[10px] font-black uppercase leading-tight">Link device to Gateway ID</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- ─── MAIN BROADCAST DASHBOARD ─────────────────────────────────────── -->
    <div x-show="gatewayStatus === 'CONNECTED' || gatewayStatus === 'RECONNECTING'" x-cloak
         class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">

        <!-- LEFT: Compose (expanded to 3 columns) -->
        <div class="lg:col-span-3 space-y-8">

            <!-- Compose Card -->
            <div class="bg-white rounded-md border border-stone-200 shadow-xl overflow-hidden transition-all hover:shadow-2xl">
                <div class="px-8 py-8 bg-stone-50 border-b border-stone-100 flex items-center justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-brand-primary/5 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
                    <h2 class="font-black text-stone-900 text-[12px] uppercase tracking-[0.25em] flex items-center gap-4 relative z-10">
                        <div class="p-2 bg-brand-primary text-white rounded shadow-lg">
                            <i data-lucide="pencil-line" class="w-4 h-4"></i>
                        </div>
                        Craft Transmission
                    </h2>
                    <div class="flex gap-2 relative z-10">
                        <span x-show="selectedCustomers.length > 0" class="px-4 py-2 bg-brand-primary text-white text-[10px] font-black uppercase tracking-widest rounded shadow-xl animate-in zoom-in">
                            <span x-text="selectedCustomers.length"></span> Targeted Recipients
                        </span>
                    </div>
                </div>

                <div class="p-10 space-y-10">
                    <!-- Template Picker -->
                    <div x-data="{ open: false, search: '' }" class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="h-[1px] flex-1 bg-stone-100"></div>
                            <label class="text-[9px] font-black text-stone-400 uppercase tracking-[0.25em]">Canned Messages</label>
                            <div class="h-[1px] flex-1 bg-stone-100"></div>
                        </div>
                        <div class="relative">
                            <div @click="open = !open"
                                 class="w-full flex items-center justify-between bg-stone-50 border-2 border-stone-100 rounded-md px-5 py-4 cursor-pointer hover:border-brand-primary/30 transition-all group">
                                <span class="text-[11px] font-black uppercase tracking-widest text-stone-700 group-hover:text-brand-primary transition-colors" x-text="selectedTemplateName || 'SELECT TEMPLATE ARCHIVE...'"></span>
                                <i data-lucide="database" class="w-4 h-4 text-stone-400 group-hover:text-brand-primary transition-colors"></i>
                            </div>
                            <!-- Dropdown -->
                            <div x-show="open" @click.outside="open = false"
                                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 class="absolute top-full left-0 right-0 mt-3 bg-white border border-stone-200 rounded-md shadow-[0_20px_50px_rgba(0,0,0,0.15)] z-40 overflow-hidden backdrop-blur-xl">
                                <div class="p-4 bg-stone-50 border-b border-stone-100">
                                    <div class="relative">
                                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-stone-400"></i>
                                        <input x-model="search" type="text" placeholder="FILTER TEMPLATES..."
                                               class="w-full text-[10px] font-black uppercase tracking-widest bg-white border border-stone-200 rounded px-10 py-3 outline-none focus:border-brand-primary shadow-inner">
                                    </div>
                                </div>
                                <div class="max-h-64 overflow-y-auto custom-scrollbar">
                                    @forelse($templates as $tmpl)
                                    <div x-show="search === '' || '{{ strtolower($tmpl->name) }}'.includes(search.toLowerCase())"
                                         @click="loadTemplate({{ $tmpl->id }}, '{{ addslashes($tmpl->name) }}', `{{ addslashes($tmpl->message) }}`); open = false"
                                         class="flex items-start gap-4 p-5 hover:bg-brand-primary/5 cursor-pointer transition-all group border-b border-stone-50 last:border-0 relative">
                                        <div class="w-1.5 h-0 bg-brand-primary absolute left-0 top-1/2 -translate-y-1/2 group-hover:h-full transition-all"></div>
                                        <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-[0.2em] rounded shrink-0 mt-1
                                            {{ $tmpl->type === 'promotion' ? 'bg-brand-light text-brand-primary border border-brand-primary/20' : ($tmpl->type === 'info' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                            {{ $tmpl->type }}
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-[11px] font-black uppercase tracking-widest text-stone-900 mb-1 group-hover:text-brand-primary transition-colors">{{ $tmpl->name }}</div>
                                            <div class="text-[10px] text-stone-500 line-clamp-1 italic font-medium">{{ $tmpl->message }}</div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="p-10 text-center flex flex-col items-center gap-4">
                                        <i data-lucide="inbox" class="w-10 h-10 text-stone-200"></i>
                                        <p class="text-[10px] text-stone-400 font-black uppercase tracking-widest leading-relaxed">No archival templates found.<br><a href="{{ route('master.index') }}" class="text-brand-primary hover:underline">Provision New Template →</a></p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Image Picker -->
                    <div x-data="{ dragging: false }">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-[1px] flex-1 bg-stone-100"></div>
                            <label class="text-[9px] font-black text-stone-400 uppercase tracking-[0.25em]">Media Asset</label>
                            <div class="h-[1px] flex-1 bg-stone-100"></div>
                        </div>
                        <div 
                            class="relative min-h-[160px] rounded-md border-2 border-dashed transition-all flex flex-col items-center justify-center p-6 group overflow-hidden"
                            :class="[
                                dragging ? 'border-brand-primary bg-brand-light' : 'border-stone-200 bg-stone-50/50 hover:border-brand-primary/40',
                                selectedImagePreview ? 'border-emerald-300 bg-emerald-50/5' : ''
                            ]"
                            @dragover.prevent="dragging = true"
                            @dragleave.prevent="dragging = false"
                            @drop.prevent="dragging = false; handleImageDrop($event)">
                            
                            <input type="file" @change="handleImageSelect" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" accept="image/*" id="imageInput">
                            
                            <!-- State: No Image -->
                            <div x-show="!selectedImagePreview" class="flex flex-col items-center gap-4 pointer-events-none relative z-10 text-center">
                                <div class="w-14 h-14 bg-white border border-stone-100 rounded-md shadow-lg flex items-center justify-center text-stone-400 group-hover:text-brand-primary group-hover:rotate-12 transition-all duration-300">
                                    <i data-lucide="image-plus" class="w-7 h-7"></i>
                                </div>
                                <div>
                                    <div class="text-[11px] font-black text-stone-900 uppercase tracking-widest mb-1">Drag Asset Here</div>
                                    <div class="text-[9px] font-bold text-stone-400 uppercase tracking-widest">Optimized for JPG, PNG, WEBP</div>
                                </div>
                            </div>

                            <!-- State: Image Preview -->
                            <div x-show="selectedImagePreview" class="relative w-full flex flex-col md:flex-row items-center gap-8 z-10" x-cloak>
                                <div class="relative group/preview">
                                    <div class="absolute inset-0 bg-emerald-400/20 blur-xl opacity-0 group-hover/preview:opacity-100 transition-opacity"></div>
                                    <img :src="selectedImagePreview" class="relative w-32 h-32 object-cover rounded shadow-2xl border-2 border-white ring-8 ring-emerald-500/5">
                                    <button @click.stop="clearImage" class="absolute -top-3 -right-3 w-8 h-8 bg-stone-900 text-white rounded-full flex items-center justify-center shadow-2xl hover:bg-rose-600 transition-all transform hover:scale-110 active:scale-90 z-20 border-2 border-white">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                <div class="flex-1 text-center md:text-left min-w-0">
                                    <div class="text-[12px] font-black text-stone-900 uppercase tracking-widest truncate mb-1" x-text="selectedImageFile ? selectedImageFile.name : 'Unknown Asset'"></div>
                                    <div class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] tabular-nums" x-text="selectedImageFile ? (selectedImageFile.size / 1024 / 1024).toFixed(2) + ' MEGA B' : ''"></div>
                                    <div class="mt-4 flex items-center justify-center md:justify-start gap-4">
                                        <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-500 text-white text-[9px] font-black uppercase tracking-widest rounded shadow-lg">
                                            <i data-lucide="check" class="w-3 h-3"></i> Sync Ready
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Textarea -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="h-[1px] flex-1 bg-stone-100"></div>
                                <label class="text-[9px] font-black text-stone-400 uppercase tracking-[0.25em]">Copywriting</label>
                                <div class="h-[1px] flex-1 bg-stone-100"></div>
                            </div>
                        </div>
                        <div class="relative group">
                            <div class="absolute -inset-1 bg-brand-primary/5 rounded-md opacity-0 group-focus-within:opacity-100 transition-opacity blur"></div>
                            <div class="relative">
                                <textarea x-model="messageBody" rows="8"
                                    class="w-full bg-stone-50 border-2 border-stone-100 text-stone-900 text-sm rounded-md focus:ring-4 focus:ring-brand-primary/5 focus:border-brand-primary block p-6 transition-all outline-none resize-none placeholder-stone-300 font-bold leading-loose shadow-inner"
                                    placeholder="Enter transmission payload..."></textarea>
                                
                                <!-- Placeholder Tags -->
                                <div class="absolute bottom-4 right-4 flex gap-2">
                                    <button @click="messageBody += '{name}'"
                                        class="text-[9px] font-black px-4 py-2 bg-stone-900 text-brand-primary rounded shadow-xl hover:translate-y-[-2px] active:translate-y-0 transition-all uppercase tracking-widest border border-white/10">
                                        + INJECT {NAME}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 px-2">
                            <p class="text-[9px] text-stone-400 uppercase font-black tracking-widest flex items-center gap-2">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-brand-primary"></i> 
                                Tokens like <code class="text-stone-900 bg-stone-100 px-1 rounded font-black italic">{name}</code> are dynamically replaced.
                            </p>
                            <div class="flex items-center gap-3">
                                <div class="h-1.5 w-32 bg-stone-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand-primary transition-all duration-300" :style="`width: ${Math.min(messageBody.length / 10, 100)}%`"></div>
                                </div>
                                <p class="text-[11px] font-black uppercase tabular-nums tracking-widest min-w-[80px] text-right" :class="messageBody.length > 900 ? 'text-rose-500' : 'text-stone-900'">
                                    <span x-text="messageBody.length"></span> <span class="text-stone-400">/ 1000</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Broadcast Results -->
            <div x-show="broadcastResults.length > 0" 
                 x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white rounded-md border-2 border-stone-100 shadow-2xl p-8 mt-8 border-l-4 border-l-brand-primary">
                <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-stone-900 text-brand-primary rounded-md flex items-center justify-center shadow-xl">
                            <i data-lucide="history" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-stone-900 text-xs uppercase tracking-[0.2em] mb-1">Transmission Ledger</h3>
                            <p class="text-[9px] font-black text-stone-400 uppercase tracking-widest">Real-time gateway feedback</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex flex-col items-end px-5 py-3 bg-emerald-50 rounded border border-emerald-100 min-w-[120px]">
                            <span class="text-xs font-black text-emerald-600 tabular-nums uppercase tracking-widest mb-1">
                                <span x-text="broadcastResults.filter(r => r.status === 'success').length"></span> Delivered
                            </span>
                            <div class="h-1 w-full bg-emerald-200 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500" :style="`width: ${(broadcastResults.filter(r => r.status === 'success').length / (broadcastResults.length || 1)) * 100}%` "></div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end px-5 py-3 bg-rose-50 rounded border border-rose-100 min-w-[120px]">
                            <span class="text-xs font-black text-rose-600 tabular-nums uppercase tracking-widest mb-1">
                                <span x-text="broadcastResults.filter(r => r.status === 'failed').length"></span> Dropped
                            </span>
                            <div class="h-1 w-full bg-rose-200 rounded-full overflow-hidden">
                                <div class="h-full bg-rose-500" :style="`width: ${(broadcastResults.filter(r => r.status === 'failed').length / (broadcastResults.length || 1)) * 100}%` "></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="max-h-64 overflow-y-auto space-y-2 pr-3 custom-scrollbar">
                    <template x-for="(res, i) in broadcastResults" :key="i">
                        <div class="flex items-center justify-between px-5 py-4 bg-stone-50 rounded border border-stone-100 hover:border-brand-primary/20 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full" :class="res.status === 'success' ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.5)]'"></div>
                                <span class="font-black text-[11px] text-stone-900 uppercase tracking-[0.1em] tabular-nums" x-text="res.to"></span>
                            </div>
                            <div x-show="res.status === 'success'" class="flex items-center gap-2 text-emerald-600 text-[10px] font-black uppercase tracking-[0.2em] bg-emerald-100/50 px-3 py-1.5 rounded">
                                <i data-lucide="check-check" class="w-3.5 h-3.5"></i> Finalized
                            </div>
                            <div x-show="res.status === 'failed'" class="flex items-center gap-2 text-rose-600 bg-rose-100 px-3 py-1.5 rounded text-[9px] font-black uppercase tracking-widest max-w-[200px]" :title="res.error">
                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> <span class="truncate" x-text="res.error || 'Request Failed'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- RIGHT: Audience Selector (adjusted to 2 columns) -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Mode Switcher -->
            <div class="bg-stone-900 rounded-md shadow-2xl p-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-brand-primary"></div>
                <p class="text-[10px] font-black text-stone-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-brand-primary"></i>
                    Segmentation Mode
                </p>
                <div class="grid grid-cols-2 gap-4">
                    <button @click="audienceMode = 'manual'; selectedCustomers = []"
                        class="relative flex flex-col items-center gap-3 p-5 rounded border-2 transition-all outline-none group overflow-hidden"
                        :class="audienceMode === 'manual' ? 'border-brand-primary bg-brand-primary/10 text-white' : 'border-white/5 bg-white/5 text-stone-500 hover:border-white/10'">
                        <div class="absolute inset-0 bg-brand-primary/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <i data-lucide="hand" class="w-5 h-5 relative z-10" :class="audienceMode === 'manual' ? 'text-brand-primary' : ''"></i>
                        <span class="text-[11px] font-black uppercase tracking-widest relative z-10">Atomic</span>
                    </button>
                    <button @click="audienceMode = 'tag'; selectedCustomers = []; tagCustomers = []"
                        class="relative flex flex-col items-center gap-3 p-5 rounded border-2 transition-all outline-none group overflow-hidden"
                        :class="audienceMode === 'tag' ? 'border-brand-primary bg-brand-primary/10 text-white' : 'border-white/5 bg-white/5 text-stone-500 hover:border-white/10'">
                        <div class="absolute inset-0 bg-brand-primary/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <i data-lucide="layers" class="w-5 h-5 relative z-10" :class="audienceMode === 'tag' ? 'text-brand-primary' : ''"></i>
                        <span class="text-[11px] font-black uppercase tracking-widest relative z-10">Cluster</span>
                    </button>
                </div>
            </div>

            <!-- Audience Panel -->
            <div class="bg-white rounded-md border border-stone-200 shadow-xl flex flex-col overflow-hidden" style="height: 620px;">
                <div class="p-6 bg-stone-50 border-b border-stone-100 shrink-0">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[11px] font-black text-stone-900 uppercase tracking-[0.2em] flex items-center gap-2">
                            <i data-lucide="target" class="w-4 h-4 text-brand-primary"></i>
                            Payload Recipient
                        </h3>
                        <button @click="audienceMode === 'manual' ? toggleSelectAllManual() : toggleSelectAllTagCustomers()"
                            class="text-[9px] font-black text-brand-primary hover:bg-brand-primary/5 px-2 py-1 rounded transition-colors uppercase tracking-widest">
                            <span x-text="selectedCustomers.length > 0 ? 'Wipe Selection' : 'Batch Select'"></span>
                        </button>
                    </div>
                    
                    <!-- Search Field for Manual -->
                    <template x-if="audienceMode === 'manual'">
                        <div class="relative">
                            <i data-lucide="fingerprint" class="w-4 h-4 text-stone-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                            <input x-model="manualSearch" type="text" placeholder="QUERY IDENTIFIER..."
                                   class="w-full pl-11 pr-4 py-4 bg-white border-2 border-stone-100 rounded text-[10px] font-black uppercase tracking-widest focus:border-brand-primary outline-none transition-all shadow-inner">
                        </div>
                    </template>
                    
                    <!-- Cluster Picker for Tag -->
                    <template x-if="audienceMode === 'tag'">
                        <div class="space-y-4">
                            <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-1 custom-scrollbar">
                                @foreach($tags as $tag)
                                <button @click="selectTag({{ $tag->id }}, '{{ addslashes($tag->name) }}')"
                                    class="flex items-center gap-2 px-3 py-2 rounded text-[10px] font-black uppercase tracking-widest transition-all border-2 outline-none group"
                                    :class="selectedTagId === {{ $tag->id }}
                                        ? 'bg-stone-900 text-brand-primary border-stone-900 shadow-xl scale-[1.03]'
                                        : 'bg-white text-stone-500 border-stone-100 hover:border-stone-200'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></div>
                                    {{ $tag->name }}
                                    <span class="ml-1 px-1.5 py-0.5 rounded text-[8px] font-black tabular-nums border"
                                        :class="selectedTagId === {{ $tag->id }} ? 'bg-white/10 text-brand-primary border-white/20' : 'bg-stone-50 text-stone-400 border-stone-100'">
                                        {{ $tag->customer_count }}
                                    </span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-2 custom-scrollbar bg-white">
                    <!-- Manual Mode List -->
                    <template x-if="audienceMode === 'manual'">
                        <div class="space-y-1">
                            @foreach($customers as $customer)
                                @if($customer->phone)
                                <label x-show="manualSearch === '' || '{{ strtolower($customer->name . ' ' . $customer->phone) }}'.includes(manualSearch.toLowerCase())"
                                       class="flex items-center p-4 hover:bg-stone-50 rounded border-2 border-transparent hover:border-stone-100 transition-all cursor-pointer group relative overflow-hidden">
                                    <input type="checkbox" value="{{ $customer->id }}" x-model="selectedCustomers"
                                        class="manual-customer-checkbox w-5 h-5 rounded border-2 border-stone-200 text-brand-primary focus:ring-brand-primary/20 mr-4 shrink-0 cursor-pointer">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[11px] font-black text-stone-900 group-hover:text-brand-primary transition-colors truncate uppercase tracking-widest">{{ $customer->name }}</div>
                                        <div class="text-[10px] text-stone-400 font-black mt-1 tracking-[0.1em] tabular-nums">{{ $customer->phone }}</div>
                                    </div>
                                    <div class="absolute right-0 top-0 h-full w-1 bg-brand-primary translate-x-full group-hover:translate-x-0 transition-transform"></div>
                                </label>
                                @else
                                <div x-show="manualSearch === '' || '{{ strtolower($customer->name) }}'.includes(manualSearch.toLowerCase())"
                                     class="flex items-center p-4 opacity-40 grayscale rounded border-2 border-transparent">
                                    <div class="w-5 h-5 rounded-full bg-stone-100 border-2 border-stone-200 mr-4 shrink-0 flex items-center justify-center">
                                        <i data-lucide="slash" class="w-3 h-3 text-stone-300"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[11px] font-black text-stone-500 truncate uppercase tracking-widest">{{ $customer->name }}</div>
                                        <div class="text-[9px] text-rose-500 font-black flex items-center gap-1.5 mt-1 uppercase tracking-widest">
                                            UNREACHABLE
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </template>

                    <!-- Tag Mode List (AJAX) -->
                    <template x-if="audienceMode === 'tag'">
                        <div class="space-y-1">
                            <div x-show="tagLoading" class="flex flex-col items-center justify-center py-20 text-stone-400 gap-4">
                                <div class="w-12 h-12 border-4 border-brand-primary border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] animate-pulse">Syncing Audience...</span>
                            </div>
                            <div x-show="!tagLoading && !selectedTagId" class="flex flex-col items-center justify-center py-20 text-stone-400 gap-6 text-center">
                                <div class="p-6 bg-stone-50 rounded-full border-2 border-stone-100">
                                    <i data-lucide="search-check" class="w-12 h-12 opacity-30"></i>
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] max-w-[240px] leading-relaxed">System awaiting cluster selection from the archive above.</p>
                            </div>
                            <template x-if="!tagLoading && selectedTagId">
                                <div class="space-y-1">
                                    <template x-for="(cust, idx) in tagCustomers" :key="cust.id">
                                        <label class="flex items-center p-4 hover:bg-stone-50 rounded border-2 border-transparent hover:border-stone-100 transition-all cursor-pointer group">
                                            <input type="checkbox" :value="String(cust.id)" x-model="selectedCustomers"
                                                class="w-5 h-5 rounded border-2 border-stone-200 text-brand-primary focus:ring-brand-primary/20 mr-4 shrink-0 cursor-pointer">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[11px] font-black text-stone-900 uppercase tracking-widest truncate" x-text="cust.name"></div>
                                                <div class="text-[10px] text-stone-400 font-black tracking-widest mt-1 tabular-nums" x-text="cust.phone"></div>
                                            </div>
                                            <div class="shrink-0 ml-4 px-3 py-1 bg-stone-900 text-white text-[9px] font-black uppercase tracking-widest rounded-sm tabular-nums">
                                                <span x-text="cust.tag_count"></span> HITS
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Footer Summary / Send Button -->
                <div class="p-8 bg-stone-900 shrink-0 shadow-[0_-20px_50px_rgba(0,0,0,0.1)]">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <span class="text-[10px] font-black text-stone-500 uppercase tracking-[0.2em] block mb-1">Queue Potential</span>
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-black text-white tabular-nums" x-text="selectedCustomers.length"></span>
                                <span class="text-xs font-black text-brand-primary uppercase">Recipients</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-black text-stone-500 uppercase tracking-[0.2em] block mb-1">Mode</span>
                            <span class="px-3 py-1 bg-white/10 text-white rounded text-[10px] font-black uppercase tracking-widest border border-white/10" x-text="audienceMode"></span>
                        </div>
                    </div>
                    <button @click="sendBroadcast" :disabled="selectedCustomers.length === 0 || (!messageBody.trim() && !selectedImageFile) || isSending"
                        class="relative w-full group overflow-hidden bg-brand-primary text-white py-5 rounded-md font-black text-[12px] uppercase tracking-[0.3em] transition-all shadow-2xl disabled:opacity-20 disabled:grayscale disabled:cursor-not-allowed">
                        <div class="absolute inset-0 bg-white/20 translate-y-full hover:translate-y-0 group-hover:translate-y-0 transition-transform duration-500 pointer-events-none"></div>
                        <div class="relative flex items-center justify-center gap-4">
                            <i data-lucide="send" class="w-5 h-5" x-show="!isSending"></i>
                            <i data-lucide="loader-2" class="w-5 h-5 animate-spin" x-show="isSending"></i>
                            <span x-text="isSending ? 'DEPLOYING PAYLOAD...' : 'DISPATCH CAMPAIGN'"></span>
                        </div>
                    </button>
                    <div class="mt-6 flex items-center justify-center gap-3">
                        <div class="h-[1px] w-8 bg-white/10"></div>
                        <p class="text-[8px] font-black uppercase tracking-[0.2em] text-stone-500 text-center leading-relaxed">
                            Bypass spam filters via <span class="text-white">staggered dispatch</span> protocol.
                        </p>
                        <div class="h-[1px] w-8 bg-white/10"></div>
                    </div>
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
