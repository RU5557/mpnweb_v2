<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Admin - MPNWEB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>
</head>

<body
    class="bg-slate-100 min-h-screen flex items-center justify-center p-4 font-sans text-xs antialiased selection:bg-amber-400 selection:text-[#0A1931]">

    <div x-data="{ showPassword: false, loading: false }"
        class="bg-white rounded-xl shadow-[0_8px_30px_rgba(10,25,49,0.15)] border border-slate-200/80 w-full max-w-[400px] overflow-hidden">

        {{-- Header Navy DJP --}}
        <div class="bg-[#0A1931] px-6 py-5 border-b border-[#1a365d] relative">
            <div class="flex items-center gap-3.5">
                <div
                    class="w-10 h-10 rounded-lg bg-[#112A46] border border-amber-500/30 text-amber-400 flex items-center justify-center shadow-md shadow-black/20 shrink-0">
                    <i class="fa-solid fa-chart-column text-sm"></i>
                </div>
                <div class="leading-none space-y-1">
                    <h1 class="text-[14px] font-extrabold text-white tracking-wider uppercase">MPNWEB</h1>
                    <p class="text-[10px] text-amber-400 font-mono font-bold tracking-wider uppercase">DJP MONITORING •
                        Admin Access</p>
                </div>
            </div>
            <div
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-amber-400 via-amber-500 to-amber-400">
            </div>
        </div>

        <div class="p-6 space-y-5">
            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition
                    class="bg-rose-50 border border-rose-200 text-rose-800 text-xs px-3.5 py-3 rounded-lg flex items-start gap-2.5 font-medium">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 mt-0.5 shrink-0"></i>
                    <span class="flex-1">{{ session('error') }}</span>
                    <button @click="show = false" class="text-rose-400 hover:text-rose-700"><i
                            class="fa-solid fa-xmark text-[11px]"></i></button>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" @submit="loading = true" class="space-y-4">
                @csrf
                <div>
                    <label for="username"
                        class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Username
                        Admin</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><i
                                class="fa-solid fa-user text-[11px]"></i></span>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required
                            autofocus autocomplete="username" placeholder="Masukkan username"
                            class="w-full bg-slate-50/80 border {{ $errors->has('username') ? 'border-rose-400 bg-rose-50/50' : 'border-slate-300' }} text-slate-800 text-[13px] rounded-lg pl-9 pr-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-medium placeholder:text-slate-400">
                    </div>
                    @error('username')
                        <p class="text-rose-600 text-[11px] mt-1.5 flex items-center gap-1 font-medium"><i
                                class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password"
                        class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><i
                                class="fa-solid fa-lock text-[11px]"></i></span>
                        <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                            autocomplete="current-password" placeholder="Masukkan password"
                            class="w-full bg-slate-50/80 border {{ $errors->has('password') ? 'border-rose-400 bg-rose-50/50' : 'border-slate-300' }} text-slate-800 text-[13px] rounded-lg pl-9 pr-9 py-2.5 focus:bg-white focus:ring-2 focus:ring-[#0A1931]/20 focus:border-[#0A1931] outline-none transition font-medium placeholder:text-slate-400">
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition p-1">
                            <i :class="showPassword ? 'fa-eye-slash' : 'fa-eye'" class="fa-solid text-[12px]"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-rose-600 text-[11px] mt-1.5 flex items-center gap-1 font-medium"><i
                                class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full bg-[#0A1931] hover:bg-[#112A46] disabled:bg-slate-700 disabled:cursor-not-allowed text-amber-400 border border-amber-500/30 font-bold py-2.5 rounded-lg text-[12.5px] shadow-sm transition-all flex items-center justify-center gap-2 mt-1 group">
                    <template x-if="!loading"><span class="flex items-center gap-2"><i
                                class="fa-solid fa-right-to-bracket text-[12px] group-hover:translate-x-0.5 transition-transform"></i>
                            Otentikasi Admin</span></template>
                    <template x-if="loading"><span class="flex items-center gap-2"><i
                                class="fa-solid fa-circle-notch fa-spin text-[12px]"></i> Memproses...</span></template>
                </button>
            </form>

            <div class="space-y-3 pt-4 border-t border-slate-100">
                <a href="{{ route('penerimaan.dashboard') }}"
                    class="w-full flex items-center justify-center gap-2 text-[11.5px] text-slate-500 hover:text-[#0A1931] font-semibold py-2 rounded-lg hover:bg-slate-50 transition">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Dashboard Utama
                </a>
                <p class="text-center text-[10px] text-slate-400 font-mono"><i
                        class="fa-solid fa-shield-halved mr-1"></i> Secure access • DJP Executive v1.0
                </p>
            </div>
        </div>
    </div>

</body>

</html>
