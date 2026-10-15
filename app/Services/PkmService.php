<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PkmService
{
    public function resolvePeriod(Request $request): array
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $bulan = (int) $request->input('bulan', date('n'));
        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = (int) date('Y');
        }
        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) date('n');
        }

        return [$tahun, $bulan];
    }

    public function resolveSeksiFilter(Request $request): string
    {
        return trim((string) $request->input('seksi', ''));
    }

    public function resolveDspcFilter(Request $request): string
    {
        $f = (string) $request->input('dspc_filter', '');

        return in_array($f, ['DSPC', 'NON-DSPC'], true) ? $f : '';
    }

    public function resolveSearch(Request $request): string
    {
        return trim((string) $request->input('search', ''));
    }

    public function sumColumn($data, string $col): float
    {
        if ($data instanceof LengthAwarePaginator) {
            return (float) $data->getCollection()->sum($col);
        }
        if ($data instanceof Collection) {
            return (float) $data->sum($col);
        }

        return (float) $data;
    }

    public function calcGrowth(float $ini, float $lalu): float
    {
        return $lalu != 0 ? (($ini - $lalu) / $lalu * 100) : 0;
    }

    public function calcComparison(float $totalSaatIni, float $totalBulanLalu): array
    {
        $selisih = $totalSaatIni - $totalBulanLalu;
        $growth = $this->calcGrowth($totalSaatIni, $totalBulanLalu);

        return [
            'saat_ini' => $totalSaatIni,
            'bulan_lalu' => $totalBulanLalu,
            'selisih' => $selisih,
            'growth' => $growth,
        ];
    }

    public function getBulanLaluLabel(int $tahun, int $bulan): array
    {
        $bulanLalu = $bulan - 1;
        $tahunLalu = $tahun;
        if ($bulanLalu < 1) {
            $bulanLalu = 12;
            $tahunLalu = $tahun - 1;
        }

        return [$tahunLalu, $bulanLalu];
    }
}
