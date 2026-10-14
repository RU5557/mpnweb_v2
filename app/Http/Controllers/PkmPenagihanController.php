<?php

namespace App\Http\Controllers;

use App\Repositories\PkmPenagihanRepository;
use App\Services\CsvExportService;
use App\Services\PkmService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PkmPenagihanController extends Controller
{
    public function __construct(protected PkmPenagihanRepository $repository, protected CsvExportService $csvExportService, protected PkmService $pkmService) {}

    public function index(Request $request)
    {
        [$tahun,$bulan] = $this->pkmService->resolvePeriod($request);
        $dspcFilter = $this->pkmService->resolveDspcFilter($request);
        $sortColumn = (string) $request->input('sort', 'nip_jspn');
        $sortDirection = (string) $request->input('direction', 'asc');

        try {
            $pkmData = $this->repository->getSummaryPkm($tahun, $bulan, $dspcFilter, $sortColumn, $sortDirection);
            $totalSaatIni = (float) $pkmData->sum('akt_penagihan');

            [$tahunLalu, $bulanLalu] = $this->pkmService->getBulanLaluLabel($tahun, $bulan);
            $pkmDataLalu = $this->repository->getSummaryPkm($tahunLalu, $bulanLalu, $dspcFilter, $sortColumn, $sortDirection);
            $totalBulanLalu = (float) $pkmDataLalu->sum('akt_penagihan');

            $comparison = $this->pkmService->calcComparison($totalSaatIni, $totalBulanLalu);

        } catch (QueryException $e) {
            Log::error('Gagal PKM Penagihan', ['tahun' => $tahun, 'bulan' => $bulan, 'msg' => $e->getMessage()]);
            abort(503, 'Data PKM Penagihan tidak tersedia');
        }

        return view('pkm.penagihan', array_merge(compact('pkmData', 'sortColumn', 'sortDirection', 'tahun', 'bulan', 'dspcFilter'), $comparison, ['tahunLalu' => $tahunLalu, 'bulanLalu' => $bulanLalu]));
    }

    public function exportDetil(Request $request)
    {
        [$tahun,$bulan] = $this->pkmService->resolvePeriod($request);
        $dspcFilter = $this->pkmService->resolveDspcFilter($request);
        $filename = "detil_pkm_penagihan_{$tahun}_{$bulan}.csv";
        $columnsMap = ['nip_jspn' => 'NIP JSPN', 'nama_jspn' => 'NAMA JSPN', 'npwp15' => 'NPWP', 'nama_wp' => 'NAMA WP', 'flag_skp' => 'FLAG SKP', 'kd_map' => 'KD MAP', 'kd_bayar' => 'KD BAYAR', 'fungsi' => 'FUNGSI', 'bln_setor' => 'BULAN', 'thn_setor' => 'TAHUN', 'jml_setor' => 'JUMLAH SETOR'];
        $query = $this->repository->getExportDetilQuery($tahun, $bulan, $dspcFilter);

        return $this->csvExportService->exportFromQuery($filename,$columnsMap,$query);
    }
}
