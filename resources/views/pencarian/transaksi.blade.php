@extends('layouts.app')

@section('title', 'Pencarian Detil Transaksi / DRM - MPNWEB')

@section('content')
    <div x-data="{ loading: false }" class="space-y-4">

        <!-- HEADER PAGE (LOOKER STUDIO STYLE) -->
        <div
            class="bg-white border border-slate-200/90 rounded-lg p-3 flex items-center gap-3 shadow-2xs border-t-4 border-t-amber-500">
            <div
                class="w-9 h-9 rounded-md bg-blue-950 text-amber-400 flex items-center justify-center font-bold text-base shrink-0 shadow-xs border border-blue-900">
                <i class="fa-solid fa-receipt text-xs"></i>
            </div>
            <div>
                <h1 class="text-base font-extrabold text-blue-950 leading-tight tracking-tight uppercase">Pencarian Detil
                    Transaksi / DRM</h1>
                <p class="text-[11px] text-slate-500 font-medium">Saring dan telusuri transaksi pembayaran pajak secara
                    real-time</p>
            </div>
        </div>

        <!-- LAYOUT SIDE-BY-SIDE -->
        <div class="flex flex-col lg:flex-row gap-4 items-start">

            <!-- SIDEBAR FILTER (SEBELAH KIRI) -->
            <div
                class="w-full lg:w-80 flex-shrink-0 bg-white p-3.5 rounded-lg shadow-2xs border border-slate-200/90 space-y-3 sticky top-20">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <div class="flex items-center gap-2 text-blue-950 font-extrabold text-xs uppercase tracking-wider">
                        <i class="fa-solid fa-filter text-amber-500"></i>
                        <span>Filter DRM</span>
                    </div>
                    <a href="{{ route('pencarian.transaksi') }}"
                        class="text-[11px] text-slate-400 hover:text-blue-950 font-medium transition flex items-center gap-1">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i> Reset
                    </a>
                </div>

                <form id="searchTransaksiForm" action="{{ route('pencarian.transaksi') }}" method="GET"
                    @submit="loading = true" class="space-y-2.5">
                    <input type="hidden" name="has_search" value="1">
                    <input type="hidden" name="sort_by" value="{{ $sortBy ?? 'tgl_setor' }}">
                    <input type="hidden" name="sort_order" value="{{ $sortOrder ?? 'desc' }}">

                    <!-- NPWP (9/15) -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">NPWP (9 / 15 Digit)</label>
                        <input type="text" name="npwp" value="{{ $npwpInput ?? '' }}" placeholder="Masukkan NPWP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1 focus:outline-none focus:border-blue-950 font-medium">
                    </div>

                    <!-- Nama WP -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Nama Wajib Pajak</label>
                        <input type="text" name="nama" value="{{ $namaWp ?? '' }}" placeholder="Masukkan Nama WP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1 focus:outline-none focus:border-blue-950 font-medium">
                    </div>

                    <!-- Kode MAP -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Kode MAP</label>
                        <input type="text" name="kd_map" value="{{ $kdMap ?? '' }}" placeholder="Contoh: 411121"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1 focus:outline-none focus:border-blue-950 font-medium font-mono">
                    </div>

                    <!-- Kode Bayar -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Kode Bayar</label>
                        <input type="text" name="kd_bayar" value="{{ $kdBayar ?? '' }}" placeholder="Contoh: 100"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1 focus:outline-none focus:border-blue-950 font-medium font-mono">
                    </div>

                    <!-- Tanggal Setor (Start & End) -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Tanggal Setor (Mulai)</label>
                        <input type="date" name="tgl_setor_start" value="{{ $tglSetorStart ?? '' }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium mb-1.5">

                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Tanggal Setor (Sampai)</label>
                        <input type="date" name="tgl_setor_end" value="{{ $tglSetorEnd ?? '' }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium">
                    </div>

                    <!-- Filter Masa1, Masa2 & Thn Pajak Dalam 1 Baris -->
                    <div class="grid grid-cols-3 gap-1.5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Masa Awal</label>
                            <select name="masa1"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-1.5 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                                <option value="">-</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}"
                                        {{ (int) ($masa1 ?? 0) === $m ? 'selected' : '' }}>
                                        {{ sprintf('%02d', $m) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Masa Akhir</label>
                            <select name="masa2"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-1.5 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                                <option value="">-</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}"
                                        {{ (int) ($masa2 ?? 0) === $m ? 'selected' : '' }}>
                                        {{ sprintf('%02d', $m) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Thn Pajak</label>
                            <input type="number" name="thn_pajak" value="{{ $thnPajak ?? '' }}" placeholder="2025"
                                min="2000" max="2099"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-1.5 py-1 focus:outline-none focus:border-blue-950 font-medium font-mono">
                        </div>
                    </div>

                    <!-- NTPN -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">NTPN</label>
                        <input type="text" name="ntpn" value="{{ $ntpn ?? '' }}" placeholder="16 Karakter NTPN"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1 focus:outline-none focus:border-blue-950 font-medium font-mono">
                    </div>

                    <!-- Kota -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Kota / Kabupaten</label>
                        <select name="kota"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua Kota --</option>
                            @foreach ($listKota ?? [] as $k)
                                <option value="{{ $k }}" {{ ($kotaSelected ?? '') === $k ? 'selected' : '' }}>
                                    {{ $k }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jenis WP -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Jenis Wajib Pajak</label>
                        <select name="jenis_wp"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua Jenis WP --</option>
                            @foreach ($listJenisWp ?? [] as $j)
                                <option value="{{ $j }}"
                                    {{ ($jenisWpSelected ?? '') === $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sektor Usaha (KLU) -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Sektor Usaha (KLU)</label>
                        <select name="sektor"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua Sektor Usaha --</option>
                            @foreach ($listSektor ?? [] as $s)
                                <option value="{{ $s }}"
                                    {{ ($sektorSelected ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Seksi -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Seksi</label>
                        <select name="seksi_id"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua Seksi --</option>
                            @foreach ($listSeksi ?? [] as $sek)
                                <option value="{{ $sek->id }}"
                                    {{ ($seksiSelected ?? '') == $sek->id ? 'selected' : '' }}>{{ $sek->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Multi-select AR -->
                    @php
                        $listArArr = collect($listAr ?? [])
                            ->pluck('nip')
                            ->toArray();
                        $currentAr = array_values((array) ($nipAr ?? []));
                        $isAllAr = count($currentAr) === count($listArArr) && count($listArArr) > 0;
                    @endphp
                    <div x-data="{ open: false, selectAll: {{ $isAllAr ? 'true' : 'false' }}, selected: {{ json_encode($currentAr) }}, options: {{ json_encode($listArArr) }} }" class="relative">
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Nama AR</label>
                        <button type="button" @click="open = !open"
                            class="w-full bg-slate-50 border border-slate-300 rounded-md p-1.5 text-left text-xs text-slate-800 flex justify-between items-center cursor-pointer">
                            <span
                                x-text="selected.length === options.length && options.length > 0 ? 'Semua AR Terpilih' : (selected.length ? selected.length + ' AR Dipilih' : 'Semua AR')"></span>
                            <i class="fa-solid fa-chevron-down text-slate-400 text-[10px]"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-md shadow-xl p-2.5 max-h-52 overflow-y-auto">
                            <label
                                class="flex items-center gap-2 font-bold text-xs pb-1.5 border-b border-slate-100 cursor-pointer text-blue-950">
                                <input type="checkbox" x-model="selectAll"
                                    @change="selected = selectAll ? [...options] : []"> Pilih Semua
                            </label>
                            @foreach ($listAr ?? [] as $ar)
                                <label
                                    class="flex items-center gap-2 text-xs py-1 cursor-pointer hover:bg-slate-50 px-1 rounded">
                                    <input type="checkbox" name="nip_ar[]" value="{{ $ar->nip }}"
                                        x-model="selected">
                                    <span class="truncate">{{ $ar->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Multi-select JS -->
                    @php
                        $listJsArr = collect($listJs ?? [])
                            ->pluck('nip')
                            ->toArray();
                        $currentJs = array_values((array) ($nipJs ?? []));
                        $isAllJs = count($currentJs) === count($listJsArr) && count($listJsArr) > 0;
                    @endphp
                    <div x-data="{ open: false, selectAll: {{ $isAllJs ? 'true' : 'false' }}, selected: {{ json_encode($currentJs) }}, options: {{ json_encode($listJsArr) }} }" class="relative">
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Nama JS</label>
                        <button type="button" @click="open = !open"
                            class="w-full bg-slate-50 border border-slate-300 rounded-md p-1.5 text-left text-xs text-slate-800 flex justify-between items-center cursor-pointer">
                            <span
                                x-text="selected.length === options.length && options.length > 0 ? 'Semua JS Terpilih' : (selected.length ? selected.length + ' JS Dipilih' : 'Semua JS')"></span>
                            <i class="fa-solid fa-chevron-down text-slate-400 text-[10px]"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-md shadow-xl p-2.5 max-h-52 overflow-y-auto">
                            <label
                                class="flex items-center gap-2 font-bold text-xs pb-1.5 border-b border-slate-100 cursor-pointer text-blue-950">
                                <input type="checkbox" x-model="selectAll"
                                    @change="selected = selectAll ? [...options] : []"> Pilih Semua
                            </label>
                            @foreach ($listJs ?? [] as $js)
                                <label
                                    class="flex items-center gap-2 text-xs py-1 cursor-pointer hover:bg-slate-50 px-1 rounded">
                                    <input type="checkbox" name="nip_js[]" value="{{ $js->nip }}"
                                        x-model="selected">
                                    <span class="truncate">{{ $js->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tombol Cari DJP Style -->
                    <div class="pt-1">
                        <button type="submit" :disabled="loading"
                            class="w-full bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-extrabold py-2 rounded-md transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <i x-show="loading" class="fa-solid fa-circle-notch fa-spin text-xs" x-cloak></i>
                            <i x-show="!loading" class="fa-solid fa-magnifying-glass text-xs"></i>
                            <span x-text="loading ? 'Mencari...' : 'CARI DRM'"></span>
                        </button>
                    </div>

                </form>
            </div>

            <!-- TABEL HASIL (SEBELAH KANAN) -->
            <div class="flex-1 w-full min-w-0">
                @if (isset($results) && $results)
                    <div x-show="!loading"
                        class="bg-white rounded-lg shadow-2xs border border-slate-200/90 overflow-hidden">

                        <!-- Header Box -->
                        <div
                            class="p-3 border-b border-slate-200 bg-slate-50/50 flex flex-wrap justify-between items-center gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-950"></span>
                                <span class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">
                                    Hasil Pencarian (Total: <span
                                        class="text-amber-600 font-mono">{{ number_format($results->total(), 0, ',', '.') }}</span>
                                    Data)
                                </span>
                            </div>

                            @if ($results->total() > 0)
                                <a href="{{ route('pencarian.transaksi.export', request()->all()) }}"
                                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1 rounded-md transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-file-excel text-xs text-emerald-600"></i>
                                    <span>Export CSV</span>
                                </a>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead
                                    class="bg-slate-100/80 text-blue-950 font-extrabold border-b border-slate-200 uppercase tracking-tight text-[11px]">
                                    <tr>
                                        <th class="p-2.5 whitespace-nowrap">Tgl Setor</th>
                                        <th class="p-2.5 whitespace-nowrap">NPWP15 / Nama WP</th>
                                        <th class="p-2.5 whitespace-nowrap">Fungsi</th>
                                        <th class="p-2.5 whitespace-nowrap">MAP / Bayar</th>
                                        <th class="p-2.5 whitespace-nowrap">Masa / Thn Pajak</th>
                                        <th class="p-2.5 text-right whitespace-nowrap">Jumlah Setor (Rp)</th>
                                        <th class="p-2.5 whitespace-nowrap">AR / JS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/70 text-slate-700 font-medium">
                                    @forelse($results as $item)
                                        <tr class="hover:bg-amber-50/40 transition-colors">
                                            <td class="p-2.5 whitespace-nowrap font-mono text-[11px] text-slate-600">
                                                {{ $item->tgl_setor ? \Carbon\Carbon::parse($item->tgl_setor)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="p-2.5">
                                                <div class="font-mono font-bold text-blue-950 text-[11px]">
                                                    {{ $item->npwp15 }}</div>
                                                <div class="text-[11px] text-slate-800 font-semibold">
                                                    {{ $item->nama_wp ?? $item->nama_master }}</div>
                                            </td>
                                            <td class="p-2.5 whitespace-nowrap">
                                                <span
                                                    class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-mono text-[10px] font-bold border border-slate-200">
                                                    {{ $item->fungsi ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="p-2.5 whitespace-nowrap">
                                                <div class="font-mono font-extrabold text-blue-950 text-[11px]">
                                                    {{ $item->kd_map }} / {{ $item->kd_bayar }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $item->jenis_pajak ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="p-2.5 whitespace-nowrap font-mono text-[11px] text-slate-600">
                                                {{ str_pad($item->masa1, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($item->masa2, 2, '0', STR_PAD_LEFT) }}
                                                / {{ $item->thn_pajak ?? '-' }}
                                            </td>
                                            <td
                                                class="p-2.5 text-right font-mono font-extrabold text-blue-950 whitespace-nowrap text-[11px]">
                                                Rp {{ number_format($item->jml_setor, 0, ',', '.') }}
                                            </td>
                                            <td class="p-2.5 whitespace-nowrap text-[11px]">
                                                <div class="font-semibold text-slate-800">AR: <span
                                                        class="text-slate-600 font-normal">{{ $item->nama_ar ?? '-' }}</span>
                                                </div>
                                                <div class="font-semibold text-slate-500 text-[10px]">JS: <span
                                                        class="text-slate-500 font-normal">{{ $item->nama_js ?? '-' }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7"
                                                class="py-8 text-center text-slate-400 italic text-xs bg-slate-50/30">
                                                Data Transaksi tidak ditemukan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-2.5 border-t border-slate-200 bg-slate-50">
                            {{ $results->links() }}
                        </div>
                    </div>
                @else
                    <!-- Placeholder / Sebelum Cari -->
                    <div class="bg-white p-10 rounded-lg shadow-2xs border border-slate-200/90 text-center">
                        <div
                            class="w-12 h-12 bg-blue-50 border border-blue-100 text-blue-950 rounded-lg flex items-center justify-center mx-auto mb-3 shadow-2xs">
                            <i class="fa-solid fa-magnifying-glass text-lg text-amber-500"></i>
                        </div>
                        <h3 class="text-sm font-extrabold text-blue-950 uppercase tracking-tight">Gunakan Filter di Sebelah
                            Kiri</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                            Pilih parameter pencarian lalu klik tombol <span class="font-bold text-blue-950">"CARI
                                DRM"</span> untuk menampilkan data DRM.
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
