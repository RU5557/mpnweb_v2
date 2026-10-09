<?php

namespace App\Http\Controllers;

use App\Repositories\PkmPemeriksaanRepository;
use App\Services\CsvExportService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PkmPemeriksaanController extends Controller
{
    public function __construct(
        protected PkmPemeriksaanRepository $repository,
        protected CsvExportService $csvExportService
    ) {}

    public function index(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $search = trim((string) $request->input('search', ''));
        $sortColumn = (string) $request->input('sort', 'total_akt_pemeriksaan');
        $sortDirection = (string) $request->input('direction', 'desc');
        $page = max(1, (int) $request->input('page', 1));

        try {
            $pkmData = $this->repository->getPaginatedPkm(
                $tahun,
                $bulan,
                $search,
                $sortColumn,
                $sortDirection,
                $page
            );
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary PKM Pemeriksaan.', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'search' => $search,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data PKM Pemeriksaan sedang tidak tersedia. Silakan coba lagi.');
        }

        return view('pkm.pemeriksaan', compact(
            'pkmData',
            'sortColumn',
            'sortDirection',
            'tahun',
            'bulan'
        ));
    }

    public function exportDetil(Request $request): StreamedResponse
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $search = trim((string) $request->input('search', ''));

        $filename = "detil_pkm_pemeriksaan_{$tahun}_{$bulan}.csv";
        $columnsMap = [
            'npwp15' => 'NPWP',
            'nama_wp' => 'NAMA WP',
            'kd_klu' => 'KD KLU',
            'nm_klu' => 'NAMA KLU',
            'kd_map' => 'KD MAP',
            'kd_bayar' => 'KD BAYAR',
            'fungsi' => 'FUNGSI',
            'bln_setor' => 'BULAN',
            'thn_setor' => 'TAHUN',
            'jml_setor' => 'JUMLAH SETOR',
        ];

        $query = $this->repository->getExportDetilQuery($tahun, $bulan, $search);

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
