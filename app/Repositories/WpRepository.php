<?php

namespace App\Repositories;

use App\Models\Mfwp;
use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseQueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * WpRepository - VERSI CURSOR PAGINATE (adopsi CustomerController) + sesuai .airules
 * - Thin Repository, Query terpusat, Hemat I/O HDD
 * - Select tipis tanpa alamat_full saat COUNT
 * - CursorPaginate (no OFFSET) = paling cepat untuk HDD 1TB
 * - Load AR/JSPN hanya untuk 20 data yang tampil
 */
class WpRepository
{
    public function getFilterDropdownOptions(int $tahun): array
    {
        $ttl = 21600;

        return [
            'listKlu' => Cache::remember('mf_filter_klu_v2', $ttl, fn () => DB::table('mfwp')->whereNotNull('klu')->where('klu', '!=', '')->distinct()->orderBy('klu')->pluck('klu')->toArray()),
            'listKelurahan' => Cache::remember('mf_filter_kelurahan_v2', $ttl, fn () => DB::table('mfwp')->whereNotNull('kelurahan')->where('kelurahan', '!=', '')->distinct()->orderBy('kelurahan')->limit(500)->pluck('kelurahan')->toArray()),
            'listKecamatan' => Cache::remember('mf_filter_kecamatan_v2', $ttl, fn () => DB::table('mfwp')->whereNotNull('kecamatan')->where('kecamatan', '!=', '')->distinct()->orderBy('kecamatan')->pluck('kecamatan')->toArray()),
            'listJenis' => Cache::remember('mf_filter_jenis_v2', $ttl, fn () => DB::table('mfwp')->whereNotNull('jenis')->where('jenis', '!=', '')->distinct()->orderBy('jenis')->pluck('jenis')->toArray()),
            'listStatus' => Cache::remember('mf_filter_status_v2', $ttl, fn () => DB::table('mfwp')->whereNotNull('status')->where('status', '!=', '')->distinct()->orderBy('status')->pluck('status')->toArray()),
            'listAr' => Cache::remember("filter_ar_5_{$tahun}_v3", $ttl, fn () => Pegawai::where('tahun', $tahun)->where('jabatan', '5')->select('nip', 'nama')->orderBy('nama')->get()),
            'listJspn' => Cache::remember("filter_jspn_11_{$tahun}_v3", $ttl, fn () => Pegawai::where('tahun', $tahun)->where('jabatan', '11')->select('nip', 'nama')->orderBy('nama')->get()),
            'listJs' => Cache::remember("filter_js_11_{$tahun}_v3", $ttl, fn () => Pegawai::where('tahun', $tahun)->where('jabatan', '11')->select('nip', 'nama')->orderBy('nama')->get()),
        ];
    }

