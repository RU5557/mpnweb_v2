<?php

namespace App\Services;

use App\Models\Target;
use stdClass;

/**
 * DashboardService - Otak Kalkulasi Ringkasan Penerimaan
 * Sesuai .airules: thin controller, logic di service, hemat memory
 *
 * Pemahaman Bisnis:
 * - PPM = Penerimaan Pajak dari Wajib Pajak yang diawasi (PPM BRUTO, SPKMP)
 * - PKM = Penerimaan dari Kegiatan Pengawasan, Pemeriksaan, Penagihan (AKT PENGAWASAN, dll)
 * - PBP = Pajak Bumi dan Bangunan
 * - Growth MoM = (SaatIni - BlnLalu) / BlnLalu * 100 -> kesehatan bulanan
 * - Growth YoY = (SaatIni - ThnLalu) / ThnLalu * 100 -> kesehatan tahunan
 * - Capaian Kantor = Realisasi / Target Kantor * 100
 */
class DashboardService
{
    public const PPM_FUNGSI = ['PPM BRUTO', 'SPMKP'];

    public const PKM_FUNGSI = [
        'AKT PEMERIKSAAN', 'AKT PENGAWASAN', 'AKT PENAGIHAN', 'LAINNYA',
        'AKT PENEGAKAN HUKUM', 'WRA PENGAWASAN', 'WRA EDUKASI', 'WRA PENEGAKAN HUKUM',
    ];

    public const PBP_FUNGSI = ['PBP'];

    public const PENGAWASAN_FUNGSI = ['AKT PENGAWASAN', 'LAINNYA', 'WRA PENGAWASAN', 'WRA EDUKASI'];

    public const PEMERIKSAAN_FUNGSI = ['AKT PEMERIKSAAN'];

    public const PENAGIHAN_FUNGSI = ['AKT PENAGIHAN'];

