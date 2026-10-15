@props([
    'title' => 'Penerimaan',
    'value' => 0,
    'icon' => 'fa-wallet',
    'growthMoM' => null,
    'growthYoY' => null,
    'type' => 'primary',
    'prefix' => 'Rp',
])

<div
    class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col justify-between hover:border-amber-400 transition shadow-2xs relative overflow-hidden min-h-">
    <div class="absolute top-0 left-0 right-0 h-1 bg-blue-950"></div>
    <div>
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $title }}</span>
            <i class="fa-solid {{ $icon }} text-amber-500 text-xs"></i>
        </div>
        <div class="mt-2 flex items-baseline gap-1">
            <span class="text-xs text-slate-400 font-normal">{{ $prefix }}</span>
            <span
                class="text-2xl font-black text-blue-950 font-mono tracking-tight tabular-nums">{{ number_format($value, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs gap-1">
        @if ($growthMoM !== null || $growthYoY !== null)
            @if ($growthMoM !== null)
                <span
                    class="px-1.5 py-0.5 rounded text- font-bold font-mono {{ $growthMoM >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60' }}">
                    <i class="fa-solid {{ $growthMoM >= 0 ? 'fa-caret-up' : 'fa-caret-down' }}"></i>
                    {{ number_format(abs($growthMoM), 1, ',', '.') }}% MoM
                </span>
            @endif
            @if ($growthYoY !== null)
                <span
                    class="px-1.5 py-0.5 rounded text- font-bold font-mono {{ $growthYoY >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60' }}">
                    <i class="fa-solid {{ $growthYoY >= 0 ? 'fa-caret-up' : 'fa-caret-down' }}"></i>
                    {{ number_format(abs($growthYoY), 1, ',', '.') }}% YoY
                </span>
            @endif
        @else
            {{ $slot }}
        @endif
    </div>
</div>
