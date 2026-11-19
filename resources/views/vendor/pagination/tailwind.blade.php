@if ($paginator->hasPages())
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-[11.5px] text-slate-500">
            Menampilkan
            <span class="font-semibold tabular-nums text-slate-700">{{ $paginator->firstItem() }}</span>–<span class="font-semibold tabular-nums text-slate-700">{{ $paginator->lastItem() }}</span>
            dari <span class="font-semibold tabular-nums text-slate-700">{{ $paginator->total() }}</span> data
        </p>

        <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[12px] font-semibold text-slate-400">
                    <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-[12px] font-semibold text-slate-700 transition hover:border-gov-300 hover:bg-gov-50 hover:text-gov-800">
                    <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Sebelumnya
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1.5 text-[12px] text-slate-400" aria-hidden="true">…</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex min-w-[2rem] items-center justify-center rounded-md bg-gov-700 px-2.5 py-1.5 text-[12px] font-semibold tabular-nums text-white">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" aria-label="Halaman {{ $page }}"
                               class="inline-flex min-w-[2rem] items-center justify-center rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-[12px] font-semibold tabular-nums text-slate-700 transition hover:border-gov-300 hover:bg-gov-50 hover:text-gov-800">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-[12px] font-semibold text-slate-700 transition hover:border-gov-300 hover:bg-gov-50 hover:text-gov-800">
                    Berikutnya <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            @else
                <span aria-disabled="true" class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[12px] font-semibold text-slate-400">
                    Berikutnya <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                </span>
            @endif
        </nav>
    </div>
@endif
