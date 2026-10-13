@extends('layouts.app')
@section('title', 'Penjagaan Bulanan - MPNWEB')
@section('content')
    <div class="space-y-4 font-sans" x-data="{
        allFungsiOptions: {{ json_encode($fungsiOptions->toArray()) }},
        selectedFungsi: {{ json_encode(array_values($fungsi)) }},
        openFungsi: false,
        toggleAllFungsi(checked) { this.selectedFungsi = checked ? [...this.allFungsiOptions] : [] }
    }">

        <x-layout.page-header title="Penjagaan Bulanan"
            subtitle="Perbandingan penerimaan per bulan {{ $tahunIni }} vs {{ $tahunLalu }}" icon="fa-chart-line">
            <form method="GET" action="{{ route('penerimaan.penjagaan.bulanan') }}" class="flex items-center gap-2">
                <div class="relative">
                    <button type="button" @click="openFungsi=!openFungsi"
                        class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-md px-2.5 py-1.5 flex items-center gap-2 hover:bg-slate-100 font-medium">
                        <span class="text-xs font-semibold text-slate-600"><i
                                class="fa-solid fa-filter mr-1 text-amber-500"></i>Fungsi:</span>
                        <span class="bg-blue-950 text-amber-400 text-xs font-mono font-bold px-1.5 py-0.5 rounded"
                            x-text="selectedFungsi.length"></span>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                    </button>
                    <div x-show="openFungsi" @click.outside="openFungsi=false" x-transition x-cloak
                        class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-lg shadow-lg z-50 p-3 space-y-2">
                        <div
                            class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-1 border-b border-slate-100">
                            Pilih Fungsi Kantor</div>
                        <div class="max-h-56 overflow-y-auto space-y-1 pr-1">
                            <label
                                class="flex items-center gap-2 text-xs font-semibold text-blue-950 hover:bg-slate-50 p-1.5 rounded cursor-pointer border-b border-slate-100 mb-1">
                                <input type="checkbox"
                                    :checked="selectedFungsi.length === allFungsiOptions.length && allFungsiOptions.length > 0"
                                    @change="toggleAllFungsi($event.target.checked)"
                                    class="rounded border-slate-300 text-blue-950">
                                <span>Pilih Semua</span>
                            </label>
                            @foreach ($fungsiOptions as $opt)
                                <label
                                    class="flex items-center gap-2 text-xs text-slate-700 hover:bg-slate-50 p-1.5 rounded cursor-pointer">
                                    <input type="checkbox" name="fungsi[]" value="{{ $opt }}"
                                        x-model="selectedFungsi" class="rounded border-slate-300 text-blue-950">
                                    <span class="truncate">{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button type="submit"
                    class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-bold px-3 py-1.5 rounded-md transition shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-filter text-xs"></i><span>Terapkan</span>
                </button>
                @if (request()->has('fungsi'))
                    <a href="{{ route('penerimaan.penjagaan.bulanan') }}"
                        class="text-slate-400 hover:text-slate-700 p-1.5 rounded transition" title="Reset"><i
                            class="fa-solid fa-rotate-left text-xs"></i></a>
                @endif
                <a href="{{ route('penerimaan.penjagaan.bulanan.export-detil', request()->all()) }}"
                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1.5 rounded-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-file-excel text-xs text-emerald-600"></i><span>Export CSV</span>
                </a>
            </form>
        </x-layout.page-header>

        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm w-full border-t-4 border-t-blue-950">
            <div class="relative w-full h-96 block"><canvas id="chartBulanan"></canvas></div>
        </div>

    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('chartBulanan');
            if (!el) return;
            const existing = Chart.getChart(el);
            if (existing) existing.destroy();
            const ctx = el.getContext('2d');
            const gradIni = ctx.createLinearGradient(0, 0, 0, 400);
            gradIni.addColorStop(0, 'rgba(15,23,42,0.95)');
            gradIni.addColorStop(1, 'rgba(30,58,138,0.85)');
            const gradLalu = ctx.createLinearGradient(0, 0, 0, 400);
            gradLalu.addColorStop(0, 'rgba(245,158,11,0.9)');
            gradLalu.addColorStop(1, 'rgba(252,211,77,0.6)');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($months) !!},
                    datasets: [{
                        label: 'Tahun {{ $tahunIni }}',
                        data: {!! json_encode(array_values($dataTahunIni)) !!},
                        backgroundColor: gradIni,
                        borderRadius: 4,
                        categoryPercentage: 0.6,
                        barPercentage: 0.7
                    }, {
                        label: 'Tahun {{ $tahunLalu }}',
                        data: {!! json_encode(array_values($dataTahunLalu)) !!},
                        backgroundColor: gradLalu,
                        borderRadius: 4,
                        categoryPercentage: 0.6,
                        barPercentage: 0.7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
                            callbacks: {
                                label: function(c) {
                                    return c.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID')
                                        .format(c.raw || 0);
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
                                color: 'rgba(226,232,240,0.8)'
                            },
                            ticks: {
                                font: {
                                    family: 'JetBrains Mono',
                                    size: 10,
                                    weight: '500'
                                },
                                color: '#64748b',
                                callback: function(v) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                        notation: 'compact'
                                    }).format(v);
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
