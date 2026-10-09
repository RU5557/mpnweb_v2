<?php

namespace App\Http\Controllers;

use App\Repositories\PkmPengawasanRepository;
use App\Services\CsvExportService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PkmPengawasanController extends Controller
{
    public function __construct(
        protected PkmPengawasanRepository $repository,
        protected CsvExportService $csvExportService
    ) {}

    public function index(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $seksiFilter = trim((string) $request->input('seksi', ''));
        $sortColumn = (string) $request->input('sort', 'nama_seksi');
        $sortDirection = (string) $request->input('direction', 'asc');

        try {
            $pkmData = $this->repository->getSummaryPkm($tahun, $bulan, $seksiFilter, $sortColumn, $sortDirection);
            $daftarSeksi = $this->repository->getDaftarSeksi();
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary PKM Pengawasan.', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data PKM Pengawasan sedang tidak tersedia. Silakan coba lagi.');
        }

        return view('pkm.pengawasan', compact(
            'pkmData',
            'daftarSeksi',
            'sortColumn',
            'sortDirection',
            'tahun',
            'bulan',
            'seksiFilter'
        ));
    }

    public function exportDetil(Request $request): StreamedResponse
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $seksiFilter = trim((string) $request->input('seksi', ''));

        $filename = "detil_pkm_pengawasan_{$tahun}_{$bulan}.csv";
        $columnsMap = [
            'npwp15' => 'NPWP',
            'nama_wp' => 'NAMA WP',
            'nama_seksi' => 'SEKSI',
            'nama_ar' => 'NAMA AR',
            'fungsi' => 'FUNGSI',
            'kd_map' => 'KD MAP',
            'kd_bayar' => 'KD BAYAR',
            'bln_setor' => 'BULAN',
            'thn_setor' => 'TAHUN',
            'jml_setor' => 'JUMLAH SETOR',
        ];

        $query = $this->repository->getExportDetilQuery($tahun, $bulan, $seksiFilter);

        return $this->csvExportService->exportFromQuery($filename, $columnsMap, $query);
    }

    private function resolvePeriod(Request $request): array
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $bulan = (int) $request->input('bulan', date('n'));

        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = (int) date('Y');
        }

        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) date('n');
        }

        return [$tahun, $bulan];
    }
}
