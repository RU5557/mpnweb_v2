<?php

namespace App\Repositories;

use App\Models\Mfwp;
use App\Models\Pegawai;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseQueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * WpRepository - Versi dengan alamat tetap tampil (hemat I/O HDD)
 * Trik: alamat di listing pakai LEFT(alamat,80) agar tidak load TEXT full
 */
class WpRepository
{
    // Tambah alamat tapi tetap minimal
    private const LIST_COLUMNS = [
        'mfwp.npwp15',
        'mfwp.npwp16',
        'mfwp.nama',
        'mfwp.klu',
        'mfwp.kelurahan',
        'mfwp.kecamatan',
        'mfwp.jenis',
        'mfwp.status',
        'mfwp.tanggal_daftar',
        'mfwp.nip_ar',
        'mfwp.nip_js',
        'mfwp.kota',
        'mfwp.alamat', // tetap tampil
    ];

    public function getFilterDropdownOptions(int $tahun): array
    {
        $ttl = 21600;

        return [
            'listKlu' => Cache::remember('mf_filter_klu_v2', $ttl, fn () => DB::table('mfwp')->whereNotNull('klu')->where('klu', '!=', '')->distinct()->orderBy('klu')->pluck('klu')->toArray()
            ),
            'listKelurahan' => Cache::remember('mf_filter_kelurahan_v2', $ttl, fn () => DB::table('mfwp')->whereNotNull('kelurahan')->where('kelurahan', '!=', '')->distinct()->orderBy('kelurahan')->limit(500)->pluck('kelurahan')->toArray()
            ),
            'listKecamatan' => Cache::remember('mf_filter_kecamatan_v2', $ttl, fn () => DB::table('mfwp')->whereNotNull('kecamatan')->where('kecamatan', '!=', '')->distinct()->orderBy('kecamatan')->pluck('kecamatan')->toArray()
            ),
            'listJenis' => Cache::remember('mf_filter_jenis_v2', $ttl, fn () => DB::table('mfwp')->whereNotNull('jenis')->where('jenis', '!=', '')->distinct()->orderBy('jenis')->pluck('jenis')->toArray()
            ),
            'listStatus' => Cache::remember('mf_filter_status_v2', $ttl, fn () => DB::table('mfwp')->whereNotNull('status')->where('status', '!=', '')->distinct()->orderBy('status')->pluck('status')->toArray()
            ),
            'listAr' => Cache::remember("filter_ar_5_{$tahun}_v2", $ttl, fn () => Pegawai::where('jabatan', 5)->where('tahun', $tahun)->select('nip', 'nama')->orderBy('nama')->get()
            ),
            'listJs' => Cache::remember("filter_js_11_{$tahun}_v2", $ttl, fn () => Pegawai::where('jabatan', 11)->where('tahun', $tahun)->select('nip', 'nama')->orderBy('nama')->get()
            ),
        ];
    }

    public function buildMasterfileQuery(array $filters): Builder
    {
        // Untuk listing: potong alamat 120 char di SQL agar tidak load TEXT full (hemat I/O HDD)
        // Jika mau full alamat, hapus DB::raw ini dan pakai mfwp.alamat langsung
        $query = Mfwp::query()->from('mfwp')->select([
            'mfwp.npwp15',
            'mfwp.npwp16',
            'mfwp.nama',
            'mfwp.klu',
            'mfwp.kelurahan',
            'mfwp.kecamatan',
            'mfwp.jenis',
            'mfwp.status',
            'mfwp.tanggal_daftar',
            'mfwp.nip_ar',
            'mfwp.nip_js',
            'mfwp.kota',
            DB::raw('mfwp.alamat as alamat_full'), // full untuk tooltip/modal
            DB::raw('LEFT(mfwp.alamat, 100) as alamat'), // potong untuk listing
        ]);

        if (! empty($filters['npwp'])) {
            $clean = preg_replace('/[^0-9]/', '', (string) $filters['npwp']);
            if ($clean !== '') {
                if (strlen($clean) === 16) {
                    $query->where('mfwp.npwp16', 'LIKE', "{$clean}%");
                } else {
                    $query->where(function (Builder $q) use ($clean) {
                        $q->where('mfwp.npwp15', 'LIKE', "{$clean}%")
                            ->orWhere('mfwp.npwp16', 'LIKE', "{$clean}%");
                    });
                }
            }
        }

        if (! empty($filters['nama'])) {
            $nama = trim((string) $filters['nama']);
            $words = array_filter(explode(' ', $nama), fn ($w) => $w !== '');
            $hasShort = false;
            foreach ($words as $w) {
                if (mb_strlen($w) < 4) {
                    $hasShort = true;
                    break;
                }
            }
            if ($hasShort || count($words) === 0) {
                $query->where('mfwp.nama', 'LIKE', "%{$nama}%");
            } else {
                $phrase = '+'.implode(' +', $words).'*';
                $query->whereRaw('MATCH(mfwp.nama) AGAINST(? IN BOOLEAN MODE)', [$phrase]);
            }
        }

        foreach (['klu', 'kelurahan', 'kecamatan', 'jenis', 'status', 'nip_ar', 'nip_js'] as $col) {
            if (! empty($filters[$col])) {
                $query->where("mfwp.{$col}", $filters[$col]);
            }
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

    public function searchMasterfilePaginated(array $filters, int $perPage = 20, ?int $tahun = null): LengthAwarePaginator
    {
        $tahun = $tahun ?? (int) date('Y');
        $query = $this->buildMasterfileQuery($filters);
        $sortBy = $filters['sort_by'] ?? 'nama';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $allowedSorts = ['npwp15', 'nama', 'jenis', 'status', 'tanggal_daftar'];
        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy("mfwp.{$sortBy}", $sortOrder);
        } else {
            $query->orderBy('mfwp.nama', 'asc');
        }
        $query->orderBy('mfwp.npwp15', 'asc');
        $results = $query->paginate($perPage);
        $results->getCollection()->load([
            'ar' => fn ($q) => $q->where('tahun', $tahun)->select('nip', 'nama', 'tahun'),
            'js' => fn ($q) => $q->where('tahun', $tahun)->select('nip', 'nama', 'tahun'),
        ]);

        return $results;
    }

    public function buildExportBaseQuery(array $filters, int $tahun): BaseQueryBuilder
    {
        $eloquent = $this->buildMasterfileQuery($filters);
        $base = $eloquent->getQuery();
        $base->select([
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
                $join->on('mfwp.nip_ar', '=', 'peg_ar.nip')->where('peg_ar.tahun', '=', $tahun);
            })
            ->leftJoin('pegawai as peg_js', function ($join) use ($tahun) {
                $join->on('mfwp.nip_js', '=', 'peg_js.nip')->where('peg_js.tahun', '=', $tahun);
            });

        return $base;
    }
}
