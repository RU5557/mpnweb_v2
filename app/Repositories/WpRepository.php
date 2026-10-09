<?php

namespace App\Repositories;

use App\Models\Mfwp;
use App\Models\Pegawai;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseQueryBuilder;
use Illuminate\Support\Facades\Cache;

class WpRepository
{
    /**
     * Cache pilihan dropdown filter.
     */
    public function getFilterDropdownOptions(int $tahun): array
    {
        return [
            'listKlu' => Cache::remember('mf_filter_klu', 3600, fn () => Mfwp::whereNotNull('klu')->where('klu', '!=', '')->distinct()->orderBy('klu', 'asc')->pluck('klu')->toArray()
            ),
            'listKelurahan' => Cache::remember('mf_filter_kelurahan', 3600, fn () => Mfwp::whereNotNull('kelurahan')->where('kelurahan', '!=', '')->distinct()->orderBy('kelurahan', 'asc')->pluck('kelurahan')->toArray()
            ),
            'listKecamatan' => Cache::remember('mf_filter_kecamatan', 3600, fn () => Mfwp::whereNotNull('kecamatan')->where('kecamatan', '!=', '')->distinct()->orderBy('kecamatan', 'asc')->pluck('kecamatan')->toArray()
            ),
            'listJenis' => Cache::remember('mf_filter_jenis', 3600, fn () => Mfwp::whereNotNull('jenis')->where('jenis', '!=', '')->distinct()->orderBy('jenis', 'asc')->pluck('jenis')->toArray()
            ),
            'listStatus' => Cache::remember('mf_filter_status', 3600, fn () => Mfwp::whereNotNull('status')->where('status', '!=', '')->distinct()->orderBy('status', 'asc')->pluck('status')->toArray()
            ),
            'listAr' => Cache::remember("filter_ar_jabatan_5_{$tahun}", 3600, fn () => Pegawai::where('jabatan', 5)->where('tahun', $tahun)->select('nip', 'nama')->orderBy('nama', 'asc')->get()
            ),
            'listJs' => Cache::remember("filter_js_jabatan_11_{$tahun}", 3600, fn () => Pegawai::where('jabatan', 11)->where('tahun', $tahun)->select('nip', 'nama')->orderBy('nama', 'asc')->get()
            ),
        ];
    }

    /**
     * Membangun Eloquent Query Builder untuk Masterfile WP.
     */
    public function buildMasterfileQuery(array $filters): Builder
    {
        $query = Mfwp::query();

        // 1. Filter NPWP
        if (! empty($filters['npwp'])) {
            $cleanNpwp = preg_replace('/[^0-9]/', '', (string) $filters['npwp']);
            if ($cleanNpwp !== '') {
                $query->where(function ($q) use ($cleanNpwp) {
                    $q->where('mfwp.npwp15', 'LIKE', "{$cleanNpwp}%")
                        ->orWhere('mfwp.npwp16', 'LIKE', "{$cleanNpwp}%");
                });
            }
        }

        // 2. Filter Nama WP (Fulltext Match dengan Fallback ke LIKE jika kata < 4 karakter)
        if (! empty($filters['nama'])) {
            $nama = trim((string) $filters['nama']);
            $words = array_filter(explode(' ', $nama));

            // Cek apakah ada kata yang panjangnya kurang dari 4 karakter
            $hasShortWord = false;
            foreach ($words as $word) {
                if (mb_strlen($word) < 4) {
                    $hasShortWord = true;
                    break;
                }
            }

            if ($hasShortWord || empty($words)) {
                // Fallback ke LIKE jika kata kunci pendek (< 4 huruf, misal "jnl")
                $query->where('mfwp.nama', 'LIKE', "%{$nama}%");
            } else {
                // Gunakan Fulltext Search jika kata >= 4 karakter
                $searchPhrase = '+'.implode(' +', $words).'*';
                $query->whereRaw('MATCH(mfwp.nama) AGAINST(? IN BOOLEAN MODE)', [$searchPhrase]);
            }
        }

        // 3. Exact Filter
        if (! empty($filters['klu'])) {
            $query->where('mfwp.klu', $filters['klu']);
        }
        if (! empty($filters['kelurahan'])) {
            $query->where('mfwp.kelurahan', $filters['kelurahan']);
        }
        if (! empty($filters['kecamatan'])) {
            $query->where('mfwp.kecamatan', $filters['kecamatan']);
        }
        if (! empty($filters['jenis'])) {
            $query->where('mfwp.jenis', $filters['jenis']);
        }
        if (! empty($filters['status'])) {
            $query->where('mfwp.status', $filters['status']);
        }

        // 4. Range Tanggal Daftar
        $tglAwal = $filters['tgl_daftar_awal'] ?? null;
        $tglAkhir = $filters['tgl_daftar_akhir'] ?? null;

        if (! empty($tglAwal) && ! empty($tglAkhir)) {
            $query->whereBetween('mfwp.tanggal_daftar', [$tglAwal, $tglAkhir]);
        } elseif (! empty($tglAwal)) {
            $query->where('mfwp.tanggal_daftar', '>=', $tglAwal);
        } elseif (! empty($tglAkhir)) {
            $query->where('mfwp.tanggal_daftar', '<=', $tglAkhir);
        }

        // 5. Filter AR & JS
        if (! empty($filters['nip_ar'])) {
            $query->where('mfwp.nip_ar', $filters['nip_ar']);
        }
        if (! empty($filters['nip_js'])) {
            $query->where('mfwp.nip_js', $filters['nip_js']);
        }

        return $query;
    }

