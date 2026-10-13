<header
    class="h-14 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-2xs backdrop-blur-md bg-white/95">

    <!-- Informasi Ticker & Kinerja Harian -->
    <div class="flex items-center gap-3 text-xs font-medium text-slate-700 overflow-hidden max-w-4xl">
        <span
            class="bg-amber-50 border border-amber-300 text-blue-950 text-[11px] px-2.5 py-1 rounded-md font-extrabold flex items-center gap-1.5 whitespace-nowrap shadow-2xs shrink-0">
            <span class="relative flex h-2 w-2">
                <span
                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
            </span>
            Kinerja Harian
        </span>

        <div class="truncate text-xs text-slate-600 flex items-center gap-2">
            @if (isset($rollingText) && $rollingText)
                <span class="text-slate-400 font-mono text-[11px] shrink-0">
                    <i
                        class="fa-regular fa-clock mr-1 text-amber-500"></i>{{ \Carbon\Carbon::parse($rollingText->tanggal)->format('d/m/Y') }}
                </span>
                <span class="text-slate-300">|</span>
                <span>NKO: <strong
                        class="text-blue-950 font-mono font-extrabold">{{ number_format($rollingText->nko, 2) }}%</strong></span>
                <span class="text-slate-300">|</span>
                <span>Rank Nasional: <strong
                        class="text-blue-950 font-mono font-extrabold">#{{ $rollingText->ranking_nasional }}</strong></span>
                <span class="text-slate-300">|</span>
                <span>Rank Kanwil: <strong
                        class="text-blue-950 font-mono font-extrabold">#{{ $rollingText->ranking_kanwil }}</strong></span>
            @else
                <span class="text-slate-400 italic flex items-center gap-1">
                    <i class="fa-solid fa-circle-info text-amber-500"></i> Belum ada data info kinerja harian.
                </span>
            @endif
        </div>
    </div>

    <!-- Right Menu / Admin Profile -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.index') }}"
            class="flex items-center gap-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 py-1 px-3 rounded-lg transition group">
            <div class="text-right hidden sm:block">
                <div
                    class="text-xs font-extrabold text-blue-950 leading-tight group-hover:text-amber-600 transition-colors">
                    Admin KPP</div>
                <div class="text-[10px] text-slate-500 font-medium leading-none">Seksi Penjaminan Kualitas Data</div>
            </div>
            <div
                class="w-7 h-7 rounded-lg bg-blue-950 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold shadow-xs shrink-0 group-hover:bg-amber-500 group-hover:text-blue-950 transition-colors">
                <i class="fa-solid fa-user-gear text-xs"></i>
            </div>
        </a>
    </div>
</header>
