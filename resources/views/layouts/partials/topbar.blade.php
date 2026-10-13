<header
    class="h-[60px] bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
    <div class="flex items-center gap-3 min-w-0 flex-1">
        <x-layout.performance-ticker :rollingText="$rollingText ?? null" />
    </div>
    <div class="flex items-center gap-3 ml-4 shrink-0">
        <a href="{{ route('admin.index') }}"
            class="flex items-center gap-2.5 bg-white hover:bg-slate-50 border border-slate-200 py-1.5 px-3.5 rounded-xl transition group">
            <div class="text-right hidden sm:block leading-tight">
                <div
                    class="text-[12.5px] font-bold text-djp-navy leading-none group-hover:text-djp-goldDark transition-colors">
                    Admin KPP</div>
                <div class="text-[10.5px] text-slate-500 font-medium leading-none mt-1">Seksi Penjaminan Kualitas Data
                </div>
            </div>
            <div
                class="w-8 h-8 rounded-lg bg-djp-navy text-djp-gold border border-djp-gold/20 flex items-center justify-center shadow-sm group-hover:bg-djp-gold group-hover:text-djp-navy transition-colors">
                <i class="fa-solid fa-user-gear text-xs"></i>
            </div>
        </a>
    </div>
</header>
