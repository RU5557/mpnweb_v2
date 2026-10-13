@props(['label', 'icon', 'active' => false, 'children' => []])

<div x-data="{ open: @js($active) }" class="space-y-1">
    <button @click="if(!isExpanded) { sidebarOpen = true; $nextTick(() => open = !open); } else { open = !open; }"
        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-[12.5px] font-medium transition-all duration-200 group border
        {{ $active
            ? 'bg-[#112A46] text-white border-amber-500/30 shadow-md shadow-black/20'
            : 'text-slate-300 hover:bg-[#112A46] hover:text-white border-transparent' }}">
        <i
            class="fa-solid {{ $icon }} w-5 text-center text-[13px] {{ $active ? 'text-amber-400' : 'text-slate-400 group-hover:text-amber-400' }} transition-colors"></i>
        <span x-show="isExpanded" x-cloak x-transition.opacity
            class="whitespace-nowrap flex-1 text-left">{{ $label }}</span>
        <i x-show="isExpanded" :class="open ? 'rotate-90' : ''"
            class="fa-solid fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200"></i>
    </button>

    <div x-show="isExpanded && open" x-cloak x-collapse class="ml-3 pl-3 border-l border-[#1a365d]/60 space-y-1 mt-1">
        @foreach ($children as $child)
            @php $hasChildRoute = \Illuminate\Support\Facades\Route::has($child['route']); @endphp
            @if ($hasChildRoute)
                <a href="{{ route($child['route']) }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-md text-[12px] transition-colors
               {{ request()->routeIs($child['route']) ? 'bg-amber-500/10 text-amber-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-[#112A46]/50' }}">
                    <span
                        class="w-1 h-1 rounded-full {{ request()->routeIs($child['route']) ? 'bg-amber-400' : 'bg-slate-600' }}"></span>
                    {{ $child['label'] }}
                </a>
            @else
                <div
                    class="flex items-center gap-2 px-3 py-2 rounded-md text-[12px] text-slate-500 opacity-50 cursor-not-allowed">
                    <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                    {{ $child['label'] }} <span class="ml-auto text-[8px] bg-slate-800 px-1 py-0.5 rounded">soon</span>
                </div>
            @endif
        @endforeach
    </div>
</div>
