<?php

namespace App\Repositories;

use App\Models\Spt;
use App\Services\CsvExportService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SptRepository
{
    public function __construct(
        protected CsvExportService $csvExportService
    ) {}

    /**
     * Reusable Query Builder untuk Pencarian & Export Data SPT
     */
    public function buildSptQuery(array $filters): Builder
    {
        $query = DB::table('spt')
            // Join ke mfwp (Masterfile WP) berbasis NPWP 15 Digit / NPWP
            ->leftJoin('mfwp', function ($join) {
                $join->on('spt.npwp', '=', 'mfwp.npwp15')
                    ->orOn('spt.npwp', '=', 'mfwp.npwp');
            })
            // Join ke pegawai berbasis NIP AR (jabatan = '5') & Tahun
            ->leftJoin('pegawai', function ($join) {
                $join->on('mfwp.nip_ar', '=', 'pegawai.nip')
                    ->on('spt.tahun', '=', 'pegawai.tahun')
                    ->where('pegawai.jabatan', '=', '5');
            });

        // 1. Filter NPWP
        if (! empty($filters['npwp'])) {
            $cleanNpwp = preg_replace('/[^0-9]/', '', $filters['npwp']);

            if (strlen($cleanNpwp) === 15 || strlen($cleanNpwp) === 16) {
                $query->where('spt.npwp', $cleanNpwp);
            } else {
                $query->where('spt.npwp', 'LIKE', $cleanNpwp.'%');
            }
        }

        // 2. Filter Nama Wajib Pajak
        if (! empty($filters['nama'])) {
            $searchTerm = trim($filters['nama']);
            $query->where('spt.nama', 'LIKE', '%'.$searchTerm.'%');
        }

        // 3. Filter Masa Pajak
        if (! empty($filters['masa1'])) {
            $query->where('spt.masa1', '>=', (int) $filters['masa1']);
        }
        if (! empty($filters['masa2'])) {
            $query->where('spt.masa2', '<=', (int) $filters['masa2']);
        }

        // 4. Filter Tahun Pajak
        if (! empty($filters['thn_pajak'])) {
            $query->where('spt.thn_pajak', (int) $filters['thn_pajak']);
        }

        // 5. Filter Pembetulan
        if (! empty($filters['pembetulan'])) {
            $query->where('spt.pembetulan', trim($filters['pembetulan']));
        }

        // 6. Filter Jenis SPT
        if (! empty($filters['jenis_spt'])) {
            $query->where('spt.jenis_spt', $filters['jenis_spt']);
        }

        // 7. Filter Status SPT
        if (! empty($filters['status_spt'])) {
            $query->where('spt.status_spt', $filters['status_spt']);
        }

        // 8. Filter Tanggal Terima (Range)
        if (! empty($filters['tgl_terima_mulai'])) {
            $query->where('spt.tgl_terima', '>=', $filters['tgl_terima_mulai']);
        }
        if (! empty($filters['tgl_terima_selesai'])) {
            $query->where('spt.tgl_terima', '<=', $filters['tgl_terima_selesai']);
        }

        // 9. Filter AR (NIP AR)
        if (! empty($filters['nip_ar'])) {
            $query->where('mfwp.nip_ar', $filters['nip_ar']);
        }

        return $query;
    }

    /**
     * Pencarian SPT Paginated
     */
    public function searchSpt(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->buildSptQuery($filters)
            ->select([
                'spt.nomor_tanda_terima',
                'spt.kanal_pelaporan',
                'spt.nama',
                'spt.npwp',
                'spt.jenis_spt',
                'spt.status_spt',
                'spt.masa1',
                'spt.masa2',
                'spt.thn_pajak',
                'spt.pembetulan',
                'spt.tgl_terima',
                'mfwp.nip_ar',
                'pegawai.nama as nama_ar',
            ])
            ->orderBy('spt.tgl_terima', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Streaming CSV Export untuk Data SPT
     */
    public function exportSptCsv(array $filters): StreamedResponse
    {
        $filename = 'export_spt_'.date('Ymd_His').'.csv';

        $columnsMap = [
            'nomor_tanda_terima' => 'NO BPE / TANDA TERIMA',
            'kanal_pelaporan' => 'KANAL PELAPORAN',
            'npwp' => 'NPWP',
            'nama' => 'NAMA WAJIB PAJAK',
            'jenis_spt' => 'JENIS SPT',
            'status_spt' => 'STATUS SPT',
            'masa1' => 'MASA AWAL',
            'masa2' => 'MASA AKHIR',
            'thn_pajak' => 'THN PAJAK',
            'pembetulan' => 'PEMBETULAN',
            'tgl_terima' => 'TGL TERIMA',
            'nip_ar' => 'NIP AR',
            'nama_ar' => 'NAMA AR',
        ];

        $query = $this->buildSptQuery($filters)
            ->select([
                'spt.nomor_tanda_terima',
                'spt.kanal_pelaporan',
                'spt.npwp',
                'spt.nama',
                'spt.jenis_spt',
                'spt.status_spt',
                'spt.masa1',
                'spt.masa2',
                'spt.thn_pajak',
                'spt.pembetulan',
                'spt.tgl_terima',
                'mfwp.nip_ar',
                'pegawai.nama as nama_ar',
            ])
            ->orderBy('spt.tgl_terima', 'desc');

        return $this->csvExportService->exportFromQuery($filename, $columnsMap, $query, 3000);
    }

    public function getDistinctJenisSpt(): Collection
    {
        return DB::table('spt')
            ->whereNotNull('jenis_spt')
            ->where('jenis_spt', '!=', '')
            ->distinct()
            ->orderBy('jenis_spt')
            ->pluck('jenis_spt');
    }

    public function getDistinctStatusSpt(): Collection
    {
        return DB::table('spt')
            ->whereNotNull('status_spt')
            ->where('status_spt', '!=', '')
            ->distinct()
            ->orderBy('status_spt')
            ->pluck('status_spt');
    }

    public function getDistinctPembetulan(): Collection
    {
        return DB::table('spt')
            ->whereNotNull('pembetulan')
            ->where('pembetulan', '!=', '')
            ->distinct()
            ->orderBy('pembetulan')
            ->pluck('pembetulan');
    }

    public function getDistinctAR(int $tahun = 2026): Collection
    {
        return DB::table('pegawai')
            ->select('nip', 'nama')
            ->where('jabatan', '5')
            ->where('tahun', $tahun)
            ->whereNotNull('nip')
            ->where('nip', '!=', '')
            ->groupBy('nip', 'nama')
            ->orderBy('nama', 'asc')
            ->get();
    }
}
