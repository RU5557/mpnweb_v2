<?php

namespace App\Http\Controllers;

use App\Repositories\WpRepository;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WpSearchController extends Controller
{
    public function __construct(
        protected WpRepository $wpRepository,
        protected CsvExportService $csvExportService
    ) {}

    public function searchMasterfile(Request $request)
    {
        $tahun = (int) date('Y');
        $filters = $request->all();

        $dropdowns = $this->wpRepository->getFilterDropdownOptions($tahun);
        $hasSearch = $request->has('has_search');

        $results = null;
        if ($hasSearch) {
            $results = $this->wpRepository->searchMasterfilePaginated($filters, 20, $tahun);
            $results->appends($filters);
        }

        $npwpInput = trim((string) $request->get('npwp'));
        $namaWp = trim((string) $request->get('nama'));
        $klu = $request->get('klu');
        $kelurahan = $request->get('kelurahan');
        $kecamatan = $request->get('kecamatan');
        $jenis = $request->get('jenis');
        $status = $request->get('status');
        $tglDaftarAwal = $request->get('tgl_daftar_awal');
        $tglDaftarAkhir = $request->get('tgl_daftar_akhir');
        $nipAr = $request->get('nip_ar');
        $nipJs = $request->get('nip_js');
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        return view('pencarian.masterfile', array_merge($dropdowns, compact(
            'hasSearch',
            'results',
            'npwpInput',
            'namaWp',
            'klu',
            'kelurahan',
            'kecamatan',
            'jenis',
            'status',
            'tglDaftarAwal',
            'tglDaftarAkhir',
            'nipAr',
            'nipJs',
            'sortBy',
            'sortOrder'
        )));
    }

    public function exportMasterfileCsv(Request $request): StreamedResponse
    {
        $tahun = (int) date('Y');
        $filters = $request->all();
        $filename = 'export_masterfile_'.date('Ymd_His').'.csv';

        $columnsMap = [
            'npwp15' => 'NPWP15',
            'npwp16' => 'NPWP16',
            'nama' => 'Nama Wajib Pajak',
            'klu' => 'KLU',
            'alamat' => 'Alamat',
            'kelurahan' => 'Kelurahan',
            'kecamatan' => 'Kecamatan',
            'jenis' => 'Jenis WP',
            'status' => 'Status WP',
            'tanggal_daftar' => 'Tgl Daftar',
            'nama_ar' => 'Nama AR',
            'nama_js' => 'Nama JS',
        ];

        $queryBuilder = $this->wpRepository->buildExportBaseQuery($filters, $tahun);

        return $this->csvExportService->exportFromQuery($filename, $columnsMap, $queryBuilder, 2000);
    }
}
