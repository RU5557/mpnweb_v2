@extends('layouts.app')

@section('content')
    <div class="space-y-5 font-sans">

        <!-- HEADER & FILTER TOOLBAR (DISAMAKAN KETINGGIAN DENGAN HALAMAN PENJAGAAN BULANAN) -->
        <div
            class="bg-white border border-slate-200/90 rounded-lg p-4 sm:p-4.5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3.5 shadow-2xs border-t-4 border-t-amber-500 w-full min-w-0">

            <!-- Report Title -->
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-md bg-blue-950 text-amber-400 flex items-center justify-center font-bold text-base shrink-0 shadow-xs border border-blue-900">
                    <i class="fa-solid fa-chart-column text-sm"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-blue-950 leading-tight tracking-tight uppercase">Dashboard
                        Ringkasan Penerimaan</h1>
                    <p class="text-[11px] text-slate-500 font-medium">Laporan realisasi dan pencapaian target penerimaan</p>
                </div>
            </div>

            <!-- Filter Controls Bar (Standard Height match with Penjagaan Bulanan) -->
            <form action="{{ route('penerimaan.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">

                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-md px-3 py-1.5 gap-2">
                    <span class="text-[11px] font-semibold text-slate-600"><i
                            class="fa-regular fa-calendar mr-1 text-amber-500"></i>Periode:</span>

                    <select name="bulan_awal"
                        class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-1 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                        @foreach ($listBulan as $m => $namaBulan)
                            <option value="{{ $m }}" {{ $blnAwal == $m ? 'selected' : '' }}>{{ $namaBulan }}
                            </option>
                        @endforeach
                    </select>

                    <span class="text-xs text-slate-400 font-semibold">s.d.</span>

                    <select name="bulan_akhir"
                        class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-1 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                        @foreach ($listBulan as $m => $namaBulan)
                            <option value="{{ $m }}" {{ $blnAkhir == $m ? 'selected' : '' }}>{{ $namaBulan }}
                            </option>
                        @endforeach
                    </select>

                    <select name="tahun"
                        class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-1 font-mono font-bold focus:outline-none focus:border-blue-950 cursor-pointer">
                        @foreach (range(date('Y') - 3, date('Y')) as $year)
                            <option value="{{ $year }}" {{ $thnIni == $year ? 'selected' : '' }}>{{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-bold px-3.5 py-1.5 rounded-md transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    <span>Terapkan</span>
                </button>

                @if (request()->has('bulan_awal') || request()->has('bulan_akhir') || request()->has('tahun') || request()->has('bulan'))
                    <a href="{{ route('penerimaan.dashboard') }}"
                        class="text-slate-400 hover:text-slate-700 p-1.5 rounded transition" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                    </a>
                @endif

                <a href="{{ route('penerimaan.dashboard.export-detil', ['tahun' => $thnIni, 'bulan_awal' => $blnAwal, 'bulan_akhir' => $blnAkhir]) }}"
                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1.5 rounded-md transition flex items-center gap-1.5 ml-auto">
                    <i class="fa-solid fa-file-excel text-xs text-emerald-600"></i>
                    <span>Export CSV</span>
                </a>
            </form>
        </div>

        <!-- SCORECARDS ROW 1: DJP CORE KPI METRICS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <!-- Scorecard: Penerimaan Saat Ini -->
            <div
                class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col justify-between hover:border-amber-400 transition shadow-2xs relative overflow-hidden min-h-[140px]">
                <div class="absolute top-0 left-0 right-0 h-1 bg-blue-950"></div>
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Penerimaan Saat Ini</span>
                        <i class="fa-solid fa-wallet text-amber-500 text-xs"></i>
                    </div>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-xs text-slate-400 font-normal">Rp</span>
                        <span class="text-2xl font-black text-blue-950 font-mono tracking-tight tabular-nums">
                            {{ number_format($penerimaanSaatIni, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span
                        class="px-1.5 py-0.5 rounded text-[10px] font-bold font-mono {{ $growthMoM >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60' }}"
                        title="Growth Month-on-Month">
                        <i class="fa-solid {{ $growthMoM >= 0 ? 'fa-caret-up' : 'fa-caret-down' }}"></i>
                        {{ number_format(abs($growthMoM), 1, ',', '.') }}% MoM
                    </span>

                    <span
                        class="px-1.5 py-0.5 rounded text-[10px] font-bold font-mono {{ $growthYoY >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60' }}"
                        title="Growth Year-on-Year">
                        <i class="fa-solid {{ $growthYoY >= 0 ? 'fa-caret-up' : 'fa-caret-down' }}"></i>
                        {{ number_format(abs($growthYoY), 1, ',', '.') }}% YoY
                    </span>
                </div>
            </div>

            <!-- Scorecard: Penerimaan Bulan Lalu -->
            <div
                class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col justify-between hover:border-amber-400 transition shadow-2xs relative overflow-hidden min-h-[140px]">
                <div class="absolute top-0 left-0 right-0 h-1 bg-blue-950"></div>
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Penerimaan Bulan Lalu</span>
                        <i class="fa-regular fa-calendar-check text-slate-400 text-xs"></i>
                    </div>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-xs text-slate-400 font-normal">Rp</span>
                        <span class="text-2xl font-bold text-slate-800 font-mono tracking-tight tabular-nums">
                            {{ number_format($penerimaanBlnLalu, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div
                    class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Pembanding MoM</span>
                    <i class="fa-solid fa-arrow-right-arrow-left text-slate-300 text-[10px]"></i>
                </div>
            </div>

            <!-- Scorecard: Penerimaan Tahun Lalu -->
            <div
                class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col justify-between hover:border-amber-400 transition shadow-2xs relative overflow-hidden min-h-[140px]">
                <div class="absolute top-0 left-0 right-0 h-1 bg-blue-950"></div>
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Penerimaan Tahun Lalu</span>
                        <i class="fa-solid fa-clock-rotate-left text-amber-500 text-xs"></i>
                    </div>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-xs text-slate-400 font-normal">Rp</span>
                        <span class="text-2xl font-bold text-slate-800 font-mono tracking-tight tabular-nums">
                            {{ number_format($penerimaanThnLalu, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div
                    class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Pembanding YoY</span>
                    <i class="fa-solid fa-arrow-right-arrow-left text-slate-300 text-[10px]"></i>
                </div>
            </div>

            <!-- Scorecard Summary Badge: DJP Blue & Gold Signature Card -->
            <div
                class="bg-gradient-to-br from-blue-950 via-slate-900 to-blue-950 text-white border border-blue-900 rounded-lg p-4 flex flex-col justify-between shadow-md relative overflow-hidden min-h-[140px]">
                <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-amber-400/10 rounded-full blur-xl pointer-events-none">
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Status Kantor</span>
                        <span
                            class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm shadow-amber-400/50 animate-pulse"></span>
                    </div>
                    <div class="mt-2">
                        <div class="text-xs text-slate-300 font-medium">Total Capaian Kantor</div>
                        <div class="text-2xl font-black font-mono text-amber-400 mt-0.5 tracking-tight drop-shadow-xs">
                            {{ number_format($capaianKantor, 2, ',', '.') }}%
                        </div>
                    </div>
                </div>

                <div
                    class="mt-3 pt-3 border-t border-blue-900/80 text-[11px] text-slate-300 flex items-center justify-between">
                    <span>Update data harian</span>
                    <span
                        class="font-mono text-[10px] text-amber-400 font-bold bg-blue-900/60 px-2 py-0.5 rounded border border-amber-400/30">Pondok
                        Gede</span>
                </div>
            </div>
        </div>

        <!-- BREAKDOWN PERFORMANCE METRICS CONTAINER -->
        <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-2xs border-t-4 border-t-blue-950">

            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                <h2 class="text-xs font-extrabold text-blue-950 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-amber-500"></i>
                    Breakdown Target & Realisasi Per Komponen
                </h2>
                <span class="text-[11px] text-slate-400 font-mono">6 Variabel Metrik</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ([
            'ppm' => 'PPM',
            'pkm' => 'PKM Total',
            'pbp' => 'PBP',
            'pengawasan' => 'PKM Pengawasan',
            'pemeriksaan' => 'PKM Pemeriksaan',
            'penagihan' => 'PKM Penagihan',
        ] as $key => $title)
                    <div
                        class="bg-slate-50/70 border border-slate-200/90 rounded-lg p-3.5 flex flex-col justify-between hover:bg-white hover:border-amber-400/80 hover:shadow-xs transition">

                        <div>
                            <!-- Card Header -->
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-extrabold text-blue-950 text-xs">{{ $title }}</span>
                                <span
                                    class="bg-blue-950 text-amber-400 font-mono font-extrabold text-xs px-2.5 py-0.5 rounded shadow-2xs border border-blue-900">
                                    {{ number_format($metrics[$key]['persen'], 1, ',', '.') }}%
                                </span>
                            </div>

                            <!-- Progress Bar DJP Gold Style -->
                            <div class="w-full bg-slate-200 rounded-full h-1.5 mb-3 overflow-hidden">
                                <div class="bg-gradient-to-r from-amber-500 to-yellow-400 h-1.5 rounded-full"
                                    style="width: {{ min($metrics[$key]['persen'], 100) }}%"></div>
                            </div>

                            <!-- Data Table Mini -->
                            <div class="space-y-1.5 text-xs font-mono bg-white p-2.5 rounded border border-slate-200/80">
                                <div class="flex justify-between items-center text-slate-500">
                                    <span class="font-sans text-[11px]">Target</span>
                                    <span class="font-medium text-slate-700">Rp
                                        {{ number_format($metrics[$key]['target'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-500">
                                    <span class="font-sans text-[11px]">Realisasi</span>
                                    <span class="font-bold text-blue-950">Rp
                                        {{ number_format($metrics[$key]['realisasi'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Metrik -->
                        <div class="mt-3 pt-2.5 border-t border-slate-200/80 flex items-center justify-between text-[11px]">
                            @if ($key === 'pbp')
                                <span class="text-slate-500 font-sans font-medium">Sisa Target:</span>
                                <span class="text-rose-600 font-mono font-bold">
                                    Rp {{ number_format($metrics[$key]['sisa'], 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-slate-500 font-sans font-medium">Growth YoY:</span>
                                <span
                                    class="font-mono font-bold {{ $metrics[$key]['growthYoY'] >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                    <i
                                        class="fa-solid {{ $metrics[$key]['growthYoY'] >= 0 ? 'fa-caret-up' : 'fa-caret-down' }}"></i>
                                    {{ number_format(abs($metrics[$key]['growthYoY']), 1, ',', '.') }}%
                                </span>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

    </div>
@endsection
