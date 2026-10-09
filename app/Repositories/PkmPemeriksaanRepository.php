<?php

namespace App\Repositories;

use App\Models\SummaryPkm;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PkmPemeriksaanRepository
{
    public function getPaginatedPkm(
        int $tahun,
        int $bulan,
        string $search,
        string $sortColumn,
        string $sortDirection,
        int $page = 1,
        int $perPage = 10
    ): LengthAwarePaginator {
        $allowedSorts = [
            'npwp' => 'sp.npwp15',
            'nama_wp' => 'mw.nama',
            'kd_klu' => 'mw.klu',
            'nm_klu' => 'k.nm_klu',
            'total_akt_pemeriksaan' => DB::raw('SUM(sp.total_setor)'),
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? DB::raw('SUM(sp.total_setor)');
        $sortDir = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $cacheKey = "pkm_pemeriksaan_v7_{$tahun}_{$bulan}_s".md5($search)."_{$sortColumn}_{$sortDir}_p{$page}";

        return Cache::remember($cacheKey, 600, function () use ($tahun, $bulan, $search, $sortBy, $sortDir, $perPage) {
            return SummaryPkm::query()
                ->toBase()
                ->from('summary_pkm as sp')
                ->leftJoin('mfwp as mw', 'sp.npwp15', '=', 'mw.npwp15')
                ->leftJoin('klu as k', 'mw.klu', '=', 'k.kd_klu')
                ->where('sp.fungsi', 'AKT PEMERIKSAAN')
                ->where('sp.thn_setor', $tahun)
                ->whereBetween('sp.bln_setor', [1, $bulan])
                ->when($search !== '', function ($query) use ($search) {
                    $like = '%'.addcslashes($search, '%_\\').'%';

                    return $query->where(function ($q) use ($like) {
                        $q->where('sp.npwp15', 'like', $like)
                            ->orWhere('mw.nama', 'like', $like)
                            ->orWhere('mw.klu', 'like', $like)
                            ->orWhere('k.nm_klu', 'like', $like);
                    });
                })
                ->select([
                    'sp.npwp15',
                    DB::raw("COALESCE(NULLIF(TRIM(mw.nama), ''), 'WP Tidak Terdaftar') as nama_wp"),
                    DB::raw("COALESCE(NULLIF(TRIM(mw.klu), ''), '-') as kd_klu"),
                    DB::raw("COALESCE(NULLIF(TRIM(k.nm_klu), ''), '-') as nm_klu"),
                    DB::raw('SUM(sp.total_setor) as total_akt_pemeriksaan'),
                ])
                ->groupBy('sp.npwp15', 'mw.nama', 'mw.klu', 'k.nm_klu')
                ->orderBy($sortBy, $sortDir)
                ->paginate($perPage)
                ->withQueryString();
        });
    }

    /**
     * Mengembalikan Query Builder murni untuk ekspor CSV
     */
    public function getExportDetilQuery(int $tahun, int $bulan, string $search): Builder
    {
        return DB::table('drm as dt')
            ->leftJoin('mfwp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoin('klu as k', 'mw.klu', '=', 'k.kd_klu')
            ->where('dt.fungsi', 'AKT PEMERIKSAAN')
            ->where('dt.thn_setor', $tahun)
            ->whereBetween('dt.bln_setor', [1, $bulan])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';

                return $query->where(function ($q) use ($like) {
                    $q->where('dt.npwp15', 'like', $like)
                        ->orWhere('mw.nama', 'like', $like)
                        ->orWhere('mw.klu', 'like', $like)
                        ->orWhere('k.nm_klu', 'like', $like);
                });
            })
            ->select([
                'dt.npwp15',
                DB::raw("COALESCE(NULLIF(TRIM(mw.nama), ''), 'WP Tidak Terdaftar') as nama_wp"),
                DB::raw("COALESCE(NULLIF(TRIM(mw.klu), ''), '-') as kd_klu"),
                DB::raw("COALESCE(NULLIF(TRIM(k.nm_klu), ''), '-') as nm_klu"),
                'dt.kd_map',
                'dt.kd_bayar',
                'dt.fungsi',
                'dt.bln_setor',
                'dt.thn_setor',
                'dt.jml_setor',
            ])
            ->orderBy('mw.nama', 'asc')
            ->orderBy('dt.bln_setor', 'asc');
    }
}
