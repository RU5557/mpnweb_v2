<?php

namespace App\Http\Controllers;

use App\Repositories\DashboardRepository;
use App\Services\CsvExportService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardRepository $dashboardRepository,
        protected CsvExportService $csvExportService
    ) {}

    public function index(Request $request)
    {
        [$thnIni, $blnAwal, $blnAkhir] = $this->resolvePeriod($request);

        try {
            $target = $this->dashboardRepository->getTargetByTahun($thnIni);
            $penerimaanData = $this->dashboardRepository->getSummaryMetrics($thnIni, $blnAwal, $blnAkhir);
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary dashboard.', [
                'tahun' => $thnIni,
                'bulan_awal' => $blnAwal,
                'bulan_akhir' => $blnAkhir,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data dashboard sedang tidak tersedia. Silakan coba lagi.');
        }

        // Extrak Nilai Nominal
        $penerimaanSaatIni = $penerimaanData->penerimaanSaatIni ?? 0;
        $penerimaanBlnLalu = $penerimaanData->penerimaanBlnLalu ?? 0;
        $penerimaanThnLalu = $penerimaanData->penerimaanThnLalu ?? 0;

        $realisasiPPM = $penerimaanData->realisasiPPM ?? 0;
        $realisasiPKM = $penerimaanData->realisasiPKM ?? 0;
        $realisasiPBP = $penerimaanData->realisasiPBP ?? 0;

        $realisasiPengawasan = $penerimaanData->realisasiPengawasan ?? 0;
        $realisasiPemeriksaan = $penerimaanData->realisasiPemeriksaan ?? 0;
        $realisasiPenagihan = $penerimaanData->realisasiPenagihan ?? 0;

        $realisasiPPMLalu = $penerimaanData->realisasiPPMLalu ?? 0;
        $realisasiPKMLalu = $penerimaanData->realisasiPKMLalu ?? 0;
        $realisasiPengawasanLalu = $penerimaanData->realisasiPengawasanLalu ?? 0;
        $realisasiPemeriksaanLalu = $penerimaanData->realisasiPemeriksaanLalu ?? 0;
        $realisasiPenagihanLalu = $penerimaanData->realisasiPenagihanLalu ?? 0;

        // Kalkulasi Indikator & Growth
        $targetKantor = $target?->target_kantor ?? 0;
        $capaianKantor = $targetKantor > 0 ? ($penerimaanSaatIni / $targetKantor) * 100 : 0;

        $growthMoM = $penerimaanBlnLalu != 0 ? (($penerimaanSaatIni - $penerimaanBlnLalu) / abs($penerimaanBlnLalu)) * 100 : 0;
        $growthYoY = $penerimaanThnLalu != 0 ? (($penerimaanSaatIni - $penerimaanThnLalu) / abs($penerimaanThnLalu)) * 100 : 0;

        // Helper perhitungan persen & growth
        $calcPersen = fn ($targetVal, $realisasiVal) => ($targetVal ?? 0) > 0 ? ($realisasiVal / $targetVal) * 100 : 0;
        $calcGrowth = fn ($realisasiIni, $realisasiLalu) => $realisasiLalu != 0 ? (($realisasiIni - $realisasiLalu) / abs($realisasiLalu)) * 100 : 0;

        // Metrics Card Configs
        $metrics = [
            'ppm' => [
                'target' => $target?->target_ppm ?? 0,
                'realisasi' => $realisasiPPM,
                'persen' => $calcPersen($target?->target_ppm, $realisasiPPM),
                'growthYoY' => $calcGrowth($realisasiPPM, $realisasiPPMLalu),
            ],
            'pkm' => [
                'target' => $target?->target_pkm ?? 0,
                'realisasi' => $realisasiPKM,
                'persen' => $calcPersen($target?->target_pkm, $realisasiPKM),
                'growthYoY' => $calcGrowth($realisasiPKM, $realisasiPKMLalu),
            ],
            'pbp' => [
                'target' => $target?->target_pbp ?? 0,
                'realisasi' => $realisasiPBP,
                'persen' => $calcPersen($target?->target_pbp, $realisasiPBP),
                'sisa' => max(0, ($target?->target_pbp ?? 0) - $realisasiPBP),
            ],
            'pengawasan' => [
                'target' => $target?->target_pkm_pengawasan ?? 0,
                'realisasi' => $realisasiPengawasan,
                'persen' => $calcPersen($target?->target_pkm_pengawasan, $realisasiPengawasan),
                'growthYoY' => $calcGrowth($realisasiPengawasan, $realisasiPengawasanLalu),
            ],
            'pemeriksaan' => [
                'target' => $target?->target_pkm_pemeriksaan ?? 0,
                'realisasi' => $realisasiPemeriksaan,
                'persen' => $calcPersen($target?->target_pkm_pemeriksaan, $realisasiPemeriksaan),
                'growthYoY' => $calcGrowth($realisasiPemeriksaan, $realisasiPemeriksaanLalu),
            ],
            'penagihan' => [
                'target' => $target?->target_pkm_penagihan ?? 0,
                'realisasi' => $realisasiPenagihan,
                'persen' => $calcPersen($target?->target_pkm_penagihan, $realisasiPenagihan),
                'growthYoY' => $calcGrowth($realisasiPenagihan, $realisasiPenagihanLalu),
            ],
        ];

        $listBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('penerimaan.dashboard', compact(
            'thnIni',
            'blnAwal',
            'blnAkhir',
            'listBulan',
            'capaianKantor',
            'penerimaanSaatIni',
            'penerimaanBlnLalu',
            'penerimaanThnLalu',
            'growthMoM',
            'growthYoY',
            'metrics'
        ));
    }

    public function exportDetil(Request $request): StreamedResponse
    {
        [$tahun, $bulanAwal, $bulanAkhir] = $this->resolvePeriod($request);

        $tahunLalu = $tahun - 1;
        $tahunFilter = [$tahunLalu, $tahun];
        $filename = "dashboard_detil_{$tahunLalu}_{$tahun}_Jan_sd_Bln_{$bulanAkhir}.csv";

        $columnsMap = [
            'id' => 'ID',
            'kd_kanwil' => 'KD Kanwil',
            'kpp_adm' => 'KPP Adm',
            'npwp' => 'NPWP',
            'kpp' => 'KPP',
            'cabang' => 'Cabang',
            'no_produk_hukum' => 'No Produk Hukum',
            'npwp15' => 'NPWP15',
            'nama_wp' => 'Nama WP',
            'no_pbk' => 'No PBK',
            'ntpn' => 'NTPN',
            'tgl_setor' => 'Tgl Setor',
            'thn_setor' => 'Thn Setor',
            'bln_setor' => 'Bln Setor',
            'thn_pajak' => 'Thn Pajak',
            'masa1' => 'Masa 1',
            'masa2' => 'Masa 2',
            'jml_setor' => 'Jml Setor (Rp)',
            'kd_map' => 'Kode MAP',
            'kd_bayar' => 'Kode Bayar',
            'fungsi' => 'Fungsi',
            'jenis' => 'Jenis',
            'flag_skp' => 'Flag SKP',
            'id_sbr_data' => 'ID Sbr Data',
            'tipe' => 'Tipe',
        ];

        $query = $this->dashboardRepository->getExportDetilQuery(
            $tahunFilter,
            $bulanAwal,
            $bulanAkhir,
            array_keys($columnsMap)
        );

        return $this->csvExportService->exportFromQuery($filename, $columnsMap, $query, 2000);
    }

    private function resolvePeriod(Request $request): array
    {
        $currentYear = (int) date('Y');
        $tahun = (int) $request->input('tahun', $currentYear);

        $bulanAwal = (int) $request->input('bulan_awal', 1);
        $bulanAkhir = (int) $request->input('bulan_akhir', $request->input('bulan', date('n')));

        if ($tahun < 2000 || $tahun > $currentYear + 1) {
            $tahun = $currentYear;
        }

        if ($bulanAwal < 1 || $bulanAwal > 12) {
            $bulanAwal = 1;
        }

        if ($bulanAkhir < 1 || $bulanAkhir > 12) {
            $bulanAkhir = (int) date('n');
        }

        if ($bulanAwal > $bulanAkhir) {
            $bulanAwal = $bulanAkhir;
        }

        return [$tahun, $bulanAwal, $bulanAkhir];
    }
}
