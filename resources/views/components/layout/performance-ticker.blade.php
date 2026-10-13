@props(['rollingText'])

<div class="flex items-center gap-3 text-xs font-medium text-slate-700 overflow-hidden max-w-4xl">
    <span class="bg-amber-50 border border-amber-300 text-blue-950 text-[11px] px-2.5 py-1 rounded-md font-extrabold flex items-center gap-1.5 whitespace-nowrap shrink-0">
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
        </span>
        Kinerja Harian
    </span>

    <div class="truncate text-xs text-slate-600 flex items-center gap-2">
        @if($rollingText)
            <span class="text-slate-400 font-mono text-[11px] shrink-0"><i class="fa-regular fa-clock mr-1 text-amber-500"></i>{{ \Carbon\Carbon::parse($rollingText->tanggal)->format('d/m/Y') }}</span>
            <span class="text-slate-300">|</span>
            <span>NKO: <strong class="text-blue-950 font-mono font-extrabold">{{ number_format($rollingText->nko, 2) }}%</strong></span>
            <span class="text-slate-300">|</span>
            <span>Rank Nasional: <strong class="text-blue-950 font-mono font-extrabold">#{{ $rollingText->ranking_nasional }}</strong></span>
            <span class="text-slate-300">|</span>
            <span>Rank Kanwil: <strong class="text-blue-950 font-mono font-extrabold">#{{ $rollingText->ranking_kanwil }}</strong></span>
        @else
            <span class="text-slate-400 italic flex items-center gap-1"><i class="fa-solid fa-circle-info text-amber-500"></i> Belum ada data info kinerja harian.</span>
        @endif
    </div>
</div>
