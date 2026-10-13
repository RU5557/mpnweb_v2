@extends('layouts.app')

@section('title', 'Panel Admin - Kelola Target & Info')

@section('content')
    <div x-data="{ savingRolling: false, savingTarget: false }" class="space-y-6">

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl shadow-sm space-y-2">
                <div class="flex items-center gap-2 font-bold text-[12px]">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                    <span>Terdapat kesalahan pada inputan:</span>
                </div>
                <ul class="list-disc list-inside text-[12px] pl-2 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header Admin Executive Navy -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-lg bg-[#0A1931] border border-amber-500/30 text-amber-400 flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                    </div>
                    <div>
                        <h1 class="text-[14px] font-extrabold text-[#0A1931] uppercase tracking-tight">Panel Admin MPNWEB
                        </h1>
                        <p class="text-[11px] text-slate-500 font-medium">Pengaturan Parameter Target & Informasi Harian •
                            DJP Executive</p>
                    </div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="bg-white hover:bg-slate-50 text-slate-600 hover:text-[#0A1931] border border-slate-200 text-[12px] font-semibold px-3.5 py-2 rounded-lg transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-right-from-bracket text-[11px]"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>

        <!-- CARD 1: ROLLING TEXT HARIAN - Navy Edition -->
        <section
            class="bg-white border border-slate-200 rounded-xl shadow-[0_2px_12px_rgba(10,25,49,0.04)] overflow-hidden">
            <div class="bg-[#0A1931] px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-7 h-7 rounded-md bg-[#112A46] border border-amber-500/30 text-amber-400 flex items-center justify-center">
                        <i class="fa-solid fa-bullhorn text-[11px]"></i>
                    </div>
                    <div>
                        <h2 class="text-[12px] font-bold text-white uppercase tracking-wider">Update Rolling Text & Performa
                            Harian</h2>
                        <p class="text-[10px] text-slate-400 font-mono">NKO, Ranking Nasional & Kanwil</p>
                    </div>
                </div>
                <span
                    class="text-[10px] font-bold font-mono text-amber-400 bg-amber-400/10 border border-amber-500/20 px-2.5 py-1 rounded-full">Parameter
                    Harian</span>
            </div>

            <form action="{{ route('admin.rolling-text.update') }}" method="POST" @submit="savingRolling = true"
                class="p-5 space-y-5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Tanggal
                            Update</label>
                        <div class="relative">
                            <input type="date" name="tanggal"
                                value="{{ old('tanggal', isset($rollingText) ? $rollingText->tanggal : date('Y-m-d')) }}"
                                required
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-[13px] rounded-lg px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-medium">
                            <i
                                class="fa-regular fa-calendar absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Nilai NKO
                            (%)</label>
                        <input type="number" step="0.01" min="0" max="120" name="nko"
                            value="{{ old('nko', isset($rollingText) ? $rollingText->nko : 0) }}" required
                            class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-[13px] rounded-lg px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-mono font-bold">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Ranking
                            Nasional</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">#</span>
                            <input type="number" min="1" name="ranking_nasional"
                                value="{{ old('ranking_nasional', isset($rollingText) ? $rollingText->ranking_nasional : 1) }}"
                                required
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-[13px] rounded-lg pl-7 pr-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-bold">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Ranking
                            Kanwil</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">#</span>
                            <input type="number" min="1" name="ranking_kanwil"
                                value="{{ old('ranking_kanwil', isset($rollingText) ? $rollingText->ranking_kanwil : 1) }}"
                                required
                                class="w-full bg-slate-50 border border-slate-300 text-slate-800 text-[13px] rounded-lg pl-7 pr-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-bold">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button type="submit" :disabled="savingRolling"
                        class="bg-[#0A1931] hover:bg-[#112A46] disabled:bg-slate-400 disabled:cursor-not-allowed text-amber-400 border border-amber-500/20 font-bold text-[12.5px] px-5 py-2.5 rounded-lg transition flex items-center gap-2 shadow-sm">
                        <template x-if="!savingRolling"><span class="flex items-center gap-2"><i
                                    class="fa-solid fa-floppy-disk text-[12px]"></i> Simpan Rolling Text</span></template>
                        <template x-if="savingRolling"><span class="flex items-center gap-2"><i
                                    class="fa-solid fa-circle-notch fa-spin text-[12px]"></i> Menyimpan...</span></template>
                    </button>
                </div>
            </form>
        </section>

        <!-- CARD 2: TARGET TAHUNAN - Navy Edition -->
        <section
            class="bg-white border border-slate-200 rounded-xl shadow-[0_2px_12px_rgba(10,25,49,0.04)] overflow-hidden">
            <div class="bg-white border-b border-slate-200 px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-7 h-7 rounded-md bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center">
                        <i class="fa-solid fa-bullseye text-[11px]"></i>
                    </div>
                    <div>
                        <h2 class="text-[12px] font-bold text-[#0A1931] uppercase tracking-wider">Update Target Penerimaan
                            Tahunan</h2>
                        <p class="text-[10px] text-slate-500 font-medium">7 variabel target komponen</p>
                    </div>
                </div>
                <span
                    class="text-[10px] font-bold font-mono text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full">Parameter
                    Tahunan - {{ $tahunSekarang }}</span>
            </div>

            <form action="{{ route('admin.target.update') }}" method="POST" @submit="savingTarget = true"
                class="p-5 space-y-5">
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahunSekarang }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Row 1 --}}
                    <div class="lg:col-span-1 space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Target
                            Kantor (Global)</label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px] font-bold">Rp</span>
                            <input type="text" name="target_kantor" inputmode="numeric"
                                value="{{ old('target_kantor', isset($target) ? number_format($target->target_kantor, 0, ',', '.') : 0) }}"
                                required
                                class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-[13px] rounded-lg pl-8 pr-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-mono font-bold">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Target
                            PPM</label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px] font-bold">Rp</span>
                            <input type="text" name="target_ppm" inputmode="numeric"
                                value="{{ old('target_ppm', isset($target) ? number_format($target->target_ppm, 0, ',', '.') : 0) }}"
                                required
                                class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-[13px] rounded-lg pl-8 pr-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-mono font-bold">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Target PKM
                            (Total)</label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px] font-bold">Rp</span>
                            <input type="text" name="target_pkm" inputmode="numeric"
                                value="{{ old('target_pkm', isset($target) ? number_format($target->target_pkm, 0, ',', '.') : 0) }}"
                                required
                                class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-[13px] rounded-lg pl-8 pr-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-mono font-bold">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Target
                            PBP</label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px] font-bold">Rp</span>
                            <input type="text" name="target_pbp" inputmode="numeric"
                                value="{{ old('target_pbp', isset($target) ? number_format($target->target_pbp, 0, ',', '.') : 0) }}"
                                required
                                class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-[13px] rounded-lg pl-8 pr-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-mono font-bold">
                        </div>
                    </div>

                    {{-- Row 2 --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Target PKM
                            Pengawasan</label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px] font-bold">Rp</span>
                            <input type="text" name="target_pkm_pengawasan" inputmode="numeric"
                                value="{{ old('target_pkm_pengawasan', isset($target) ? number_format($target->target_pkm_pengawasan, 0, ',', '.') : 0) }}"
                                required
                                class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-[13px] rounded-lg pl-8 pr-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-mono font-bold">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Target PKM
                            Pemeriksaan</label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px] font-bold">Rp</span>
                            <input type="text" name="target_pkm_pemeriksaan" inputmode="numeric"
                                value="{{ old('target_pkm_pemeriksaan', isset($target) ? number_format($target->target_pkm_pemeriksaan, 0, ',', '.') : 0) }}"
                                required
                                class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-[13px] rounded-lg pl-8 pr-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-mono font-bold">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Target PKM
                            Penagihan</label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px] font-bold">Rp</span>
                            <input type="text" name="target_pkm_penagihan" inputmode="numeric"
                                value="{{ old('target_pkm_penagihan', isset($target) ? number_format($target->target_pkm_penagihan, 0, ',', '.') : 0) }}"
                                required
                                class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-[13px] rounded-lg pl-8 pr-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-mono font-bold">
                        </div>
                    </div>

                    <div class="flex items-end">
                        <div
                            class="w-full bg-amber-50/70 border border-dashed border-amber-200 rounded-lg p-3 text-[11px] text-amber-800">
                            <div class="flex gap-2">
                                <i class="fa-solid fa-circle-info mt-0.5"></i>
                                <span>Format otomatis ribuan (1.000). Data disimpan sebagai integer di database.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button type="submit" :disabled="savingTarget"
                        class="bg-[#0A1931] hover:bg-[#112A46] disabled:bg-slate-400 disabled:cursor-not-allowed text-amber-400 border border-amber-500/20 font-bold text-[12.5px] px-5 py-2.5 rounded-lg transition flex items-center gap-2 shadow-sm">
                        <template x-if="!savingTarget"><span class="flex items-center gap-2"><i
                                    class="fa-solid fa-floppy-disk text-[12px]"></i> Simpan Target
                                Tahunan</span></template>
                        <template x-if="savingTarget"><span class="flex items-center gap-2"><i
                                    class="fa-solid fa-circle-notch fa-spin text-[12px]"></i>
                                Menyimpan...</span></template>
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
                        let value = e.target.value.replace(/[^\d]/g, '');
                        if (value) {
                            e.target.value = new Intl.NumberFormat('id-ID').format(value);
                        } else {
                            e.target.value = '0';
                        }
                    });
                    // Saat submit, ubah ke angka murni
                    input.closest('form').addEventListener('submit', function() {
                        document.querySelectorAll('.rupiah-input').forEach(function(i) {
                            i.value = i.value.replace(/\./g, '');
                        });
                    });
                });
            });
        </script>
    @endpush

@endsection
