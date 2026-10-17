{{-- resources/views/pencarian/spt.blade.php - VERSI REFACTOR PIMPINAN CLEAN (adopsi masterfile) --}}
@extends('layouts.app')
@section('title', 'Pencarian Tanda Terima SPT - MPNWEB')

@section('content')
    @php
        $subtitleClean = 'Tanda Terima SPT Coretax • Tahun ' . date('Y');
        if (isset($totalExact) && $totalExact > 0) {
            $subtitleClean .= ' • Ditemukan ' . number_format($totalExact, 0, ',', '.') . ' BPE/SPT';
        }
    @endphp

    <div class="space-y-4" x-data="{ loading: false }">
        {{-- HEADER PAKAI COMPONENT - sama persis dengan masterfile --}}
        <x-layout.page-header :title="__('Pencarian Tanda Terima SPT')" :subtitle="$subtitleClean" icon="fa-file-invoice">
            @if (isset($totalExact) && $hasSearch && $totalExact > 0)
                <span
                    class="hidden md:inline-flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-bold px-2.5 py-1 rounded-md">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Total: {{ number_format($totalExact, 0, ',', '.') }}
                </span>
            @endif
        </x-layout.page-header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
            {{-- SIDEBAR FILTER --}}
            <div
                class="lg:col-span-3 bg-white p-3.5 rounded-lg shadow-2xs border border-slate-200/90 space-y-3 sticky top-20">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                    <div class="flex items-center gap-2 text-blue-950 font-extrabold text-[11px] uppercase tracking-wider">
                        <i class="fa-solid fa-filter text-amber-500"></i><span>Filter SPT</span>
                    </div>
                    <a href="{{ route('pencarian.spt') }}"
                        class="text-[11px] text-slate-400 hover:text-blue-950 transition flex items-center gap-1">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i> Reset
                    </a>
                </div>

                <form action="{{ route('pencarian.spt') }}" method="GET" @submit="loading=true" class="space-y-2.5">
                    <input type="hidden" name="has_search" value="1">
                    <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                    <input type="hidden" name="sort_order" value="{{ $sortOrder }}">

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">NPWP (16 / 15 / 9 Digit)</label>
                        <input type="text" name="npwp" value="{{ $filters['npwp'] ?? '' }}"
                            placeholder="Masukkan NPWP..."
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1.5 focus:border-blue-950 font-mono">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Wajib Pajak</label>
                        <input type="text" name="nama" value="{{ $filters['nama'] ?? '' }}"
                            placeholder="Masukkan Nama WP..."
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1.5 focus:border-blue-950 font-medium">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1">Masa Awal</label>
                            <select name="masa1"
                                class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1.5">
                                <option value="">-</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected(($filters['masa1'] ?? null) == $m)>
                                        {{ sprintf('%02d', $m) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1">Masa Akhir</label>
                            <select name="masa2"
                                class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1.5">
                                <option value="">-</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected(($filters['masa2'] ?? null) == $m)>
                                        {{ sprintf('%02d', $m) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1">Thn Pajak</label>
                            <input type="number" min="2000" max="2030" name="thn_pajak"
                                value="{{ $filters['thn_pajak'] ?? '' }}" placeholder="2025"
                                class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1.5 font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1">Pembetulan</label>
                            <select name="pembetulan"
                                class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1.5">
                                <option value="">-- Semua --</option>
                                @foreach ($listPembetulan as $pem)
                                    <option value="{{ $pem }}" @selected(($filters['pembetulan'] ?? '') == $pem)>{{ $pem }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis SPT</label>
                        <select name="jenis_spt"
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1.5">
                            <option value="">-- Semua Jenis SPT --</option>
                            @foreach ($listJenis as $jenis)
                                <option value="{{ $jenis }}" @selected(($filters['jenis_spt'] ?? '') == $jenis)>{{ $jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Status SPT</label>
                        <select name="status_spt"
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1.5">
                            <option value="">-- Semua Status --</option>
                            @foreach ($listStatus as $status)
                                <option value="{{ $status }}" @selected(($filters['status_spt'] ?? '') == $status)>{{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Terima</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" name="tgl_terima_mulai" value="{{ $filters['tgl_terima_mulai'] ?? '' }}"
                                class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1.5">
                            <input type="date" name="tgl_terima_selesai"
                                value="{{ $filters['tgl_terima_selesai'] ?? '' }}"
                                class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1.5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Account Representative</label>
                        <select name="nip_ar"
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1.5">
                            <option value="">-- Semua AR --</option>
                            @foreach ($listAr as $ar)
                                <option value="{{ $ar->nip }}" @selected(($filters['nip_ar'] ?? '') == $ar->nip)>{{ $ar->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="loading"
                            class="w-full bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/20 text-xs font-extrabold py-2.5 rounded-md transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                            <i x-show="loading" class="fa-solid fa-circle-notch fa-spin" x-cloak></i>
                            <i x-show="!loading" class="fa-solid fa-magnifying-glass"></i>
                            <span x-text="loading ? 'Mencari...' : 'CARI SPT'"></span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- TABEL HASIL --}}
            <div class="lg:col-span-9">
                @if ($hasSearch && isset($results))
                    <div class="bg-white rounded-lg shadow-2xs border border-slate-200/90 overflow-hidden">
                        <div
                            class="px-3.5 py-2.5 border-b border-slate-200 bg-slate-50/50 flex flex-wrap justify-between items-center gap-2">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-[11px] font-extrabold text-blue-950 uppercase tracking-wider">Hasil
                                    Pencarian</span>
                            </div>
                            @if ($results->count() > 0)
                                <a href="{{ route('pencarian.spt.export', request()->all()) }}"
                                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-[11px] font-bold px-3 py-1 rounded-md flex items-center gap-1.5">
                                    <i class="fa-solid fa-file-excel text-emerald-600"></i>Export CSV
                                </a>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead
                                    class="bg-slate-100/70 text-blue-950 font-extrabold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                                    <tr>
                                        <th class="px-3 py-2.5 whitespace-nowrap">No. BPE / Tanda Terima</th>
                                        <th class="px-3 py-2.5">NPWP & Wajib Pajak</th>
                                        <th class="px-3 py-2.5">Jenis & Status</th>
                                        <th class="px-3 py-2.5 text-center">Masa / Thn</th>
                                        <th class="px-3 py-2.5 text-center">Pembetulan</th>
                                        <th class="px-3 py-2.5">Tgl Terima</th>
                                        <th class="px-3 py-2.5">AR</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($results as $item)
                                        <tr class="hover:bg-slate-50/70 transition-colors">
                                            <td
                                                class="px-3 py-2.5 font-mono text-[11px] font-bold text-blue-950 leading-tight">
                                                {{ $item->nomor_tanda_terima ?? '-' }}
                                                <div class="text-[10px] text-slate-400 font-normal mt-0.5">
                                                    Kanal: {{ $item->kanal_pelaporan ?? '-' }}</div>
                                            </td>
                                            <td class="px-3 py-2.5 leading-tight">
                                                <div class="font-bold text-blue-950 text-[12px] max-w-[200px] truncate">
                                                    {{ $item->nama }}</div>
                                                <div class="font-mono text-[10px] text-slate-500 mt-0.5">
                                                    {{ $item->npwp }}</div>
                                            </td>
                                            <td class="px-3 py-2.5">
                                                <div
                                                    class="font-semibold text-slate-800 text-[11px] max-w-[160px] truncate">
                                                    {{ $item->jenis_spt }}</div>
                                                <span
                                                    class="inline-block mt-1 px-2 py-0.5 text-[10px] rounded-full font-bold uppercase border {{ strtolower($item->status_spt ?? '') == 'nihil' || strtolower($item->status_spt ?? '') == 'lengkap' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                                    {{ $item->status_spt ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2.5 text-center text-[11px] font-medium whitespace-nowrap">
                                                {{ $item->masa1 ?? '-' }}/{{ $item->masa2 ?? '-' }}<br>
                                                <span class="font-bold text-blue-950">{{ $item->thn_pajak ?? '-' }}</span>
                                            </td>
                                            <td class="px-3 py-2.5 text-center font-mono text-[11px]">
                                                {{ $item->pembetulan ?? '0' }}</td>
                                            <td class="px-3 py-2.5 text-[11px] whitespace-nowrap">
                                                {{ $item->tgl_terima ? \Carbon\Carbon::parse($item->tgl_terima)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="px-3 py-2.5 text-[11px] leading-tight">
                                                <div class="font-semibold text-slate-800 max-w-[120px] truncate">
                                                    {{ $item->nama_ar ?? ($item->nip_ar ?? '-') }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                                    {{ $item->nip_ar ?? '' }}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-3 py-8 text-center text-slate-400 text-xs">Tidak
                                                ada data
                                                SPT ditemukan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="px-3.5 py-2.5 flex justify-end items-center bg-white border-t border-slate-100">
                            <div class="flex items-center gap-2">
                                @if ($results->hasPages())
                                    @if ($results->onFirstPage())
                                        <span
                                            class="px-3 py-1.5 text-[11px] bg-slate-100 text-slate-400 rounded-md border">←
                                            Previous</span>
                                    @else
                                        <a href="{{ $results->previousPageUrl() }}"
                                            class="px-3 py-1.5 text-[11px] bg-white border border-slate-300 text-blue-950 font-bold rounded-md hover:bg-slate-50">←
                                            Previous</a>
                                    @endif
                                    @if ($results->hasMorePages())
                                        <a href="{{ $results->nextPageUrl() }}"
                                            class="px-3 py-1.5 text-[11px] bg-blue-950 text-amber-400 font-bold rounded-md border border-amber-500/20">Next
                                            →</a>
                                    @else
                                        <span
                                            class="px-3 py-1.5 text-[11px] bg-slate-100 text-slate-400 rounded-md border">Next
                                            →</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white border border-dashed border-slate-300 rounded-lg p-12 text-center">
                        <div
                            class="w-12 h-12 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-file-invoice text-lg text-amber-500"></i>
                        </div>
                        <h3 class="text-sm font-extrabold text-blue-950 uppercase">Pencarian Tanda Terima SPT</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Gunakan filter di samping untuk mencari
                            data
                            Tanda Terima SPT Coretax beserta profil AR Wajib Pajak.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
