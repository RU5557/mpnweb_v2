<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100" x-data="{
    sidebarOpen: false,
    isPinned: false,
    handleMouseEnter() {
        if (!this.isPinned) {
            this.sidebarOpen = true;
            $nextTick(() => window.dispatchEvent(new Event('resize')));
        }
    },
    handleMouseLeave() {
        if (!this.isPinned) {
            this.sidebarOpen = false;
            $nextTick(() => window.dispatchEvent(new Event('resize')));
        }
    }
}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MPNWEB — DJP Executive Dashboard')</title>

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Global JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js (Defer) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar halus */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    @stack('styles')
</head>

<body
    class="font-sans text-slate-800 text-xs antialiased h-full flex bg-slate-100 selection:bg-amber-400 selection:text-blue-950">

    <!-- ==================== SIDEBAR ==================== -->
    @include('layouts.partials.sidebar')

    <!-- ==================== CONTENT WRAPPER ==================== -->
    <div :class="sidebarOpen || isPinned ? 'ml-60' : 'ml-16'"
        class="flex-grow transition-all duration-300 ease-in-out flex flex-col min-h-screen min-w-0">

        <!-- TOPBAR -->
        @include('layouts.partials.topbar')

        <!-- MAIN CONTENT CONTAINER -->
        <main class="px-4 sm:px-6 py-6 flex-grow bg-slate-100/80 min-w-0">
            <div class="max-w-7xl mx-auto w-full min-w-0 space-y-6">

                {{-- Flash Message Success (Otomatis hilang dalam 5 detik) --}}
                @if (session('success'))
                    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs px-4 py-3 rounded-lg flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2.5 font-medium">
                            <span class="p-1 bg-emerald-100 text-emerald-700 rounded-md">
                                <i class="fa-solid fa-circle-check text-sm"></i>
                            </span>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-800 transition p-1">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                @endif

                {{-- View Content --}}
                @yield('content')
            </div>
        </main>

        <!-- FOOTER HALAMAN -->
        <footer
            class="px-6 py-3 border-t border-slate-200 bg-white text-slate-400 text-[11px] flex justify-between items-center">
            <div>
                <span class="font-extrabold text-blue-950">Direktorat Jenderal Pajak</span> &copy; {{ date('Y') }}
                MPNWEB. All rights reserved.
            </div>
            <div class="flex items-center gap-2 font-mono text-[10px]">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-slate-500 font-medium">System Normal</span>
            </div>
        </footer>
    </div>

    <!-- Push Script Stack -->
    @stack('scripts')
</body>

</html>
