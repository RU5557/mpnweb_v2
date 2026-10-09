<aside @mouseenter="handleMouseEnter()" @mouseleave="handleMouseLeave()"
    :class="sidebarOpen || isPinned ? 'w-60' : 'w-16'"
    class="bg-slate-950 text-slate-300 min-h-screen transition-[width] duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] flex flex-col justify-between fixed left-0 top-0 bottom-0 z-40 border-r border-blue-900/40 shadow-2xl">

    <div>
        <!-- Sidebar Header (Ikon Logo Diam Presisi di Kiri) -->
        <div
            class="h-14 px-3.5 border-b border-blue-900/50 bg-blue-950/40 flex items-center justify-between shrink-0 overflow-hidden">

            <div class="flex items-center gap-3 min-w-0">
                <!-- Ikon Logo (Selalu Tampil & Diam di Tempat) -->
                <div
                    class="bg-gradient-to-tr from-blue-950 to-slate-900 border border-amber-500/40 text-amber-400 p-2 rounded-lg font-bold flex items-center justify-center w-8 h-8 shadow-md shadow-amber-500/10 shrink-0">
                    <i class="fa-solid fa-chart-column text-xs"></i>
                </div>

                <!-- Teks Judul (Hanya Teks yang Fade Out saat Mencengkeram) -->
                <div x-show="sidebarOpen || isPinned" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="flex flex-col justify-center leading-none space-y-1 whitespace-nowrap">
                    <span
                        class="font-extrabold text-white text-sm tracking-wider uppercase font-sans leading-none">MPNWEB</span>
                    <span
                        class="text-[9px] text-amber-400 font-bold tracking-wider uppercase font-mono leading-none">DJP
                        Monitoring</span>
                </div>
            </div>

            <!-- Tombol Pin (Bergeser Halus saat Tertutup) -->
            <button @click="isPinned = !isPinned; sidebarOpen = isPinned"
                class="p-1.5 text-slate-400 hover:text-amber-400 hover:bg-blue-950/60 rounded-lg transition-colors flex items-center justify-center shrink-0"
                :class="!(sidebarOpen || isPinned) && 'hidden'"
                :title="isPinned ? 'Lepas Kunci Sidebar' : 'Kunci Sidebar'">
                <i class="fa-solid text-xs transition-transform duration-200"
                    :class="isPinned ? 'fa-thumbtack text-amber-400 rotate-45' : (sidebarOpen ? 'fa-indent' : 'fa-outdent')"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="p-2.5 pt-4 space-y-1 overflow-x-hidden">
            {{-- Dashboard --}}
            @php $isDashboard = request()->routeIs('penerimaan.dashboard'); @endphp
            <a href="{{ route('penerimaan.dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-all duration-150 {{ $isDashboard ? 'bg-blue-950 text-amber-400 border border-amber-500/40 shadow-md shadow-blue-950/50' : 'hover:bg-blue-950/50 hover:text-white text-slate-400' }}">
                <i
                    class="fa-solid fa-border-all text-sm w-5 text-center shrink-0 {{ $isDashboard ? 'text-amber-400' : 'text-slate-400' }}"></i>

                <span x-show="sidebarOpen || isPinned" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="truncate whitespace-nowrap">Dashboard Ringkasan</span>
            </a>

            {{-- Penjagaan Dropdown --}}
            @php $isPenjagaan = request()->routeIs('penerimaan.penjagaan.*'); @endphp
            <div x-data="{ open: {{ $isPenjagaan ? 'true' : 'false' }} }" class="space-y-1">
                <button
                    @click="if(!sidebarOpen && !isPinned) { sidebarOpen = true; open = true; } else { open = !open; }"
                    :class="open ? 'bg-blue-950/40 text-white' : 'text-slate-400 hover:bg-blue-950/30 hover:text-white'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <i class="fa-solid fa-chart-pie text-sm w-5 text-center shrink-0"></i>
                        <span x-show="sidebarOpen || isPinned" x-cloak
                            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            class="truncate whitespace-nowrap">Penjagaan</span>
                    </div>
                    <i x-show="sidebarOpen || isPinned" x-cloak
                        class="fa-solid text-[10px] transition-transform duration-200 text-slate-500 shrink-0"
                        :class="open ? 'fa-chevron-down text-amber-400' : 'fa-chevron-right'"></i>
                </button>

                <div x-show="open && (sidebarOpen || isPinned)" x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="transform opacity-0 -translate-y-1"
                    x-transition:enter-end="transform opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="transform opacity-100 translate-y-0"
                    x-transition:leave-end="transform opacity-0 -translate-y-1"
                    class="pl-8 space-y-1 relative before:absolute before:left-5 before:top-2 before:bottom-2 before:w-px before:bg-blue-900/50">

                    <a href="{{ route('penerimaan.penjagaan.bulanan') }}"
                        class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ request()->routeIs('penerimaan.penjagaan.bulanan') ? 'bg-blue-950/80 text-amber-400 font-bold border-l-2 border-amber-400' : 'text-slate-400 hover:text-slate-200 hover:bg-blue-950/30' }}">
                        Bulanan
                    </a>
                    <a href="{{ route('penerimaan.penjagaan.harian') }}"
                        class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ request()->routeIs('penerimaan.penjagaan.harian') ? 'bg-blue-950/80 text-amber-400 font-bold border-l-2 border-amber-400' : 'text-slate-400 hover:text-slate-200 hover:bg-blue-950/30' }}">
                        Harian
                    </a>
                    <a href="{{ route('penerimaan.penjagaan.vs-bulan-lalu') }}"
                        class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ request()->routeIs('penerimaan.penjagaan.vs-bulan-lalu') ? 'bg-blue-950/80 text-amber-400 font-bold border-l-2 border-amber-400' : 'text-slate-400 hover:text-slate-200 hover:bg-blue-950/30' }}">
                        Vs Bulan Lalu
                    </a>
                </div>
            </div>

            {{-- PKM Dropdown --}}
            @php $isPkm = request()->routeIs('pkm.pengawasan.*') || request()->routeIs('pkm.*'); @endphp
            <div x-data="{ open: {{ $isPkm ? 'true' : 'false' }} }" class="space-y-1">
                <button
                    @click="if(!sidebarOpen && !isPinned) { sidebarOpen = true; open = true; } else { open = !open; }"
                    :class="open ? 'bg-blue-950/40 text-white' : 'text-slate-400 hover:bg-blue-950/30 hover:text-white'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <i class="fa-solid fa-bullseye text-sm w-5 text-center shrink-0"></i>
                        <span x-show="sidebarOpen || isPinned" x-cloak
                            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            class="truncate whitespace-nowrap">PKM</span>
                    </div>
                    <i x-show="sidebarOpen || isPinned" x-cloak
                        class="fa-solid text-[10px] transition-transform duration-200 text-slate-500 shrink-0"
                        :class="open ? 'fa-chevron-down text-amber-400' : 'fa-chevron-right'"></i>
                </button>

                <div x-show="open && (sidebarOpen || isPinned)" x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="transform opacity-0 -translate-y-1"
                    x-transition:enter-end="transform opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="transform opacity-100 translate-y-0"
                    x-transition:leave-end="transform opacity-0 -translate-y-1"
                    class="pl-8 space-y-1 relative before:absolute before:left-5 before:top-2 before:bottom-2 before:w-px before:bg-blue-900/50">

                    <a href="{{ route('pkm.pengawasan') }}"
                        class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ request()->routeIs('pkm.pengawasan') ? 'bg-blue-950/80 text-amber-400 font-bold border-l-2 border-amber-400' : 'text-slate-400 hover:text-slate-200 hover:bg-blue-950/30' }}">
                        Pengawasan
                    </a>
                    <a href="{{ route('pkm.pemeriksaan') }}"
                        class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ request()->routeIs('pkm.pemeriksaan') ? 'bg-blue-950/80 text-amber-400 font-bold border-l-2 border-amber-400' : 'text-slate-400 hover:text-slate-200 hover:bg-blue-950/30' }}">
                        Pemeriksaan
                    </a>
                    <a href="{{ route('pkm.penagihan') }}"
                        class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ request()->routeIs('pkm.penagihan') ? 'bg-blue-950/80 text-amber-400 font-bold border-l-2 border-amber-400' : 'text-slate-400 hover:text-slate-200 hover:bg-blue-950/30' }}">
                        Penagihan
                    </a>
                </div>
            </div>

            {{-- Pencarian Dropdown --}}
            @php $isPencarian = request()->routeIs('pencarian.*'); @endphp
            <div x-data="{ open: {{ $isPencarian ? 'true' : 'false' }} }" class="space-y-1">
                <button
                    @click="if(!sidebarOpen && !isPinned) { sidebarOpen = true; open = true; } else { open = !open; }"
                    :class="open ? 'bg-blue-950/40 text-white' : 'text-slate-400 hover:bg-blue-950/30 hover:text-white'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <i class="fa-solid fa-magnifying-glass text-sm w-5 text-center shrink-0"></i>
                        <span x-show="sidebarOpen || isPinned" x-cloak
                            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            class="truncate whitespace-nowrap">Pencarian Data</span>
                    </div>
                    <i x-show="sidebarOpen || isPinned" x-cloak
                        class="fa-solid text-[10px] transition-transform duration-200 text-slate-500 shrink-0"
                        :class="open ? 'fa-chevron-down text-amber-400' : 'fa-chevron-right'"></i>
                </button>

                <div x-show="open && (sidebarOpen || isPinned)" x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="transform opacity-0 -translate-y-1"
                    x-transition:enter-end="transform opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="transform opacity-100 translate-y-0"
                    x-transition:leave-end="transform opacity-0 -translate-y-1"
                    class="pl-8 space-y-1 relative before:absolute before:left-5 before:top-2 before:bottom-2 before:w-px before:bg-blue-900/50">

                    <a href="{{ route('pencarian.masterfile') }}"
                        class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ request()->routeIs('pencarian.masterfile') ? 'bg-blue-950/80 text-amber-400 font-bold border-l-2 border-amber-400' : 'text-slate-400 hover:text-slate-200 hover:bg-blue-950/30' }}">
                        Masterfile WP
                    </a>
                    <a href="{{ route('pencarian.transaksi') }}"
                        class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ request()->routeIs('pencarian.transaksi') ? 'bg-blue-950/80 text-amber-400 font-bold border-l-2 border-amber-400' : 'text-slate-400 hover:text-slate-200 hover:bg-blue-950/30' }}">
                        Transaksi Penerimaan
                    </a>
                    <a href="{{ route('pencarian.spt') }}"
                        class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ request()->routeIs('pencarian.spt') ? 'bg-blue-950/80 text-amber-400 font-bold border-l-2 border-amber-400' : 'text-slate-400 hover:text-slate-200 hover:bg-blue-950/30' }}">
                        Tanda Terima SPT
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- Sidebar Footer -->
    <div
        class="p-3.5 border-t border-blue-900/40 bg-blue-950/30 text-[11px] text-slate-400 flex justify-between items-center shrink-0">
        <span x-show="sidebarOpen || isPinned" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="font-bold text-slate-400 whitespace-nowrap">
            DJP Executive
        </span>
        <span
            class="bg-blue-950 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded-md font-mono text-[10px] font-bold"
            :class="!(sidebarOpen || isPinned) && 'mx-auto'">
            v1.0
        </span>
    </div>
</aside>
