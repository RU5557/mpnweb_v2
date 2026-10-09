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
            'flag_skp' => 'flag_skp',
            'akt_penagihan' => 'akt_penagihan',
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? 'nip_jspn';
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        $cacheKey = "pkm_penagihan_v3_{$tahun}_{$bulan}_{$dspcFilter}_{$sortBy}_{$sortDir}";

        return Cache::remember($cacheKey, 600, function () use ($tahun, $bulan, $dspcFilter, $sortBy, $sortDir) {
            $subPegawai = DB::table('pegawai')->where('tahun', $tahun);

            return SummaryPkm::query()
                ->toBase()
                ->from('summary_pkm as sp')
                ->leftJoin('masterfile_wp as mw', 'sp.npwp15', '=', 'mw.npwp15')
                ->leftJoinSub($subPegawai, 'p', function ($join) {
                    $join->on('mw.nip_js', '=', 'p.nip');
                })
                ->where('sp.fungsi', 'akt penagihan')
                ->where('sp.thn_setor', $tahun)
                ->whereBetween('sp.bln_setor', [1, $bulan])
                ->when($dspcFilter !== '', function ($query) use ($dspcFilter) {
                    if ($dspcFilter === 'DSPC') {
                        return $query->where('sp.flag_skp', 'DSPC');
                    }

                    return $query->where(function ($q) {
                        $q->where('sp.flag_skp', '!=', 'DSPC')
                            ->orWhereNull('sp.flag_skp');
                    });
                })
                ->select([
                    DB::raw("COALESCE(mw.nip_js, 'Unassign') as nip_jspn"),
                    DB::raw("COALESCE(p.nama, 'Unassign') as nama_jspn"),
                    DB::raw("COALESCE(sp.flag_skp, 'NON-DSPC') as flag_skp"),
                    DB::raw('SUM(sp.total_setor) as akt_penagihan'),
                ])
                ->groupBy(
                    DB::raw("COALESCE(mw.nip_js, 'Unassign')"),
                    DB::raw("COALESCE(p.nama, 'Unassign')"),
                    DB::raw("COALESCE(sp.flag_skp, 'NON-DSPC')")
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
                'NO', 'NPWP', 'NAMA WP', 'NIP JSPN', 'NAMA JSPN', 'FLAG SKP',
                'KD MAP', 'KD BAYAR', 'FUNGSI', 'BULAN', 'TAHUN', 'JUMLAH SETOR',
            ]);

            $subPegawai = DB::table('pegawai')->where('tahun', $tahun);

            $query = DB::table('drm as dt')
                ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
                ->leftJoinSub($subPegawai, 'p', function ($join) {
                    $join->on('mw.nip_js', '=', 'p.nip');
                })
                ->where('dt.fungsi', 'akt penagihan')
                ->where('dt.thn_setor', $tahun)
                ->whereBetween('dt.bln_setor', [1, $bulan])
                ->when($dspcFilter !== '', function ($query) use ($dspcFilter) {
                    if ($dspcFilter === 'DSPC') {
                        return $query->where('dt.flag_skp', 'DSPC');
                    }

                    return $query->where(function ($q) {
                        $q->where('dt.flag_skp', '!=', 'DSPC')
                            ->orWhereNull('dt.flag_skp');
                    });
                })
                ->select([
                    'dt.npwp15',
                    DB::raw("COALESCE(mw.nama, '-') as nama_wp"),
                    DB::raw("COALESCE(mw.nip_js, 'Unassign') as nip_jspn"),
                    DB::raw("COALESCE(p.nama, 'Unassign') as nama_jspn"),
                    DB::raw("COALESCE(dt.flag_skp, 'NON-DSPC') as flag_skp"),
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
                    ! empty($row->npwp15) ? $row->npwp15 : '',
                    $row->nama_wp,
                    $row->nip_jspn,
                    $row->nama_jspn,
                    $row->flag_skp,
                    $row->kd_map,
                    $row->kd_bayar,
                    $row->fungsi,
                    $row->bln_setor,
                    $row->thn_setor,
                    $row->jml_setor,
                ]);

                if ($index % 2000 === 0 && ob_get_level() > 0) {
                    ob_flush();
                    flush();
                }
            }

            fclose($file);
        }, 200, $headers);
    }
}
