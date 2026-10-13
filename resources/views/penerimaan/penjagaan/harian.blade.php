@extends('layouts.app')
@section('title', 'Penjagaan Harian - MPNWEB')
@section('content')
    <div class="space-y-4 font-sans" x-data="{ allFungsiOptions: {{ json_encode($fungsiOptions->toArray()) }}, selectedFungsi: {{ json_encode(array_values($fungsi)) }}, openFungsi: false, toggleAllFungsi(c) { this.selectedFungsi = c ? [...this.allFungsiOptions] : [] } }">

        <x-layout.page-header title="Penjagaan Harian"
            subtitle="Perbandingan harian bulan {{ $bulan }} - {{ $tahunIni }} vs {{ $tahunLalu }} (1-{{ count($days) }} hari)"
            icon="fa-chart-area">
            <form method="GET" action="{{ route('penerimaan.penjagaan.harian') }}" class="flex items-center gap-2">
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-md px-2 py-1 gap-2">
                    <span class="text-xs font-semibold text-slate-600"><i
                            class="fa-regular fa-calendar mr-1 text-amber-500"></i>Bulan:</span>
                    <select name="bulan"
                        class="bg-white border border-slate-300 text-slate-800 text-xs rounded px-2 py-0.5 font-medium focus:outline-none focus:border-blue-950 cursor-pointer">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                        @endfor
                    </select>
                </div>
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
                        <div class="text-xs font-extrabold text-slate-400 uppercase pb-1 border-b">Pilih Fungsi</div>
                        <div class="max-h-56 overflow-y-auto space-y-1 pr-1">
                            <label
                                class="flex items-center gap-2 text-xs font-semibold text-blue-950 hover:bg-slate-50 p-1.5 rounded cursor-pointer border-b mb-1"><input
                                    type="checkbox" :checked="selectedFungsi.length === allFungsiOptions.length"
                                    @change="toggleAllFungsi($event.target.checked)"
                                    class="rounded border-slate-300 text-blue-950"><span>Pilih Semua</span></label>
                            @foreach ($fungsiOptions as $opt)
                                <label
                                    class="flex items-center gap-2 text-xs text-slate-700 hover:bg-slate-50 p-1.5 rounded cursor-pointer"><input
                                        type="checkbox" name="fungsi[]" value="{{ $opt }}"
                                        x-model="selectedFungsi" class="rounded border-slate-300 text-blue-950"><span
                                        class="truncate">{{ $opt }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button type="submit"
                    class="bg-blue-950 hover:bg-slate-900 text-amber-400 border border-amber-500/30 text-xs font-bold px-3 py-1.5 rounded-md flex items-center gap-1.5"><i
                        class="fa-solid fa-filter text-xs"></i><span>Terapkan</span></button>
                @if (request()->has('fungsi'))
                    <a href="{{ route('penerimaan.penjagaan.harian', ['bulan' => $bulan]) }}"
                        class="text-slate-400 hover:text-slate-700 p-1.5 rounded" title="Reset"><i
                            class="fa-solid fa-rotate-left text-xs"></i></a>
                @endif
                <a href="{{ route('penerimaan.penjagaan.harian.export-detil', request()->all()) }}"
                    class="bg-white hover:bg-slate-50 text-blue-950 border border-slate-300 text-xs font-bold px-3 py-1.5 rounded-md flex items-center gap-1.5"><i
                        class="fa-solid fa-file-excel text-xs text-emerald-600"></i><span>Export</span></a>
            </form>
        </x-layout.page-header>

        @php
            $tIni = $totalIni ?? array_sum($dataTahunIni);
            $tLalu = $totalLalu ?? array_sum($dataTahunLalu);
            $growth = $growth ?? ($tLalu != 0 ? (($tIni - $tLalu) / $tLalu) * 100 : 0);
        @endphp

        <x-penjagaan-chips :fungsi="$fungsi" :totalIni="$tIni" :totalLalu="$tLalu" :bulan="$bulan" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <x-penjagaan-mini-card
                title="Total {{ DateTime::createFromFormat('!m', $bulan)->format('F') }} {{ $tahunIni }}"
                :value="$tIni" icon="fa-calendar-day" :growth="$growth" type="primary" />
            <x-penjagaan-mini-card
                title="Total {{ DateTime::createFromFormat('!m', $bulan)->format('F') }} {{ $tahunLalu }}"
                :value="$tLalu" icon="fa-clock-rotate-left" type="secondary" />
            <x-penjagaan-mini-card title="Selisih Harian" :value="$tIni - $tLalu" icon="fa-arrow-trend-up" :growth="$growth"
                type="secondary" />
        </div>

        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm border-t-4 border-t-blue-950">
            <div class="relative w-full h-96 block"><canvas id="chartHarian"></canvas></div>
        </div>

    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/penjagaan.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            renderChartHarian('chartHarian', {!! json_encode($days) !!}, {!! json_encode(array_values($dataTahunIni)) !!},
                {!! json_encode(array_values($dataTahunLalu)) !!}, 'Tahun {{ $tahunIni }}', 'Tahun {{ $tahunLalu }}');
        });
    </script>
@endpush
