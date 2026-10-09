<?php

namespace App\Repositories;

use App\Models\SummaryPkm;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PkmPengawasanRepository
{
    public function getDaftarSeksi(): array
    {
        return Cache::remember('daftar_seksi_pengawasan_v8', 86400, function () {
            $seksi = DB::table('seksi')
                ->where('nama', 'LIKE', '%Pengawasan%')
                ->orderBy('nama', 'asc')
                ->pluck('nama')
                ->toArray();

            $seksi[] = 'Unassign';

            return $seksi;
        });
    }

    public function getSummaryPkm(int $tahun, int $bulan, string $seksiFilter, string $sortColumn, string $sortDirection): Collection
    {
        $allowedSorts = [
            'nama_seksi' => 'nama_seksi',
            'nama_ar' => 'nama_ar',
            'total_akt_pengawasan' => 'total_akt_pengawasan',
            'total_lainnya' => 'total_lainnya',
            'total_wra_pengawasan' => 'total_wra_pengawasan',
            'total_pkm_pengawasan' => 'total_pkm_pengawasan',
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? 'nama_seksi';
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        $cacheKey = "pkm_pengawasan_v12_{$tahun}_{$bulan}_".md5($seksiFilter)."_{$sortColumn}_{$sortDir}";

        return Cache::remember($cacheKey, 600, function () use ($tahun, $bulan, $seksiFilter, $sortBy, $sortDir) {
            $tahunPegawai = $tahun > 0 ? $tahun : (int) date('Y');

            $subQuery = SummaryPkm::query()
                ->toBase()
                ->from('summary_pkm as sp')
                ->leftJoin('mfwp as mw', 'sp.npwp15', '=', 'mw.npwp15')
                ->leftJoin('pegawai as p', function ($join) use ($tahunPegawai) {
                    $join->on('mw.nip_ar', '=', 'p.nip')
                        ->where('p.tahun', '=', $tahunPegawai);
                })
                ->leftJoin('seksi as s', function ($join) {
                    $join->on(DB::raw('CAST(s.id AS CHAR)'), '=', 'p.seksi');
                })
                ->whereIn('sp.fungsi', ['AKT PENGAWASAN', 'LAINNYA', 'WRA PENGAWASAN'])
                ->where('sp.thn_setor', $tahun)
                ->whereBetween('sp.bln_setor', [1, $bulan])
                ->select([
                    'sp.fungsi',
                    'sp.total_setor',
                    DB::raw("
                        CASE 
                            WHEN p.nama IS NULL OR s.nama IS NULL OR TRIM(p.nama) = '' OR TRIM(s.nama) = '' THEN 'Unassign'
                            ELSE TRIM(mw.nip_ar)
                        END as clean_nip_ar
                    "),
                    DB::raw("
                        CASE 
                            WHEN p.nama IS NULL OR TRIM(p.nama) = '' THEN 'Unassign'
                            ELSE TRIM(p.nama)
                        END as clean_nama_ar
                    "),
                    DB::raw("
                        CASE 
                            WHEN s.nama IS NULL OR TRIM(s.nama) = '' THEN 'Unassign'
                            ELSE TRIM(s.nama)
                        END as clean_nama_seksi
                    "),
                ]);

            $mainQuery = DB::query()
                ->fromSub($subQuery, 'src');

            if ($seksiFilter !== '') {
                $mainQuery->where('src.clean_nama_seksi', $seksiFilter);
            }

            return $mainQuery->select([
                'src.clean_nip_ar as nip_ar',
                'src.clean_nama_ar as nama_ar',
                'src.clean_nama_seksi as nama_seksi',
                DB::raw("SUM(CASE WHEN src.fungsi = 'AKT PENGAWASAN' THEN src.total_setor ELSE 0 END) as total_akt_pengawasan"),
                DB::raw("SUM(CASE WHEN src.fungsi = 'LAINNYA' THEN src.total_setor ELSE 0 END) as total_lainnya"),
                DB::raw("SUM(CASE WHEN src.fungsi = 'WRA PENGAWASAN' THEN src.total_setor ELSE 0 END) as total_wra_pengawasan"),
                DB::raw('SUM(src.total_setor) as total_pkm_pengawasan'),
            ])
                ->groupBy('src.clean_nip_ar', 'src.clean_nama_ar', 'src.clean_nama_seksi')
                ->orderBy($sortBy, $sortDir)
                ->get();
        });
    }

    /**
     * Mengembalikan Query Builder murni untuk ekspor CSV
     */
    public function getExportDetilQuery(int $tahun, int $bulan, string $seksiFilter): Builder
    {
        $tahunPegawai = $tahun > 0 ? $tahun : (int) date('Y');

        $query = DB::table('drm as dt')
            ->leftJoin('mfwp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoin('pegawai as p', function ($join) use ($tahunPegawai) {
                $join->on('mw.nip_ar', '=', 'p.nip')
                    ->where('p.tahun', '=', $tahunPegawai);
            })
            ->leftJoin('seksi as s', function ($join) {
                $join->on(DB::raw('CAST(s.id AS CHAR)'), '=', 'p.seksi');
            })
            ->whereIn('dt.fungsi', ['AKT PENGAWASAN', 'LAINNYA', 'WRA PENGAWASAN'])
            ->where('dt.thn_setor', $tahun)
            ->whereBetween('dt.bln_setor', [1, $bulan]);

        if ($seksiFilter !== '') {
            if ($seksiFilter === 'Unassign') {
                $query->where(function ($q) {
                    $q->whereNull('s.nama')
                        ->orWhere('s.nama', '')
                        ->orWhere('s.nama', 'Unassign');
                });
            } else {
                $query->where('s.nama', $seksiFilter);
            }
        }

        return $query->select([
            'dt.npwp15',
            DB::raw("COALESCE(mw.nama, '-') as nama_wp"),
            DB::raw("COALESCE(NULLIF(TRIM(s.nama), ''), 'Unassign') as nama_seksi"),
            DB::raw("COALESCE(NULLIF(TRIM(p.nama), ''), 'Unassign') as nama_ar"),
            'dt.fungsi',
            'dt.kd_map',
            'dt.kd_bayar',
            'dt.bln_setor',
            'dt.thn_setor',
            'dt.jml_setor',
        ])
            ->orderBy('s.nama', 'asc')
            ->orderBy('p.nama', 'asc');
    }
}
