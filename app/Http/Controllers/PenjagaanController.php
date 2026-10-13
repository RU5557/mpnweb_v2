<?php

namespace App\Http\Controllers;

use App\Repositories\PenjagaanRepository;
use App\Services\CsvExportService;
use App\Services\PenjagaanService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenjagaanController extends Controller
{
    protected array $penjagaanColumnsMap = [
        'thn_setor' => 'Tahun Setor', 'bln_setor' => 'Bulan Setor', 'tgl_setor' => 'Tanggal Setor',
        'npwp15' => 'NPWP15', 'nama_wp' => 'Nama WP', 'jenis' => 'Jenis', 'fungsi' => 'Fungsi',
        'kd_map' => 'Kode MAP', 'kd_bayar' => 'Kode Bayar', 'masa1' => 'Masa 1', 'masa2' => 'Masa 2',
        'thn_pajak' => 'Tahun Pajak', 'jml_setor' => 'Jumlah Setor (Rp)', 'ntpn' => 'NTPN',
    ];

    public function __construct(
        protected PenjagaanRepository $repository,
        protected PenjagaanService $penjagaanService,
        protected CsvExportService $csvExportService
    ) {}

    public function bulanan(Request $request)
    {
        [$fungsiOptions, $fungsi] = $this->penjagaanService->resolveFungsiFilter($request);
        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;

        $raw = $this->repository->getSummaryBulanan($fungsi, $tahunIni, $tahunLalu);
        [$dataTahunIni, $dataTahunLalu] = $this->penjagaanService->normalizeBulanan($raw);

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        return view('penerimaan.penjagaan.bulanan', compact('months', 'dataTahunLalu', 'dataTahunIni', 'fungsiOptions', 'fungsi', 'tahunIni', 'tahunLalu'));
    }

    public function harian(Request $request)
    {
        [$fungsiOptions, $fungsi] = $this->penjagaanService->resolveFungsiFilter($request);
        $bulan = $this->penjagaanService->resolveBulan($request);
        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;

        $raw = $this->repository->getSummaryHarian($bulan, $fungsi, $tahunIni, $tahunLalu);
        [$dataTahunIni, $dataTahunLalu] = $this->penjagaanService->normalizeHarian($raw);
        $days = range(1, 31);

        return view('penerimaan.penjagaan.harian', compact('days', 'dataTahunLalu', 'dataTahunIni', 'bulan', 'fungsiOptions', 'fungsi', 'tahunIni', 'tahunLalu'));
    }

    public function vsBulanLalu(Request $request)
    {
        [$fungsiOptions, $fungsi] = $this->penjagaanService->resolveFungsiFilter($request);
        $bulan = $this->penjagaanService->resolveBulan($request);
        $tahunIni = (int) date('Y');
        $bulanLalu = $bulan == 1 ? 12 : $bulan - 1;
        $tahunBulanLalu = $bulan == 1 ? $tahunIni - 1 : $tahunIni;

        $raw = $this->repository->getSummaryVsBulanLalu($bulan, $bulanLalu, $tahunIni, $tahunBulanLalu, $fungsi);
        [$dataBulanIni, $dataBulanLalu] = $this->penjagaanService->normalizeHarian($raw);
        $days = range(1, 31);

        return view('penerimaan.penjagaan.vs_bulan_lalu', compact('days', 'dataBulanIni', 'dataBulanLalu', 'bulan', 'bulanLalu', 'fungsiOptions', 'fungsi', 'tahunIni', 'tahunBulanLalu'));
    }

    public function exportBulananCsv(Request $request): StreamedResponse
    {
        [, $fungsi] = $this->penjagaanService->resolveFungsiFilter($request);
        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;
        $filename = 'penjagaan_bulanan_detil_'.date('Ymd_His').'.csv';
        $query = $this->repository->getExportBulananQuery($fungsi, $tahunIni, $tahunLalu, array_keys($this->penjagaanColumnsMap));

        return $this->csvExportService->exportFromQuery($filename, $this->penjagaanColumnsMap, $query);
    }

    public function exportHarianCsv(Request $request): StreamedResponse
    {
        [, $fungsi] = $this->penjagaanService->resolveFungsiFilter($request);
        $bulan = $this->penjagaanService->resolveBulan($request);
        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;
        $filename = 'penjagaan_harian_detil_bln_'.$bulan.'_'.date('Ymd_His').'.csv';
        $query = $this->repository->getExportHarianQuery($bulan, $fungsi, $tahunIni, $tahunLalu, array_keys($this->penjagaanColumnsMap));

        return $this->csvExportService->exportFromQuery($filename, $this->penjagaanColumnsMap, $query);
    }

    public function exportVsBulanLaluCsv(Request $request): StreamedResponse
    {
        [, $fungsi] = $this->penjagaanService->resolveFungsiFilter($request);
        $bulan = $this->penjagaanService->resolveBulan($request);
        $tahunIni = (int) date('Y');
        $bulanLalu = $bulan == 1 ? 12 : $bulan - 1;
        $tahunBulanLalu = $bulan == 1 ? $tahunIni - 1 : $tahunIni;
        $filename = 'penjagaan_vs_bulan_lalu_bln_'.$bulan.'_'.date('Ymd_His').'.csv';
        $query = $this->repository->getExportVsBulanLaluQuery($bulan, $bulanLalu, $tahunIni, $tahunBulanLalu, $fungsi, array_keys($this->penjagaanColumnsMap));

        return $this->csvExportService->exportFromQuery($filename, $this->penjagaanColumnsMap, $query);
    }
}
