@extends('layouts.app')

@section('title', 'Penjagaan Harian - MPNWEB')

@section('content')
    <div class="space-y-5 font-sans" x-data="{
        allFungsiOptions: {{ json_encode($fungsiOptions->toArray()) }},
        selectedFungsi: {{ json_encode(array_values($fungsi)) }},
        toggleAllFungsi(checked) {
            this.selectedFungsi = checked ? [...this.allFungsiOptions] : [];
        }
    }" x-init="$nextTick(() => renderChartHarian())">

        <!-- HEADER & FILTER TOOLBAR -->
        <div
            class="bg-white border border-slate-200/90 rounded-lg p-3 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 shadow-2xs border-t-4 border-t-amber-500">
            <!-- Report Title -->
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-md bg-blue-950 text-amber-400 flex items-center justify-center font-bold text-base shrink-0 shadow-xs border border-blue-900">
                    <i class="fa-solid fa-chart-area text-xs"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-blue-950 leading-tight tracking-tight uppercase">Penjagaan
                        Harian</h1>
                    <p class="text-[11px] text-slate-500 font-medium">Perbandingan harian {{ $tahunIni }} vs
                        {{ $tahunLalu }} (s.d 31 hari)</p>
                </div>
            </div>

            <!-- Filter Controls -->
            <form method="GET" action="{{ route('penerimaan.penjagaan.harian') }}"
                class="flex flex-wrap items-center gap-2">

                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-md px-2 py-1 gap-2">
                    <span class="text-[11px] font-semibold text-slate-600"><i
                            class="fa-regular fa-calendar mr-1 text-amber-500"></i>Bulan:</span>
                    <select name="bulan"
                        class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-0.5 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Alpine Multi-select Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open"
                        class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-md px-2.5 py-1 flex items-center gap-2 hover:bg-slate-100 focus:outline-none focus:border-blue-950 font-medium cursor-pointer">
                        <span class="text-[11px] font-semibold text-slate-600"><i
                                class="fa-solid fa-filter mr-1 text-amber-500"></i>Fungsi:</span>
                        <span class="bg-blue-950 text-amber-400 text-[10px] font-mono font-bold px-1.5 py-0.2 rounded"
                            x-text="selectedFungsi.length"></span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-transition
                        class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-lg shadow-lg z-50 p-3 space-y-2">
                        <div
                            class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider pb-1 border-b border-slate-100">
                            Pilih Fungsi Kantor
                        </div>
                        <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1">
                            <label
                                class="flex items-center gap-2 text-xs font-semibold text-blue-950 hover:bg-slate-50 p-1.5 rounded cursor-pointer border-b border-slate-100 mb-1">
                                <input type="checkbox"
                                    :checked="selectedFungsi.length === allFungsiOptions.length && allFungsiOptions.length > 0"
                                    @change="toggleAllFungsi($event.target.checked)"
                                    class="rounded border-slate-300 text-blue-950 focus:ring-blue-950">
                                <span>Pilih Semua</span>
                            </label>

                            @foreach ($fungsiOptions as $opt)
                                <label
                                    class="flex items-center gap-2 text-xs text-slate-700 hover:bg-slate-50 p-1.5 rounded cursor-pointer select-none">
                                    <input type="checkbox" name="fungsi[]" value="{{ $opt }}"
                                        x-model="selectedFungsi"
                                        class="rounded border-slate-300 text-blue-950 focus:ring-blue-950">
                                    <span class="truncate">{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Tombol Terapkan -->
                <button type="submit"
                    class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-bold px-3.5 py-1 rounded-md transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    <span>Terapkan</span>
                </button>

                <!-- Tombol Reset -->
                @if (request()->has('fungsi'))
                    <a href="{{ route('penerimaan.penjagaan.harian', ['bulan' => $bulan]) }}"
                        class="text-slate-400 hover:text-slate-700 p-1 rounded transition" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                    </a>
                @endif

                <!-- Tombol Export CSV -->
                <a href="{{ route('penerimaan.penjagaan.harian.export-detil', request()->all()) }}"
                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1 rounded-md transition flex items-center gap-1.5 ml-auto">
                    <i class="fa-solid fa-file-excel text-xs text-emerald-600"></i>
                    <span>Export CSV</span>
                </a>
            </form>
        </div>

        <!-- CANVAS TILE GRAPH CONTAINER -->
        <div
            class="bg-white p-5 rounded-lg border border-slate-200/90 shadow-2xs w-full min-w-0 border-t-4 border-t-blue-950">
            <div class="relative w-full h-[400px] min-w-0 block">
                <canvas id="chartHarian"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function renderChartHarian() {
            const el = document.getElementById('chartHarian');
            if (!el) return;

            const existingChart = Chart.getChart(el);
            if (existingChart) {
                existingChart.destroy();
            }

            const ctx = el.getContext('2d');

            // DJP Blue Fill Gradient
            const bgTahunIni = ctx.createLinearGradient(0, 0, 0, 300);
            bgTahunIni.addColorStop(0, 'rgba(15, 23, 42, 0.2)');
            bgTahunIni.addColorStop(1, 'rgba(15, 23, 42, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_values(array_map(fn($d) => "$d", $days))) !!},
                    datasets: [{
                            label: 'Tahun {{ $tahunIni }}',
                            data: {!! json_encode(array_values($dataTahunIni)) !!},
                            borderColor: '#0f172a',
                            backgroundColor: bgTahunIni,
                            borderWidth: 2.5,
                            pointRadius: 2,
                            pointHoverRadius: 6,
                            tension: 0.35,
                            fill: true
                        },
                        {
                            label: 'Tahun {{ $tahunLalu }}',
                            data: {!! json_encode(array_values($dataTahunLalu)) !!},
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.05)',
                            borderWidth: 2,
                            pointRadius: 2,
                            pointHoverRadius: 6,
                            tension: 0.35,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    resizeDelay: 100,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: {
                                    family: 'Inter',
                                    size: 11,
                                    weight: '700'
                                },
                                color: '#0f172a'
                            }
                        },
                        tooltip: {
                            padding: 12,
                            cornerRadius: 6,
                            backgroundColor: '#0f172a',
                            titleFont: {
                                family: 'Inter',
                                size: 12,
                                weight: 'bold'
                            },
                            bodyFont: {
                                family: 'JetBrains Mono',
                                size: 11
                            },
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID')
                                        .format(context.raw || 0);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: 11,
                                    weight: '600'
                                },
                                color: '#64748b'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(226, 232, 240, 0.8)'
                            },
                            ticks: {
                                font: {
                                    family: 'JetBrains Mono',
                                    size: 10,
                                    weight: '500'
                                },
                                color: '#64748b',
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                        notation: "compact"
                                    }).format(value);
                                }
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', renderChartHarian);
        window.addEventListener('resize', () => {
            const chart = Chart.getChart('chartHarian');
            if (chart) chart.resize();
        });
    </script>
@endpush
