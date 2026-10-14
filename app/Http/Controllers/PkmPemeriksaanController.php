<?php

namespace App\Http\Controllers;

use App\Repositories\PkmPemeriksaanRepository;
use App\Services\CsvExportService;
use App\Services\PkmService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PkmPemeriksaanController extends Controller
{
    public function __construct(protected PkmPemeriksaanRepository $repository, protected CsvExportService $csvExportService, protected PkmService $pkmService) {}

    public function index(Request $request)
    {
        [$tahun,$bulan] = $this->pkmService->resolvePeriod($request);
        $search = $this->pkmService->resolveSearch($request);
        $sortColumn = (string) $request->input('sort', 'total_akt_pemeriksaan');
        $sortDirection = (string) $request->input('direction', 'desc');
        $page = max(1, (int) $request->input('page', 1));

        try {
            // Halaman utama - paginate 15 (hemat RAM 8GB & I/O HDD)
            $pkmData = $this->repository->getPaginatedPkm($tahun, $bulan, $search, $sortColumn, $sortDirection, $page, 15);
            $totalSaatIni = $this->repository->getTotalForComparison($tahun, $bulan, $search);

            // Bulan lalu - ambil TOTAL akurat (bukan cuma halaman pertama)
            // Pakai getTotalForComparison yang sum langsung di MariaDB 10.4 pakai idx_pkm_lookup - hemat memory
            [$tahunLalu, $bulanLalu] = $this->pkmService->getBulanLaluLabel($tahun, $bulan);
            $totalBulanLalu = $this->repository->getTotalForComparison($tahunLalu, $bulanLalu, $search);

            $comparison = $this->pkmService->calcComparison($totalSaatIni, $totalBulanLalu);

            // Untuk kompatibilitas blade lama yang pakai getPaginatedPkm limit 10000 - disediakan opsi
            // $pkmDataLaluFull = $this->repository->getPaginatedPkm($tahunLalu,$bulanLalu,$search,$sortColumn,$sortDirection,1,10000);

        } catch (QueryException $e) {
            Log::error('Gagal PKM Pemeriksaan', ['tahun' => $tahun, 'bulan' => $bulan, 'search' => $search, 'msg' => $e->getMessage()]);
            abort(503, 'Data PKM Pemeriksaan tidak tersedia');
        }

        return view('pkm.pemeriksaan', array_merge(compact('pkmData', 'sortColumn', 'sortDirection', 'tahun', 'bulan', 'search'), $comparison, ['tahunLalu' => $tahunLalu, 'bulanLalu' => $bulanLalu]));
    }

    public function exportDetil(Request $request)
    {
        [$tahun,$bulan] = $this->pkmService->resolvePeriod($request);
        $search = $this->pkmService->resolveSearch($request);
        $filename = "detil_pkm_pemeriksaan_{$tahun}_{$bulan}.csv";
        $columnsMap = ['npwp15' => 'NPWP', 'nama_wp' => 'NAMA WP', 'kd_klu' => 'KD KLU', 'nm_klu' => 'NAMA KLU', 'kd_map' => 'KD MAP', 'kd_bayar' => 'KD BAYAR', 'fungsi' => 'FUNGSI', 'bln_setor' => 'BULAN', 'thn_setor' => 'TAHUN', 'jml_setor' => 'JUMLAH SETOR'];
        $query = $this->repository->getExportDetilQuery($tahun, $bulan, $search);

        return $this->csvExportService->exportFromQuery($filename,$columnsMap,$query);
    }
}