    public function getListBulan(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    public function resolvePeriod(array $input): array
    {
        $currentYear = (int) date('Y');
        $tahun = (int) ($input['tahun'] ?? $currentYear);
        $blnAwal = (int) ($input['bulan_awal'] ?? 1);
        $blnAkhir = (int) ($input['bulan_akhir'] ?? $input['bulan'] ?? date('n'));

        if ($tahun < 2020 || $tahun > $currentYear + 1) {
            $tahun = $currentYear;
        }
        if ($blnAwal < 1 || $blnAwal > 12) {
            $blnAwal = 1;
        }
        if ($blnAkhir < 1 || $blnAkhir > 12) {
            $blnAkhir = (int) date('n');
        }
        if ($blnAwal > $blnAkhir) {
            $blnAwal = $blnAkhir;
        }

        return [$tahun, $blnAwal, $blnAkhir];
    }

    private function calcPersen(?float $target, ?float $realisasi): float
    {
        return ($target ?? 0) > 0 ? ($realisasi / $target) * 100 : 0;
    }

    private function calcGrowth(?float $saatIni, ?float $lalu): float
    {
        return $lalu != 0 ? (($saatIni - $lalu) / abs($lalu)) * 100 : 0;
    }

    /**
     * Build metrics terstruktur - hemat memory, gak extract 12 variabel manual
     */
    public function buildMetrics(stdClass $summary, ?Target $target): array
    {
        $targetKantor = $target?->target_kantor ?? 0;
        $penerimaanSaatIni = $summary->penerimaanSaatIni ?? 0;
        $penerimaanBlnLalu = $summary->penerimaanBlnLalu ?? 0;
        $penerimaanThnLalu = $summary->penerimaanThnLalu ?? 0;

        return [
            'summary' => [
                'penerimaanSaatIni' => $penerimaanSaatIni,
                'penerimaanBlnLalu' => $penerimaanBlnLalu,
                'penerimaanThnLalu' => $penerimaanThnLalu,
                'capaianKantor' => $targetKantor > 0 ? ($penerimaanSaatIni / $targetKantor) * 100 : 0,
                'growthMoM' => $this->calcGrowth($penerimaanSaatIni, $penerimaanBlnLalu),
                'growthYoY' => $this->calcGrowth($penerimaanSaatIni, $penerimaanThnLalu),
                'targetKantor' => $targetKantor,
            ],
            'cards' => [
                'ppm' => [
                    'key' => 'ppm',
                    'title' => 'PPM',
                    'subtitle' => 'Pajak Pengawasan Mandiri',
                    'target' => $target?->target_ppm ?? 0,
                    'realisasi' => $summary->realisasiPPM ?? 0,
                    'realisasiLalu' => $summary->realisasiPPMLalu ?? 0,
                    'persen' => $this->calcPersen($target?->target_ppm, $summary->realisasiPPM ?? 0),
                    'growthYoY' => $this->calcGrowth($summary->realisasiPPM ?? 0, $summary->realisasiPPMLalu ?? 0),
                ],
                'pkm' => [
                    'key' => 'pkm',
                    'title' => 'PKM Total',
                    'subtitle' => 'Pengawasan + Pemeriksaan + Penagihan',
                    'target' => $target?->target_pkm ?? 0,
                    'realisasi' => $summary->realisasiPKM ?? 0,
                    'realisasiLalu' => $summary->realisasiPKMLalu ?? 0,
                    'persen' => $this->calcPersen($target?->target_pkm, $summary->realisasiPKM ?? 0),
                    'growthYoY' => $this->calcGrowth($summary->realisasiPKM ?? 0, $summary->realisasiPKMLalu ?? 0),
                ],
                'pbp' => [
                    'key' => 'pbp',
                    'title' => 'PBP',
                    'subtitle' => 'Pajak Bumi & Bangunan',
                    'target' => $target?->target_pbp ?? 0,
                    'realisasi' => $summary->realisasiPBP ?? 0,
                    'persen' => $this->calcPersen($target?->target_pbp, $summary->realisasiPBP ?? 0),
                    'sisa' => max(0, ($target?->target_pbp ?? 0) - ($summary->realisasiPBP ?? 0)),
                    'isSisa' => true,
                ],
                'pengawasan' => [
                    'key' => 'pengawasan',
                    'title' => 'PKM Pengawasan',
                    'subtitle' => 'AKT & WRA Pengawasan',
                    'target' => $target?->target_pkm_pengawasan ?? 0,
                    'realisasi' => $summary->realisasiPengawasan ?? 0,
                    'realisasiLalu' => $summary->realisasiPengawasanLalu ?? 0,
                    'persen' => $this->calcPersen($target?->target_pkm_pengawasan, $summary->realisasiPengawasan ?? 0),
                    'growthYoY' => $this->calcGrowth($summary->realisasiPengawasan ?? 0, $summary->realisasiPengawasanLalu ?? 0),
                ],
                'pemeriksaan' => [
                    'key' => 'pemeriksaan',
                    'title' => 'PKM Pemeriksaan',
                    'subtitle' => 'AKT Pemeriksaan',
                    'target' => $target?->target_pkm_pemeriksaan ?? 0,
                    'realisasi' => $summary->realisasiPemeriksaan ?? 0,
                    'realisasiLalu' => $summary->realisasiPemeriksaanLalu ?? 0,
                    'persen' => $this->calcPersen($target?->target_pkm_pemeriksaan, $summary->realisasiPemeriksaan ?? 0),
                    'growthYoY' => $this->calcGrowth($summary->realisasiPemeriksaan ?? 0, $summary->realisasiPemeriksaanLalu ?? 0),
                ],
                'penagihan' => [
                    'key' => 'penagihan',
                    'title' => 'PKM Penagihan',
                    'subtitle' => 'AKT Penagihan',
                    'target' => $target?->target_pkm_penagihan ?? 0,
                    'realisasi' => $summary->realisasiPenagihan ?? 0,
                    'realisasiLalu' => $summary->realisasiPenagihanLalu ?? 0,
                    'persen' => $this->calcPersen($target?->target_pkm_penagihan, $summary->realisasiPenagihan ?? 0),
                    'growthYoY' => $this->calcGrowth($summary->realisasiPenagihan ?? 0, $summary->realisasiPenagihanLalu ?? 0),
                ],
            ],
        ];
    }
}
