@props(['label', 'icon', 'active' => false, 'children' => []])

<div x-data="{ open: @js($active) }" class="space-y-1">
    <button
        @click="if(!isExpanded) { sidebarOpen = true; open = true; } else { open = !open; }"
        :class="open ? 'bg-blue-950/40 text-white' : 'text-slate-400 hover:bg-blue-950/30 hover:text-white'"
        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors"
    >
        <div class="flex items-center gap-3 min-w-0">
            <i class="fa-solid {{ $icon }} text-sm w-5 text-center shrink-0"></i>
            <span x-show="isExpanded" x-cloak x-transition.opacity class="truncate whitespace-nowrap">{{ $label }}</span>
        </div>
        <i x-show="isExpanded" x-cloak class="fa-solid text-[10px] transition-transform duration-200 text-slate-500 shrink-0" :class="open ? 'fa-chevron-down text-amber-400' : 'fa-chevron-right'"></i>
    </button>

    <div x-show="open && isExpanded" x-cloak
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
        class="pl-8 space-y-1 relative before:absolute before:left-5 before:top-2 before:bottom-2 before:w-px before:bg-blue-900/50"
    >
        @foreach($children as $child)
            @if(\Illuminate\Support\Facades\Route::has($child['route']))
                <a href="{{ route($child['route']) }}"
                   class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ request()->routeIs($child['route']) ? 'bg-blue-950/80 text-amber-400 font-bold border-l-2 border-amber-400' : 'text-slate-400 hover:text-slate-200 hover:bg-blue-950/30' }}">
                    {{ $child['label'] }}
                </a>
            @else
                {{-- Route belum dibuat, tampilkan disabled biar tidak error --}}
                <span class="block px-3 py-1.5 rounded-md text-xs font-medium text-slate-600 cursor-not-allowed flex items-center gap-2">
                    {{ $child['label'] }} <span class="text-[9px] bg-slate-800 px-1 rounded">soon</span>
                </span>
            @endif
        @endforeach
    </div>
</div>
