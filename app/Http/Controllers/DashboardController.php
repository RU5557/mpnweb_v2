<?php

namespace App\Http\Controllers;

use App\Repositories\DashboardRepository;
use App\Services\CsvExportService;
use App\Services\DashboardService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardRepository $dashboardRepository,
        protected DashboardService $dashboardService,
        protected CsvExportService $csvExportService
    ) {}

    /**
     * Dashboard Ringkasan Penerimaan
     * Thin Controller - semua kalkulasi di DashboardService
     * Query di DashboardRepository (sesuai .airules)
     */
    public function index(Request $request)
    {
        [$thnIni, $blnAwal, $blnAkhir] = $this->dashboardService->resolvePeriod($request->all());

        try {
            $target = $this->dashboardRepository->getTargetByTahun($thnIni);
            $summary = $this->dashboardRepository->getSummaryMetrics($thnIni, $blnAwal, $blnAkhir);
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary dashboard.', [
                'tahun' => $thnIni, 'bulan_awal' => $blnAwal, 'bulan_akhir' => $blnAkhir,
                'message' => $e->getMessage(),
            ]);
            abort(503, 'Data dashboard sedang tidak tersedia. Silakan coba lagi.');
        }

        $metricsData = $this->dashboardService->buildMetrics($summary, $target);
        $listBulan = $this->dashboardService->getListBulan();

        // Kirim terstruktur, bukan 12 variabel terpisah (hemat memory view)
        return view('penerimaan.dashboard', [
            'thnIni' => $thnIni,
            'blnAwal' => $blnAwal,
            'blnAkhir' => $blnAkhir,
            'listBulan' => $listBulan,
            'summary' => $metricsData['summary'],
            'cards' => $metricsData['cards'],
            // Backward compatibility untuk blade lama (optional)
            'penerimaanSaatIni' => $metricsData['summary']['penerimaanSaatIni'],
            'penerimaanBlnLalu' => $metricsData['summary']['penerimaanBlnLalu'],
            'penerimaanThnLalu' => $metricsData['summary']['penerimaanThnLalu'],
            'capaianKantor' => $metricsData['summary']['capaianKantor'],
            'growthMoM' => $metricsData['summary']['growthMoM'],
            'growthYoY' => $metricsData['summary']['growthYoY'],
            'metrics' => collect($metricsData['cards'])->map(fn ($c) => [
                'target' => $c['target'], 'realisasi' => $c['realisasi'],
                'persen' => $c['persen'], 'growthYoY' => $c['growthYoY'] ?? 0, 'sisa' => $c['sisa'] ?? 0,
            ])->toArray(),
        ]);
    }

    /**
     * Export CSV - sesuai .airules pakai Service + Trait
     */
    public function exportDetil(Request $request): StreamedResponse
    {
        [$tahun, $bulanAwal, $bulanAkhir] = $this->dashboardService->resolvePeriod($request->all());

        $tahunLalu = $tahun - 1;
        $filename = "detil_dashboard_{$tahunLalu}_s.d._{$tahun}_{$bulanAwal}_s.d._{$bulanAkhir}.csv";

        $columnsMap = [
            'id' => 'ID', 'kd_kanwil' => 'KD Kanwil', 'kpp_adm' => 'KPP Adm',
            'npwp' => 'NPWP', 'kpp' => 'KPP', 'cabang' => 'Cabang',
            'no_produk_hukum' => 'No Produk Hukum', 'npwp15' => 'NPWP15',
            'nama_wp' => 'Nama WP', 'no_pbk' => 'No PBK', 'ntpn' => 'NTPN',
            'tgl_setor' => 'Tgl Setor', 'thn_setor' => 'Thn Setor',
            'bln_setor' => 'Bln Setor', 'thn_pajak' => 'Thn Pajak',
            'masa1' => 'Masa 1', 'masa2' => 'Masa 2', 'jml_setor' => 'Jml Setor (Rp)',
            'kd_map' => 'Kode MAP', 'kd_bayar' => 'Kode Bayar',
            'fungsi' => 'Fungsi', 'jenis' => 'Jenis', 'flag_skp' => 'Flag SKP',
            'id_sbr_data' => 'ID Sbr Data', 'tipe' => 'Tipe',
        ];

        $query = $this->dashboardRepository->getExportDetilQuery(
            [$tahunLalu, $tahun], $bulanAwal, $bulanAkhir, array_keys($columnsMap)
        );

        return $this->csvExportService->exportFromQuery($filename, $columnsMap, $query, 2000);
    }
}
