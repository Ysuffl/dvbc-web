@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-center py-4">
        <div class="flex items-stretch bg-stone-900 text-white rounded-md overflow-hidden shadow-xl border border-white/5 backdrop-blur-sm">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="flex items-center justify-center w-10 h-10 text-stone-600 cursor-default" aria-disabled="true">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex items-center justify-center w-10 h-10 text-white hover:bg-white/10 transition-colors" aria-label="{{ __('pagination.previous') }}">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="flex items-center justify-center px-4 h-10 text-stone-500 bg-stone-900/50 cursor-default border-l border-white/10">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="flex items-center justify-center px-4 h-10 text-brand-primary bg-stone-800/80 font-black text-[11px] tracking-widest border-l border-white/5 relative">
                                {{ $page }}
                                <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-brand-primary shadow-[0_0_8px_rgba(255,179,71,0.5)]"></div>
                            </span>
                        @else
                            <a href="{{ $url }}" class="flex items-center justify-center px-4 h-10 text-stone-400 hover:text-white hover:bg-white/[0.03] font-black text-[11px] tracking-widest transition-all border-l border-white/5" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex items-center justify-center w-10 h-10 text-white hover:bg-white/10 transition-colors border-l border-white/10" aria-label="{{ __('pagination.next') }}">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            @else
                <span class="flex items-center justify-center w-10 h-10 text-stone-600 cursor-default border-l border-white/10" aria-disabled="true">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
