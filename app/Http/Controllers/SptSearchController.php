<?php

namespace App\Http\Controllers;

use App\Repositories\SptRepository;
use App\Services\CsvExportService;
use App\Services\SptSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * SptSearchController - ADOPSI pola WpSearchController (Cursor Paginate + Exact Count)
 * .airules: Thin Controller, Query di Repository, Filter di Service, Export via Service+Trait
 */
class SptSearchController extends Controller
{
    public function __construct(
        protected SptRepository $sptRepository,
        protected CsvExportService $csvExportService,
        protected SptSearchService $sptSearchService
    ) {}

    public function index(Request $request): View
    {
        $tahun = (int) date('Y');
        $filters = $this->sptSearchService->resolveFilters($request);
        $hasSearch = $request->has('has_search');

        $dropdowns = $this->sptRepository->getFilterDropdownOptions($tahun);

        $results = null;
        $totalExact = 0;

        if ($hasSearch) {
            $data = $this->sptRepository->searchSptCursorPaginated($filters, 20, $tahun);
            $results = $data['results'];
            $totalExact = $data['totalExact'];

            $results->appends($request->all());
        }

        return view('pencarian.spt', array_merge($dropdowns, [
            'hasSearch' => $hasSearch,
            'results' => $results,
            'totalExact' => $totalExact,
            'filters' => $filters,
            'sortBy' => $filters['sort_by'],
            'sortOrder' => $filters['sort_order'],
        ]));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $tahun = (int) date('Y');
        $filters = $this->sptSearchService->resolveFilters($request);

        $filename = 'export_spt_'.date('Ymd_His').'.csv';
        $columnsMap = [
            'nomor_tanda_terima' => 'NO BPE / TANDA TERIMA',
            'kanal_pelaporan' => 'KANAL PELAPORAN',
            'npwp' => 'NPWP',
            'nama' => 'NAMA WAJIB PAJAK',
            'jenis_spt' => 'JENIS SPT',
            'status_spt' => 'STATUS SPT',
            'masa1' => 'MASA AWAL',
            'masa2' => 'MASA AKHIR',
            'thn_pajak' => 'THN PAJAK',
            'pembetulan' => 'PEMBETULAN',
            'tgl_terima' => 'TGL TERIMA',
            'nip_ar' => 'NIP AR',
            'nama_ar' => 'NAMA AR',
        ];

        $queryBuilder = $this->sptRepository->buildExportBaseQuery($filters, $tahun);

        return $this->csvExportService->exportFromQuery($filename, $columnsMap, $queryBuilder, 2000);
    }

    // Optional AJAX count - sesuai WpSearchController
    public function count(Request $request)
    {
        $filters = $this->sptSearchService->resolveFilters($request);
        $total = $this->sptRepository->getTotalCountCached($filters);

        return response()->json(['total' => $total]);
    }
}