    /**
     * Build query tipis - TANPA alamat_full, TANPA JOIN berat
     * Sesuai pola CustomerController: select('id','code','name'...) saja
     */
    public function buildMasterfileQuery(array $filters): Builder
    {
        // .airules #3: select tipis hemat I/O, alamat dipotong 120 char di DB
        $query = Mfwp::query()->from('mfwp')->select([
            'mfwp.npwp15', 'mfwp.npwp16', 'mfwp.nama', 'mfwp.kelurahan', 'mfwp.kecamatan', 'mfwp.kota', 'mfwp.jenis', 'mfwp.status', 'mfwp.klu', 'mfwp.tanggal_daftar', 'mfwp.nip_ar', 'mfwp.nip_js',
            DB::raw('LEFT(mfwp.alamat, 120) as alamat'),
            DB::raw('mfwp.alamat as alamat_full'),
        ]);

        if (! empty($filters['npwp'])) {
            $clean = preg_replace('/[^0-9]/', '', (string) $filters['npwp']);
            if ($clean !== '') {
                if (strlen($clean) === 16) {
                    $query->where('mfwp.npwp16', 'LIKE', "{$clean}%");
                } else {
                    $query->where(function (Builder $q) use ($clean) {
                        $q->where('mfwp.npwp15', 'LIKE', "{$clean}%")->orWhere('mfwp.npwp16', 'LIKE', "{$clean}%");
                    });
                }
            }
        }
        if (! empty($filters['nama'])) {
            $nama = trim((string) $filters['nama']);
            $words = array_filter(explode(' ', $nama));
            $hasShort = false;
            foreach ($words as $w) {
                if (mb_strlen($w) < 4) {
                    $hasShort = true;
                    break;
                }
            }
            if ($hasShort || count($words) == 0) {
                $query->where('mfwp.nama', 'LIKE', "%{$nama}%");
            } else {
                $phrase = '+'.implode(' +', $words).'*';
                $query->whereRaw('MATCH(mfwp.nama) AGAINST(? IN BOOLEAN MODE)', [$phrase]);
            }
        }
        foreach (['klu', 'kelurahan', 'kecamatan', 'jenis', 'status'] as $col) {
            if (! empty($filters[$col])) {
                $query->where("mfwp.{$col}", $filters[$col]);
            }
        }
        if (! empty($filters['nip_ar'])) {
            $query->where('mfwp.nip_ar', $filters['nip_ar']);
        }
        $nipJspn = $filters['nip_jspn'] ?? $filters['nip_js'] ?? null;
        if (! empty($nipJspn)) {
            $query->where('mfwp.nip_js', $nipJspn);
        }
        $awal = $filters['tgl_daftar_awal'] ?? null;
        $akhir = $filters['tgl_daftar_akhir'] ?? null;
        if ($awal && $akhir) {
            $query->whereBetween('mfwp.tanggal_daftar', [$awal, $akhir]);
        } elseif ($awal) {
            $query->where('mfwp.tanggal_daftar', '>=', $awal);
        } elseif ($akhir) {
            $query->where('mfwp.tanggal_daftar', '<=', $akhir);
        }

        return $query;
    }

    /**
     * METODE ADOPSI CustomerController - Exact Total + Cursor Paginate
     * .airules: Hemat I/O HDD, Thin Repository
     */
    public function searchMasterfileCursorPaginated(array $filters, int $perPage = 20, ?int $tahun = null): array
    {
        $tahun = $tahun ?? (int) date('Y');
        $query = $this->buildMasterfileQuery($filters);

        // 3. Hitung Exact Total - Clone query tanpa ORDER BY, tanpa LIMIT (sesuai pola CustomerController)
        // MariaDB baca dari Index, bukan full table scan
        $totalExact = (clone $query)->getQuery()->count();

        // 4. Cursor Pagination - Super cepat, tanpa OFFSET (OFFSET 50000 di HDD sangat berat)
        // Pakai npwp15 sebagai cursor karena unique & indexed, bukan id
        $sortBy = $filters['sort_by'] ?? 'nama';
        $allowed = ['npwp15', 'nama', 'jenis', 'status', 'tanggal_daftar'];
        $sortColumn = in_array($sortBy, $allowed, true) ? "mfwp.{$sortBy}" : 'mfwp.nama';

        $paginator = $query->orderBy($sortColumn, $filters['sort_order'] ?? 'asc')
            ->orderBy('mfwp.npwp15', 'asc') // tie-breaker wajib untuk cursor
            ->cursorPaginate($perPage);

        // 5. Load AR/JSPN hanya untuk 20 data yang tampil (sesuai CustomerController ->load('address'))
        $paginator->load([
            'ar' => fn ($q) => $q->where('tahun', $tahun)->select('nip', 'nama', 'tahun'),
            'js' => fn ($q) => $q->where('tahun', $tahun)->select('nip', 'nama', 'tahun'),
        ]);

        return [
            'results' => $paginator,
            'totalExact' => $totalExact,
        ];
    }

