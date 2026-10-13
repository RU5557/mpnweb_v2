@props(['fungsi' => [], 'totalIni' => 0, 'totalLalu' => 0, 'bulan' => null])

@php
    $growth = $totalLalu != 0 ? (($totalIni - $totalLalu) / $totalLalu) * 100 : 0;
    $isUp = $growth >= 0;
@endphp

<div class="flex flex-wrap items-center gap-2 mt-2.5">
    <span
        class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-xs px-2.5 py-1 rounded-full font-medium">
        <i class="fa-solid fa-layer-group text-[10px] text-slate-400"></i>
        Fungsi: <b class="font-mono text-blue-950">{{ count($fungsi) }} terpilih</b>
        <span
            class="max-w-[180px] truncate text-slate-500 font-normal ml-1 hidden md:inline">({{ implode(', ', array_slice($fungsi, 0, 2)) }}{{ count($fungsi) > 2 ? ' +' . (count($fungsi) - 2) . ' lainnya' : '' }})</span>
    </span>

    @if ($bulan)
        <span
            class="inline-flex items-center gap-1.5 bg-blue-50 border border-blue-200 text-blue-900 text-xs px-2.5 py-1 rounded-full font-medium">
            <i class="fa-regular fa-calendar text-[10px]"></i>
            Bulan: <b>{{ DateTime::createFromFormat('!m', $bulan)->format('F') }}</b>
        </span>
    @endif

    <span
        class="inline-flex items-center gap-1.5 {{ $isUp ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }} border text-xs px-2.5 py-1 rounded-full font-bold">
        <i class="fa-solid {{ $isUp ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-[10px]"></i>
        {{ $isUp ? '+' : '' }}{{ number_format($growth, 1, ',', '.') }}% vs lalu
    </span>

    <span
        class="inline-flex items-center gap-1.5 bg-amber-50 border border-amber-200 text-amber-900 text-xs px-2.5 py-1 rounded-full font-mono font-medium">
        Selisih: Rp {{ number_format($totalIni - $totalLalu, 0, ',', '.') }}
    </span>
</div>
