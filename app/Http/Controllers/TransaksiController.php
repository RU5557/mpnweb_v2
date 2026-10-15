<?php

namespace App\Http\Controllers;

use App\Repositories\TransaksiRepository;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransaksiController extends Controller
{
    public function __construct(
        protected TransaksiRepository $transaksiRepository,
        protected CsvExportService $csvExportService
    ) {}

    public function index(Request $request): View
    {
        $tahun = (int) date('Y');
        $filters = $request->all();
        $dropdowns = $this->transaksiRepository->getFilterDropdownOptions($tahun);

        $results = null;
        $totalJumlah = 0;
        $totalCount = 0;
        $hasSearch = $request->has('has_search');

        if ($hasSearch) {
            $data = $this->transaksiRepository->searchTransaksiCursorPaginated($filters, 20, $tahun);
            $results = $data['results'];
            $totalJumlah = $data['totalJumlah'] ?? 0; // SUM jml_setor
            $totalCount = $data['totalCount'] ?? 0;
            $results->appends($filters);
        }

        return view('pencarian.transaksi', array_merge($dropdowns, [
            'results' => $results,
            'totalJumlah' => $totalJumlah, // ganti totalExact
            'totalCount' => $totalCount,
            'hasSearch' => $hasSearch,
            'npwpInput' => $request->get('npwp'),
            'namaWp' => $request->get('nama'),
            'fungsi' => $request->get('fungsi'),
            'fungsiSelected' => $request->get('fungsi'),
            'kdMap' => $request->get('kd_map'),
            'kdBayar' => $request->get('kd_bayar'),
            'masa1' => $request->get('masa1'),
            'masa2' => $request->get('masa2'),
            'thnPajak' => $request->get('thn_pajak'),
            'tglSetorAwal' => $request->get('tgl_setor_awal', $request->get('tgl_setor_start')),
            'tglSetorAkhir' => $request->get('tgl_setor_akhir', $request->get('tgl_setor_end')),
            'ntpn' => $request->get('ntpn'),
            'noProdukHukum' => $request->get('no_produk_hukum'),
            'kecamatanSelected' => $request->get('kecamatan'),
            'jenisWpSelected' => $request->get('jenis_wp'),
            'sektorSelected' => $request->get('sektor'),
            'nipAr' => $request->get('nip_ar'),
            'seksiSelected' => $request->get('seksi_id', $request->get('seksi_nama')),
            'sortBy' => $request->get('sort_by', 'tgl_setor'),
            'sortOrder' => $request->get('sort_order', 'desc'),
        ]));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $tahun = (int) date('Y');
        $filename = 'export_transaksi_drm_'.date('Ymd_His').'.csv';
        $columnsMap = [
            'tgl_setor' => 'TGL SETOR', 'npwp' => 'NPWP', 'npwp15' => 'NPWP15', 'nama_wp' => 'NAMA WP', 'fungsi' => 'FUNGSI', 'kd_map' => 'KD MAP', 'kd_bayar' => 'KD BAYAR', 'masa1' => 'MASA AWAL', 'masa2' => 'MASA AKHIR', 'thn_pajak' => 'THN PAJAK', 'jml_setor' => 'JUMLAH SETOR', 'ntpn' => 'NTPN', 'no_produk_hukum' => 'NO PRODUK HUKUM', 'kecamatan' => 'KECAMATAN', 'jenis_wp' => 'JENIS WP', 'sektor' => 'SEKTOR', 'nama_ar' => 'NAMA AR', 'seksi' => 'SEKSI',
        ];
        $queryBuilder = $this->transaksiRepository->buildTransaksiExportQuery($request->all(), $tahun);

        return $this->csvExportService->exportFromQuery($filename, $columnsMap, $queryBuilder);
    }
}
