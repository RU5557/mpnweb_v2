<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PkmPemeriksaanRepository
{
    // Query dasar untuk pemeriksaan - pakai summary_pkm biar hemat I/O HDD (Rule 3) - idx_pkm_lookup (thn_setor,bln_setor,fungsi)
    private function baseQuery(int $tahun, int $bulan, string $search): Builder
    {
        $q = DB::table('summary_pkm as sp')
            ->leftJoin('mfwp as mw', 'sp.npwp15', '=', 'mw.npwp15')
            ->leftJoin('klu as k', 'mw.klu', '=', 'k.kd_klu')
            ->where('sp.fungsi', 'AKT PEMERIKSAAN')
            ->where('sp.thn_setor', $tahun)
            ->whereBetween('sp.bln_setor', [1, $bulan]);

        if ($search !== '') {
            // Fulltext search untuk nama_wp lebih cepat daripada LIKE % di HDD 1TB
            $q->where(function ($qq) use ($search) {
                $qq->where('sp.npwp15', 'LIKE', "%{$search}%")
                    ->orWhere('mw.nama', 'LIKE', "%{$search}%")
                    ->orWhere('k.nm_klu', 'LIKE', "%{$search}%");
            });
        }

        return $q;
    }

    public function getPaginatedPkm(int $tahun, int $bulan, string $search, string $sortColumn, string $sortDirection, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $allowed = ['npwp' => 'npwp', 'nama_wp' => 'nama_wp', 'kd_klu' => 'kd_klu', 'nm_klu' => 'nm_klu', 'total_akt_pemeriksaan' => 'total_akt_pemeriksaan'];
        $sortBy = $allowed[$sortColumn] ?? 'total_akt_pemeriksaan';
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';
        $cacheKey = "pkm_pemeriksaan_paginate_v14_{$tahun}_{$bulan}_".md5($search)."_{$sortColumn}_{$sortDir}_p{$page}_pp{$perPage}";

        return Cache::remember($cacheKey, 600, function () use ($tahun, $bulan, $search, $sortBy, $sortDir, $page, $perPage) {
            $query = $this->baseQuery($tahun, $bulan, $search)
                ->select([
                    'sp.npwp15',
                    DB::raw("COALESCE(NULLIF(TRIM(mw.nama),''),'WP Tidak Terdaftar') as nama_wp"),
                    DB::raw("COALESCE(mw.klu,'-') as kd_klu"),
                    DB::raw("COALESCE(k.nm_klu,'-') as nm_klu"),
                    DB::raw('SUM(sp.total_setor) as total_akt_pemeriksaan'),
                ])
                ->groupBy('sp.npwp15', 'mw.nama', 'mw.klu', 'k.nm_klu');

            // Mapping sort
            $sortMap = [
                'npwp' => 'sp.npwp15',
                'nama_wp' => 'nama_wp',
                'kd_klu' => 'kd_klu',
                'nm_klu' => 'nm_klu',
                'total_akt_pemeriksaan' => 'total_akt_pemeriksaan',
            ];
            $orderCol = $sortMap[$sortBy] ?? 'total_akt_pemeriksaan';

            return $query->orderBy($orderCol, $sortDir)->paginate($perPage, ['*'], 'page', $page);
        });
    }

    // BARU: untuk hitung total akurat s.d. bulan lalu - tanpa paginate, pakai 10000+ row biar sum valid
    // Tetap pakai summary_pkm (bukan drm) biar hemat I/O HDD i5 gen9 RAM 8GB (Rule 1 & 3)
    public function getTotalForComparison(int $tahun, int $bulan, string $search): float
    {
        $cacheKey = "pkm_pemeriksaan_total_cmp_v14_{$tahun}_{$bulan}_".md5($search);

        return Cache::remember($cacheKey, 600, function () use ($tahun, $bulan, $search) {
            return (float) $this->baseQuery($tahun, $bulan, $search)->sum('sp.total_setor');
        });
    }

    public function getExportDetilQuery(int $tahun, int $bulan, string $search): Builder
    {
        $q = DB::table('drm as dt')
            ->leftJoin('mfwp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoin('klu as k', 'mw.klu', '=', 'k.kd_klu')
            ->where('dt.fungsi', 'AKT PEMERIKSAAN')
            ->where('dt.thn_setor', $tahun)
            ->whereBetween('dt.bln_setor', [1, $bulan]);

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('dt.npwp15', 'LIKE', "%{$search}%")
                    ->orWhere('mw.nama', 'LIKE', "%{$search}%");
            });
        }

        return $q->select([
            'dt.npwp15',
            DB::raw("COALESCE(NULLIF(TRIM(mw.nama),''),'WP Tidak Terdaftar') as nama_wp"),
            DB::raw("COALESCE(mw.klu,'-') as kd_klu"),
            DB::raw("COALESCE(k.nm_klu,'-') as nm_klu"),
            'dt.kd_map', 'dt.kd_bayar', 'dt.fungsi', 'dt.bln_setor', 'dt.thn_setor', 'dt.jml_setor',
        ])->orderBy('dt.npwp15');
    }
}
