<?php

namespace App\Services;

use App\Repositories\PenjagaanRepository;
use Illuminate\Http\Request;

/**
 * PenjagaanService - sesuai .airules thin controller
 * 1x hit cache fungsi (hemat I/O HDD 1TB)
 * Normalisasi data 12 bulan / 31 hari biar chart gak bolong
 */
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
}
