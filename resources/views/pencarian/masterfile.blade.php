{{-- resources/views/pencarian/masterfile.blade.php - VERSI PIMPINAN CLEAN + SUDAH PAKAI COMPONENT page-header --}}
@extends('layouts.app')
@section('title', 'Pencarian Masterfile WP - MPNWEB')

@section('content')
    <div class="space-y-4" x-data="{ loading: false, showAlamat: false, alamatDetail: '' }">
        {{-- HEADER SUDAH PAKAI COMPONENT - ukuran besar proporsional sama seperti masterfile WP yang bagus menurut pimpinan --}}
        <x-layout.page-header title="Pencarian Masterfile Wajib Pajak"
            subtitle="Data Masterfile WP • Tahun {{ date('Y') }}" icon="fa-users-gear" />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
            {{-- FILTER CLEAN - tanpa label teknis --}}
            <div
                class="lg:col-span-3 bg-white p-3.5 rounded-lg shadow-2xs border border-slate-200/90 space-y-3 sticky top-20">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                    <div class="flex items-center gap-2 text-blue-950 font-extrabold text-[11px] uppercase tracking-wider">
                        <i class="fa-solid fa-filter text-amber-500"></i><span>Filter Pencarian</span>
                    </div>
                    <a href="{{ route('pencarian.masterfile') }}"
                        class="text-[11px] text-slate-400 hover:text-blue-950 transition flex items-center gap-1">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i> Reset
                    </a>
                </div>

                <form action="{{ route('pencarian.masterfile') }}" method="GET" @submit="loading=true"
                    class="space-y-2.5">
                    <input type="hidden" name="has_search" value="1">
                    <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                    <input type="hidden" name="sort_order" value="{{ $sortOrder }}">

                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">NPWP</label><input type="text"
                            name="npwp" value="{{ $filters['npwp'] ?? '' }}" placeholder="Masukkan NPWP..."
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-950/20 focus:border-blue-950 font-medium">
                    </div>
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Wajib Pajak</label><input
                            type="text" name="nama" value="{{ $filters['nama'] ?? '' }}"
                            placeholder="Masukkan Nama WP..."
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-950/20 focus:border-blue-950 font-medium">
                    </div>

                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">KLU</label><select name="klu"
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded-md px-2 py-1.5 font-medium">
                            <option value="">-- Semua KLU --</option>
                            @foreach ($listKlu as $opt)
                                <option value="{{ $opt }}" @selected(($filters['klu'] ?? '') == $opt)>{{ $opt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Kelurahan</label><select
                            name="kelurahan"
                            class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5 font-medium">
                            <option value="">-- Semua Kelurahan --</option>
                            @foreach ($listKelurahan as $opt)
                                <option value="{{ $opt }}" @selected(($filters['kelurahan'] ?? '') == $opt)>{{ $opt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Kecamatan</label><select
                            name="kecamatan"
                            class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5 font-medium">
                            <option value="">-- Semua Kecamatan --</option>
                            @foreach ($listKecamatan as $opt)
                                <option value="{{ $opt }}" @selected(($filters['kecamatan'] ?? '') == $opt)>{{ $opt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis Wajib Pajak</label><select
                            name="jenis"
                            class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5 font-medium">
                            <option value="">-- Semua Jenis --</option>
                            @foreach ($listJenis as $opt)
                                <option value="{{ $opt }}" @selected(($filters['jenis'] ?? '') == $opt)>{{ $opt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Status</label><select name="status"
                            class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5 font-medium">
                            <option value="">-- Semua Status --</option>
                            @foreach ($listStatus as $opt)
                                <option value="{{ $opt }}" @selected(($filters['status'] ?? '') == $opt)>{{ $opt }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="block text-[10px] font-bold text-slate-700 mb-1">Tgl Daftar Awal</label><input
                                type="date" name="tgl_daftar_awal" value="{{ $filters['tgl_daftar_awal'] ?? '' }}"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-1.5 py-1.5"></div>
                        <div><label class="block text-[10px] font-bold text-slate-700 mb-1">Akhir</label><input
                                type="date" name="tgl_daftar_akhir" value="{{ $filters['tgl_daftar_akhir'] ?? '' }}"
                                class="w-full bg-white border border-slate-300 text-xs rounded-md px-1.5 py-1.5"></div>
                    </div>

                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Account
                            Representative</label><select name="nip_ar"
                            class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5 font-medium">
                            <option value="">-- Semua AR --</option>
                            @foreach ($listAr as $ar)
                                <option value="{{ $ar->nip }}" @selected(($filters['nip_ar'] ?? '') == $ar->nip)>{{ $ar->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="block text-[11px] font-bold text-slate-700 mb-1">Juru Sita</label><select
                            name="nip_jspn"
                            class="w-full bg-white border border-slate-300 text-xs rounded-md px-2 py-1.5 font-medium">
                            <option value="">-- Semua Juru Sita --</option>
                            @foreach ($listJspn as $jspn)
                                <option value="{{ $jspn->nip }}" @selected(($filters['nip_jspn'] ?? ($filters['nip_js'] ?? '')) == $jspn->nip)>{{ $jspn->nama }}
                                </option>
                            @endforeach
                        </select></div>

                    <div class="pt-2"><button type="submit" :disabled="loading"
                            class="w-full bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/20 text-xs font-extrabold py-2.5 rounded-md transition shadow-sm flex items-center justify-center gap-2"><i
                                x-show="loading" class="fa-solid fa-circle-notch fa-spin"></i><i x-show="!loading"
                                class="fa-solid fa-magnifying-glass"></i><span
                                x-text="loading ? 'Mencari...' : 'Cari Wajib Pajak'"></span></button></div>
                </form>
            </div>

            {{-- HASIL CLEAN --}}
            <div class="lg:col-span-9">
                @if ($hasSearch && isset($results))
                    <div class="bg-white rounded-lg shadow-2xs border border-slate-200/90 overflow-hidden">
                        <div
                            class="px-3.5 py-2.5 border-b border-slate-200 bg-slate-50/50 flex flex-wrap justify-between items-center gap-2">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-[11px] font-extrabold text-blue-950 uppercase tracking-wider">Hasil
                                    Pencarian</span>
                                <span class="text-[11px] text-slate-500 font-normal">Menampilkan {{ $results->count() }}
                                    data</span>
                            </div>
                            @if ($results->count() > 0)
                                <a href="{{ route('pencarian.masterfile.export', request()->all()) }}"
                                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-[11px] font-bold px-3 py-1 rounded-md transition flex items-center gap-1.5 shadow-2xs"><i
                                        class="fa-solid fa-file-excel text-emerald-600"></i>Export CSV</a>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead
                                    class="bg-slate-100/70 text-blue-950 font-extrabold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                                    <tr>
                                        <th class="px-3 py-2.5 font-extrabold">NPWP</th>
                                        <th class="px-3 py-2.5 font-extrabold">Nama Wajib Pajak</th>
                                        <th class="px-3 py-2.5 font-extrabold w-[30%]">Alamat</th>
                                        <th class="px-3 py-2.5 font-extrabold">Petugas</th>
                                        <th class="px-3 py-2.5 font-extrabold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($results as $wp)
                                        <tr class="hover:bg-slate-50/70 transition">
                                            <td class="px-3 py-2.5 font-mono text-[11px] leading-tight text-slate-800">
                                                {{ $wp->npwp15 }}<br><span
                                                    class="text-slate-400 text-[10px]">{{ $wp->npwp16 }}</span></td>
                                            <td class="px-3 py-2.5 leading-tight">
                                                <div class="font-bold text-blue-950 text-[12px]">{{ $wp->nama }}</div>
                                                <div class="text-[10px] text-slate-500 mt-0.5">{{ $wp->jenis }} •
                                                    {{ $wp->klu }}</div>
                                            </td>
                                            <td class="px-3 py-2.5">
                                                <div class="cursor-pointer" title="{{ $wp->alamat_full ?? $wp->alamat }}"
                                                    @click="showAlamat=true; alamatDetail='{{ addslashes($wp->alamat_full ?? $wp->alamat) }}'">
                                                    <p class="text-[11px] leading-snug text-slate-600 line-clamp-2">
                                                        {{ $wp->alamat }}</p>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2.5 text-[11px] leading-tight">
                                                <div class="font-semibold text-slate-800">
                                                    {{ $wp->ar->nama ?? ($wp->nip_ar ?? '-') }}</div>
                                                <div class="text-slate-400 text-[10px] mt-0.5">
                                                    {{ $wp->js->nama ?? ($wp->nip_js ?? '-') }}</div>
                                            </td>
                                            <td class="px-3 py-2.5"><span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $wp->status == 'NORMAL' ? 'bg-slate-50 text-slate-700 border-slate-200' : ($wp->status == 'AKTIF' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200') }}">{{ $wp->status }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-8 text-center text-slate-400 text-xs">Tidak
                                                ada data yang ditemukan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3.5 py-2.5 flex justify-end bg-white border-t border-slate-100">
                            {{ $results->links() }}</div>
                    </div>

                    <div x-show="showAlamat" x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                        @click.self="showAlamat=false">
                        <div class="bg-white rounded-lg shadow-2xl max-w-lg w-full p-5 border">
                            <div class="flex justify-between mb-3">
                                <h3 class="font-extrabold text-sm text-blue-950 uppercase">Alamat Lengkap</h3><button
                                    @click="showAlamat=false" class="text-slate-400 hover:text-slate-800"><i
                                        class="fa-solid fa-xmark"></i></button>
                            </div>
                            <p class="text-sm text-slate-700 leading-relaxed" x-text="alamatDetail"></p>
                            <div class="mt-4 flex justify-end"><button @click="showAlamat=false"
                                    class="bg-blue-950 text-amber-400 text-xs font-bold px-4 py-2 rounded-md">Tutup</button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white border border-dashed border-slate-300 rounded-lg p-12 text-center">
                        <div
                            class="w-12 h-12 bg-slate-50 border border-slate-200 text-blue-950 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-users text-lg text-amber-500"></i></div>
                        <h3 class="text-sm font-extrabold text-blue-950 uppercase tracking-tight">Pencarian Masterfile</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Gunakan filter di samping untuk mencari
                            data Wajib Pajak</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
