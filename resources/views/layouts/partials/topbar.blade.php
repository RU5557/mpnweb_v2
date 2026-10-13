<header class="h-14 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-2xs">
    <x-layout.performance-ticker :rollingText="$rollingText ?? null" />
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.index') }}" class="flex items-center gap-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 py-1 px-3 rounded-lg transition group">
            <div class="text-right hidden sm:block">
                <div class="text-xs font-extrabold text-blue-950 leading-tight group-hover:text-amber-600 transition-colors">Admin KPP</div>
                <div class="text-[10px] text-slate-500 font-medium leading-none">Seksi Penjaminan Kualitas Data</div>
            </div>
            <div class="w-7 h-7 rounded-lg bg-blue-950 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold shadow-xs shrink-0 group-hover:bg-amber-500 group-hover:text-blue-950 transition-colors">
                <i class="fa-solid fa-user-gear text-xs"></i>
            </div>
        </a>
    </div>
</header>
