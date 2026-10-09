@extends('layouts.app')

@section('title', 'Pencarian Masterfile WP - MPNWEB')

@section('content')
    <div class="space-y-4" x-data="{ loading: false }">

        <!-- HEADER TITLE (LOOKER STUDIO STYLE) -->
        <div
            class="bg-white border border-slate-200/90 rounded-lg p-3 flex items-center gap-3 shadow-2xs border-t-4 border-t-amber-500">
            <div
                class="w-9 h-9 rounded-md bg-blue-950 text-amber-400 flex items-center justify-center font-bold text-base shrink-0 shadow-xs border border-blue-900">
                <i class="fa-solid fa-users-gear text-xs"></i>
            </div>
            <div>
                <h1 class="text-base font-extrabold text-blue-950 leading-tight tracking-tight uppercase">Pencarian
                    Masterfile Wajib Pajak</h1>
                <p class="text-[11px] text-slate-500 font-medium">Filter profil, Wilayah, KLU, dan pengawasan AR/JS Wajib
                    Pajak</p>
            </div>
        </div>

        <!-- ==================== MAIN LAYOUT (GRID 2 KOLOM) ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

            <!-- SIDEBAR FILTER (KOLOM KIRI) -->
            <div
                class="lg:col-span-3 bg-white p-3.5 rounded-lg shadow-2xs border border-slate-200/90 space-y-3 sticky top-20">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <div class="flex items-center gap-2 text-blue-950 font-extrabold text-xs uppercase tracking-wider">
                        <i class="fa-solid fa-filter text-amber-500"></i>
                        <span>Filter Masterfile</span>
                    </div>
                    <a href="{{ route('pencarian.masterfile') }}"
                        class="text-[11px] text-slate-400 hover:text-blue-950 font-medium transition flex items-center gap-1"
                        title="Reset Filter">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i> Reset
                    </a>
                </div>

                <form id="searchMasterfileForm" action="{{ route('pencarian.masterfile') }}" method="GET"
                    @submit="loading = true" class="space-y-2.5">
                    <input type="hidden" name="has_search" value="1">
                    <input type="hidden" name="sort_by" value="{{ $sortBy ?? '' }}">
                    <input type="hidden" name="sort_order" value="{{ $sortOrder ?? 'asc' }}">

                    <!-- NPWP -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">NPWP (9/15/16 Digit)</label>
                        <input type="text" name="npwp" value="{{ $npwpInput ?? '' }}" placeholder="Masukkan NPWP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1 focus:outline-none focus:border-blue-950 font-medium">
                    </div>

                    <!-- Nama WP -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Nama Wajib Pajak</label>
                        <input type="text" name="nama" value="{{ $namaWp ?? '' }}" placeholder="Masukkan Nama WP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1 focus:outline-none focus:border-blue-950 font-medium">
                    </div>

                    <hr class="border-slate-100 my-1">

                    <!-- KLU -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">KLU</label>
                        <select name="klu"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua KLU --</option>
                            @foreach ($listKlu as $itemKlu)
                                <option value="{{ $itemKlu }}" {{ ($klu ?? '') == $itemKlu ? 'selected' : '' }}>
                                    {{ $itemKlu }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kelurahan -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Kelurahan</label>
                        <select name="kelurahan"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua Kelurahan --</option>
                            @foreach ($listKelurahan as $itemKel)
                                <option value="{{ $itemKel }}" {{ ($kelurahan ?? '') == $itemKel ? 'selected' : '' }}>
                                    {{ $itemKel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kecamatan -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Kecamatan</label>
                        <select name="kecamatan"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua Kecamatan --</option>
                            @foreach ($listKecamatan as $itemKec)
                                <option value="{{ $itemKec }}"
                                    {{ ($kecamatan ?? '') == $itemKec ? 'selected' : '' }}>
                                    {{ $itemKec }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jenis WP -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Jenis WP</label>
                        <select name="jenis"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua Jenis --</option>
                            @foreach ($listJenis as $itemJenis)
                                <option value="{{ $itemJenis }}" {{ ($jenis ?? '') == $itemJenis ? 'selected' : '' }}>
                                    {{ $itemJenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status WP -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Status WP</label>
                        <select name="status"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua Status --</option>
                            @foreach ($listStatus as $itemStatus)
                                <option value="{{ $itemStatus }}"
                                    {{ ($status ?? '') == $itemStatus ? 'selected' : '' }}>
                                    {{ $itemStatus }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="border-slate-100 my-1">

                    <!-- Tgl Daftar (Awal & Akhir) -->
                    <div class="grid grid-cols-2 gap-1.5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Tgl Daftar (Awal)</label>
                            <input type="date" name="tgl_daftar_awal" value="{{ $tglDaftarAwal ?? '' }}"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-1.5 py-1 focus:outline-none focus:border-blue-950 font-medium">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Tgl Daftar (Akhir)</label>
                            <input type="date" name="tgl_daftar_akhir" value="{{ $tglDaftarAkhir ?? '' }}"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-1.5 py-1 focus:outline-none focus:border-blue-950 font-medium">
                        </div>
                    </div>

                    <!-- Account Officer (AR) -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Account Officer (AR)</label>
                        <select name="nip_ar"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua AR --</option>
                            @foreach ($listAr as $ar)
                                <option value="{{ $ar->nip }}" {{ ($nipAr ?? '') == $ar->nip ? 'selected' : '' }}>
                                    {{ $ar->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Juru Sita (JS) -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Juru Sita (JS)</label>
                        <select name="nip_js"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua JS --</option>
                            @foreach ($listJs as $js)
                                <option value="{{ $js->nip }}" {{ ($nipJs ?? '') == $js->nip ? 'selected' : '' }}>
                                    {{ $js->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tombol Cari DJP Style -->
                    <div class="pt-1">
                        <button type="submit" :disabled="loading"
                            :class="loading ? 'opacity-75 cursor-not-allowed' : ''"
                            class="w-full bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-extrabold py-2 rounded-md transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <i x-show="loading" class="fa-solid fa-circle-notch fa-spin text-xs" x-cloak></i>
                            <i x-show="!loading" class="fa-solid fa-magnifying-glass text-xs"></i>
                            <span x-text="loading ? 'Mencari...' : 'CARI WP'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABEL HASIL (KOLOM KANAN) -->
            <div class="lg:col-span-9">
                @if ($hasSearch && isset($results))
                    <div x-show="!loading"
                        class="bg-white rounded-lg shadow-2xs border border-slate-200/90 overflow-hidden">

                        <!-- HEADER TABEL & EXPORT -->
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
                                <a href="{{ route('pencarian.masterfile.export', request()->all()) }}"
                                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1 rounded-md transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-file-excel text-xs text-emerald-600"></i>
                                    <span>Export CSV</span>
                                </a>
                            @endif
                        </div>

                        @php
                            function sortUrlMf($field, $currentSortBy, $currentSortOrder)
                            {
                                $nextOrder = $currentSortBy === $field && $currentSortOrder === 'asc' ? 'desc' : 'asc';
                                return request()->fullUrlWithQuery(['sort_by' => $field, 'sort_order' => $nextOrder]);
                            }
                        @endphp

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead
                                    class="bg-slate-100/80 text-blue-950 font-extrabold border-b border-slate-200 uppercase tracking-tight text-[11px]">
                                    <tr>
                                        <th class="p-2.5 whitespace-nowrap">
                                            <a href="{{ sortUrlMf('npwp15', $sortBy ?? '', $sortOrder ?? 'asc') }}"
                                                class="flex items-center gap-1 hover:text-amber-600 transition select-none">
                                                NPWP / NPWP16
                                                @if (($sortBy ?? '') === 'npwp15')
                                                    <i
                                                        class="fa-solid fa-sort-{{ ($sortOrder ?? 'asc') === 'asc' ? 'up' : 'down' }} text-amber-500 text-[10px]"></i>
                                                @else
                                                    <i class="fa-solid fa-sort text-slate-300 text-[10px]"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th class="p-2.5 whitespace-nowrap">
                                            <a href="{{ sortUrlMf('nama', $sortBy ?? '', $sortOrder ?? 'asc') }}"
                                                class="flex items-center gap-1 hover:text-amber-600 transition select-none">
                                                Nama WP / KLU
                                                @if (($sortBy ?? '') === 'nama')
                                                    <i
                                                        class="fa-solid fa-sort-{{ ($sortOrder ?? 'asc') === 'asc' ? 'up' : 'down' }} text-amber-500 text-[10px]"></i>
                                                @else
                                                    <i class="fa-solid fa-sort text-slate-300 text-[10px]"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th class="p-2.5 whitespace-nowrap">Alamat / Wilayah</th>
                                        <th class="p-2.5 whitespace-nowrap">Jenis / Status</th>
                                        <th class="p-2.5 whitespace-nowrap">
                                            <a href="{{ sortUrlMf('tanggal_daftar', $sortBy ?? '', $sortOrder ?? 'asc') }}"
                                                class="flex items-center gap-1 hover:text-amber-600 transition select-none">
                                                Tgl Daftar
                                                @if (($sortBy ?? '') === 'tanggal_daftar')
                                                    <i
                                                        class="fa-solid fa-sort-{{ ($sortOrder ?? 'asc') === 'asc' ? 'up' : 'down' }} text-amber-500 text-[10px]"></i>
                                                @else
                                                    <i class="fa-solid fa-sort text-slate-300 text-[10px]"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th class="p-2.5 whitespace-nowrap">AR / JS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/70 text-slate-700 font-medium">
                                    @forelse($results as $item)
                                        <tr class="hover:bg-amber-50/40 transition-colors">
                                            <td
                                                class="p-2.5 font-mono font-semibold text-blue-950 whitespace-nowrap text-[11px]">
                                                <div>{{ $item->npwp15 ?? $item->npwp }}</div>
                                                @if (!empty($item->npwp16))
                                                    <div class="text-[10px] text-slate-400 font-normal">NIK/16:
                                                        {{ $item->npwp16 }}</div>
                                                @endif
                                            </td>
                                            <td class="p-2.5">
                                                <div class="font-bold text-slate-900">{{ $item->nama }}</div>
                                                <div class="text-[10px] text-slate-500">KLU: {{ $item->klu ?? '-' }}</div>
                                            </td>
                                            <td class="p-2.5 text-[11px] leading-tight max-w-xs">
                                                {{ $item->alamat }}
                                                @if ($item->kelurahan || $item->kecamatan)
                                                    <div class="text-slate-400 text-[10px]">Kel.
                                                        {{ $item->kelurahan ?? '-' }}, Kec. {{ $item->kecamatan ?? '-' }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="p-2.5 whitespace-nowrap">
                                                <div class="font-semibold text-slate-800 text-[11px] mb-0.5">
                                                    {{ $item->jenis ?? '-' }}</div>
                                                <span
                                                    class="bg-slate-100 text-slate-700 border border-slate-200 px-1.5 py-0.5 rounded text-[10px] font-bold">{{ $item->status ?? '-' }}</span>
                                            </td>
                                            <td class="p-2.5 whitespace-nowrap text-[11px] font-mono text-slate-600">
                                                {{ $item->tanggal_daftar ? date('d-m-Y', strtotime($item->tanggal_daftar)) : '-' }}
                                            </td>
                                            <td class="p-2.5 whitespace-nowrap text-[11px]">
                                                <div class="font-semibold text-slate-800">AR: <span
                                                        class="text-slate-600 font-normal">{{ $item->ar?->nama ?? '-' }}</span>
                                                </div>
                                                <div class="font-semibold text-slate-500 text-[10px]">JS: <span
                                                        class="text-slate-500 font-normal">{{ $item->js?->nama ?? '-' }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6"
                                                class="py-8 text-center text-slate-400 italic text-xs bg-slate-50/30">
                                                Data Masterfile tidak ditemukan.
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
                    <!-- PLACEHOLDER SEBELUM FILTER DITERAPKAN -->
                    <div class="bg-white p-10 rounded-lg shadow-2xs border border-slate-200/90 text-center">
                        <div
                            class="w-12 h-12 bg-blue-50 border border-blue-100 text-blue-950 rounded-lg flex items-center justify-center mx-auto mb-3 shadow-2xs">
                            <i class="fa-solid fa-magnifying-glass text-lg text-amber-500"></i>
                        </div>
                        <h3 class="text-sm font-extrabold text-blue-950 uppercase tracking-tight">Gunakan Filter di Sebelah
                            Kiri</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                            Pilih parameter pencarian lalu klik tombol <span class="font-bold text-blue-950">"CARI
                                WP"</span> untuk menampilkan data Masterfile.
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
