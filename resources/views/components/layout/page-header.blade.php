{{-- resources/views/components/layout/page-header.blade.php - VERSI PROPORTIONAL SAMA DENGAN MASTERFILE WP --}}
@props([
    'title' => 'Judul Halaman',
    'subtitle' => null,
    'icon' => 'fa-users-gear',
    'showSlot' => true,
])

{{-- 
    Komponen header standar MPNWEB - ukuran sama persis dengan halaman masterfile WP versi pimpinan clean
    - Icon: w-10 h-10 rounded-lg bg-blue-950 text-amber-400
    - Judul: text-[15px] font-extrabold text-blue-950 uppercase tracking-wide
    - Subtitle: text-[11px] text-slate-500
    - Container: bg-white border rounded-lg p-3.5 border-t-4 amber

    Penggunaan:
    <x-layout.page-header title="Pencarian Transaksi" subtitle="Data Transaksi Penerimaan" icon="fa-receipt" />
    <x-layout.page-header title="Dashboard" subtitle="Ringkasan Kinerja" icon="fa-chart-line">
        <form>...filter...</form>
    </x-layout.page-header>
--}}
<div
    class="bg-white border border-slate-200/90 rounded-lg p-3.5 flex items-center gap-3.5 shadow-2xs border-t-4 border-t-amber-500">
    <div class="w-10 h-10 rounded-lg bg-blue-950 text-amber-400 flex items-center justify-center shadow-sm shrink-0">
        <i class="fa-solid {{ $icon }} text-sm"></i>
    </div>
    <div class="flex-1 min-w-0">
        <h1 class="text-[15px] font-extrabold text-blue-950 uppercase tracking-wide leading-tight">{{ $title }}
        </h1>
        @if ($subtitle)
            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($showSlot && trim($slot) !== '')
        <div class="ml-auto flex items-center gap-2 shrink-0">
            {{ $slot }}
        </div>
    @endif
</div>
