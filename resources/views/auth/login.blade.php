<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - MPNWEB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 font-sans text-xs antialiased">

    <!-- Canvas Card Google Looker Studio Style -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200/90 w-full max-w-sm overflow-hidden">

        <!-- Header Canvas Bar DJP Gold Accent -->
        <div class="bg-blue-950 text-white p-5 border-b-2 border-amber-500 relative">
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-md bg-slate-900/80 text-amber-400 flex items-center justify-center font-bold text-base shrink-0 border border-amber-500/30 shadow-xs">
                    <i class="fa-solid fa-user-shield text-xs"></i>
                </div>
                <div>
                    <h1 class="text-sm font-extrabold text-white tracking-wide uppercase font-sans">Panel Admin MPNWEB
                    </h1>
                    <p class="text-[10px] text-amber-400 font-mono font-medium">Seksi Penjaminan Kualitas Data</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-4">
            @if (session('error'))
                <div
                    class="bg-rose-50 border border-rose-200 text-rose-700 text-xs p-3 rounded-md flex items-center gap-2 font-medium">
                    <i class="fa-solid fa-circle-exclamation text-sm shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label
                        class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required autofocus
                        autocomplete="username"
                        class="w-full bg-slate-50/80 border @error('username') border-rose-500 @else border-slate-300 @enderror text-slate-800 text-xs rounded px-3 py-2 focus:bg-white focus:ring-1 focus:ring-blue-950 focus:border-blue-950 outline-none transition font-medium">
                    @error('username')
                        <span class="text-rose-600 text-[10px] mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label
                        class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Password</label>
                    <input type="password" name="password" required autocomplete="current-password"
                        class="w-full bg-slate-50/80 border @error('password') border-rose-500 @else border-slate-300 @enderror text-slate-800 text-xs rounded-lg px-3 py-2 focus:bg-white focus:ring-1 focus:ring-blue-950 focus:border-blue-950 outline-none transition font-medium">
                    @error('password')
                        <span class="text-rose-600 text-[10px] mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 font-bold py-2.5 rounded text-xs shadow-xs transition flex items-center justify-center gap-2 mt-2 cursor-pointer">
                    <i class="fa-solid fa-right-to-bracket text-xs"></i>
                    <span>Otentikasi Admin</span>
                </button>
            </form>

            <div class="pt-3 border-t border-slate-100 text-center">
                <a href="{{ route('penerimaan.dashboard') }}"
                    class="text-xs text-slate-500 hover:text-blue-950 font-bold transition inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Dashboard Utama
                </a>
            </div>
        </div>
    </div>

</body>

</html>
