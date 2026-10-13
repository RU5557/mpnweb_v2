@props(['title' => '', 'subtitle' => '', 'icon' => 'fa-chart-line'])

<div
    class="bg-white border border-slate-200 rounded-md px-3 py-2 flex items-center justify-between gap-3 shadow-sm border-t-4 border-t-amber-500 w-full">
    <div class="flex items-center gap-2.5 shrink-0 min-w-0">
        <div class="w-7 h-7 rounded bg-blue-950 text-amber-400 flex items-center justify-center font-bold shrink-0">
            <i class="fa-solid {{ $icon }} text-[10px]"></i>
        </div>
        <div class="leading-tight min-w-0">
            <h1 class="text-xs font-extrabold text-blue-950 uppercase leading-none truncate">{{ $title }}</h1>
            <p class="text-[11px] text-slate-500 font-medium leading-none mt-0.5 truncate">{{ $subtitle }}</p>
        </div>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        {{ $slot }}
    </div>
</div>
