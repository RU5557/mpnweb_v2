<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MPNWEB — DJP Executive Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    },
                    colors: {
                        djp: {
                            navy: '#0A1931',
                            navyHover: '#112A46',
                            navyBorder: '#1a365d',
                            navyLight: '#1e3a5f',
                            gold: '#fbbf24',
                            goldLight: '#facc15',
                            goldDark: '#d97706',
                        }
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

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8
        }
    </style>
    @stack('styles')
</head>

<body x-data="appLayout()" x-init="init()"
    class="font-sans text-slate-800 text-xs antialiased h-full flex bg-slate-100 selection:bg-djp-gold selection:text-djp-navy">
    @include('layouts.partials.sidebar')
    <div :class="isExpanded ? 'ml-60' : 'ml-16'"
        class="flex-grow transition-all duration-300 ease-in-out flex flex-col min-h-screen min-w-0">
        @include('layouts.partials.topbar')
        <main class="px-4 sm:px-6 py-6 flex-grow bg-slate-100/80 min-w-0">
            <div class="max-w-7xl mx-auto w-full min-w-0 flex flex-col gap-6">
                <x-layout.flash />
                @yield('content')
            </div>
        </main>
        <x-layout.footer />
    </div>
    @stack('scripts')
    <script>
        function appLayout() {
            return {
                sidebarOpen: JSON.parse(localStorage.getItem('mpnweb_sidebar_open') ?? 'true'),
                isPinned: JSON.parse(localStorage.getItem('mpnweb_sidebar_pinned') ?? 'true'),
                get isExpanded() {
                    return this.sidebarOpen || this.isPinned;
                },
                init() {
                    this.$watch('sidebarOpen', v => localStorage.setItem('mpnweb_sidebar_open', JSON.stringify(v)));
                    this.$watch('isPinned', v => {
                        localStorage.setItem('mpnweb_sidebar_pinned', JSON.stringify(v));
                        this.sidebarOpen = v;
                    });
                },
                handleMouseEnter() {
                    if (!this.isPinned) {
                        this.sidebarOpen = true;
                        this.$nextTick(() => window.dispatchEvent(new Event('resize')));
                    }
                },
                handleMouseLeave() {
                    if (!this.isPinned) {
                        this.sidebarOpen = false;
                        this.$nextTick(() => window.dispatchEvent(new Event('resize')));
                    }
                }
            }
        }
    </script>
</body>

</html>
