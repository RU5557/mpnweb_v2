<?php

namespace App\Http\Controllers;

use App\Repositories\WpRepository;
use App\Services\CsvExportService;
use App\Services\WpSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * WpSearchController - Thin Controller (Wajib .airules #8)
 * - Logic filter di WpSearchService
 * - Query di WpRepository
 * - Export via CsvExportService + CanStreamCsv trait
 */
class WpSearchController extends Controller
{
    public function __construct(
        protected WpRepository $wpRepository,
        protected CsvExportService $csvExportService,
        protected WpSearchService $wpSearchService
    ) {}

    public function searchMasterfile(Request $request): View
    {
        $tahun = (int) date('Y');
        $filters = $this->wpSearchService->resolveFilters($request);
        $hasSearch = $request->has('has_search');

        $dropdowns = $this->wpRepository->getFilterDropdownOptions($tahun);
        $results = $hasSearch
            ? $this->wpRepository->searchMasterfilePaginated($filters, 20, $tahun)->appends($request->all())
            : null;

        // Kirim $filters utuh ke view, bukan 12 variabel terpisah (hemat memory)
        return view('pencarian.masterfile', array_merge($dropdowns, [
            'hasSearch' => $hasSearch,
            'results' => $results,
            'filters' => $filters,
            'sortBy' => $filters['sort_by'],
            'sortOrder' => $filters['sort_order'],
        ]));
    }

    public function exportMasterfileCsv(Request $request): StreamedResponse
    {
        $tahun = (int) date('Y');
        $filters = $this->wpSearchService->resolveFilters($request);

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
