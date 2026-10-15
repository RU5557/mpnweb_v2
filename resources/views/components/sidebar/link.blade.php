@props(['label', 'icon', 'route', 'active' => false])
@php
    $isActive = $active;
    $hasRoute = \Illuminate\Support\Facades\Route::has($route);
@endphp
@if ($hasRoute)
    <a href="{{ route($route) }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[12.5px] font-medium transition-all duration-200 group {{ $isActive ? 'bg-djp-navyHover text-white border border-djp-gold/30 shadow-md shadow-black/20' : 'text-slate-300 hover:bg-djp-navyHover hover:text-white border border-transparent' }}">
        <i
            class="fa-solid {{ $icon }} w-5 text-center text-[13px] {{ $isActive ? 'text-djp-gold' : 'text-slate-400 group-hover:text-djp-gold' }} transition-colors"></i>
        <span x-show="isExpanded" x-cloak x-transition.opacity class="whitespace-nowrap">{{ $label }}</span>
    </a>
@else
    <div
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[12.5px] font-medium text-slate-500 border border-transparent opacity-60 cursor-not-allowed">
        <i class="fa-solid {{ $icon }} w-5 text-center text-[13px] text-slate-500"></i>
        <span x-show="isExpanded" x-cloak x-transition.opacity class="whitespace-nowrap">{{ $label }}</span>
        <span x-show="isExpanded" class="ml-auto text-[9px] bg-slate-800 px-1.5 py-0.5 rounded font-mono">soon</span>
    </div>
@endif
