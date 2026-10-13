@props(['label', 'icon', 'route', 'active' => false])

@if(\Illuminate\Support\Facades\Route::has($route))
<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-all duration-150
   {{ $active ? 'bg-blue-950 text-amber-400 border border-amber-500/40 shadow-md' : 'hover:bg-blue-950/50 hover:text-white text-slate-400' }}"
>
    <i class="fa-solid {{ $icon }} text-sm w-5 text-center shrink-0 {{ $active ? 'text-amber-400' : 'text-slate-400' }}"></i>
    <span x-show="isExpanded" x-cloak x-transition.opacity class="truncate whitespace-nowrap">{{ $label }}</span>
</a>
@else
<span class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 cursor-not-allowed">
    <i class="fa-solid {{ $icon }} text-sm w-5 text-center shrink-0"></i>
    <span x-show="isExpanded" x-cloak class="truncate">{{ $label }}</span>
</span>
@endif
