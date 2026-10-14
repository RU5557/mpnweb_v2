<?php

namespace App\Repositories;

use App\Models\SummaryPkm;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PkmPenagihanRepository
{
    public function getSummaryPkm(int $tahun, int $bulan, string $dspcFilter, string $sortColumn, string $sortDirection): Collection
    {
        $allowed = ['nip_jspn' => 'nip_jspn', 'nama_jspn' => 'nama_jspn', 'flag_skp' => 'flag_skp', 'akt_penagihan' => 'akt_penagihan'];
        $sortBy = $allowed[$sortColumn] ?? 'nip_jspn';
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';
        $cacheKey = "pkm_penagihan_v13_{$tahun}_{$bulan}_d".md5($dspcFilter)."_{$sortColumn}_{$sortDir}";

        return Cache::remember($cacheKey, 600, function () use ($tahun, $bulan, $dspcFilter, $sortBy, $sortDir) {
            $tahunPegawai = $tahun > 0 ? $tahun : (int) date('Y');
            $subQuery = SummaryPkm::query()->toBase()->from('summary_pkm as sp')
                ->leftJoin('mfwp as mw', 'sp.npwp15', '=', 'mw.npwp15')
                ->leftJoin('pegawai as p', fn ($j) => $j->on('mw.nip_js', '=', 'p.nip')->where('p.tahun', '=', $tahunPegawai))
                ->where('sp.fungsi', 'AKT PENAGIHAN')->where('sp.thn_setor', $tahun)->whereBetween('sp.bln_setor', [1, $bulan])
                ->when($dspcFilter !== '', fn ($q) => $q->where('sp.flag_skp', $dspcFilter))
                ->select([
                    DB::raw("CASE WHEN p.nama IS NULL OR TRIM(p.nama)='' THEN 'Unassign' ELSE COALESCE(NULLIF(TRIM(mw.nip_js),''),'Unassign') END as nip_jspn"),
                    DB::raw("CASE WHEN p.nama IS NULL OR TRIM(p.nama)='' THEN 'Unassign' ELSE TRIM(p.nama) END as nama_jspn"),
                    DB::raw("COALESCE(NULLIF(TRIM(sp.flag_skp),''),'NON-DSPC') as flag_skp"),
                    'sp.total_setor',
                ]);

            // FIX: pakai fromSub bukan toSql raw biar hemat I/O HDD (Rule 3)
            return DB::query()->fromSub($subQuery, 'agg')->select(['nip_jspn', 'nama_jspn', 'flag_skp', DB::raw('SUM(total_setor) as akt_penagihan')])->groupBy('nip_jspn', 'nama_jspn', 'flag_skp')->orderBy($sortBy, $sortDir)->get();
        });
    }

    public function getExportDetilQuery(int $tahun, int $bulan, string $dspcFilter): Builder
    {
        $tahunPegawai = $tahun > 0 ? $tahun : (int) date('Y');

        return DB::table('drm as dt')
            ->leftJoin('mfwp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoin('pegawai as p', fn ($j) => $j->on('mw.nip_js', '=', 'p.nip')->where('p.tahun', '=', $tahunPegawai))
            ->where('dt.fungsi', 'AKT PENAGIHAN')->where('dt.thn_setor', $tahun)->whereBetween('dt.bln_setor', [1, $bulan])
            ->when($dspcFilter !== '', fn ($q) => $q->where('dt.flag_skp', $dspcFilter))
            ->select([DB::raw("CASE WHEN p.nama IS NULL OR TRIM(p.nama)='' THEN 'Unassign' ELSE COALESCE(NULLIF(TRIM(mw.nip_js),''),'Unassign') END as nip_jspn"), DB::raw("CASE WHEN p.nama IS NULL OR TRIM(p.nama)='' THEN 'Unassign' ELSE TRIM(p.nama) END as nama_jspn"), 'dt.npwp15', DB::raw("COALESCE(NULLIF(TRIM(mw.nama),''),'WP Tidak Terdaftar') as nama_wp"), DB::raw("COALESCE(NULLIF(TRIM(dt.flag_skp),''),'NON-DSPC') as flag_skp"), 'dt.kd_map', 'dt.kd_bayar', 'dt.fungsi', 'dt.bln_setor', 'dt.thn_setor', 'dt.jml_setor'])->orderBy('nama_jspn')->orderBy('dt.bln_setor');
    }
}
