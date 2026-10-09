@extends('layouts.app')

@section('title', 'PKM Pengawasan - MPNWEB')

@section('content')

    <!-- HEADER & FILTER TOOLBAR (LOOKER STUDIO DRAFT STYLE) -->
    <div
        class="bg-white border border-slate-200/90 rounded-lg p-3 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 shadow-2xs border-t-4 border-t-amber-500 w-full min-w-0">

        <!-- Title & Icon Header -->
        <div class="flex items-center gap-3">
            <div
                class="w-9 h-9 rounded-md bg-blue-950 text-amber-400 flex items-center justify-center font-bold text-base shrink-0 shadow-xs border border-blue-900">
                <i class="fa-solid fa-eye text-xs"></i>
            </div>
            <div>
                <h1 class="text-base font-extrabold text-blue-950 leading-tight tracking-tight uppercase">Penerimaan PKM
                    Pengawasan</h1>
                <p class="text-[11px] text-slate-500 font-medium">Rincian realisasi PKM Pengawasan s.d. bulan terpilih per
                    Account Representative (AR)</p>
            </div>
        </div>

        <!-- Filter Controls Bar -->
        <form action="{{ route('pkm.pengawasan') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="sort" value="{{ $sortColumn }}">
            <input type="hidden" name="direction" value="{{ $sortDirection }}">

            <!-- Filter Seksi -->
            <select name="seksi"
                class="bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-md px-2.5 py-1 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                <option value="">Semua Seksi Pengawasan</option>
                @foreach ($daftarSeksi as $seksi)
                    <option value="{{ $seksi }}" {{ $seksiFilter === $seksi ? 'selected' : '' }}>{{ $seksi }}
                    </option>
                @endforeach
            </select>

            <!-- Select Bulan & Tahun Container -->
            <div class="flex items-center bg-slate-50 border border-slate-200 rounded-md px-2 py-1 gap-1.5">
                <span class="text-[11px] font-semibold text-slate-600"><i
                        class="fa-regular fa-calendar mr-1 text-amber-500"></i>Periode:</span>

                <select name="bulan"
                    class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-0.5 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                    @foreach (range(1, 12) as $m)
                        @php
                            $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
                        @endphp
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            s.d. {{ $monthName }}
                        </option>
                    @endforeach
                </select>

                <select name="tahun"
                    class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-0.5 font-mono font-bold focus:outline-none focus:border-blue-950 cursor-pointer">
                    @foreach (range(date('Y') - 3, date('Y')) as $year)
                        <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Button DJP -->
            <button type="submit"
                class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-bold px-3.5 py-1 rounded-md transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-filter text-[10px]"></i>
                <span>Terapkan</span>
            </button>

            @if (request()->has('bulan') || request()->has('tahun') || request()->has('seksi') || request()->has('sort'))
                <a href="{{ route('pkm.pengawasan') }}" class="text-slate-400 hover:text-slate-700 p-1 rounded transition"
                    title="Reset Filter">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                </a>
            @endif

            <!-- Export Button -->
            <a href="{{ route('pkm.pengawasan.export-detil', request()->all()) }}"
                class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1 rounded-md transition flex items-center gap-1.5 ml-auto">
                <i class="fa-solid fa-file-excel text-xs text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
        </form>
    </div>

    <!-- TABEL PKM PENGAWASAN (LOOKER STUDIO DATA TABLE STYLE) -->
    <div class="bg-white border border-slate-200/90 rounded-lg shadow-2xs overflow-hidden">

        <!-- Table Header Control -->
        <div class="p-3 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-950"></span>
                <h2 class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">Tabel Penerimaan PKM Pengawasan
                </h2>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead
                    class="bg-slate-100/80 text-blue-950 font-extrabold border-b border-slate-200 uppercase tracking-tight text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3 w-10 text-center whitespace-nowrap">No</th>

                        @php
                            $cols = [
                                'nama_seksi' => ['label' => 'Nama Seksi', 'align' => 'left'],
                                'nama_ar' => ['label' => 'Nama AR', 'align' => 'left'],
                                'total_akt_pengawasan' => ['label' => 'Akt Pengawasan', 'align' => 'right'],
                                'total_lainnya' => ['label' => 'Lainnya', 'align' => 'right'],
                                'total_wra_pengawasan' => ['label' => 'WRA Pengawasan', 'align' => 'right'],
                                'total_pkm_pengawasan' => ['label' => 'Total PKM Pengawasan', 'align' => 'right'],
                            ];
                        @endphp

                        @foreach ($cols as $colKey => $colMeta)
                            @php
                                $nextDir = $sortColumn === $colKey && $sortDirection === 'asc' ? 'desc' : 'asc';
                                $sortUrl = request()->fullUrlWithQuery(['sort' => $colKey, 'direction' => $nextDir]);
                            @endphp
                            <th
                                class="py-2.5 px-3 whitespace-nowrap {{ $colMeta['align'] === 'right' ? 'text-right' : '' }}">
                                <a href="{{ $sortUrl }}"
                                    class="flex items-center {{ $colMeta['align'] === 'right' ? 'justify-end' : '' }} gap-1 hover:text-amber-600 transition select-none">
                                    <span>{{ $colMeta['label'] }}</span>
                                    @if ($sortColumn !== $colKey)
                                        <i class="fa-solid fa-sort text-slate-300 text-[10px] ml-0.5"></i>
                                    @elseif($sortDirection === 'asc')
                                        <i class="fa-solid fa-sort-up text-amber-500 text-[10px] ml-0.5"></i>
                                    @else
                                        <i class="fa-solid fa-sort-down text-amber-500 text-[10px] ml-0.5"></i>
                                    @endif
                                </a>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200/70 text-slate-700 font-medium">
                    @forelse($pkmData as $index => $row)
                        <tr class="hover:bg-amber-50/40 transition-colors">
                            <td class="py-2 px-3 text-center text-slate-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                            <td class="py-2 px-3 font-semibold text-slate-800">
                                @if ($row->nama_seksi === 'Unassign')
                                    <span
                                        class="bg-rose-50 text-rose-700 px-2 py-0.5 rounded text-[10px] border border-rose-200 inline-block font-bold">
                                        Unassign
                                    </span>
                                @else
                                    <span
                                        class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] border border-slate-200 inline-block font-medium">
                                        {{ $row->nama_seksi }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-slate-900 font-bold">
                                @if ($row->nama_ar === 'Unassign')
                                    <span class="text-rose-600 italic font-medium">Unassign</span>
                                @else
                                    {{ $row->nama_ar }}
                                @endif
                            </td>
                            <td class="py-2 px-3 text-right font-mono text-slate-600 text-[11px]">
                                Rp {{ number_format($row->total_akt_pengawasan ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-right font-mono text-slate-600 text-[11px]">
                                Rp {{ number_format($row->total_lainnya ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-right font-mono text-slate-600 text-[11px]">
                                Rp {{ number_format($row->total_wra_pengawasan ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-right font-mono font-extrabold text-blue-950 text-[11px]">
                                Rp {{ number_format($row->total_pkm_pengawasan ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 italic text-xs bg-slate-50/30">
                                Tidak ada data PKM Pengawasan untuk filter bulan/tahun ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($pkmData->count() > 0)
                    <tfoot class="bg-slate-100 font-bold text-blue-950 border-t-2 border-slate-300 text-xs">
                        <tr>
                            <td colspan="3" class="py-2.5 px-3 text-center tracking-wider uppercase">Total Keseluruhan
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono">
                                Rp {{ number_format($pkmData->sum('total_akt_pengawasan'), 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono">
                                Rp {{ number_format($pkmData->sum('total_lainnya'), 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono">
                                Rp {{ number_format($pkmData->sum('total_wra_pengawasan'), 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-extrabold text-blue-950">
                                Rp {{ number_format($pkmData->sum('total_pkm_pengawasan'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

@endsection
