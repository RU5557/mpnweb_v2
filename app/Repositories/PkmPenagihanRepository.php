<?php

namespace App\Repositories;

use App\Models\SummaryPkm;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PkmPenagihanRepository
{
    public function getSummaryPkm(
        int $tahun,
        int $bulan,
        string $dspcFilter,
        string $sortColumn,
        string $sortDirection
    ): Collection {
        $allowedSorts = [
            'nip_jspn' => 'nip_jspn',
            'nama_jspn' => 'nama_jspn',
            'flag_skp' => 'flag_skp',
            'akt_penagihan' => 'akt_penagihan',
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? 'nip_jspn';
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        $cacheKey = "pkm_penagihan_agregat_v1_{$tahun}_{$bulan}_d".md5($dspcFilter)."_{$sortColumn}_{$sortDir}";

        return Cache::remember($cacheKey, 600, function () use ($tahun, $bulan, $dspcFilter, $sortBy, $sortDir) {
            $tahunPegawai = $tahun > 0 ? $tahun : (int) date('Y');

            $query = SummaryPkm::query()
                ->toBase()
                ->from('summary_pkm as sp')
                ->leftJoin('mfwp as mw', 'sp.npwp15', '=', 'mw.npwp15')
                ->leftJoin('pegawai as p', function ($join) use ($tahunPegawai) {
                    $join->on('mw.nip_js', '=', 'p.nip')
                        ->where('p.tahun', '=', $tahunPegawai);
                })
                ->where('sp.fungsi', 'AKT PENAGIHAN')
                ->where('sp.thn_setor', $tahun)
                ->whereBetween('sp.bln_setor', [1, $bulan]);

            if ($dspcFilter !== '') {
                $query->where('sp.flag_skp', $dspcFilter);
            }

            $subQuery = $query->select([
                DB::raw("CASE WHEN p.nama IS NULL OR TRIM(p.nama) = '' THEN 'Unassign' ELSE COALESCE(NULLIF(TRIM(mw.nip_js), ''), 'Unassign') END as nip_jspn"),
                DB::raw("CASE WHEN p.nama IS NULL OR TRIM(p.nama) = '' THEN 'Unassign' ELSE TRIM(p.nama) END as nama_jspn"),
                DB::raw("COALESCE(NULLIF(TRIM(sp.flag_skp), ''), 'NON-DSPC') as flag_skp"),
                'sp.total_setor',
            ]);

            return DB::table(DB::raw("({$subQuery->toSql()}) as agg"))
                ->mergeBindings($subQuery)
                ->select([
                    'nip_jspn',
                    'nama_jspn',
                    'flag_skp',
                    DB::raw('SUM(total_setor) as akt_penagihan'),
                ])
                ->groupBy('nip_jspn', 'nama_jspn', 'flag_skp')
                ->orderBy($sortBy, $sortDir)
                ->get();
        });
    }

    /**
     * Mengembalikan Query Builder murni untuk ekspor CSV
     */
    public function getExportDetilQuery(int $tahun, int $bulan, string $dspcFilter): Builder
    {
        $tahunPegawai = $tahun > 0 ? $tahun : (int) date('Y');

        return DB::table('drm as dt')
            ->leftJoin('mfwp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoin('pegawai as p', function ($join) use ($tahunPegawai) {
                $join->on('mw.nip_js', '=', 'p.nip')
                    ->where('p.tahun', '=', $tahunPegawai);
            })
            ->where('dt.fungsi', 'AKT PENAGIHAN')
            ->where('dt.thn_setor', $tahun)
            ->whereBetween('dt.bln_setor', [1, $bulan])
            ->when($dspcFilter !== '', function ($q) use ($dspcFilter) {
                return $q->where('dt.flag_skp', $dspcFilter);
            })
            ->select([
                DB::raw("CASE WHEN p.nama IS NULL OR TRIM(p.nama) = '' THEN 'Unassign' ELSE COALESCE(NULLIF(TRIM(mw.nip_js), ''), 'Unassign') END as nip_jspn"),
                DB::raw("CASE WHEN p.nama IS NULL OR TRIM(p.nama) = '' THEN 'Unassign' ELSE TRIM(p.nama) END as nama_jspn"),
                'dt.npwp15',
                DB::raw("COALESCE(NULLIF(TRIM(mw.nama), ''), 'WP Tidak Terdaftar') as nama_wp"),
                DB::raw("COALESCE(NULLIF(TRIM(dt.flag_skp), ''), 'NON-DSPC') as flag_skp"),
                'dt.kd_map',
                'dt.kd_bayar',
                'dt.fungsi',
                'dt.bln_setor',
                'dt.thn_setor',
                'dt.jml_setor',
            ])
            ->orderBy('nama_jspn', 'asc')
            ->orderBy('dt.bln_setor', 'asc');
    }
}
