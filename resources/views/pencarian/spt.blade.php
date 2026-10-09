@extends('layouts.app')

@section('title', 'Pencarian Tanda Terima SPT - MPNWEB')

@section('content')
    <div x-data="{ loading: false }" class="space-y-4">

        <!-- HEADER PAGE (LOOKER STUDIO STYLE) -->
        <div
            class="bg-white border border-slate-200/90 rounded-lg p-3 flex items-center gap-3 shadow-2xs border-t-4 border-t-amber-500">
            <div
                class="w-9 h-9 rounded-md bg-blue-950 text-amber-400 flex items-center justify-center font-bold text-base shrink-0 shadow-xs border border-blue-900">
                <i class="fa-solid fa-file-invoice text-xs"></i>
            </div>
            <div>
                <h1 class="text-base font-extrabold text-blue-950 leading-tight tracking-tight uppercase">Pencarian Tanda
                    Terima SPT</h1>
                <p class="text-[11px] text-slate-500 font-medium">Cari dan filter data Tanda Terima SPT Coretax beserta
                    profil AR Wajib Pajak</p>
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
                        <span>Filter SPT</span>
                    </div>
                    <a href="{{ route('pencarian.spt') }}"
                        class="text-[11px] text-slate-400 hover:text-blue-950 font-medium transition flex items-center gap-1">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i> Reset
                    </a>
                </div>

                <form id="searchSptForm" action="{{ route('pencarian.spt') }}" method="GET" @submit="loading = true"
                    class="space-y-2.5">
                    <input type="hidden" name="has_search" value="1">

                    <!-- NPWP -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">NPWP (16 / 15 / 9 Digit)</label>
                        <input type="text" name="npwp" value="{{ request('npwp') }}" placeholder="Masukkan NPWP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1 focus:outline-none focus:border-blue-950 font-medium">
                    </div>

                    <!-- Nama WP -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Nama Wajib Pajak</label>
                        <input type="text" name="nama" value="{{ request('nama') }}" placeholder="Masukkan Nama WP..."
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1 focus:outline-none focus:border-blue-950 font-medium">
                    </div>

                    <!-- Range Masa Pajak -->
                    <div class="grid grid-cols-2 gap-1.5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Masa Awal</label>
                            <input type="number" min="1" max="12" name="masa1"
                                value="{{ request('masa1') }}" placeholder="1"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Masa Akhir</label>
                            <input type="number" min="1" max="12" name="masa2"
                                value="{{ request('masa2') }}" placeholder="12"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium">
                        </div>
                    </div>

                    <!-- Tahun Pajak & Pembetulan -->
                    <div class="grid grid-cols-2 gap-1.5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Thn Pajak</label>
                            <input type="text" name="thn_pajak" value="{{ request('thn_pajak') }}" placeholder="2026"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Pembetulan</label>
                            <select name="pembetulan"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                                <option value="">-- Semua --</option>
                                @foreach ($pembetulanList as $pem)
                                    <option value="{{ $pem }}"
                                        {{ request('pembetulan') == $pem ? 'selected' : '' }}>
                                        {{ $pem }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Jenis SPT -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Jenis SPT</label>
                        <select name="jenis_spt"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua Jenis SPT --</option>
                            @foreach ($jenisSptList as $jenis)
                                <option value="{{ $jenis }}"
                                    {{ request('jenis_spt') == $jenis ? 'selected' : '' }}>
                                    {{ $jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status SPT -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Status SPT</label>
                        <select name="status_spt"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua Status --</option>
                            @foreach ($statusSptList as $status)
                                <option value="{{ $status }}"
                                    {{ request('status_spt') == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Range Tanggal Terima -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Tanggal Terima (Mulai)</label>
                        <input type="date" name="tgl_terima_mulai" value="{{ request('tgl_terima_mulai') }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium mb-1.5">

                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Tanggal Terima (Sampai)</label>
                        <input type="date" name="tgl_terima_selesai" value="{{ request('tgl_terima_selesai') }}"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium">
                    </div>

                    <!-- Account Representative -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-0.5">Account Representative (AR)</label>
                        <select name="nip_ar"
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                            <option value="">-- Semua AR --</option>
                            @foreach ($arList as $ar)
                                <option value="{{ $ar->nip }}" {{ request('nip_ar') == $ar->nip ? 'selected' : '' }}>
                                    {{ $ar->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tombol Cari DJP Style -->
                    <div class="pt-1">
                        <button type="submit" :disabled="loading"
                            class="w-full bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-extrabold py-2 rounded-md transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <i x-show="loading" class="fa-solid fa-circle-notch fa-spin text-xs" x-cloak></i>
                            <i x-show="!loading" class="fa-solid fa-magnifying-glass text-xs"></i>
                            <span x-text="loading ? 'Mencari...' : 'CARI SPT'"></span>
                        </button>
                    </div>

                </form>
            </div>

            <!-- TABEL HASIL (SEBELAH KANAN) -->
            <div class="flex-1 w-full min-w-0">
                @if (isset($sptList) && $hasSearch)
                    <div x-show="!loading"
                        class="bg-white rounded-lg shadow-2xs border border-slate-200/90 overflow-hidden">

                        <!-- Header Box -->
                        <div
                            class="p-3 border-b border-slate-200 bg-slate-50/50 flex flex-wrap justify-between items-center gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-950"></span>
                                <span class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">
                                    Hasil Pencarian (Total: <span
                                        class="text-amber-600 font-mono">{{ number_format($sptList->total(), 0, ',', '.') }}</span>
                                    BPE/SPT)
                                </span>
                            </div>

                            @if ($sptList->total() > 0)
                                <a href="{{ route('pencarian.spt.export', request()->query()) }}"
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
                                        <th class="p-2.5 whitespace-nowrap">No. BPE / Tanda Terima</th>
                                        <th class="p-2.5 whitespace-nowrap">NPWP & Wajib Pajak</th>
                                        <th class="p-2.5 whitespace-nowrap">Jenis & Status SPT</th>
                                        <th class="p-2.5 text-center whitespace-nowrap">Masa / Thn Pajak</th>
                                        <th class="p-2.5 text-center whitespace-nowrap">Pembetulan</th>
                                        <th class="p-2.5 whitespace-nowrap">Tgl Terima</th>
                                        <th class="p-2.5 whitespace-nowrap">Account Representative</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/70 text-slate-700 font-medium">
                                    @forelse($sptList as $item)
                                        <tr class="hover:bg-amber-50/40 transition-colors">
                                            <td
                                                class="p-2.5 font-mono text-[11px] font-bold text-blue-950 whitespace-nowrap">
                                                {{ $item->nomor_tanda_terima ?? '-' }}
                                                <div class="text-[10px] text-slate-400 font-normal">Kanal:
                                                    {{ $item->kanal_pelaporan ?? '-' }}</div>
                                            </td>
                                            <td class="p-2.5">
                                                <div class="font-bold text-slate-900">{{ $item->nama }}</div>
                                                <div class="font-mono text-[11px] text-slate-500">{{ $item->npwp }}
                                                </div>
                                            </td>
                                            <td class="p-2.5 whitespace-nowrap">
                                                <div class="font-semibold text-slate-800 text-[11px]">
                                                    {{ $item->jenis_spt }}</div>
                                                <span
                                                    class="inline-block mt-0.5 px-1.5 py-0.5 text-[10px] rounded font-bold uppercase {{ strtolower($item->status_spt) == 'nihil' ? 'bg-emerald-50 text-emerald-800 border border-emerald-300' : 'bg-amber-50 text-amber-800 border border-amber-300' }}">
                                                    {{ $item->status_spt ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td
                                                class="p-2.5 text-center font-mono text-[11px] text-slate-700 whitespace-nowrap">
                                                {{ $item->masa1 }}-{{ $item->masa2 }} / {{ $item->thn_pajak }}
                                            </td>
                                            <td class="p-2.5 text-center whitespace-nowrap">
                                                <span
                                                    class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 font-mono">
                                                    {{ $item->pembetulan }}
                                                </span>
                                            </td>
                                            <td class="p-2.5 text-[11px] font-mono text-slate-600 whitespace-nowrap">
                                                {{ $item->tgl_terima ? \Carbon\Carbon::parse($item->tgl_terima)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="p-2.5 whitespace-nowrap text-[11px]">
                                                <div class="font-semibold text-slate-800">
                                                    {{ !empty($item->nama_ar) ? $item->nama_ar : 'Unassigned' }}
                                                </div>
                                                @if (!empty($item->nip_ar))
                                                    <div class="font-mono text-[10px] text-slate-400">{{ $item->nip_ar }}
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7"
                                                class="py-8 text-center text-slate-400 italic text-xs bg-slate-50/30">
                                                Data Tanda Terima SPT tidak ditemukan berdasarkan kriteria pencarian ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-2.5 border-t border-slate-200 bg-slate-50">
                            {{ $sptList->links() }}
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
                                SPT"</span> untuk menampilkan data Tanda Terima SPT Coretax.
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
