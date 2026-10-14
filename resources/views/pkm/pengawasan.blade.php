@extends('layouts.app')
@section('title', 'PKM Pengawasan - MPNWEB')
@section('content')
    <div class="space-y-4 font-sans">
        <x-layout.page-header title="PKM Pengawasan" subtitle="Rincian realisasi PKM Pengawasan per Seksi & AR" icon="fa-eye">
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
                <a href="{{ route('pkm.pengawasan.export-detil', request()->all()) }}"
                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1 rounded-md flex items-center gap-1.5"><i
                        class="fa-solid fa-file-excel text-xs text-emerald-600"></i><span>Export</span></a>
            </form>
        </x-layout.page-header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <x-pkm.pkm-mini-card title="Total PKM Pengawasan Saat Ini" :value="$saat_ini ?? $pkmData->sum('total_pkm_pengawasan')" icon="fa-eye" type="primary"
                :subtitle="'s.d. ' . DateTime::createFromFormat('!m', $bulan)->format('F') . ' ' . $tahun" />
            <x-pkm.pkm-mini-card title="Total PKM s.d. Bulan Lalu" :value="$bulan_lalu ?? 0" icon="fa-clock-rotate-left"
                type="secondary" :subtitle="isset($bulanLalu)
                    ? 's.d. ' . DateTime::createFromFormat('!m', $bulanLalu)->format('F') . ' ' . $tahunLalu
                    : '-'" />
            <x-pkm.pkm-mini-card title="Selisih" :value="$selisih ?? 0" icon="fa-arrow-trend-up" type="secondary"
                :isSelisih="true" :subtitle="isset($growth)
                    ? ($growth >= 0 ? '+' : '') . number_format($growth, 1, ',', '.') . '% vs bulan lalu'
                    : '-'" />
        </div>

        <div class="bg-white border border-slate-200/90 rounded-xl shadow-sm overflow-hidden">
            <div
                class="p-3.5 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white flex items-center justify-between">
                <div class="flex items-center gap-2.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-950"></span>
                    <h2 class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">Tabel PKM Pengawasan - Per
                        Seksi</h2>
                </div>
                <div class="flex items-center gap-1.5">
                    <button onclick="toggleAll(true)"
                        class="text-[11px] font-bold text-blue-950 bg-white border border-slate-300 px-3 py-1.5 rounded-md hover:bg-slate-50 shadow-sm"><i
                            class="fa-solid fa-angles-down mr-1 text-[10px]"></i>Expand</button>
                    <button onclick="toggleAll(false)"
                        class="text-[11px] font-bold text-slate-600 bg-white border border-slate-300 px-3 py-1.5 rounded-md hover:bg-slate-50 shadow-sm"><i
                            class="fa-solid fa-angles-up mr-1 text-[10px]"></i>Collapse</button>
                </div>
            </div>

            @php
                $grouped = $pkmData
                    ->groupBy('nama_seksi')
                    ->sortKeys()
                    ->sortBy(function ($items, $key) {
                        if ($key === 'Unassign') {
                            return 999;
                        }
                        preg_match('/(\d+)/', $key, $m);
                        return isset($m[1]) ? (int) $m[1] : 0;
                    });
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead
                        class="bg-slate-100 text-blue-950 font-extrabold border-b-2 border-slate-300 uppercase text-[11px] tracking-wide">
                        <tr>
                            <th class="py-3 px-4 w-10 text-center"></th>
                            <th class="py-3 px-4">NAMA SEKSI / NAMA AR</th>
                            <th class="py-3 px-4 text-right">AKT PENGAWASAN</th>
                            <th class="py-3 px-4 text-right">LAINNYA</th>
                            <th class="py-3 px-4 text-right">WRA PENGAWASAN</th>
                            <th class="py-3 px-4 text-right">TOTAL PKM PENGAWASAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60">
                        @forelse($grouped as $namaSeksi => $rows)
                            @php
                                $subAkt = $rows->sum('total_akt_pengawasan');
                                $subLainnya = $rows->sum('total_lainnya');
                                $subWra = $rows->sum('total_wra_pengawasan');
                                $subTotal = $rows->sum('total_pkm_pengawasan');
                                $isUnassign = $namaSeksi === 'Unassign';
                                $collapseId = 'seksi-' . md5($namaSeksi);
                            @endphp
                            {{-- BARIS SEKSI - 5 kolom sejajar --}}
                            <tr class="{{ $isUnassign ? 'bg-rose-50 hover:bg-rose-100' : 'bg-slate-50 hover:bg-slate-100' }} font-bold cursor-pointer transition"
                                onclick="toggleSeksi('{{ $collapseId }}')">
                                <td class="py-3 px-4 text-center"><i id="icon-{{ $collapseId }}"
                                        class="fa-solid fa-chevron-down text-[11px] text-slate-500 transition-transform"></i>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="{{ $isUnassign ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-white text-slate-700 border-slate-200' }} border px-2.5 py-0.5 rounded text-[11px]">{{ $namaSeksi }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-[11px] text-slate-700">Rp
                                    {{ number_format($subAkt, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-mono text-[11px] text-slate-700">Rp
                                    {{ number_format($subLainnya, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-mono text-[11px] text-slate-700">Rp
                                    {{ number_format($subWra, 0, ',', '.') }}</td>
                                <td
                                    class="py-3 px-4 text-right font-mono font-black text-[12px] {{ $isUnassign ? 'text-rose-700' : 'text-blue-950' }}">
                                    Rp {{ number_format($subTotal, 0, ',', '.') }}</td>
                            </tr>
                            {{-- RINCIAN AR - hidden by default --}}
                            <tr id="{{ $collapseId }}" class="seksi-body">
                                <td colspan="6" class="p-0">
                                    <div class="bg-white border-t border-slate-200">
                                        <table class="w-full text-left text-xs">
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach ($rows->sortBy('nama_ar') as $row)
                                                    <tr class="hover:bg-amber-50/40">
                                                        <td class="py-2 px-4 w-10 text-center"><span
                                                                class="w-4 h-4 rounded-full bg-slate-100 border border-slate-200 inline-flex items-center justify-center"><span
                                                                    class="w-1 h-1 bg-slate-400 rounded-full"></span></span>
                                                        </td>
                                                        <td class="py-2.5 px-4 text-slate-800 font-medium pl-8"><i
                                                                class="fa-solid fa-minus text-[8px] text-slate-300 mr-2"></i>{{ $row->nama_ar }}
                                                        </td>
                                                        <td
                                                            class="py-2.5 px-4 text-right font-mono text-[11px] text-slate-600">
                                                            Rp
                                                            {{ number_format($row->total_akt_pengawasan ?? 0, 0, ',', '.') }}
                                                        </td>
                                                        <td
                                                            class="py-2.5 px-4 text-right font-mono text-[11px] text-slate-600">
                                                            Rp {{ number_format($row->total_lainnya ?? 0, 0, ',', '.') }}
                                                        </td>
                                                        <td
                                                            class="py-2.5 px-4 text-right font-mono text-[11px] text-slate-600">
                                                            Rp
                                                            {{ number_format($row->total_wra_pengawasan ?? 0, 0, ',', '.') }}
                                                        </td>
                                                        <td
                                                            class="py-2.5 px-4 text-right font-mono font-bold text-blue-950 text-[11px]">
                                                            Rp
                                                            {{ number_format($row->total_pkm_pengawasan ?? 0, 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400 italic">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- TOTAL BAWAH - tetap 5 kolom sejajar --}}
                    @if ($pkmData->count() > 0)
                        <tfoot class="bg-blue-950 text-white border-t-2 border-amber-400 font-bold">
                            <tr>
                                <td class="py-3.5 px-4 text-center"><i
                                        class="fa-solid fa-layer-group text-amber-400 text-[11px]"></i></td>
                                <td class="py-3.5 px-4 uppercase tracking-wide text-[11px] text-amber-400">TOTAL
                                    KESELURUHAN</td>
                                <td class="py-3.5 px-4 text-right font-mono text-[11px]">Rp
                                    {{ number_format($pkmData->sum('total_akt_pengawasan'), 0, ',', '.') }}</td>
                                <td class="py-3.5 px-4 text-right font-mono text-[11px]">Rp
                                    {{ number_format($pkmData->sum('total_lainnya'), 0, ',', '.') }}</td>
                                <td class="py-3.5 px-4 text-right font-mono text-[11px]">Rp
                                    {{ number_format($pkmData->sum('total_wra_pengawasan'), 0, ',', '.') }}</td>
                                <td class="py-3.5 px-4 text-right font-mono font-black text-[13px] text-amber-400">Rp
                                    {{ number_format($pkmData->sum('total_pkm_pengawasan'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleSeksi(id) {
                const body = document.getElementById(id);
                const icon = document.getElementById('icon-' + id);
                if (!body) return;
                const isHidden = body.style.display === 'none';
                body.style.display = isHidden ? '' : 'none';
                if (icon) icon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
            }

            function toggleAll(expand) {
                document.querySelectorAll('.seksi-body').forEach(el => el.style.display = expand ? '' : 'none');
                document.querySelectorAll('[id^="icon-seksi-"]').forEach(icon => icon.style.transform = expand ?
                    'rotate(0deg)' : 'rotate(-90deg)');
            }
            // Semua collapse saat halaman dibuka - tidak ada auto expand Seksi I
            document.addEventListener('DOMContentLoaded', function() {
                toggleAll(false);
            });
        </script>
    @endpush

@endsection