    /**
     * Pencarian Masterfile WP dengan Paginasi Tampilan.
     */
    public function searchMasterfilePaginated(array $filters, int $perPage = 20, ?int $tahun = null): LengthAwarePaginator
    {
        $tahun = $tahun ?? (int) date('Y');
        $query = $this->buildMasterfileQuery($filters);

        $allowedSorts = ['npwp15', 'nama', 'jenis', 'tanggal_daftar'];
        $sortBy = $filters['sort_by'] ?? 'nama';
        $sortOrder = strtolower($filters['sort_order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy("mfwp.{$sortBy}", $sortOrder);
        } else {
            $query->orderBy('mfwp.nama', 'asc');
        }

        $results = $query->paginate($perPage);

        $results->getCollection()->load([
            'ar' => fn ($q) => $q->where('tahun', $tahun),
            'js' => fn ($q) => $q->where('tahun', $tahun),
        ]);

        return $results;
    }

    /**
     * Membangun Base DB Query Builder dengan Left Join Pegawai untuk CsvExportService.
     */
    public function buildExportBaseQuery(array $filters, int $tahun): BaseQueryBuilder
    {
        $eloquentQuery = $this->buildMasterfileQuery($filters);

        // Konversi ke Query\Builder
        $baseQuery = $eloquentQuery->getQuery();

        // Select atribut spesifik dan JOIN ke tabel pegawai
        $baseQuery->select([
            'mfwp.npwp15',
            'mfwp.npwp16',
            'mfwp.nama',
            'mfwp.klu',
            'mfwp.alamat',
            'mfwp.kelurahan',
            'mfwp.kecamatan',
            'mfwp.jenis',
            'mfwp.status',
            'mfwp.tanggal_daftar',
            'peg_ar.nama as nama_ar',
            'peg_js.nama as nama_js',
        ])
            ->leftJoin('pegawai as peg_ar', function ($join) use ($tahun) {
                $join->on('mfwp.nip_ar', '=', 'peg_ar.nip')
                    ->where('peg_ar.tahun', '=', $tahun);
            })
            ->leftJoin('pegawai as peg_js', function ($join) use ($tahun) {
                $join->on('mfwp.nip_js', '=', 'peg_js.nip')
                    ->where('peg_js.tahun', '=', $tahun);
            });

        // Ordering untuk chunking
        $allowedSorts = ['npwp15', 'nama', 'jenis', 'tanggal_daftar'];
        $sortBy = $filters['sort_by'] ?? 'nama';
        $sortOrder = strtolower($filters['sort_order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, $allowedSorts, true)) {
            $baseQuery->orderBy("mfwp.{$sortBy}", $sortOrder);
        } else {
            $baseQuery->orderBy('mfwp.nama', 'asc');
        }

        // Primary Key tie-breaker agar offset chunking konsisten
        $baseQuery->orderBy('mfwp.npwp15', 'asc');

        return $baseQuery;
    }
}
