@props(['title', 'value', 'icon', 'growth' => null, 'subtitle' => null, 'type' => 'primary'])

@php
    $isPrimary = $type === 'primary';
@endphp

<div
    class="{{ $isPrimary ? 'bg-blue-950 text-white border-blue-900' : 'bg-white text-slate-800 border-slate-200' }} border rounded-lg p-3 flex flex-col shadow-sm min-h-[96px]">
    {{-- Row 1: Title + Icon - fixed height --}}
    <div class="flex items-center justify-between h-5 shrink-0">
        <span
            class="text-[11px] font-bold uppercase tracking-wider {{ $isPrimary ? 'text-amber-400' : 'text-slate-500' }} truncate">{{ $title }}</span>
        <span
            class="w-6 h-6 rounded flex items-center justify-center shrink-0 {{ $isPrimary ? 'bg-blue-900 text-amber-400 border border-amber-500/20' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
            <i class="fa-solid {{ $icon }} text-[10px]"></i>
        </span>
    </div>

    {{-- Row 2: Value - fixed height biar sejajar card 1,2,3 --}}
    <div class="mt-2 h-7 flex items-center shrink-0">
        <div class="text-[13px] font-black font-mono {{ $isPrimary ? 'text-amber-400' : 'text-blue-950' }} leading-none">
            Rp {{ number_format($value, 0, ',', '.') }}</div>
    </div>

    {{-- Row 3: Growth / Subtitle / Placeholder - fixed height 20px biar semua card rata --}}
    <div class="mt-1.5 h-5 flex items-center shrink-0">
        @if ($growth !== null)
            <div
                class="flex items-center gap-1 text-[11px] font-medium {{ $growth >= 0 ? 'text-emerald-600' : 'text-rose-600' }} {{ $isPrimary ? ($growth >= 0 ? 'text-emerald-300' : 'text-rose-300') : '' }}">
                <i class="fa-solid {{ $growth >= 0 ? 'fa-caret-up' : 'fa-caret-down' }} text-[10px]"></i>
                <span>{{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1, ',', '.') }}%</span>
                <span class="font-normal {{ $isPrimary ? 'text-slate-300' : 'text-slate-400' }} ml-1">vs lalu</span>
            </div>
        @elseif($subtitle)
            <div
                class="flex items-center gap-1.5 text-[11px] font-medium {{ $isPrimary ? 'text-slate-300' : 'text-slate-500' }}">
                <i
                    class="fa-solid fa-arrow-right-arrow-left text-[9px] {{ $isPrimary ? 'text-slate-400' : 'text-slate-300' }}"></i>
                <span>{{ $subtitle }}</span>
            </div>
        @else
            {{-- Placeholder invisible biar tinggi tetap 20px, value tetap sejajar --}}
            <span class="text-[11px] invisible">-</span>
        @endif
    </div>
</div>
