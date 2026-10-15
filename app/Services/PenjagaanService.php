<?php

namespace App\Services;

use App\Repositories\PenjagaanRepository;
use Illuminate\Http\Request;

class PenjagaanService
{
    public function __construct(protected PenjagaanRepository $repository) {}

    public function resolveFungsiFilter(Request $request): array
    {
        $fungsiOptions = $this->repository->getFungsiOptions();
        if ($request->has('fungsi')) {
            $fungsi = array_values(array_filter((array) $request->input('fungsi', [])));

            return [$fungsiOptions, $fungsi];
        }

        return [$fungsiOptions, $fungsiOptions->toArray()];
    }

    public function resolveBulan(Request $request): int
    {
        $bulan = (int) $request->input('bulan', date('m'));

        return ($bulan < 1 || $bulan > 12) ? (int) date('m') : $bulan;
    }

    public function normalizeBulanan(array $raw): array
    {
        $ini = $lalu = [];
        for ($m = 1; $m <= 12; $m++) {
            $ini[] = (float) ($raw['ini'][$m] ?? 0);
            $lalu[] = (float) ($raw['lalu'][$m] ?? 0);
        }

        return [$ini, $lalu];
    }

    public function normalizeHarian(array $raw): array
    {
        $ini = $lalu = [];
        foreach (range(1, 31) as $d) {
            $ini[] = (float) ($raw['ini'][$d] ?? 0);
            $lalu[] = (float) ($raw['lalu'][$d] ?? 0);
        }

        return [$ini, $lalu];
    }

    // Untuk chips & 3 mini-card
    public function calcSummary(array $ini, array $lalu): array
    {
        $totalIni = array_sum($ini);
        $totalLalu = array_sum($lalu);
        $growth = $totalLalu != 0 ? (($totalIni - $totalLalu) / $totalLalu * 100) : 0;

        return [$totalIni, $totalLalu, $growth];
    }
}