    /**
     * Versi dengan Cache untuk pimpinan (jika butuh total tapi tidak mau COUNT tiap load)
     * .airules: Cache 10 menit untuk query mahal
     */
    public function getTotalCountCached(array $filters): int
    {
        $key = 'mf_count_'.md5(json_encode($filters));

        return Cache::remember($key, 600, function () use ($filters) {
            return $this->buildMasterfileQuery($filters)->getQuery()->count();
        });
    }

    // Untuk Export CSV tetap pakai QueryBuilder + JOIN (sesuai .airules export via service)
    public function buildExportBaseQuery(array $filters, int $tahun): BaseQueryBuilder
    {
        $baseQuery = Mfwp::query()->from('mfwp');
        if (! empty($filters['npwp'])) {
            $clean = preg_replace('/[^0-9]/', '', (string) $filters['npwp']);
            if ($clean !== '') {
                if (strlen($clean) === 16) {
                    $baseQuery->where('mfwp.npwp16', 'LIKE', "{$clean}%");
                } else {
                    $baseQuery->where(function (Builder $q) use ($clean) {
                        $q->where('mfwp.npwp15', 'LIKE', "{$clean}%")->orWhere('mfwp.npwp16', 'LIKE', "{$clean}%");
                    });
                }
            }
        }
        if (! empty($filters['nama'])) {
            $nama = trim((string) $filters['nama']);
            $words = array_filter(explode(' ', $nama));
            $hasShort = false;
            foreach ($words as $w) {
                if (mb_strlen($w) < 4) {
                    $hasShort = true;
                    break;
                }
            }
            if ($hasShort || count($words) == 0) {
                $baseQuery->where('mfwp.nama', 'LIKE', "%{$nama}%");
            } else {
                $phrase = '+'.implode(' +', $words).'*';
                $baseQuery->whereRaw('MATCH(mfwp.nama) AGAINST(? IN BOOLEAN MODE)', [$phrase]);
            }
        }
        foreach (['klu', 'kelurahan', 'kecamatan', 'jenis', 'status'] as $col) {
            if (! empty($filters[$col])) {
                $baseQuery->where("mfwp.{$col}", $filters[$col]);
            }
        }
        if (! empty($filters['nip_ar'])) {
            $baseQuery->where('mfwp.nip_ar', $filters['nip_ar']);
        }
        $nipJspn = $filters['nip_jspn'] ?? $filters['nip_js'] ?? null;
        if (! empty($nipJspn)) {
            $baseQuery->where('mfwp.nip_js', $nipJspn);
        }
        $awal = $filters['tgl_daftar_awal'] ?? null;
        $akhir = $filters['tgl_daftar_akhir'] ?? null;
        if ($awal && $akhir) {
            $baseQuery->whereBetween('mfwp.tanggal_daftar', [$awal, $akhir]);
        } elseif ($awal) {
            $baseQuery->where('mfwp.tanggal_daftar', '>=', $awal);
        } elseif ($akhir) {
            $baseQuery->where('mfwp.tanggal_daftar', '<=', $akhir);
        }

        $qb = $baseQuery->getQuery();
        $qb->select(['mfwp.npwp15', 'mfwp.npwp16', 'mfwp.nama', 'mfwp.klu', 'mfwp.alamat', 'mfwp.kelurahan', 'mfwp.kecamatan', 'mfwp.jenis', 'mfwp.status', 'mfwp.tanggal_daftar', 'mfwp.nip_ar', 'mfwp.nip_js', 'peg_ar.nama as nama_ar', 'peg_js.nama as nama_jspn'])
            ->leftJoin('pegawai as peg_ar', fn ($j) => $j->on('mfwp.nip_ar', '=', 'peg_ar.nip')->where('peg_ar.tahun','=',$tahun))
            ->leftJoin('pegawai as peg_js', fn ($j) => $j->on('mfwp.nip_js','=','peg_js.nip')->where('peg_js.tahun','=',$tahun))
            ->orderBy('mfwp.nama','asc')->orderBy('mfwp.npwp15','asc');

        return $qb;
    }
}
