@props(['total' => 0, 'seksi' => null, 'flag' => null, 'bulan' => null, 'tahun' => null, 'count' => null])
<div class="flex flex-wrap items-center gap-2">
    @if ($seksi)
        <span
            class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-xs px-2.5 py-1 rounded-full font-medium"><i
                class="fa-solid fa-layer-group text-[10px] text-slate-400"></i>Seksi: <b
                class="text-blue-950">{{ $seksi }}</b></span>
    @endif
    @if ($flag)
        <span
            class="inline-flex items-center gap-1.5 {{ $flag === 'DSPC' ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-slate-50 border-slate-200 text-slate-700' }} border text-xs px-2.5 py-1 rounded-full font-bold">Flag:
            {{ $flag }}</span>
    @endif
    <span
        class="inline-flex items-center gap-1.5 bg-blue-50 border border-blue-200 text-blue-900 text-xs px-2.5 py-1 rounded-full font-medium"><i
            class="fa-regular fa-calendar text-[10px]"></i>Periode: <b>s.d.
            {{ DateTime::createFromFormat('!m', $bulan)->format('F') }} {{ $tahun }}</b></span>
    @if ($count !== null)
        <span
            class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-xs px-2.5 py-1 rounded-full font-mono font-medium">{{ $count }}
            baris</span>
    @endif
</div>
