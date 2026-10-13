@props([
    'code' => '500',
    'title' => 'Terjadi Kendala',
    'subtitle' => 'DJP Executive',
    'icon' => 'fa-triangle-exclamation',
    'iconBg' => 'bg-rose-50',
    'iconBorder' => 'border-rose-200',
    'iconColor' => 'text-rose-600',
    'showLog' => false,
    'logBase64' => null,
    'logFileName' => null,
    'timestamp' => null,
])

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code }} - {{ $title }} - MPNWEB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
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
                    },
                    colors: {
                        djp: {
                            navy: '#0A1931',
                            navyHover: '#112A46',
                            navyBorder: '#1a365d',
                            gold: '#fbbf24',
                            goldLight: '#facc15'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body
    class="bg-slate-100 min-h-screen flex items-center justify-center p-4 font-sans antialiased selection:bg-djp-gold selection:text-djp-navy">

    <div
        class="w-full max-w-[520px] bg-white rounded-xl shadow-[0_8px_30px_rgba(10,25,49,0.12)] border border-slate-200/80 overflow-hidden">
        {{-- Header Navy DJP - Modular --}}
        <div class="bg-djp-navy px-6 py-4 border-b border-djp-navyBorder relative">
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-lg bg-djp-navyHover border border-djp-gold/30 text-djp-gold flex items-center justify-center">
                    <i class="fa-solid fa-chart-column text-sm"></i>
                </div>
                <div>
                    <div class="text-[13px] font-extrabold text-white tracking-wider uppercase">MPNWEB</div>
                    <div class="text-[10px] text-djp-gold font-mono font-bold tracking-wider">{{ $code }} •
                        {{ $subtitle }}</div>
                </div>
            </div>
            <div
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-djp-gold via-amber-500 to-djp-gold">
            </div>
        </div>

        <div class="p-6 md:p-7 space-y-5">
            {{-- Icon --}}
            <div class="text-center space-y-3">
                <div
                    class="mx-auto w-16 h-16 {{ $iconBg }} border {{ $iconBorder }} {{ $iconColor }} rounded-xl flex items-center justify-center text-2xl">
                    <i class="fa-solid {{ $icon }}"></i>
                </div>
                <div class="space-y-1">
                    <h1 class="text-[18px] font-extrabold text-djp-navy tracking-tight">{{ $title }}</h1>
                    <div class="text-[12.5px] text-slate-500 font-medium leading-relaxed">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            {{ $extra ?? '' }}

            {{-- Log Section - Hanya untuk 500 & 503 --}}
            @if ($showLog)
                <x-error.log-actions :logBase64="$logBase64" :logFileName="$logFileName" :timestamp="$timestamp" />
            @endif

            {{-- Footer Actions --}}
            <div
                class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px]">
                <a href="{{ url('/') }}"
                    class="text-slate-500 hover:text-djp-navy font-semibold inline-flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Dashboard
                </a>
                <span class="font-mono text-[10px] text-slate-400"><i class="fa-solid fa-shield-halved mr-1"></i> Error
                    {{ $code }} • DJP Executive v1.0</span>
            </div>
        </div>
    </div>

    @if ($timestamp)
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const el = document.getElementById('error-time');
                if (el && el.dataset.utc) {
                    try {
                        const date = new Date(el.dataset.utc);
                        if (!isNaN(date.getTime())) {
                            const tzName = new Intl.DateTimeFormat('id-ID', {
                                timeZoneName: 'short'
                            }).formatToParts(date).find(p => p.type === 'timeZoneName')?.value || 'WIB';
                            const fmt = date.toLocaleString('id-ID', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit',
                                hour12: false
                            }).replace(/\./g, ':');
                            el.innerText = `${fmt} ${tzName}`;
                        }
                    } catch (e) {}
                }
            });
        </script>
    @endif
</body>

</html>
