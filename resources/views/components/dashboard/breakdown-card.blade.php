@props(['card' => []])

<div
    class="bg-slate-50/70 border border-slate-200/90 rounded-lg p-3.5 flex flex-col justify-between hover:bg-white hover:border-amber-400/80 hover:shadow-xs transition group">
    <div>
        <div class="flex items-center justify-between mb-2">
            <div>
                <div class="font-extrabold text-blue-950 text-xs">{{ $card['title'] ?? '' }}</div>
                <div class="text-[10px] text-slate-400 font-medium">{{ $card['subtitle'] ?? '' }}</div>
            </div>
            <span
                class="bg-blue-950 text-amber-400 font-mono font-extrabold text-xs px-2.5 py-0.5 rounded shadow-2xs border border-blue-900 group-hover:bg-amber-400 group-hover:text-blue-950 transition">
                {{ number_format($card['persen'] ?? 0, 1, ',', '.') }}%
            </span>
        </div>

        <div class="w-full bg-slate-200 rounded-full h-1.5 mb-3 overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-yellow-400 h-1.5 rounded-full transition-all"
                style="width: {{ min($card['persen'] ?? 0, 100) }}%"></div>
        </div>

        <div class="space-y-1.5 text-xs font-mono bg-white p-2.5 rounded border border-slate-200/80">
            <div class="flex justify-between items-center text-slate-500">
                <span class="font-sans text-[11px]">Target</span>
                <span class="font-medium text-slate-700">Rp
                    {{ number_format($card['target'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center text-slate-500">
                <span class="font-sans text-[11px]">Realisasi</span>
                <span class="font-bold text-blue-950">Rp
                    {{ number_format($card['realisasi'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="mt-3 pt-2.5 border-t border-slate-200/80 flex items-center justify-between text-[11px]">
        @if ($card['isSisa'] ?? false)
            <span class="text-slate-500 font-sans font-medium">Sisa Target:</span>
            <span class="text-rose-600 font-mono font-bold">Rp
                {{ number_format($card['sisa'] ?? 0, 0, ',', '.') }}</span>
        @else
            <span class="text-slate-500 font-sans font-medium">Growth YoY:</span>
            <span
                class="font-mono font-bold {{ ($card['growthYoY'] ?? 0) >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                <i class="fa-solid {{ ($card['growthYoY'] ?? 0) >= 0 ? 'fa-caret-up' : 'fa-caret-down' }}"></i>
                {{ number_format(abs($card['growthYoY'] ?? 0), 1, ',', '.') }}%
            </span>
        @endif
    </div>
</div>
