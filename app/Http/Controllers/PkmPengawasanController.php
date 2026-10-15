<?php

namespace App\Http\Controllers;

use App\Repositories\PkmPengawasanRepository;
use App\Services\CsvExportService;
use App\Services\PkmService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PkmPengawasanController extends Controller
{
    public function __construct(protected PkmPengawasanRepository $repository, protected CsvExportService $csvExportService, protected PkmService $pkmService) {}

    public function index(Request $request)
    {
        [$tahun,$bulan] = $this->pkmService->resolvePeriod($request);
        $seksiFilter = $this->pkmService->resolveSeksiFilter($request);
        $sortColumn = (string) $request->input('sort', 'nama_seksi');
        $sortDirection = (string) $request->input('direction', 'asc');

        try {
            $pkmData = $this->repository->getSummaryPkm($tahun, $bulan, $seksiFilter, $sortColumn, $sortDirection);
            $daftarSeksi = $this->repository->getDaftarSeksi();

            // Hitung total saat ini (s.d. bulan terpilih)
            $totalSaatIni = (float) $pkmData->sum('total_pkm_pengawasan');

            // Hitung total bulan lalu (s.d. bulan-1) - pakai cache & summary_pkm biar hemat I/O HDD
            [$tahunLalu, $bulanLalu] = $this->pkmService->getBulanLaluLabel($tahun, $bulan);
            $pkmDataLalu = $this->repository->getSummaryPkm($tahunLalu, $bulanLalu, $seksiFilter, $sortColumn, $sortDirection);
            $totalBulanLalu = (float) $pkmDataLalu->sum('total_pkm_pengawasan');

            $comparison = $this->pkmService->calcComparison($totalSaatIni, $totalBulanLalu);

        } catch (QueryException $e) {
            Log::error('Gagal PKM Pengawasan', ['tahun' => $tahun, 'bulan' => $bulan, 'msg' => $e->getMessage()]);
            abort(503, 'Data PKM Pengawasan tidak tersedia');
        }

        return view('pkm.pengawasan', array_merge(compact('pkmData', 'daftarSeksi', 'sortColumn', 'sortDirection', 'tahun', 'bulan', 'seksiFilter'), $comparison, ['tahunLalu' => $tahunLalu, 'bulanLalu' => $bulanLalu]));
    }

    public function exportDetil(Request $request)
    {
        [$tahun,$bulan] = $this->pkmService->resolvePeriod($request);
        $seksiFilter = $this->pkmService->resolveSeksiFilter($request);
        $filename = "detil_pkm_pengawasan_{$tahun}_{$bulan}.csv";
        $columnsMap = ['npwp15' => 'NPWP', 'nama_wp' => 'NAMA WP', 'nama_seksi' => 'SEKSI', 'nama_ar' => 'NAMA AR', 'fungsi' => 'FUNGSI', 'kd_map' => 'KD MAP', 'kd_bayar' => 'KD BAYAR', 'bln_setor' => 'BULAN', 'thn_setor' => 'TAHUN', 'jml_setor' => 'JUMLAH SETOR'];
        $query = $this->repository->getExportDetilQuery($tahun, $bulan, $seksiFilter);

        return $this->csvExportService->exportFromQuery($filename,$columnsMap,$query);
    }
}
