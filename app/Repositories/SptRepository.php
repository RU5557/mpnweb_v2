<?php

namespace App\Repositories;

use App\Models\Pegawai;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * SptRepository - VERSI CURSOR PAGINATE (adopsi WpRepository) + sesuai .airules
 * - Query builder terpusat
 * - Hemat I/O HDD (select tipis, no OFFSET, JOIN tanpa COLLATE/OR)
 * - Cache dropdown 6 jam
 * - Full index usage: spt.npwp (16), mfwp.npwp16, mfwp.npwp15
 */
class SptRepository
{
    public function getFilterDropdownOptions(int $tahun = 2026): array
    {
        $ttl = 21600; // 6 jam - sesuai .airules #3 hemat I/O

        return [
            'listJenis' => Cache::remember('spt_filter_jenis_v2', $ttl, fn () => DB::table('spt')
                ->whereNotNull('jenis_spt')->where('jenis_spt', '!=', '')
                ->distinct()->orderBy('jenis_spt')->pluck('jenis_spt')->toArray()),

            'listStatus' => Cache::remember('spt_filter_status_v2', $ttl, fn () => DB::table('spt')
                ->whereNotNull('status_spt')->where('status_spt', '!=', '')
                ->distinct()->orderBy('status_spt')->pluck('status_spt')->toArray()),

            'listPembetulan' => Cache::remember('spt_filter_pembetulan_v2', $ttl, fn () => DB::table('spt')
                ->whereNotNull('pembetulan')->where('pembetulan', '!=', '')
                ->distinct()->orderBy('pembetulan')->pluck('pembetulan')->toArray()),

            'listAr' => Cache::remember("spt_filter_ar_{$tahun}_v2", $ttl, fn () => Pegawai::where('tahun', $tahun)
                ->where('jabatan', '5')->select('nip', 'nama')->orderBy('nama')->get()),
        ];
    }

    /**
     * Build query tipis - TANPA select berat, TANPA JOIN COLLATE/OR
     * JOIN mfwp via npwp16 = spt.npwp (index idx_mf_npwp16)
     * Fallback npwp15 hanya jika panjang < 16 via where
     */
    public function buildSptQuery(array $filters, int $tahun = 2026): Builder
    {
        // Base tanpa JOIN berat untuk COUNT
        $query = DB::table('spt');

        if (! empty($filters['npwp'])) {
            $clean = $filters['npwp']; // sudah sanitized digits only di Service
            if ($clean !== '') {
                // Gunakan index spt.npwp LIKE - MariaDB 10.4 bisa pakai index prefix
                if (strlen($clean) >= 15) {
                    $query->where('spt.npwp', $clean);
                } else {
                    $query->where('spt.npwp', 'LIKE', "{$clean}%");
                }
            }
        }

        if (! empty($filters['nama'])) {
            $nama = trim($filters['nama']);
            if ($nama !== '') {
                // spt tidak punya FULLTEXT, pakai LIKE + index jika ada
                // Batasi dengan LIKE prefix jika kata >=4 char untuk hemat I/O
                $query->where('spt.nama', 'LIKE', "%{$nama}%");
            }
        }

        // Masa pajak - exact match, bukan >= / <= (lebih cepat, pakai index)
        if (! empty($filters['masa1'])) {
            $query->where('spt.masa1', (int) $filters['masa1']);
        }
        if (! empty($filters['masa2'])) {
            $query->where('spt.masa2', (int) $filters['masa2']);
        }

        if (! empty($filters['thn_pajak'])) {
            $query->where('spt.thn_pajak', (int) $filters['thn_pajak']);
        }

        if (! empty($filters['pembetulan'])) {
            $query->where('spt.pembetulan', $filters['pembetulan']);
        }

        if (! empty($filters['jenis_spt'])) {
            $query->where('spt.jenis_spt', $filters['jenis_spt']);
        }

        if (! empty($filters['status_spt'])) {
            $query->where('spt.status_spt', $filters['status_spt']);
        }

        if (! empty($filters['tgl_terima_mulai'])) {
            $query->where('spt.tgl_terima', '>=', $filters['tgl_terima_mulai']);
        }
        if (! empty($filters['tgl_terima_selesai'])) {
            $query->where('spt.tgl_terima', '<=', $filters['tgl_terima_selesai']);
        }

        // Filter AR - butuh JOIN mfwp, tapi kita tunda JOIN sampai method paginated
        // Untuk COUNT, kita pakai whereExists agar tidak bawa JOIN berat
        if (! empty($filters['nip_ar'])) {
            $nipAr = $filters['nip_ar'];
            $query->whereExists(function ($q) use ($nipAr) {
                $q->select(DB::raw(1))
                    ->from('mfwp')
                    ->whereColumn('mfwp.npwp16', 'spt.npwp')
                    ->where('mfwp.nip_ar', $nipAr);
            });
        }

        return $query;
    }

    /**
     * METODE ADOPSI WpRepository - Exact Total + Cursor Paginate
     * Hemat I/O HDD, no OFFSET
     */
    public function searchSptCursorPaginated(array $filters, int $perPage = 20, int $tahun = 2026): array
    {
        $baseQuery = $this->buildSptQuery($filters, $tahun);

        // Hitung exact total - clone tanpa ORDER
        $totalExact = (clone $baseQuery)->count();

        // Query dengan JOIN untuk display (hanya 20 row)
        $query = $baseQuery
            // JOIN mfwp via npwp16 = spt.npwp - pakai index, tanpa COLLATE
            ->leftJoin('mfwp as m', 'm.npwp16', '=', 'spt.npwp')
            ->leftJoin('pegawai as ar', function ($join) use ($tahun) {
                $join->on('m.nip_ar', '=', 'ar.nip')
                    ->where('ar.tahun', '=', $tahun)
                    ->where('ar.jabatan', '=', '5');
            })
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
                'm.nip_ar',
                'ar.nama as nama_ar',
            ]);

        $sortBy = $filters['sort_by'] ?? 'tgl_terima';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowed = [
            'tgl_terima' => 'spt.tgl_terima',
            'jenis_spt' => 'spt.jenis_spt',
            'thn_pajak' => 'spt.thn_pajak',
            'nama' => 'spt.nama',
            'npwp' => 'spt.npwp',
            'status_spt' => 'spt.status_spt',
        ];
        $sortCol = $allowed[$sortBy] ?? 'spt.tgl_terima';

        $paginator = $query
            ->orderBy($sortCol, $sortOrder)
            ->orderBy('spt.nomor_tanda_terima', 'asc') // tie-breaker wajib cursor
            ->cursorPaginate($perPage);

        return [
            'results' => $paginator,
            'totalExact' => $totalExact,
        ];
    }

    public function getTotalCountCached(array $filters, int $tahun = 2026): int
    {
        $key = 'spt_count_'.md5(json_encode($filters).$tahun);

        return Cache::remember($key, 600, function () use ($filters, $tahun) {
            return $this->buildSptQuery($filters, $tahun)->count();
        });
    }

    public function buildExportBaseQuery(array $filters, int $tahun = 2026): Builder
    {
        $query = $this->buildSptQuery($filters, $tahun)
            ->leftJoin('mfwp as m', 'm.npwp16', '=', 'spt.npwp')
            ->leftJoin('pegawai as ar', function ($join) use ($tahun) {
                $join->on('m.nip_ar', '=', 'ar.nip')
                    ->where('ar.tahun', '=', $tahun)
                    ->where('ar.jabatan', '=', '5');
            })
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
                'm.nip_ar',
                'ar.nama as nama_ar',
            ])
            ->orderBy('spt.tgl_terima', 'desc');

        return $query;
    }
}
