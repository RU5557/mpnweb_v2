@php
    $nav = [
        [
            'type' => 'link',
            'label' => 'Dashboard Ringkasan',
            'icon' => 'fa-border-all',
            'route' => 'penerimaan.dashboard',
            'active' => request()->routeIs('penerimaan.dashboard'),
        ],
        [
            'type' => 'group',
            'label' => 'Penjagaan',
            'icon' => 'fa-chart-pie',
            'active' => request()->routeIs('penerimaan.penjagaan.*'),
            'children' => [
                ['label' => 'Bulanan', 'route' => 'penerimaan.penjagaan.bulanan'],
                ['label' => 'Harian', 'route' => 'penerimaan.penjagaan.harian'],
                ['label' => 'Vs Bulan Lalu', 'route' => 'penerimaan.penjagaan.vs-bulan-lalu'],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'PKM',
            'icon' => 'fa-shield-halved',
            'active' => request()->routeIs('pkm.*'),
            'children' => [
                ['label' => 'Pengawasan', 'route' => 'pkm.pengawasan'],
                ['label' => 'Pemeriksaan', 'route' => 'pkm.pemeriksaan'],
                ['label' => 'Penagihan', 'route' => 'pkm.penagihan'],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'Pencarian Data',
            'icon' => 'fa-magnifying-glass',
            'active' => request()->routeIs('pencarian.*'),
            'children' => [
                ['label' => 'Masterfile WP', 'route' => 'pencarian.masterfile'],
                ['label' => 'Transaksi Penerimaan', 'route' => 'pencarian.transaksi'],
                ['label' => 'Tanda Terima SPT', 'route' => 'pencarian.spt'],
            ],
        ],
    ];
@endphp

{{-- SIDEBAR NAVY KHAS DJP - Uniform Tone --}}
<aside @mouseenter="handleMouseEnter()" @mouseleave="handleMouseLeave()" :class="isExpanded ? 'w-60' : 'w-16'"
    class="bg-[#0A1931] text-slate-300 min-h-screen transition-[width] duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] flex flex-col justify-between fixed left-0 top-0 bottom-0 z-40 border-r border-[#1a365d] shadow-2xl">
    <div>
        {{-- Header Navy DJP uniform --}}
        <div
            class="h-[60px] px-3.5 border-b border-[#1a365d] bg-[#0A1931] flex items-center justify-between shrink-0 overflow-hidden">
            <div class="flex items-center gap-3 min-w-0">
                <div
                    class="bg-[#112A46] border border-amber-500/40 text-amber-400 p-2 rounded-lg font-bold flex items-center justify-center w-9 h-9 shadow-md shadow-black/20 shrink-0">
                    <i class="fa-solid fa-chart-column text-sm"></i>
                </div>
                <div x-show="isExpanded" x-cloak x-transition.opacity
                    class="flex flex-col leading-none space-y-1 whitespace-nowrap">
                    <span class="font-extrabold text-white text-[14px] tracking-wider uppercase">MPNWEB</span>
                    <span class="text-[10px] text-amber-400 font-bold tracking-wider uppercase font-mono">DJP
                        MONITORING</span>
                </div>
            </div>
            <button @click="isPinned = !isPinned"
                class="p-1.5 text-slate-400 hover:text-amber-400 hover:bg-[#112A46] rounded-lg transition-colors shrink-0"
                :class="!isExpanded && 'hidden'">
                <i class="fa-solid fa-thumbtack text-xs transition-all duration-200"
                    :class="isPinned ? 'text-amber-400 rotate-0' : 'text-slate-400 -rotate-45 opacity-60'"></i>
            </button>
        </div>

        <nav class="p-2.5 pt-4 space-y-1 overflow-x-hidden">
            @foreach ($nav as $item)
                @if ($item['type'] === 'link')
                    <x-sidebar.link :label="$item['label']" :icon="$item['icon']" :route="$item['route']" :active="$item['active']" />
                @else
                    <x-sidebar.group :label="$item['label']" :icon="$item['icon']" :active="$item['active']" :children="$item['children']" />
                @endif
            @endforeach
        </nav>
    </div>

    {{-- Footer Navy DJP uniform --}}
    <div
        class="h-12 px-3.5 border-t border-[#1a365d] bg-[#0A1931] text-[11px] text-slate-400 flex justify-between items-center shrink-0">
        <span x-show="isExpanded" x-cloak x-transition.opacity class="font-bold whitespace-nowrap text-slate-300">DJP
            Executive</span>
        <span
            class="bg-[#112A46] text-amber-400 border border-[#1a365d] px-2.5 py-1 rounded-md font-mono text-[10px] font-bold"
            :class="!isExpanded && 'mx-auto'">v1.0</span>
    </div>
</aside>
