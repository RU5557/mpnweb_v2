<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Kelola Target & Info</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-slate-100 font-sans min-h-screen text-slate-800 text-xs antialiased">

    <!-- Looker Studio Control Header Bar -->
    <header
        class="bg-white border-b border-slate-200/90 px-6 py-2.5 flex items-center justify-between shadow-2xs sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <div
                class="w-8 h-8 bg-blue-950 text-amber-400 rounded flex items-center justify-center font-bold text-xs shrink-0 shadow-xs border border-blue-900">
                <i class="fa-solid fa-gears text-xs"></i>
            </div>
            <div>
                <h1 class="text-sm font-extrabold text-blue-950 leading-tight tracking-tight uppercase">Panel Admin
                    MPNWEB</h1>
                <p class="text-[10px] text-slate-500 font-medium">Pengaturan Parameter Target & Informasi Harian</p>
            </div>
        </div>

        <!-- Tombol Aksi Right Controls -->
        <div class="flex items-center gap-2">
            <a href="{{ route('penerimaan.dashboard') }}"
                class="bg-slate-50 hover:bg-slate-100 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1.5 rounded transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Dashboard Utama</span>
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-bold px-3 py-1.5 rounded transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-right-from-bracket text-[10px]"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </header>

    <!-- Looker Studio Canvas Area -->
    <main class="max-w-7xl mx-auto p-5 space-y-5">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div
                class="bg-emerald-50 border border-emerald-300 text-emerald-800 p-3 rounded-lg shadow-2xs flex items-center justify-between">
                <div class="flex items-center gap-2 font-medium">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i
                        class="fa-solid fa-xmark text-xs"></i></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-300 text-rose-800 p-4 rounded-lg shadow-2xs space-y-1">
                <div class="flex items-center gap-2 font-bold text-xs">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                    <span>Terdapat kesalahan pada inputan Anda:</span>
                </div>
                <ul class="list-disc list-inside text-xs pl-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- WIDGET CARD 1: ROLLING TEXT HARIAN (Looker Studio Modular Canvas Tile) -->
        <section
            class="bg-white border border-slate-200/90 rounded-lg shadow-2xs overflow-hidden border-t-4 border-t-blue-950">
            <div class="p-3.5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-bullhorn text-amber-500 text-xs"></i>
                    <h2 class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">Update Rolling Text &
                        Performa Harian</h2>
                </div>
                <span
                    class="text-[10px] font-bold font-mono text-blue-950 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">Parameter
                    Harian</span>
            </div>

            <form action="{{ route('admin.rolling-text.update') }}" method="POST" class="p-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label
                            class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Tanggal
                            Update</label>
                        <input type="date" name="tanggal"
                            value="{{ old('tanggal', isset($rollingText) ? $rollingText->tanggal : date('Y-m-d')) }}"
                            required
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-medium">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label
                            class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Nilai
                            NKO (%)</label>
                        <input type="number" step="0.01" min="0" max="120" name="nko"
                            value="{{ old('nko', isset($rollingText) ? $rollingText->nko : 0) }}" placeholder="95.40"
                            required
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label
                            class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Ranking
                            Nasional</label>
                        <input type="number" min="1" name="ranking_nasional"
                            value="{{ old('ranking_nasional', isset($rollingText) ? $rollingText->ranking_nasional : 0) }}"
                            placeholder="12" required
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label
                            class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Ranking
                            Kanwil</label>
                        <input type="number" min="1" name="ranking_kanwil"
                            value="{{ old('ranking_kanwil', isset($rollingText) ? $rollingText->ranking_kanwil : 0) }}"
                            placeholder="2" required
                            class="w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit"
                        class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 font-bold text-xs px-4 py-1.5 rounded transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-floppy-disk text-[11px]"></i>
                        <span>Simpan Info Harian</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- WIDGET CARD 2: TARGET TAHUNAN (Looker Studio Canvas Grid) -->
        <section
            class="bg-white border border-slate-200/90 rounded-lg shadow-2xs overflow-hidden border-t-4 border-t-blue-950">
            <div class="p-3.5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-bullseye text-amber-500 text-xs"></i>
                    <h2 class="text-xs font-extrabold text-blue-950 uppercase tracking-wider">Update Target Tahunan
                        (Tahun {{ $tahunSekarang }})</h2>
                </div>
                <span
                    class="text-[10px] font-bold font-mono text-blue-950 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">Parameter
                    Statik</span>
            </div>

            <form action="{{ route('admin.target.update') }}" method="POST" class="p-4 space-y-4">
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahunSekarang }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label
                            class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Target
                            Kantor (Global)</label>
                        <input type="text" name="target_kantor"
                            value="{{ old('target_kantor', isset($target->target_kantor) ? number_format($target->target_kantor, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label
                            class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Target
                            PPM</label>
                        <input type="text" name="target_ppm"
                            value="{{ old('target_ppm', isset($target->target_ppm) ? number_format($target->target_ppm, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label
                            class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Target
                            PKM (Total)</label>
                        <input type="text" name="target_pkm"
                            value="{{ old('target_pkm', isset($target->target_pkm) ? number_format($target->target_pkm, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label
                            class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Target
                            PBP</label>
                        <input type="text" name="target_pbp"
                            value="{{ old('target_pbp', isset($target->target_pbp) ? number_format($target->target_pbp, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label
                            class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Target
                            PKM Pengawasan</label>
                        <input type="text" name="target_pkm_pengawasan"
                            value="{{ old('target_pkm_pengawasan', isset($target->target_pkm_pengawasan) ? number_format($target->target_pkm_pengawasan, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label
                            class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Target
                            PKM Pemeriksaan</label>
                        <input type="text" name="target_pkm_pemeriksaan"
                            value="{{ old('target_pkm_pemeriksaan', isset($target->target_pkm_pemeriksaan) ? number_format($target->target_pkm_pemeriksaan, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-white border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded border border-slate-200/80">
                        <label
                            class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Target
                            PKM Penagihan</label>
                        <input type="text" name="target_pkm_penagihan"
                            value="{{ old('target_pkm_penagihan', isset($target->target_pkm_penagihan) ? number_format($target->target_pkm_penagihan, 0, ',', '.') : 0) }}"
                            required
                            class="rupiah-input w-full bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded px-2.5 py-1.5 focus:outline-none focus:border-blue-950 font-mono font-bold">
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit"
                        class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 font-bold text-xs px-4 py-1.5 rounded transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <i class="fa-solid fa-floppy-disk text-[11px]"></i>
                        <span>Simpan Target Tahunan</span>
                    </button>
                </div>
            </form>
        </section>

    </main>

    <!-- SCRIPT FORMATTING MASKING RUPIAH -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const rupiahInputs = document.querySelectorAll('.rupiah-input');

            rupiahInputs.forEach(function(input) {
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

</body>

</html>
