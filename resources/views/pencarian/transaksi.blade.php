{{-- FINAL: totalJumlah SUM, tanpa Menampilkan xx Data & tanpa footer Total Jumlah Setor Hasil Pencarian --}}
@extends('layouts.app')
@section('title', 'Pencarian Detil Transaksi / DRM - MPNWEB')

@section('content')
    @php
        $subtitleClean = 'Transaksi Penerimaan Pajak • Tahun ' . date('Y');
        if (isset($totalJumlah) && $totalJumlah > 0) {
            $subtitleClean .= ' • Total Setoran Rp ' . number_format($totalJumlah, 0, ',', '.');
        }
    @endphp

    <div x-data="{ loading: false }" class="space-y-4">
        <x-layout.page-header :title="__('Pencarian Detil Transaksi / DRM')" :subtitle="$subtitleClean" icon="fa-receipt">
            @if (isset($totalJumlah) && ($hasSearch ?? false))
                <span
                    class="hidden md:inline-flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-bold px-2.5 py-1 rounded-md">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Total: Rp {{ number_format($totalJumlah, 0, ',', '.') }}
                </span>
            @endif
        </x-layout.page-header>

        <div class="flex flex-col lg:flex-row gap-4 items-start">
            <div
                class="w-full lg:w-80 flex-shrink-0 bg-white p-3.5 rounded-lg shadow-2xs border border-slate-200/90 space-y-3 sticky top-20">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <div class="flex items-center gap-2 text-blue-950 font-extrabold text-[11px] uppercase tracking-wider">
                        <i class="fa-solid fa-filter text-amber-500"></i><span>Filter Pencarian</span>
                    </div>
                    <a href="{{ route('pencarian.transaksi') }}"
                        class="text-[11px] text-slate-400 hover:text-blue-950 flex items-center gap-1"><i
                            class="fa-solid fa-rotate-left text-[10px]"></i> Reset</a>
                </div>
                <form action="{{ route('pencarian.transaksi') }}" method="GET" @submit="loading = true" class="space-y-3">
                    <input type="hidden" name="has_search" value="1">
                    <input type="hidden" name="sort_by" value="{{ $sortBy ?? 'tgl_setor' }}">
                    <input type="hidden" name="sort_order" value="{{ $sortOrder ?? 'desc' }}">
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">NPWP</label><input type="text"
                            name="npwp" value="{{ $npwpInput ?? '' }}" placeholder="9 atau 15 digit..."
                            class="w-full bg-white border border-slate-300 text-xs rounded-md px-2.5 py-1.5 font-mono focus:border-blue-950">
                    </div>
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Wajib Pajak</label><input
                            type="text" name="nama" value="{{ $namaWp ?? '' }}" placeholder="Nama wajib pajak..."
                            class="w-full bg-white border border-slate-300 text-xs rounded-md px-2.5 py-1.5 focus:border-blue-950">
                    </div>
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Fungsi</label><select name="fungsi"
                            class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5">
                            <option value="">Semua Fungsi</option>
                            @foreach ($listFungsi ?? [] as $f)
                                <option value="{{ $f }}" @selected(($fungsiSelected ?? ($fungsi ?? '')) == $f)>{{ $f }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Kode MAP</label><select
                                name="kd_map"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5 font-mono">
                                <option value="">Semua</option>
                                @foreach ($listMap ?? [] as $map)
                                    <option value="{{ $map }}" @selected(($kdMap ?? '') == $map)>{{ $map }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Kode Setor</label><select
                                name="kd_bayar"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5 font-mono">
                                <option value="">Semua</option>
                                @foreach ($listBayar ?? [] as $bayar)
                                    <option value="{{ $bayar }}" @selected(($kdBayar ?? '') == $bayar)>{{ $bayar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div><label class="block text-[10px] font-bold text-slate-700 mb-1">Masa Awal</label><select
                                name="masa1"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5">
                                <option value="">-</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected((int) ($masa1 ?? 0) === $m)>
                                        {{ sprintf('%02d', $m) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div><label class="block text-[10px] font-bold text-slate-700 mb-1">Masa Akhir</label><select
                                name="masa2"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5">
                                <option value="">-</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected((int) ($masa2 ?? 0) === $m)>
                                        {{ sprintf('%02d', $m) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div><label class="block text-[10px] font-bold text-slate-700 mb-1">Tahun Pajak</label><input
                                type="number" name="thn_pajak" value="{{ $thnPajak ?? '' }}" placeholder="2025"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5"></div>
                    </div>
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Periode Setor</label>
                        <div class="grid grid-cols-2 gap-2"><input type="date" name="tgl_setor_awal"
                                value="{{ $tglSetorAwal ?? '' }}"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5"><input
                                type="date" name="tgl_setor_akhir" value="{{ $tglSetorAkhir ?? '' }}"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5"></div>
                    </div>
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">NTPN</label><input type="text"
                            name="ntpn" value="{{ $ntpn ?? '' }}" placeholder="Nomor NTPN..."
                            class="w-full bg-white border border-slate-300 text-xs rounded-md px-2.5 py-1.5 font-mono text-[11px]">
                    </div>
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">No. Produk Hukum</label><input
                            type="text" name="no_produk_hukum" value="{{ $noProdukHukum ?? '' }}"
                            placeholder="No. SKP / STP..."
                            class="w-full bg-white border border-slate-300 text-xs rounded-md px-2.5 py-1.5 text-[11px]">
                    </div>
                    <div class="border-t border-slate-100 pt-3 space-y-2.5">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Data Masterfile</p>
                        <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Kecamatan</label><select
                                name="kecamatan"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5">
                                <option value="">Semua Kecamatan</option>
                                @foreach ($listKecamatan ?? [] as $kec)
                                    <option value="{{ $kec }}" @selected(($kecamatanSelected ?? '') == $kec)>{{ $kec }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis Wajib
                                Pajak</label><select name="jenis_wp"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5">
                                <option value="">Semua Jenis</option>
                                @foreach ($listJenisWp ?? [] as $j)
                                    <option value="{{ $j }}" @selected(($jenisWpSelected ?? '') == $j)>{{ $j }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Sektor Usaha</label><select
                                name="sektor"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5">
                                <option value="">Semua Sektor</option>
                                @foreach ($listSektor ?? [] as $sek)
                                    <option value="{{ $sek }}" @selected(($sektorSelected ?? '') == $sek)>
                                        {{ \Illuminate\Support\Str::limit($sek, 35) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 pt-3 space-y-2.5">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Petugas</p>
                        <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Account
                                Representative</label><select name="nip_ar"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5">
                                <option value="">Semua AR</option>
                                @foreach ($listAr ?? [] as $ar)
                                    <option value="{{ $ar->nip }}" @selected(($nipAr ?? '') == $ar->nip)>{{ $ar->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Seksi Pengawasan</label><select
                                name="seksi_id"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5">
                                <option value="">Semua Seksi</option>
                                @foreach ($listSeksi ?? [] as $seksi)
                                    <option value="{{ $seksi->id }}" @selected(($seksiSelected ?? '') == $seksi->id)>{{ $seksi->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="pt-2"><button type="submit" :disabled="loading"
                            class="w-full bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/20 text-xs font-extrabold py-2.5 rounded-md flex items-center justify-center gap-2"><i
                                x-show="loading" class="fa-solid fa-circle-notch fa-spin" x-cloak></i><i
                                x-show="!loading" class="fa-solid fa-magnifying-glass"></i><span
                                x-text="loading ? 'Mencari...' : 'Cari Transaksi'"></span></button></div>
                </form>
            </div>

            <div class="flex-1 w-full min-w-0">
                @if (isset($results) && $results)
                    <div class="bg-white rounded-lg shadow-2xs border border-slate-200/90 overflow-hidden">
                        <div
                            class="p-3 border-b border-slate-200 bg-slate-50/50 flex flex-wrap justify-between items-center gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-[11px] font-extrabold text-blue-950 uppercase tracking-wider">
                                    Hasil Pencarian
                                </span>
                            </div>
                            @if ($results->count() > 0)
                                <a href="{{ route('pencarian.transaksi.export', request()->all()) }}"
                                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-[11px] font-bold px-3 py-1 rounded-md flex items-center gap-1.5"><i
                                        class="fa-solid fa-file-excel text-emerald-600"></i>Export CSV</a>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead
                                    class="bg-slate-100/80 text-blue-950 font-extrabold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                                    <tr>
                                        <th class="p-2.5 whitespace-nowrap">Tgl Setor</th>
                                        <th class="p-2.5">Wajib Pajak</th>
                                        <th class="p-2.5">Fungsi</th>
                                        <th class="p-2.5 whitespace-nowrap">Kode</th>
                                        <th class="p-2.5 whitespace-nowrap">Masa Pajak</th>
                                        <th class="p-2.5 text-right whitespace-nowrap">Jumlah</th>
                                        <th class="p-2.5">Wilayah & Petugas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($results as $row)
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="p-2.5 font-mono text-[11px] whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($row->tgl_setor)->format('d/m/Y') }}</td>
                                            <td class="p-2.5 leading-tight">
                                                <div class="font-bold text-blue-950 text-[11px] max-w-[180px] truncate">
                                                    {{ $row->nama_wp ?? ($row->nama_master ?? '-') }}</div>
                                                <div class="font-mono text-[10px] text-slate-500 mt-0.5">
                                                    {{ $row->npwp15 }}</div>
                                            </td>
                                            <td class="p-2.5"><span
                                                    class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border {{ str_contains(strtolower($row->fungsi ?? ''), 'pph') ? 'bg-blue-50 text-blue-700 border-blue-200' : (str_contains(strtolower($row->fungsi ?? ''), 'ppn') ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-50 text-slate-700 border-slate-200') }}">{{ $row->fungsi ?? '-' }}</span>
                                            </td>
                                            <td class="p-2.5 font-mono text-[11px] whitespace-nowrap">
                                                {{ $row->kd_map }}-{{ $row->kd_bayar }}</td>
                                            <td class="p-2.5 text-[11px] whitespace-nowrap">
                                                {{ $row->masa1 }}/{{ $row->masa2 }}/{{ $row->thn_pajak }}</td>
                                            <td
                                                class="p-2.5 text-right font-mono font-bold text-blue-950 whitespace-nowrap">
                                                Rp {{ number_format($row->jml_setor, 0, ',', '.') }}</td>
                                            <td class="p-2.5 text-[10px] leading-tight">
                                                <div class="text-slate-700">{{ $row->kecamatan ?? '-' }} • <span
                                                        class="uppercase">{{ $row->jenis_wp ?? '-' }}</span></div>
                                                <div class="text-slate-400 text-[9px] mt-0.5 truncate max-w-[200px]">
                                                    {{ $row->sektor ?? '-' }}</div>
                                                <div class="font-semibold text-slate-800 text-[11px] mt-1">
                                                    {{ $row->nama_ar ?? '-' }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="p-6 text-center text-slate-400">Tidak ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{-- FOOTER TOTAL DIHILANGKAN SESUAI REQUEST --}}
                        <div class="px-3.5 py-2.5 flex justify-end bg-white border-t border-slate-100">
                            <div class="flex gap-2">
                                @if ($results->onFirstPage())
                                    <span class="px-3 py-1.5 text-[11px] bg-slate-100 text-slate-400 rounded-md border">←
                                    Prev</span>@else<a href="{{ $results->previousPageUrl() }}"
                                        class="px-3 py-1.5 text-[11px] bg-white border border-slate-300 text-blue-950 font-bold rounded-md hover:bg-slate-50">←
                                        Prev</a>
                                    @endif @if ($results->hasMorePages())
                                        <a href="{{ $results->nextPageUrl() }}"
                                            class="px-3 py-1.5 text-[11px] bg-blue-950 text-amber-400 font-bold rounded-md border border-amber-500/20">Next
                                        →</a>@else<span
                                            class="px-3 py-1.5 text-[11px] bg-slate-100 text-slate-400 rounded-md border">Next
                                            →</span>
                                    @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white border border-dashed border-slate-300 rounded-lg p-12 text-center">
                        <div
                            class="w-12 h-12 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-receipt text-lg text-amber-500"></i></div>
                        <h3 class="text-sm font-extrabold text-blue-950 uppercase">Pencarian Transaksi</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Gunakan filter untuk mencari data transaksi
                            penerimaan pajak.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
