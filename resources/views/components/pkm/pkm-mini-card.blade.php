@props([
    'title',
    'value',
    'icon' => 'fa-sack-dollar',
    'subtitle' => null,
    'type' => 'primary',
    'growth' => null,
    'isSelisih' => false,
])
@php
    $isPrimary = $type === 'primary';
    $isSelisihCard = $isSelisih;
    $valueAbs = abs($value);
    $isNegative = $value < 0;
@endphp
<div
    class="{{ $isPrimary ? 'bg-blue-950 text-white border-blue-900' : ($isSelisihCard ? ($isNegative ? 'bg-rose-50 text-rose-900 border-rose-200' : 'bg-emerald-50 text-emerald-900 border-emerald-200') : 'bg-white text-slate-800 border-slate-200') }} border rounded-lg p-3 flex flex-col shadow-sm min-h-[96px]">

    <div class="flex items-center justify-between h-5 shrink-0">
        <span
            class="text-[11px] font-bold uppercase tracking-wider {{ $isPrimary ? 'text-amber-400' : ($isSelisihCard ? ($isNegative ? 'text-rose-600' : 'text-emerald-600') : 'text-slate-500') }} truncate">{{ $title }}</span>
        <span
            class="w-6 h-6 rounded flex items-center justify-center shrink-0 {{ $isPrimary ? 'bg-amber-500/20 text-amber-400 border border-amber-400/30' : ($isSelisihCard ? ($isNegative ? 'bg-rose-100 text-rose-600 border border-rose-200' : 'bg-emerald-100 text-emerald-600 border border-emerald-200') : 'bg-slate-100 text-slate-500 border border-slate-200') }}"><i
                class="fa-solid {{ $icon }} text-[10px]"></i></span>
    </div>

    <div class="mt-2 h-7 flex items-center shrink-0">
        <div
            class="text-[13px] font-black font-mono {{ $isPrimary ? 'text-amber-400' : ($isSelisihCard ? ($isNegative ? 'text-rose-700' : 'text-emerald-700') : 'text-blue-950') }} leading-none">
            @if ($isSelisihCard)
                {{ $isNegative ? '-' : '+' }}Rp {{ number_format($valueAbs, 0, ',', '.') }}
            @else
                Rp {{ number_format($value, 0, ',', '.') }}
            @endif
        </div>
    </div>

    <div class="mt-1.5 h-5 flex items-center shrink-0">
        @if ($subtitle)
            <div
                class="flex items-center gap-1.5 text-[11px] font-medium {{ $isPrimary ? 'text-slate-300' : ($isSelisihCard ? ($isNegative ? 'text-rose-600' : 'text-emerald-600') : 'text-slate-500') }}">
                <i
                    class="fa-solid {{ $isSelisihCard ? ($isNegative ? 'fa-arrow-trend-down' : 'fa-arrow-trend-up') : 'fa-arrow-right-arrow-left' }} text-[9px]"></i>
                <span>{{ $subtitle }}</span>
            </div>
        @else
            <span class="text-[11px] invisible">-</span>
        @endif
    </div>
</div>
