@extends('layouts.app')

@section('title', 'PKM Penagihan - MPNWEB')

@section('content')

    <!-- HEADER & FILTER TOOLBAR (LOOKER STUDIO DRAFT STYLE) -->
    <div
        class="bg-white border border-slate-200/90 rounded-lg p-3 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 shadow-2xs border-t-4 border-t-amber-500 w-full min-w-0">

        <!-- Title & Icon Header -->
        <div class="flex items-center gap-3">
            <div
                class="w-9 h-9 rounded-md bg-blue-950 text-amber-400 flex items-center justify-center font-bold text-base shrink-0 shadow-xs border border-blue-900">
                <i class="fa-solid fa-gavel text-xs"></i>
            </div>
            <div>
                <h1 class="text-base font-extrabold text-blue-950 leading-tight tracking-tight uppercase">Penerimaan PKM
                    Penagihan</h1>
                <p class="text-[11px] text-slate-500 font-medium">Rincian realisasi PKM Penagihan s.d. bulan terpilih per
                    Juru Sita Pajak Negara (JSPN)</p>
            </div>
        </div>

        <!-- Filter Controls Bar -->
        <form action="{{ route('pkm.penagihan') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="sort" value="{{ $sortColumn }}">
            <input type="hidden" name="direction" value="{{ $sortDirection }}">

            <!-- Flag SKP Filter -->
            <select name="dspc_filter"
                class="bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-md px-2.5 py-1 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                <option value="">Semua Flag SKP</option>
                <option value="DSPC" {{ $dspcFilter === 'DSPC' ? 'selected' : '' }}>DSPC</option>
                <option value="NON-DSPC" {{ $dspcFilter === 'NON-DSPC' ? 'selected' : '' }}>NON-DSPC</option>
            </select>

            <!-- Select Bulan & Tahun Container -->
            <div class="flex items-center bg-slate-50 border border-slate-200 rounded-md px-2 py-1 gap-1.5">
                <span class="text-[11px] font-semibold text-slate-600"><i
                        class="fa-regular fa-calendar mr-1 text-amber-500"></i>Periode:</span>

                <select name="bulan"
                    class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-0.5 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            s.d. {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
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

            @if (request()->has('bulan') || request()->has('tahun') || request()->has('dspc_filter') || request()->has('sort'))
                <a href="{{ route('pkm.penagihan') }}" class="text-slate-400 hover:text-slate-700 p-1 rounded transition"
                    title="Reset Filter">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                </a>
            @endif

            <!-- Export Button -->
            <a href="{{ route('pkm.penagihan.export-detil', request()->all()) }}"
                class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1 rounded-md transition flex items-center gap-1.5 ml-auto">
                <i class="fa-solid fa-file-excel text-xs text-emerald-600"></i>
                <span>Export CSV</span>
            </a>
        </form>
    </div>

    <!-- TABEL PKM PENAGIHAN (LOOKER STUDIO DATA TABLE STYLE) -->
    <div class="bg-white border border-slate-200/90 rounded-lg shadow-2xs overflow-hidden">

        <!-- Table Header Control -->
        <div class="p-3 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <h2 class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">Tabel Realisasi PKM Penagihan</h2>
            </div>
            <span class="text-[11px] font-bold text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">
                Total: {{ $pkmData->count() }} Baris
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead
                    class="bg-slate-100/80 text-blue-950 font-extrabold border-b border-slate-200 uppercase tracking-tight text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3 w-10 text-center whitespace-nowrap">No</th>

                        <th class="py-2.5 px-3 whitespace-nowrap">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nip_jspn', 'direction' => $sortColumn === 'nip_jspn' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                class="flex items-center gap-1 hover:text-amber-600 transition select-none">
                                <span>NIP JSPN</span>
                                @if ($sortColumn === 'nip_jspn')
                                    <i
                                        class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-amber-500 text-[10px] ml-0.5"></i>
                                @else
                                    <i class="fa-solid fa-sort text-slate-300 text-[10px] ml-0.5"></i>
                                @endif
                            </a>
                        </th>

                        <th class="py-2.5 px-3 whitespace-nowrap">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama_jspn', 'direction' => $sortColumn === 'nama_jspn' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                class="flex items-center gap-1 hover:text-amber-600 transition select-none">
                                <span>Nama JSPN</span>
                                @if ($sortColumn === 'nama_jspn')
                                    <i
                                        class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-amber-500 text-[10px] ml-0.5"></i>
                                @else
                                    <i class="fa-solid fa-sort text-slate-300 text-[10px] ml-0.5"></i>
                                @endif
                            </a>
                        </th>

                        <th class="py-2.5 px-3 text-center whitespace-nowrap">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'flag_skp', 'direction' => $sortColumn === 'flag_skp' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                class="flex items-center justify-center gap-1 hover:text-amber-600 transition select-none">
                                <span>Flag SKP</span>
                                @if ($sortColumn === 'flag_skp')
                                    <i
                                        class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-amber-500 text-[10px] ml-0.5"></i>
                                @else
                                    <i class="fa-solid fa-sort text-slate-300 text-[10px] ml-0.5"></i>
                                @endif
                            </a>
                        </th>

                        <th class="py-2.5 px-3 text-right whitespace-nowrap">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'akt_penagihan', 'direction' => $sortColumn === 'akt_penagihan' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                class="flex items-center justify-end gap-1 hover:text-amber-600 transition select-none">
                                <span>Total PKM Penagihan</span>
                                @if ($sortColumn === 'akt_penagihan')
                                    <i
                                        class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-amber-500 text-[10px] ml-0.5"></i>
                                @else
                                    <i class="fa-solid fa-sort text-slate-300 text-[10px] ml-0.5"></i>
                                @endif
                            </a>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200/70 text-slate-700 font-medium">
                    @forelse($pkmData as $index => $row)
                        <tr class="hover:bg-amber-50/40 transition-colors">
                            <td class="py-2 px-3 text-center text-slate-400 font-mono text-[11px]">
                                {{ $index + 1 }}
                            </td>

                            <td class="py-2 px-3 font-mono font-semibold text-blue-950 whitespace-nowrap text-[11px]">
                                @if ($row->nip_jspn === 'Unassign')
                                    <span class="text-rose-600 italic font-sans font-medium">Unassign</span>
                                @else
                                    {{ $row->nip_jspn }}
                                @endif
                            </td>

                            <td class="py-2 px-3 text-slate-900 font-bold">
                                @if ($row->nama_jspn === 'Unassign')
                                    <span class="text-rose-600 italic font-medium">Unassign</span>
                                @else
                                    {{ $row->nama_jspn }}
                                @endif
                            </td>

                            <td class="py-2 px-3 text-center whitespace-nowrap">
                                @if (strtoupper($row->flag_skp) === 'DSPC')
                                    <span
                                        class="bg-amber-50 text-amber-800 border border-amber-300 text-[10px] px-2 py-0.5 rounded font-extrabold">
                                        DSPC
                                    </span>
                                @else
                                    <span
                                        class="bg-slate-100 text-slate-600 border border-slate-200 text-[10px] px-2 py-0.5 rounded font-bold">
                                        NON-DSPC
                                    </span>
                                @endif
                            </td>

                            <td
                                class="py-2 px-3 text-right font-mono font-extrabold text-blue-950 whitespace-nowrap text-[11px]">
                                Rp {{ number_format($row->akt_penagihan ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 italic text-xs bg-slate-50/30">
                                Tidak ada data PKM Penagihan yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($pkmData->count() > 0)
                    <tfoot class="bg-slate-100 font-bold text-blue-950 border-t-2 border-slate-300 text-xs">
                        <tr>
                            <td colspan="4" class="py-2.5 px-3 text-center tracking-wider uppercase">Total Keseluruhan
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-extrabold text-blue-950 whitespace-nowrap">
                                Rp {{ number_format($pkmData->sum('akt_penagihan'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

@endsection
