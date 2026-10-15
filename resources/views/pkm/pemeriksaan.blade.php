@extends('layouts.app')
@section('title', 'PKM Pemeriksaan - MPNWEB')
@section('content')
    <div class="space-y-4 font-sans">

        {{-- HEADER: pakai icon yang sama dengan card 1 biar konsisten - FA6 fallback ke fa-magnifying-glass yang pasti ada --}}
        <x-layout.page-header title="PKM Pemeriksaan" subtitle="Rincian realisasi PKM Pemeriksaan s.d. bulan terpilih per WP"
            icon="fa-magnifying-glass">
            <form action="{{ route('pkm.pemeriksaan') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="sort" value="{{ $sortColumn }}"><input type="hidden" name="direction"
                    value="{{ $sortDirection }}">
                <div class="relative flex-1 sm:w-64 min-w-[180px]"><span
                        class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400 text-xs"><i
                            class="fa-solid fa-magnifying-glass"></i></span><input type="text" name="search"
                        value="{{ $search }}" placeholder="Cari NPWP / WP / KLU..."
                        class="w-full pl-7 pr-2.5 py-1 bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-md focus:outline-none focus:border-blue-950 placeholder:text-slate-400 font-medium">
                </div>
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-md px-2 py-1 gap-1.5"><span
                        class="text-[11px] font-semibold text-slate-600"><i
                            class="fa-regular fa-calendar mr-1 text-amber-500"></i>Periode:</span><select name="bulan"
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
                <a href="{{ route('pkm.pemeriksaan.export-detil', request()->only(['tahun', 'bulan', 'search'])) }}"
                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1 rounded-md flex items-center gap-1.5"><i
                        class="fa-solid fa-file-excel text-xs text-emerald-600"></i><span>Export</span></a>
            </form>
        </x-layout.page-header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            {{-- Card 1: icon disamakan dengan header + icon card Pengawasan (fa-eye) & Penagihan (fa-gavel) pakai FA standard yang pasti ada --}}
            <x-pkm.pkm-mini-card title="Total PKM Pemeriksaan Saat Ini" :value="$saat_ini" icon="fa-magnifying-glass"
                type="primary" :subtitle="'s.d. ' . DateTime::createFromFormat('!m', $bulan)->format('F') . ' ' . $tahun" />
            <x-pkm.pkm-mini-card title="Total PKM s.d. Bulan Lalu" :value="$bulan_lalu" icon="fa-clock-rotate-left"
                type="secondary" :subtitle="'s.d. ' . DateTime::createFromFormat('!m', $bulanLalu)->format('F') . ' ' . $tahunLalu" />
            <x-pkm.pkm-mini-card title="Selisih" :value="$selisih" icon="fa-arrow-trend-up" type="secondary"
                :isSelisih="true" :subtitle="($growth >= 0 ? '+' : '') . number_format($growth, 1, ',', '.') . '% vs bulan lalu'" />
        </div>

        <div class="bg-white border border-slate-200/90 rounded-lg shadow-2xs overflow-hidden">
            <div class="p-3 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-950"></span>
                    <h2 class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">Tabel Realisasi PKM
                        Pemeriksaan</h2>
                </div><span
                    class="text-[11px] font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">Hal.
                    {{ $pkmData->currentPage() }} dari {{ $pkmData->lastPage() }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-slate-100/80 text-blue-950 font-extrabold border-b border-slate-200 uppercase text-[11px]">
                        <tr>
                            <th class="py-2.5 px-3 w-10 text-center">No</th>
                            <th class="py-2.5 px-3">NPWP</th>
                            <th class="py-2.5 px-3">Nama WP</th>
                            <th class="py-2.5 px-3 text-center">Kode KLU</th>
                            <th class="py-2.5 px-3">Nama KLU</th>
                            <th class="py-2.5 px-3 text-right">Total PKM Pemeriksaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 text-slate-700 font-medium">
                        @forelse($pkmData as $idx=>$row)
                            @php $isUnregistered = trim($row->nama_wp) === 'WP Tidak Terdaftar'; @endphp
                            {{-- CUKUP HIGHLIGHT SAJA - tanpa badge, sesuai request --}}
                            <tr
                                class="{{ $isUnregistered ? 'bg-amber-50/50 hover:bg-amber-100/60' : 'hover:bg-amber-50/40' }}">
                                <td class="py-2 px-3 text-center text-slate-400 font-mono text-[11px]">
                                    {{ $pkmData->firstItem() + $idx }}</td>
                                <td class="py-2 px-3 font-mono font-semibold text-blue-950 whitespace-nowrap text-[11px]">
                                    {{ $row->npwp15 }}</td>
                                <td
                                    class="py-2 px-3 font-bold {{ $isUnregistered ? 'text-amber-800 italic' : 'text-slate-900' }}">
                                    {{ $row->nama_wp }}
                                </td>
                                <td class="py-2 px-3 text-center font-mono text-[11px]">
                                    <span
                                        class="{{ $isUnregistered ? 'text-slate-400' : 'bg-slate-100 text-slate-700 border border-slate-200 px-2 py-0.5 rounded' }}">{{ $isUnregistered ? '-' : $row->kd_klu }}</span>
                                </td>
                                <td class="py-2 px-3 text-slate-600 max-w-xs truncate {{ $isUnregistered ? 'text-slate-400 italic' : '' }}"
                                    title="{{ $row->nm_klu }}">
                                    {{ $isUnregistered ? '-' : $row->nm_klu }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-extrabold text-blue-950 text-[11px]">
                                    Rp {{ number_format($row->total_akt_pemeriksaan ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 italic text-xs bg-slate-50/30">
                                    Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($pkmData->count() > 0)
                        <tfoot class="bg-slate-100 font-bold text-blue-950 border-t-2 border-slate-300 text-xs">
                            <tr>
                                <td colspan="5" class="py-2.5 px-3 text-center uppercase">Total Subhalaman Ini</td>
                                <td class="py-2.5 px-3 text-right font-mono">Rp
                                    {{ number_format($pkmData->getCollection()->sum('total_akt_pemeriksaan'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            <div class="p-2.5 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                <span class="text-[11px] text-slate-500">Menampilkan <span
                        class="font-bold text-slate-700">{{ $pkmData->firstItem() }}-{{ $pkmData->lastItem() }}</span>
                    dari <span class="font-bold">{{ $pkmData->total() }}</span></span>
                <div>{{ $pkmData->links() }}</div>
            </div>
        </div>
    </div>
@endsection
