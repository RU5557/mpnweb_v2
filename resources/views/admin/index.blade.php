@extends('layouts.app')

@section('title', 'Panel Admin - Kelola Target & Info')

@section('content')
    <div class="space-y-5">
        {{-- HAPUS flash di sini, sudah ada di layouts/app.blade.php --}}

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-300 text-rose-800 p-4 rounded-lg shadow-2xs space-y-1">
                <div class="flex items-center gap-2 font-bold text-xs">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                    <span>Terdapat kesalahan pada inputan:</span>
                </div>
                <ul class="list-disc list-inside text-xs pl-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header Admin Mini -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-sm font-extrabold text-blue-950 uppercase tracking-tight">PANEL ADMIN MPNWEB</h1>
                <p class="text-[11px] text-slate-500">Pengaturan Parameter Target & Informasi Harian</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-bold px-3 py-1.5 rounded transition flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket text-[10px]"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>

        <!-- CARD 1: ROLLING TEXT HARIAN -->
        <section
            class="bg-white border border-slate-200/90 rounded-lg shadow-2xs overflow-hidden border-t-4 border-t-blue-950">
            <div class="p-3.5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-bullhorn text-amber-500 text-xs"></i>
                    <h2 class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">UPDATE ROLLING TEXT & PERFORMA
                        HARIAN</h2>
                </div>
                <span
                    class="text-[10px] font-bold font-mono text-blue-950 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">Parameter
                    Harian</span>
            </div>

            <form action="{{ route('admin.rolling-text.update') }}" method="POST" class="p-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">TANGGAL
                            UPDATE</label>
                        <input type="date" name="tanggal"
                            value="{{ old('tanggal', isset($rollingText) ? $rollingText->tanggal : date('Y-m-d')) }}"
                            required
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-medium">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">NILAI
                            NKO (%)</label>
                        <input type="number" step="0.01" min="0" max="120" name="nko"
                            value="{{ old('nko', isset($rollingText) ? $rollingText->nko : 0) }}" required
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">RANKING
                            NASIONAL</label>
                        <input type="number" min="1" name="ranking_nasional"
                            value="{{ old('ranking_nasional', isset($rollingText) ? $rollingText->ranking_nasional : 1) }}"
                            required
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-medium">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">RANKING
                            KANWIL</label>
                        <input type="number" min="1" name="ranking_kanwil"
                            value="{{ old('ranking_kanwil', isset($rollingText) ? $rollingText->ranking_kanwil : 1) }}"
                            required
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-medium">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 font-bold text-xs px-4 py-1.5 rounded transition flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-floppy-disk text-[11px]"></i>
                        <span>Simpan Rolling Text</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- CARD 2: TARGET TAHUNAN -->
        <section
            class="bg-white border border-slate-200/90 rounded-lg shadow-2xs overflow-hidden border-t-4 border-t-amber-400">
            <div class="p-3.5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-bullseye text-blue-950 text-xs"></i>
                    <h2 class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">UPDATE TARGET PENERIMAAN
                        TAHUNAN</h2>
                </div>
                <span
                    class="text-[10px] font-bold font-mono text-blue-950 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded">Parameter
                    Tahunan - {{ $tahunSekarang }}</span>
            </div>

            <form action="{{ route('admin.target.update') }}" method="POST" class="p-4 space-y-4">
                @csrf
                {{-- INI FIX UNTUK ERROR tahun field is required --}}
                <input type="hidden" name="tahun" value="{{ $tahunSekarang }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">TARGET
                            KANTOR (GLOBAL)</label>
                        <input type="text" name="target_kantor"
                            value="{{ old('target_kantor', isset($target) ? number_format($target->target_kantor, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">TARGET
                            PPM</label>
                        <input type="text" name="target_ppm"
                            value="{{ old('target_ppm', isset($target) ? number_format($target->target_ppm, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">TARGET
                            PKM (TOTAL)</label>
                        <input type="text" name="target_pkm"
                            value="{{ old('target_pkm', isset($target) ? number_format($target->target_pkm, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">TARGET
                            PBP</label>
                        <input type="text" name="target_pbp"
                            value="{{ old('target_pbp', isset($target) ? number_format($target->target_pbp, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">TARGET
                            PKM PENGAWASAN</label>
                        <input type="text" name="target_pkm_pengawasan"
                            value="{{ old('target_pkm_pengawasan', isset($target) ? number_format($target->target_pkm_pengawasan, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">TARGET
                            PKM PEMERIKSAAN</label>
                        <input type="text" name="target_pkm_pemeriksaan"
                            value="{{ old('target_pkm_pemeriksaan', isset($target) ? number_format($target->target_pkm_pemeriksaan, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">TARGET
                            PKM PENAGIHAN</label>
                        <input type="text" name="target_pkm_penagihan"
                            value="{{ old('target_pkm_penagihan', isset($target) ? number_format($target->target_pkm_penagihan, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 font-bold text-xs px-4 py-1.5 rounded transition flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-floppy-disk text-[11px]"></i>
                        <span>Simpan Target Tahunan</span>
                    </button>
                </div>
            </form>
        </section>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.rupiah-input').forEach(function(input) {
                    input.addEventListener('input', function(e) {
                        let value = this.value.replace(/[^,\d]/g, '').toString();
                        let split = value.split(',');
                        let sisa = split[0].length % 3;
                        let rupiah = split[0].substr(0, sisa);
                        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                        if (ribuan) {
                            let separator = sisa ? '.' : '';
                            rupiah += separator + ribuan.join('.');
                        }
                        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                        this.value = rupiah;
                    });
                });
            });
        </script>
    @endpush
@endsection
