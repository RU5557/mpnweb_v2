{{-- resources/views/pencarian/masterfile.blade.php - Versi tooltip bawaan browser saja --}}
@extends('layouts.app')
@section('title', 'Pencarian Masterfile WP - MPNWEB')

@section('content')
    <div class="space-y-4" x-data="{ loading: false, showAlamat: false, alamatDetail: '' }">
        <div
            class="bg-white border border-slate-200/90 rounded-lg p-3 flex items-center gap-3 shadow-2xs border-t-4 border-t-amber-500">
            <div class="w-9 h-9 rounded-md bg-blue-950 text-amber-400 flex items-center justify-center"><i
                    class="fa-solid fa-users-gear text-xs"></i></div>
            <div>
                <h1 class="text-base font-extrabold text-blue-950 uppercase">Pencarian Masterfile WP</h1>
                <p class="text-[11px] text-slate-500">Hover alamat untuk tooltip bawaan, klik untuk modal lengkap</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
            <div
                class="lg:col-span-3 bg-white p-3.5 rounded-lg shadow-2xs border border-slate-200/90 space-y-3 sticky top-20">
                <div class="flex items-center justify-between border-b pb-2">
                    <div class="flex items-center gap-2 text-blue-950 font-extrabold text-xs uppercase"><i
                            class="fa-solid fa-filter text-amber-500"></i><span>Filter</span></div>
                    <a href="{{ route('pencarian.masterfile') }}" class="text-[11px] text-slate-400">Reset</a>
                </div>
                <form action="{{ route('pencarian.masterfile') }}" method="GET" @submit="loading=true"
                    class="space-y-2.5">
                    <input type="hidden" name="has_search" value="1">
                    <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                    <input type="hidden" name="sort_order" value="{{ $sortOrder }}">
                    <div><label class="text-[11px] font-bold">NPWP</label><input type="text" name="npwp"
                            value="{{ $filters['npwp'] ?? '' }}"
                            class="w-full bg-slate-50 border text-xs rounded-md px-2 py-1"></div>
                    <div><label class="text-[11px] font-bold">Nama WP</label><input type="text" name="nama"
                            value="{{ $filters['nama'] ?? '' }}"
                            class="w-full bg-slate-50 border text-xs rounded-md px-2 py-1"></div>
                    @foreach (['klu' => 'KLU', 'kelurahan' => 'Kelurahan', 'kecamatan' => 'Kecamatan', 'jenis' => 'Jenis', 'status' => 'Status'] as $k => $lbl)
                        <div><label class="text-[11px] font-bold">{{ $lbl }}</label>
                            <select name="{{ $k }}"
                                class="w-full bg-slate-50 border text-xs rounded-md px-2 py-1">
                                <option value="">-- Semua --</option>
                                @foreach (${'list' . ucfirst($k)} as $opt)
                                    <option value="{{ $opt }}" @selected(($filters[$k] ?? '') == $opt)>{{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                    <div class="grid grid-cols-2 gap-1.5">
                        <div><label class="text-[10px] font-bold">Tgl Awal</label><input type="date"
                                name="tgl_daftar_awal" value="{{ $filters['tgl_daftar_awal'] ?? '' }}"
                                class="w-full border text-xs rounded-md px-1 py-1"></div>
                        <div><label class="text-[10px] font-bold">Akhir</label><input type="date" name="tgl_daftar_akhir"
                                value="{{ $filters['tgl_daftar_akhir'] ?? '' }}"
                                class="w-full border text-xs rounded-md px-1 py-1"></div>
                    </div>
                    <button type="submit" :disabled="loading"
                        class="w-full bg-blue-950 text-amber-400 text-xs font-bold py-2 rounded-md">CARI WP</button>
                </form>
            </div>

            <div class="lg:col-span-9">
                @if ($hasSearch && isset($results))
                    <div class="bg-white rounded-lg border shadow-2xs overflow-hidden">
                        <div class="p-3 border-b bg-slate-50/50 flex justify-between">
                            <span class="text-xs font-extrabold uppercase">Hasil: <span
                                    class="text-amber-600 font-mono">{{ number_format($results->total()) }}</span></span>
                            @if ($results->total() > 0)
                                <a href="{{ route('pencarian.masterfile.export', request()->all()) }}"
                                    class="border text-xs font-bold px-3 py-1 rounded-md">Export CSV</a>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 text-blue-950 font-extrabold uppercase text-[11px] border-b">
                                    <tr>
                                        <th class="p-2.5">NPWP</th>
                                        <th class="p-2.5">Nama WP</th>
                                        <th class="p-2.5 w-[32%]">Alamat</th>
                                        <th class="p-2.5">Kec/Kel</th>
                                        <th class="p-2.5">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($results as $wp)
                                        <tr class="border-b hover:bg-slate-50">
                                            <td class="p-2.5 font-mono text-[11px]">{{ $wp->npwp15 }}<br><span
                                                    class="text-slate-400">{{ $wp->npwp16 }}</span></td>
                                            <td class="p-2.5 font-bold text-blue-950">{{ $wp->nama }}<div
                                                    class="text-[10px] text-slate-500 font-normal">{{ $wp->jenis }} •
                                                    {{ $wp->klu }}</div>
                                            </td>

                                            {{-- ALAMAT: hanya tooltip bawaan browser (title) + klik modal --}}
                                            <td class="p-2.5">
                                                <div class="cursor-pointer" title="{{ $wp->alamat_full ?? $wp->alamat }}"
                                                    @click="showAlamat=true; alamatDetail='{{ addslashes($wp->alamat_full ?? $wp->alamat) }}'">
                                                    <p class="text-[12px] leading-snug text-slate-700 line-clamp-2">
                                                        {{ $wp->alamat }}
                                                    </p>
                                                    @if (strlen($wp->alamat_full ?? $wp->alamat) > 100)
                                                        <span
                                                            class="inline-flex gap-1 mt-1 text-[10px] font-bold text-amber-600 hover:text-amber-700">
                                                            <i
                                                                class="fa-solid fa-up-right-from-square text-[9px] mt-0.5"></i>
                                                            lihat lengkap
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="p-2.5 text-[11px]">{{ $wp->kecamatan }}<br><span
                                                    class="text-slate-400">{{ $wp->kelurahan }}</span></td>
                                            <td class="p-2.5"><span
                                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $wp->status == 'AKTIF' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100' }}">{{ $wp->status }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-6 text-center text-slate-400">Tidak ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">{{ $results->links() }}</div>
                    </div>

                    {{-- Modal tetap ada untuk lihat alamat full saat klik --}}
                    <div x-show="showAlamat" x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                        @click.self="showAlamat=false">
                        <div class="bg-white rounded-lg shadow-2xl max-w-lg w-full p-5 border">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="font-extrabold text-sm text-blue-950 uppercase">Alamat Lengkap</h3>
                                <button @click="showAlamat=false" class="text-slate-400 hover:text-slate-800"><i
                                        class="fa-solid fa-xmark"></i></button>
                            </div>
                            <p class="text-sm text-slate-700 leading-relaxed" x-text="alamatDetail"></p>
                            <div class="mt-4 flex justify-end"><button @click="showAlamat=false"
                                    class="bg-blue-950 text-amber-400 text-xs font-bold px-4 py-2 rounded-md">Tutup</button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white border border-dashed rounded-lg p-12 text-center">
                        <h3 class="text-sm font-extrabold text-blue-950 uppercase">Gunakan Filter</h3>
                        <p class="text-xs text-slate-500">Hover alamat untuk tooltip bawaan browser</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
