@props(['rollingText'])

@if ($rollingText)
    <div class="flex items-center gap-3 text-[12.5px] min-w-0 overflow-hidden">
        <div
            class="flex items-center gap-1.5 bg-amber-50 border border-amber-200/80 text-amber-900 px-3 py-1 rounded-full text-[11px] font-bold shadow-sm shrink-0">
            <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
            Kinerja Harian
        </div>
        <div class="flex items-center gap-3 font-medium text-slate-600 min-w-0">
            <span class="flex items-center gap-1 shrink-0 text-slate-500">
                <i class="fa-regular fa-clock text-amber-500 text-xs"></i>
                <span
                    class="font-mono text-[12px]">{{ \Carbon\Carbon::parse($rollingText->tanggal)->format('d/m/Y') }}</span>
            </span>
            <span class="w-px h-3.5 bg-slate-200 shrink-0"></span>
            <span class="shrink-0">NKO: <span
                    class="font-extrabold text-slate-900 font-mono text-[13px]">{{ number_format($rollingText->nko, 2, ',', '.') }}%</span></span>
            <span class="w-px h-3.5 bg-slate-200 shrink-0 hidden sm:block"></span>
            <span class="shrink-0 hidden sm:inline">Rank Nasional: <span
                    class="font-bold text-blue-950">#{{ $rollingText->ranking_nasional }}</span></span>
            <span class="w-px h-3.5 bg-slate-200 shrink-0 hidden md:block"></span>
            <span class="shrink-0 hidden md:inline">Rank Kanwil: <span
                    class="font-bold text-blue-950">#{{ $rollingText->ranking_kanwil }}</span></span>
        </div>
    </div>
@else
    <div class="flex items-center gap-2 text-[12px] text-slate-400">
        <i class="fa-solid fa-circle-info"></i>
        <span>Data kinerja harian belum tersedia</span>
    </div>
@endif
