<?php

namespace App\Repositories;

use App\Models\SummaryPenjagaan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenjagaanRepository
{
    /**
     * Ambil opsi fungsi dengan caching 24 jam dari tabel summary.
     */
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

    /**
     * Agregasi Penjagaan Bulanan (Tahun Ini vs Tahun Lalu)
     * Membaca dari tabel summary_penjagaan
     */
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

    /**
     * Agregasi Penjagaan Harian (Tahun Ini vs Tahun Lalu pada Bulan yang Sama)
     * Membaca dari tabel summary_penjagaan
     */
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

    /**
     * Agregasi Penjagaan vs Bulan Lalu (Bulan Ini vs Bulan Sebelumnya)
     * Membaca dari tabel summary_penjagaan
     */
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
     * Export Detil Transaksi ke Streamed CSV Response dari tabel drm
     */
    public function exportCsv(string $filename, callable $queryCallback): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Tahun Setor', 'Bulan Setor', 'Tanggal Setor', 'NPWP15',
            'Nama WP', 'Jenis', 'Fungsi', 'Kode MAP', 'Kode Bayar',
            'Masa 1', 'Masa 2', 'Tahun Pajak', 'Jumlah Setor (Rp)', 'NTPN',
        ];

        $callback = function () use ($queryCallback, $columns) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM untuk Excel
            fputcsv($file, $columns);

            $query = DB::table('drm')
                ->select([
                    'thn_setor', 'bln_setor', 'tgl_setor', 'npwp15',
                    'nama_wp', 'jenis', 'fungsi', 'kd_map', 'kd_bayar',
                    'masa1', 'masa2', 'thn_pajak', 'jml_setor', 'ntpn',
                ]);

            $queryCallback($query);

            foreach ($query->cursor() as $row) {
                fputcsv($file, [
                    $row->thn_setor,
                    $row->bln_setor,
                    $row->tgl_setor,
                    $row->npwp15,
                    $row->nama_wp,
                    $row->jenis,
                    $row->fungsi,
                    $row->kd_map,
                    $row->kd_bayar,
                    $row->masa1,
                    $row->masa2,
                    $row->thn_pajak,
                    $row->jml_setor,
                    $row->ntpn,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
