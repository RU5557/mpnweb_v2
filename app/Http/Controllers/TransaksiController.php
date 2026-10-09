<?php

namespace App\Http\Controllers;

use App\Repositories\TransaksiRepository;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransaksiController extends Controller
{
    public function __construct(
        protected TransaksiRepository $transaksiRepository,
        protected CsvExportService $csvExportService
    ) {}

    /**
     * Halaman Pencarian & Filter Transaksi / DRM
     */
    public function index(Request $request)
    {
        $tahun = (int) date('Y');
        $filters = $request->all();

        // 1. Opsi dropdown yang sudah di-cache
        $dropdowns = $this->transaksiRepository->getFilterDropdownOptions($tahun);

        // 2. Query paginasi
        $results = null;
        if ($request->has('has_search')) {
            $results = $this->transaksiRepository->searchTransaksiPaginated($filters, 20, $tahun);
            $results->appends($filters);
        }

        return view('pencarian.transaksi', array_merge($dropdowns, [
            'results' => $results,
            'npwpInput' => $request->get('npwp'),
            'namaWp' => $request->get('nama'),
            'kdMap' => $request->get('kd_map'),
            'kdBayar' => $request->get('kd_bayar'),
            'tglSetorStart' => $request->get('tgl_setor_start'),
            'tglSetorEnd' => $request->get('tgl_setor_end'),
            'masa1' => $request->get('masa1'),
            'masa2' => $request->get('masa2'),
            'thnPajak' => $request->get('thn_pajak'),
            'ntpn' => $request->get('ntpn'),
            'kotaSelected' => $request->get('kota'),
            'jenisWpSelected' => $request->get('jenis_wp'),
            'sektorSelected' => $request->get('sektor'),
            'seksiSelected' => $request->get('seksi_id'),
            'thnSetor' => $request->get('thn_setor', []),
            'blnSetor' => $request->get('bln_setor', []),
            'fungsi' => $request->get('fungsi', []),
            'nipAr' => $request->get('nip_ar', []),
            'nipJs' => $request->get('nip_js', []),
            'sortBy' => $request->get('sort_by', 'tgl_setor'),
            'sortOrder' => $request->get('sort_order', 'desc'),
        ]));
    }

    /**
     * Export CSV Transaksi via Service
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $tahun = (int) date('Y');
        $filters = $request->all();

        $filename = 'export_transaksi_drm_'.date('Ymd_His').'.csv';

        $columnsMap = [
            'tgl_setor' => 'TGL SETOR',
            'npwp15' => 'NPWP15',
            'nama_wp' => 'NAMA WP',
            'fungsi' => 'FUNGSI',
            'kd_map' => 'KD MAP',
            'kd_bayar' => 'KD BAYAR',
            'masa_pajak' => 'MASA PAJAK',
            'thn_pajak' => 'THN PAJAK',
            'jml_setor' => 'JUMLAH SETOR',
            'ntpn' => 'NTPN',
            'nama_ar' => 'NAMA AR',
            'nama_js' => 'NAMA JS',
            'kota' => 'KOTA',
        ];

        $queryBuilder = $this->transaksiRepository->buildTransaksiExportQuery($filters, $tahun);

        return $this->csvExportService->exportFromQuery($filename, $columnsMap, $queryBuilder);
    }
}
