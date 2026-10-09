<?php

namespace App\Repositories;

use App\Models\SummaryMartPenerimaan;
use App\Models\Target;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use stdClass;

class DashboardRepository
{
    public function getTargetByTahun(int $tahun): ?Target
    {
        return Cache::remember("dashboard_target_{$tahun}", 600, function () use ($tahun) {
            return Target::where('tahun', $tahun)->first();
        });
    }

    public function getSummaryMetrics(int $thnIni, int $blnAwal, int $blnAkhir): stdClass
    {
        $thnLalu = $thnIni - 1;
        $cacheKey = "dashboard_summary_v4_{$thnIni}_{$blnAwal}_{$blnAkhir}";

        return Cache::remember($cacheKey, 600, function () use ($thnIni, $thnLalu, $blnAwal, $blnAkhir) {
            $ppmFungsi = ['PPM BRUTO', 'SPMKP'];
            $pkmFungsi = [
                'AKT PEMERIKSAAN', 'AKT PENGAWASAN', 'AKT PENAGIHAN', 'LAINNYA',
                'AKT PENEGAKAN HUKUM', 'WRA PENGAWASAN', 'WRA EDUKASI', 'WRA PENEGAKAN HUKUM',
            ];
            $pbpFungsi = ['PBP'];
            $pengawasanFungsi = ['AKT PENGAWASAN', 'LAINNYA', 'WRA PENGAWASAN', 'WRA EDUKASI'];
            $pemeriksaanFungsi = ['AKT PEMERIKSAAN'];
            $penagihanFungsi = ['AKT PENAGIHAN'];

            /** @var stdClass|null $result */
            $result = SummaryMartPenerimaan::query()
                ->toBase()
                ->whereIn('thn_setor', [$thnIni, $thnLalu])
                ->whereBetween('bln_setor', [$blnAwal, $blnAkhir])
                ->selectRaw('
                    /* Penerimaan Total */
                    SUM(CASE WHEN thn_setor = ? THEN total_setor ELSE 0 END) as penerimaanSaatIni,
                    SUM(CASE WHEN thn_setor = ? AND bln_setor < ? THEN total_setor ELSE 0 END) as penerimaanBlnLalu,
                    SUM(CASE WHEN thn_setor = ? THEN total_setor ELSE 0 END) as penerimaanThnLalu,

                    /* Realisasi Tahun Ini */
                    SUM(CASE WHEN thn_setor = ? AND fungsi IN ('.$this->quoteArray($ppmFungsi).') THEN total_setor ELSE 0 END) as realisasiPPM,
                    SUM(CASE WHEN thn_setor = ? AND fungsi IN ('.$this->quoteArray($pkmFungsi).') THEN total_setor ELSE 0 END) as realisasiPKM,
                    SUM(CASE WHEN thn_setor = ? AND fungsi IN ('.$this->quoteArray($pbpFungsi).') THEN total_setor ELSE 0 END) as realisasiPBP,
                    SUM(CASE WHEN thn_setor = ? AND fungsi IN ('.$this->quoteArray($pengawasanFungsi).') THEN total_setor ELSE 0 END) as realisasiPengawasan,
                    SUM(CASE WHEN thn_setor = ? AND fungsi IN ('.$this->quoteArray($pemeriksaanFungsi).') THEN total_setor ELSE 0 END) as realisasiPemeriksaan,
                    SUM(CASE WHEN thn_setor = ? AND fungsi IN ('.$this->quoteArray($penagihanFungsi).') THEN total_setor ELSE 0 END) as realisasiPenagihan,

                    /* Realisasi Tahun Lalu */
                    SUM(CASE WHEN thn_setor = ? AND fungsi IN ('.$this->quoteArray($ppmFungsi).') THEN total_setor ELSE 0 END) as realisasiPPMLalu,
                    SUM(CASE WHEN thn_setor = ? AND fungsi IN ('.$this->quoteArray($pkmFungsi).') THEN total_setor ELSE 0 END) as realisasiPKMLalu,
                    SUM(CASE WHEN thn_setor = ? AND fungsi IN ('.$this->quoteArray($pengawasanFungsi).') THEN total_setor ELSE 0 END) as realisasiPengawasanLalu,
                    SUM(CASE WHEN thn_setor = ? AND fungsi IN ('.$this->quoteArray($pemeriksaanFungsi).') THEN total_setor ELSE 0 END) as realisasiPemeriksaanLalu,
                    SUM(CASE WHEN thn_setor = ? AND fungsi IN ('.$this->quoteArray($penagihanFungsi).') THEN total_setor ELSE 0 END) as realisasiPenagihanLalu
                ', [
                    $thnIni, $thnIni, $blnAkhir, $thnLalu,
                    $thnIni, $thnIni, $thnIni, $thnIni, $thnIni, $thnIni,
                    $thnLalu, $thnLalu, $thnLalu, $thnLalu, $thnLalu,
                ])
                ->first();

            return $result ?? new stdClass;
        });
    }

    /**
     * Ambil Query Builder DRM Detil untuk Export Dashboard
     */
    public function getExportDetilQuery(array $tahunFilter, int $bulanAwal, int $bulanAkhir, array $selectColumns): Builder
    {
        return DB::table('drm')
            ->select($selectColumns)
            ->whereIn('thn_setor', $tahunFilter)
            ->whereBetween('bln_setor', [$bulanAwal, $bulanAkhir])
            ->orderBy('id', 'asc');
    }

    private function quoteArray(array $array): string
    {
        return implode(',', array_map(fn ($item) => "'".addslashes($item)."'", $array));
    }
}
