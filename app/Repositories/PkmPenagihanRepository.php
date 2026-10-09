<?php

namespace App\Repositories;

use App\Models\SummaryPkm;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'flag_skp' => 'sp.flag_skp',
            'akt_penagihan' => 'akt_penagihan',
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? 'nip_jspn';
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        $cacheKey = "pkm_penagihan_v5_{$tahun}_{$bulan}_d".md5($dspcFilter)."_{$sortColumn}_{$sortDir}";

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

            return $query->select([
                DB::raw("CASE WHEN p.nama IS NULL THEN 'Unassign' ELSE COALESCE(NULLIF(TRIM(mw.nip_js), ''), 'Unassign') END as nip_jspn"),
                DB::raw("COALESCE(NULLIF(TRIM(p.nama), ''), 'Unassign') as nama_jspn"),
                DB::raw("COALESCE(NULLIF(TRIM(sp.flag_skp), ''), 'NON-DSPC') as flag_skp"),
                DB::raw('SUM(sp.total_setor) as akt_penagihan'),
            ])
                ->groupBy(
                    DB::raw("CASE WHEN p.nama IS NULL THEN 'Unassign' ELSE COALESCE(NULLIF(TRIM(mw.nip_js), ''), 'Unassign') END"),
                    DB::raw("COALESCE(NULLIF(TRIM(p.nama), ''), 'Unassign')"),
                    DB::raw("COALESCE(NULLIF(TRIM(sp.flag_skp), ''), 'NON-DSPC')")
                )
                ->orderBy($sortBy, $sortDir)
                ->get();
        });
    }

    public function exportDetilCsv(int $tahun, int $bulan, string $dspcFilter): StreamedResponse
    {
        $filename = "Export_Detil_PKM_Penagihan_{$tahun}_{$bulan}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tahun, $bulan, $dspcFilter) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'NO', 'NIP JSPN', 'NAMA JSPN', 'NPWP', 'NAMA WP',
                'FLAG SKP', 'KD MAP', 'KD BAYAR', 'FUNGSI', 'BULAN', 'TAHUN', 'JUMLAH SETOR',
            ]);

            $tahunPegawai = $tahun > 0 ? $tahun : (int) date('Y');

            $query = DB::table('drm as dt')
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
                    DB::raw("COALESCE(NULLIF(TRIM(mw.nip_js), ''), 'Unassign') as nip_jspn"),
                    DB::raw("COALESCE(NULLIF(TRIM(p.nama), ''), 'Unassign') as nama_jspn"),
                    'dt.npwp15',
                    DB::raw("COALESCE(NULLIF(TRIM(mw.nama), ''), 'WP Tidak Terdaftar') as nama_wp"),
                    DB::raw("COALESCE(NULLIF(TRIM(dt.flag_skp), ''), 'NON-DSPC') as flag_skp"),
                    'dt.kd_map',
                    'dt.kd_bayar',
                    'dt.jml_setor',
                    'dt.bln_setor',
                    'dt.thn_setor',
                    'dt.fungsi',
                ])
                ->orderBy('p.nama', 'asc')
                ->orderBy('dt.bln_setor', 'asc');

            $index = 1;
            foreach ($query->cursor() as $row) {
                fputcsv($file, [
                    $index++,
                    $row->nip_jspn,
                    $row->nama_jspn,
                    $row->npwp15,
                    $row->nama_wp,
                    $row->flag_skp,
                    $row->kd_map,
                    $row->kd_bayar,
                    $row->fungsi,
                    $row->bln_setor,
                    $row->thn_setor,
                    $row->jml_setor,
                ]);

                if ($index % 1000 === 0 && ob_get_level() > 0) {
                    ob_flush();
                    flush();
                }
            }

            fclose($file);
        }, 200, $headers);
    }
}
