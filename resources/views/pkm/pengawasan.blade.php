@extends('layouts.app')
@section('title', 'PKM Pengawasan - MPNWEB')
@section('content')
    <div class="space-y-4 font-sans">
        <x-layout.page-header title="PKM Pengawasan" subtitle="Rincian realisasi PKM Pengawasan per AR" icon="fa-eye">
            <form action="{{ route('pkm.pengawasan') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="sort" value="{{ $sortColumn }}"><input type="hidden" name="direction"
                    value="{{ $sortDirection }}">
                <select name="seksi"
                    class="bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-md px-2.5 py-1 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                    <option value="">Semua Seksi Pengawasan</option>
                    @foreach ($daftarSeksi as $seksi)
                        <option value="{{ $seksi }}" {{ $seksiFilter === $seksi ? 'selected' : '' }}>
                            {{ $seksi }}
                        </option>
                    @endforeach
                </select>
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-md px-2 py-1 gap-1.5">
                    <span class="text-[11px] font-semibold text-slate-600"><i
                            class="fa-regular fa-calendar mr-1 text-amber-500"></i>Periode:</span>
                    <select name="bulan"
                        class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-0.5 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>s.d.
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                        @endforeach
                    </select>
                    <select name="tahun"
                        class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-0.5 font-mono font-bold focus:outline-none focus:border-blue-950 cursor-pointer">
                        @foreach (range(date('Y') - 3, date('Y')) as $y)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-bold px-3.5 py-1 rounded-md flex items-center gap-1.5"><i
                        class="fa-solid fa-filter text-[10px]"></i><span>Terapkan</span></button>
                @if (request()->has('bulan') || request()->has('tahun') || request()->has('seksi') || request()->has('sort'))
                    <a href="{{ route('pkm.pengawasan') }}" class="text-slate-400 hover:text-slate-700 p-1 rounded"
                        title="Reset"><i class="fa-solid fa-rotate-left text-xs"></i></a>
                @endif
                <a href="{{ route('pkm.pengawasan.export-detil', request()->all()) }}"
                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1 rounded-md flex items-center gap-1.5"><i
                        class="fa-solid fa-file-excel text-xs text-emerald-600"></i><span>Export</span></a>
            </form>
        </x-layout.page-header>

        {{-- 3 MINI CARD BARU: Saat Ini | Bulan Lalu | Selisih --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <x-pkm.pkm-mini-card title="Total PKM Pengawasan Saat Ini" :value="$saat_ini" icon="fa-sack-dollar"
                type="primary" :subtitle="'s.d. ' . DateTime::createFromFormat('!m', $bulan)->format('F') . ' ' . $tahun" />
            <x-pkm.pkm-mini-card title="Total PKM s.d. Bulan Lalu" :value="$bulan_lalu" icon="fa-clock-rotate-left"
                type="secondary" :subtitle="'s.d. ' . DateTime::createFromFormat('!m', $bulanLalu)->format('F') . ' ' . $tahunLalu" />
            <x-pkm.pkm-mini-card title="Selisih" :value="$selisih" icon="fa-arrow-trend-up" type="secondary"
                :isSelisih="true" :subtitle="($growth >= 0 ? '+' : '') . number_format($growth, 1, ',', '.') . '% vs bulan lalu'" />
        </div>

        <div class="bg-white border border-slate-200/90 rounded-lg shadow-2xs overflow-hidden">
            <div class="p-3 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-950"></span>
                    <h2 class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">Tabel Penerimaan PKM
                        Pengawasan</h2>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-slate-100/80 text-blue-950 font-extrabold border-b border-slate-200 uppercase text-[11px]">
                        <tr>
                            <th class="py-2.5 px-3 w-10 text-center">No</th>
                            @php $cols=['nama_seksi'=>['label'=>'Nama Seksi','align'=>'left'],'nama_ar'=>['label'=>'Nama AR','align'=>'left'],'total_akt_pengawasan'=>['label'=>'Akt Pengawasan','align'=>'right'],'total_lainnya'=>['label'=>'Lainnya','align'=>'right'],'total_wra_pengawasan'=>['label'=>'WRA Pengawasan','align'=>'right'],'total_pkm_pengawasan'=>['label'=>'Total PKM','align'=>'right']]; @endphp
                            @foreach ($cols as $k => $m)
                                <th class="py-2.5 px-3 {{ $m['align'] === 'right' ? 'text-right' : '' }}"><a
                                        href="{{ request()->fullUrlWithQuery(['sort' => $k, 'direction' => $sortColumn === $k && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                        class="flex items-center {{ $m['align'] === 'right' ? 'justify-end' : '' }} gap-1 hover:text-amber-600">{{ $m['label'] }}
                                        @if ($sortColumn !== $k)
                                            <i class="fa-solid fa-sort text-slate-300 text-[10px] ml-0.5"></i>
                                        @elseif($sortDirection === 'asc')
                                        <i class="fa-solid fa-sort-up text-amber-500 text-[10px] ml-0.5"></i>@else<i
                                                class="fa-solid fa-sort-down text-amber-500 text-[10px] ml-0.5"></i>
                                        @endif
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 text-slate-700 font-medium">
                        @forelse($pkmData as $i=>$row)
                            <tr class="hover:bg-amber-50/40">
                                <td class="py-2 px-3 text-center text-slate-400 font-mono text-[11px]">{{ $i + 1 }}
                                </td>
                                <td class="py-2 px-3">
                                    @if ($row->nama_seksi === 'Unassign')
                                        <span
                                        class="bg-rose-50 text-rose-700 px-2 py-0.5 rounded text-[10px] border border-rose-200 font-bold">Unassign</span>@else<span
                                            class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] border border-slate-200">{{ $row->nama_seksi }}</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-slate-900 font-bold">{{ $row->nama_ar }}</td>
                                <td class="py-2 px-3 text-right font-mono text-[11px]">Rp
                                    {{ number_format($row->total_akt_pengawasan ?? 0, 0, ',', '.') }}</td>
                                <td class="py-2 px-3 text-right font-mono text-[11px]">Rp
                                    {{ number_format($row->total_lainnya ?? 0, 0, ',', '.') }}</td>
                                <td class="py-2 px-3 text-right font-mono text-[11px]">Rp
                                    {{ number_format($row->total_wra_pengawasan ?? 0, 0, ',', '.') }}</td>
                                <td class="py-2 px-3 text-right font-mono font-extrabold text-blue-950 text-[11px]">Rp
                                    {{ number_format($row->total_pkm_pengawasan ?? 0, 0, ',', '.') }}</td>
                        </tr>@empty<tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 italic text-xs bg-slate-50/30">
                                    Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
