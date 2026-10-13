@extends('layouts.app')

@section('content')
    <div class="space-y-5 font-sans">

        {{-- HEADER & FILTER - Modular --}}
        <div
            class="bg-white border border-slate-200/90 rounded-lg p-3 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 shadow-2xs border-t-4 border-t-amber-500 w-full min-w-0">
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-md bg-blue-950 text-amber-400 flex items-center justify-center font-bold text-base shrink-0 shadow-xs border border-blue-900">
                    <i class="fa-solid fa-chart-column text-xs"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-blue-950 leading-tight tracking-tight uppercase">Dashboard
                        Ringkasan Penerimaan</h1>
                    <p class="text-[11px] text-slate-500 font-medium">Ringkasan realisasi & pencapaian target per fungsi
                    </p>
                </div>
            </div>

            <form action="{{ route('penerimaan.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-md px-2 py-1 gap-2">
                    <span class="text-[11px] font-semibold text-slate-600"><i
                            class="fa-regular fa-calendar mr-1 text-amber-500"></i>Periode:</span>
                    <select name="bulan_awal"
                        class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-0.5 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                        @foreach ($listBulan as $m => $namaBulan)
                            <option value="{{ $m }}" {{ $blnAwal == $m ? 'selected' : '' }}>{{ $namaBulan }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-xs text-slate-400 font-semibold">s.d.</span>
                    <select name="bulan_akhir"
                        class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-0.5 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                        @foreach ($listBulan as $m => $namaBulan)
                            <option value="{{ $m }}" {{ $blnAkhir == $m ? 'selected' : '' }}>{{ $namaBulan }}
                            </option>
                        @endforeach
                    </select>
                    <select name="tahun"
                        class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-0.5 font-mono font-bold focus:outline-none focus:border-blue-950 cursor-pointer">
                        @foreach (range(date('Y') - 2, date('Y')) as $year)
                            <option value="{{ $year }}" {{ $thnIni == $year ? 'selected' : '' }}>{{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-bold px-3.5 py-1 rounded-md transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-filter text-[10px]"></i><span>Terapkan</span>
                </button>
                <a href="{{ route('penerimaan.dashboard.export-detil', ['tahun' => $thnIni, 'bulan_awal' => $blnAwal, 'bulan_akhir' => $blnAkhir]) }}"
                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1 rounded-md transition flex items-center gap-1.5 ml-auto">
                    <i class="fa-solid fa-file-excel text-xs text-emerald-600"></i><span>Export CSV</span>
                </a>
            </form>
        </div>

        {{-- ROW 1: KPI UTAMA - Pakai Component Modular --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-dashboard.scorecard title="Penerimaan Saat Ini" :value="$summary['penerimaanSaatIni']" icon="fa-wallet" :growthMoM="$summary['growthMoM']"
                :growthYoY="$summary['growthYoY']" />
            <x-dashboard.scorecard title="Penerimaan s.d. Bulan Lalu" :value="$summary['penerimaanBlnLalu']" icon="fa-calendar-check"
                type="secondary">
                <div class="flex items-center justify-between text-[11px] text-slate-400 w-full"><span>Pembanding
                        MoM</span><i class="fa-solid fa-arrow-right-arrow-left text-slate-300 text-[10px]"></i></div>
            </x-dashboard.scorecard>
            <x-dashboard.scorecard title="Penerimaan Tahun Lalu" :value="$summary['penerimaanThnLalu']" icon="fa-clock-rotate-left"
                type="secondary">
                <div class="flex items-center justify-between text-[11px] text-slate-400 w-full"><span>Pembanding
                        YoY</span><i class="fa-solid fa-arrow-right-arrow-left text-slate-300 text-[10px]"></i></div>
            </x-dashboard.scorecard>

            {{-- Status Kantor Card --}}
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
                        <div class="text-2xl font-black font-mono text-amber-400 mt-0.5 tracking-tight">
                            {{ number_format($summary['capaianKantor'], 2, ',', '.') }}%</div>
                    </div>
                </div>
                <div
                    class="mt-3 pt-3 border-t border-blue-900/80 text-[11px] text-slate-300 flex items-center justify-between">
                    <span>Update harian</span>
                    <span
                        class="font-mono text-[10px] text-amber-400 font-bold bg-blue-900/60 px-2 py-0.5 rounded border border-amber-400/30">Pondok
                        Gede</span>
                </div>
            </div>
        </div>

        {{-- BREAKDOWN - Pakai Component --}}
        <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-2xs border-t-4 border-t-blue-950">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                <h2 class="text-xs font-extrabold text-blue-950 uppercase tracking-wider flex items-center gap-2"><i
                        class="fa-solid fa-list-check text-amber-500"></i>Breakdown Target & Realisasi Per Komponen</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($cards as $card)
                    <x-dashboard.breakdown-card :card="$card" />
                @endforeach
            </div>
        </div>

        {{-- PEMAHAMAN / DOKUMENTASI --}}
        <div class="bg-amber-50/60 border border-amber-200/60 rounded-lg p-4">
            <h3 class="text-xs font-extrabold text-blue-950 uppercase flex items-center gap-2"><i
                    class="fa-solid fa-circle-info text-amber-500"></i>Pemahaman Dashboard (untuk pegawai)</h3>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-3 text-[11.5px] text-slate-600 leading-relaxed">
                <div class="bg-white border border-slate-200 rounded p-3 space-y-1">
                    <div class="font-bold text-blue-950">PPM</div>
                    <div>Fungsi: <span class="font-mono bg-slate-100 px-1 rounded">PPM BRUTO, SPKMP</span> - Penerimaan dari
                        WP yang diawasi rutin. Target: <span
                            class="font-bold">{{ number_format($cards['ppm']['target'] ?? 0, 0, ',', '.') }}</span></div>
                    <div>Growth YoY {{ number_format($cards['ppm']['growthYoY'] ?? 0, 1) }}% = bandingkan tahun ini vs
                        tahun lalu periode sama.</div>
                </div>
                <div class="bg-white border border-slate-200 rounded p-3 space-y-1">
                    <div class="font-bold text-blue-950">PKM (Pengawasan, Pemeriksaan, Penagihan)</div>
                    <div>Fungsi: AKT PENGAWASAN, PEMERIKSAAN, PENAGIHAN, WRA, dll. Ini tracking kinerja Seksi. Dibagi lagi
                        jadi 3 sub: Pengawasan, Pemeriksaan, Penagihan.</div>
                    <div>PBP terpisah karena target baru dan belum ada realisasi historis.</div>
                </div>
                <div class="bg-white border border-slate-200 rounded p-3">
                    <div class="font-bold text-blue-950">MoM vs YoY</div>
                    <div><b>MoM</b> = Month-on-Month: (SaatIni - BlnLalu)/BlnLalu. Untuk cek apakah bulan ini naik dibanding
                        bulan lalu.</div>
                    <div><b>YoY</b> = Year-on-Year: (SaatIni - ThnLalu)/ThnLalu. Untuk cek pertumbuhan tahunan (fair karena
                        musim pajak sama).</div>
                </div>
                <div class="bg-white border border-slate-200 rounded p-3">
                    <div class="font-bold text-blue-950">Export CSV</div>
                    <div>Export csv sudah memuat detil pembayaran atau transaksi penerimaan tanpa diagregasi (raw data).
                    </div>
                    <div>Jenis dan fungsi penerimaan juga sudah ada di detil pembayaran yang dapat di unduh melalui
                        menu export csv.</div>
                </div>
            </div>
        </div>

    </div>
@endsection
