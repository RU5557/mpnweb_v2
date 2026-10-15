<header
    class="h-12 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-sm shrink-0">
    <div class="flex items-center gap-3 min-w-0 flex-1">
        <x-layout.performance-ticker :rollingText="$rollingText ?? null" />
    </div>
    <div class="flex items-center gap-2 ml-3 shrink-0">
        <a href="{{ route('admin.index') }}"
            class="flex items-center gap-2 bg-white hover:bg-slate-50 border border-slate-200 py-1 px-2.5 rounded-lg transition group">
            <div class="text-right hidden sm:block leading-tight">
                <div class="text-xs font-bold text-slate-900 leading-none">Admin KPP</div>
                <div class="text-[10px] text-slate-500 leading-none mt-0.5">Seksi Penjaminan Kualitas Data</div>
            </div>
            <div class="w-6 h-6 rounded bg-blue-950 text-amber-400 flex items-center justify-center">
                <i class="fa-solid fa-user-gear text-[10px]"></i>
            </div>
        </a>
    </div>
</header>
