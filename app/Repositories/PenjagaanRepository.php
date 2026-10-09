<?php

namespace App\Repositories;

use App\Models\SummaryPenjagaan;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PenjagaanRepository
{
    public function getFungsiOptions(): Collection
    {
        return Cache::remember('penjagaan_fungsi_options', 86400, function () {
            return SummaryPenjagaan::query()
                ->whereNotNull('fungsi')
                ->where('fungsi', '!=', '')
                ->distinct()
                ->orderBy('fungsi')
                ->pluck('fungsi');
        });
    }

    public function getSummaryBulanan(array $fungsi, int $tahunIni, int $tahunLalu): array
    {
        sort($fungsi);
        $fungsiKey = ! empty($fungsi) ? implode(',', $fungsi) : 'all';
        $cacheKey = 'penjagaan_bulanan_v3_'.md5("y:{$tahunIni}_f:{$fungsiKey}");

        return Cache::remember($cacheKey, 3600, function () use ($fungsi, $tahunIni, $tahunLalu) {
            $query = SummaryPenjagaan::query()
                ->toBase()
                ->selectRaw('
                    bln_setor,
                    SUM(CASE WHEN thn_setor = ? THEN total_setor ELSE 0 END) as total_ini,
                    SUM(CASE WHEN thn_setor = ? THEN total_setor ELSE 0 END) as total_lalu
                ', [$tahunIni, $tahunLalu])
                ->whereIn('thn_setor', [$tahunIni, $tahunLalu]);

            if (! empty($fungsi)) {
                $query->whereIn('fungsi', $fungsi);
            }

            $results = $query->groupBy('bln_setor')->get();

            $ini = [];
            $lalu = [];

            foreach ($results as $row) {
                $ini[$row->bln_setor] = (float) $row->total_ini;
                $lalu[$row->bln_setor] = (float) $row->total_lalu;
            }

            return [
                'ini' => $ini,
                'lalu' => $lalu,
            ];
        });
    }

    public function getSummaryHarian(int $bulan, array $fungsi, int $tahunIni, int $tahunLalu): array
    {
        sort($fungsi);
        $fungsiKey = ! empty($fungsi) ? implode(',', $fungsi) : 'all';
        $cacheKey = 'penjagaan_harian_v3_'.md5("y:{$tahunIni}_b:{$bulan}_f:{$fungsiKey}");

        return Cache::remember($cacheKey, 3600, function () use ($bulan, $fungsi, $tahunIni, $tahunLalu) {
            $query = SummaryPenjagaan::query()
                ->toBase()
                ->selectRaw('
                    DAY(tgl_setor) as tgl,
                    SUM(CASE WHEN thn_setor = ? THEN total_setor ELSE 0 END) as total_ini,
                    SUM(CASE WHEN thn_setor = ? THEN total_setor ELSE 0 END) as total_lalu
                ', [$tahunIni, $tahunLalu])
                ->whereIn('thn_setor', [$tahunIni, $tahunLalu])
                ->where('bln_setor', $bulan);

            if (! empty($fungsi)) {
                $query->whereIn('fungsi', $fungsi);
            }

            $results = $query->groupBy('tgl')->get();

            $ini = [];
            $lalu = [];

            foreach ($results as $row) {
                $ini[$row->tgl] = (float) $row->total_ini;
                $lalu[$row->tgl] = (float) $row->total_lalu;
            }

            return [
                'ini' => $ini,
                'lalu' => $lalu,
            ];
        });
    }

    public function getSummaryVsBulanLalu(int $bulan, int $bulanLalu, int $tahunIni, int $tahunBulanLalu, array $fungsi): array
    {
        sort($fungsi);
        $fungsiKey = ! empty($fungsi) ? implode(',', $fungsi) : 'all';
        $cacheKey = 'penjagaan_vs_bulan_lalu_v3_'.md5("y:{$tahunIni}_b:{$bulan}_f:{$fungsiKey}");

        return Cache::remember($cacheKey, 3600, function () use ($bulan, $bulanLalu, $tahunIni, $tahunBulanLalu, $fungsi) {
            $query = SummaryPenjagaan::query()
                ->toBase()
                ->selectRaw('
                    DAY(tgl_setor) as tgl,
                    SUM(CASE WHEN thn_setor = ? AND bln_setor = ? THEN total_setor ELSE 0 END) as total_ini,
                    SUM(CASE WHEN thn_setor = ? AND bln_setor = ? THEN total_setor ELSE 0 END) as total_lalu
                ', [$tahunIni, $bulan, $tahunBulanLalu, $bulanLalu])
                ->where(function ($q) use ($bulan, $bulanLalu, $tahunIni, $tahunBulanLalu) {
                    $q->where(function ($q1) use ($bulan, $tahunIni) {
                        $q1->where('thn_setor', $tahunIni)->where('bln_setor', $bulan);
                    })->orWhere(function ($q2) use ($bulanLalu, $tahunBulanLalu) {
                        $q2->where('thn_setor', $tahunBulanLalu)->where('bln_setor', $bulanLalu);
                    });
                });

            if (! empty($fungsi)) {
                $query->whereIn('fungsi', $fungsi);
            }

            $results = $query->groupBy('tgl')->get();

            $ini = [];
            $lalu = [];

            foreach ($results as $row) {
                $ini[$row->tgl] = (float) $row->total_ini;
                $lalu[$row->tgl] = (float) $row->total_lalu;
            }

            return [
                'ini' => $ini,
                'lalu' => $lalu,
            ];
        });
    }

    /**
     * Query Builder untuk Export Penjagaan Bulanan
     */
    public function getExportBulananQuery(array $fungsi, int $tahunIni, int $tahunLalu, array $selectColumns): Builder
    {
        $query = DB::table('drm')
            ->select($selectColumns)
            ->whereIn('thn_setor', [$tahunLalu, $tahunIni]);

        if (! empty($fungsi)) {
            $query->whereIn('fungsi', $fungsi);
        }

        return $query->orderBy('thn_setor', 'desc')->orderBy('bln_setor', 'desc');
    }

    /**
     * Query Builder untuk Export Penjagaan Harian
     */
    public function getExportHarianQuery(int $bulan, array $fungsi, int $tahunIni, int $tahunLalu, array $selectColumns): Builder
    {
        $query = DB::table('drm')
            ->select($selectColumns)
            ->whereIn('thn_setor', [$tahunLalu, $tahunIni])
            ->where('bln_setor', $bulan);

        if (! empty($fungsi)) {
            $query->whereIn('fungsi', $fungsi);
        }

        return $query->orderBy('tgl_setor', 'desc');
    }

    /**
     * Query Builder untuk Export Penjagaan Vs Bulan Lalu
     */
    public function getExportVsBulanLaluQuery(int $bulan, int $bulanLalu, int $tahunIni, int $tahunBulanLalu, array $fungsi, array $selectColumns): Builder
    {
        $query = DB::table('drm')
            ->select($selectColumns)
            ->where(function ($q) use ($bulan, $bulanLalu, $tahunIni, $tahunBulanLalu) {
                $q->where(function ($q1) use ($bulan, $tahunIni) {
                    $q1->where('thn_setor', $tahunIni)->where('bln_setor', $bulan);
                })->orWhere(function ($q2) use ($bulanLalu, $tahunBulanLalu) {
                    $q2->where('thn_setor', $tahunBulanLalu)->where('bln_setor', $bulanLalu);
                });
            });

        if (! empty($fungsi)) {
            $query->whereIn('fungsi', $fungsi);
        }

        return $query->orderBy('tgl_setor', 'desc');
    }
}
