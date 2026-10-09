<?php

namespace App\Repositories;

use App\Models\Drm;
use App\Models\Klu;
use App\Models\Mfwp;
use App\Models\Pegawai;
use App\Models\Seksi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransaksiRepository
{
    /**
     * Ambil Opsi Dropdown Filter dengan Caching 24 Jam
     */
    public function getFilterDropdownOptions(int $tahun): array
    {
        return Cache::remember("transaksi_filter_options_{$tahun}", 86400, function () use ($tahun) {
            return [
                'listKota' => Mfwp::query()
                    ->whereNotNull('kota')
                    ->where('kota', '!=', '')
                    ->distinct()
                    ->orderBy('kota')
                    ->pluck('kota'),

                'listJenisWp' => Mfwp::query()
                    ->whereNotNull('jenis')
                    ->where('jenis', '!=', '')
                    ->distinct()
                    ->orderBy('jenis')
                    ->pluck('jenis'),

                'listSektor' => Klu::query()
                    ->whereNotNull('nm_kategori')
                    ->where('nm_kategori', '!=', '')
                    ->select('nm_kategori')
                    ->distinct()
                    ->orderBy('nm_kategori')
                    ->pluck('nm_kategori'),

                'listSeksi' => Seksi::query()
                    ->orderBy('nama')
                    ->get(['id', 'nama', 'kode']),

                'listAr' => Pegawai::query()
                    ->where('jabatan', '5')
                    ->where('tahun', $tahun)
                    ->orderBy('nama')
                    ->get(['nip', 'nama', 'seksi']),

                'listJs' => Pegawai::query()
                    ->where('jabatan', '11')
                    ->where('tahun', $tahun)
                    ->orderBy('nama')
                    ->get(['nip', 'nama', 'seksi']),

                'listTahunSetor' => Drm::query()
                    ->whereNotNull('thn_setor')
                    ->distinct()
                    ->orderBy('thn_setor', 'desc')
                    ->pluck('thn_setor'),

                'listFungsi' => Drm::query()
                    ->whereNotNull('fungsi')
                    ->where('fungsi', '!=', '')
                    ->distinct()
                    ->orderBy('fungsi')
                    ->pluck('fungsi'),
            ];
        });
    }

    /**
     * Reusable Query Builder untuk Transaksi DRM (Pencarian & Export)
     */
    public function buildTransaksiQuery(array $filters, int $tahun = 2026): Builder
    {
        $query = DB::table('drm as t')
            ->leftJoin('mfwp as m', 't.npwp15', '=', 'm.npwp15');

        // === FILTERING SISI TRANSAKSI (DRM) ===

        // 1. Filter NPWP (9 / 15 Digit)
        if (! empty($filters['npwp'])) {
            $cleanNpwp = preg_replace('/[^0-9]/', '', $filters['npwp']);
            if (strlen($cleanNpwp) >= 15) {
                $npwp15 = substr($cleanNpwp, 0, 15);
                $query->where('t.npwp15', $npwp15);
            } else {
                $query->where('t.npwp', 'LIKE', $cleanNpwp.'%');
            }
        }

        // 2. Filter Nama WP
        if (! empty($filters['nama'])) {
            $nama = trim($filters['nama']);
            if (strlen($nama) >= 3) {
                $query->whereRaw('MATCH(t.nama_wp) AGAINST(? IN BOOLEAN MODE)', [$nama.'*']);
            } else {
                $query->where('t.nama_wp', 'LIKE', '%'.$nama.'%');
            }
        }

        // 3. Kode MAP & Kode Bayar
        if (! empty($filters['kd_map'])) {
            $query->where('t.kd_map', trim($filters['kd_map']));
        }
        if (! empty($filters['kd_bayar'])) {
            $query->where('t.kd_bayar', trim($filters['kd_bayar']));
        }

        // 4. Tanggal Setor (Start & End)
        if (! empty($filters['tgl_setor_start']) && ! empty($filters['tgl_setor_end'])) {
            $query->whereBetween('t.tgl_setor', [$filters['tgl_setor_start'], $filters['tgl_setor_end']]);
        } elseif (! empty($filters['tgl_setor_start'])) {
            $query->where('t.tgl_setor', '>=', $filters['tgl_setor_start']);
        } elseif (! empty($filters['tgl_setor_end'])) {
            $query->where('t.tgl_setor', '<=', $filters['tgl_setor_end']);
        }

        // 5. Masa Pajak 1, Masa Pajak 2, & Tahun Pajak
        if (! empty($filters['masa1'])) {
            $query->where('t.masa1', (int) $filters['masa1']);
        }
        if (! empty($filters['masa2'])) {
            $query->where('t.masa2', (int) $filters['masa2']);
        }
        if (! empty($filters['thn_pajak'])) {
            $query->where('t.thn_pajak', (int) $filters['thn_pajak']);
        }

        // 6. Filter Tahun Setor & Bulan Setor
        if (! empty($filters['thn_setor']) && is_array($filters['thn_setor'])) {
            $query->whereIn('t.thn_setor', $filters['thn_setor']);
        }
        if (! empty($filters['bln_setor']) && is_array($filters['bln_setor'])) {
            $query->whereIn('t.bln_setor', $filters['bln_setor']);
        }

        // 7. NTPN
        if (! empty($filters['ntpn'])) {
            $query->where('t.ntpn', trim($filters['ntpn']));
        }

        // 8. Fungsi
        if (! empty($filters['fungsi']) && is_array($filters['fungsi'])) {
            $query->whereIn('t.fungsi', $filters['fungsi']);
        }

        // === FILTERING SISI MASTERFILE (MFWP) & PEGAWAI ===

        // 9. Kota
        if (! empty($filters['kota'])) {
            $query->where('m.kota', $filters['kota']);
        }

        // 10. Jenis WP
        if (! empty($filters['jenis_wp'])) {
            $query->where('m.jenis', $filters['jenis_wp']);
        }

        // 11. Sektor Usaha (KLU)
        if (! empty($filters['sektor'])) {
            $query->join('klu as k', 'm.klu', '=', 'k.kd_klu')
                ->where('k.nm_kategori', $filters['sektor']);
        }

        // 12. Seksi
        if (! empty($filters['seksi_id'])) {
            $seksi = Seksi::find($filters['seksi_id']);
            if ($seksi) {
                $query->join('pegawai as p_ar_filter', function ($join) use ($tahun) {
                    $join->on('m.nip_ar', '=', 'p_ar_filter.nip')
                        ->where('p_ar_filter.tahun', '=', $tahun)
                        ->where('p_ar_filter.jabatan', '=', '5');
                })->where('p_ar_filter.seksi', $seksi->nama);
            }
        }

        // 13. AR (Multi-select NIP)
        if (! empty($filters['nip_ar']) && is_array($filters['nip_ar'])) {
            $query->whereIn('m.nip_ar', $filters['nip_ar']);
        }

        // 14. JS (Multi-select NIP)
        if (! empty($filters['nip_js']) && is_array($filters['nip_js'])) {
            $query->whereIn('m.nip_js', $filters['nip_js']);
        }

        return $query;
    }

    /**
     * Pencarian Detil Transaksi / DRM Paginated
     */
    public function searchTransaksiPaginated(array $filters, int $perPage = 20, int $tahun = 2026): LengthAwarePaginator
    {
        $query = $this->buildTransaksiQuery($filters, $tahun);

        $query->leftJoin('pegawai as ar', function ($join) use ($tahun) {
            $join->on('m.nip_ar', '=', 'ar.nip')
                ->where('ar.tahun', '=', $tahun)
                ->where('ar.jabatan', '=', '5');
        })
            ->leftJoin('pegawai as js', function ($join) use ($tahun) {
                $join->on('m.nip_js', '=', 'js.nip')
                    ->where('js.tahun', '=', $tahun)
                    ->where('js.jabatan', '=', '11');
            });

        $query->select([
            't.id', 't.tgl_setor', 't.npwp15', 't.npwp', 't.nama_wp',
            'm.nama as nama_master', 't.fungsi', 't.kd_map', 't.kd_bayar',
            't.jenis as jenis_pajak', 't.masa1', 't.masa2', 't.thn_pajak', 't.jml_setor',
            't.ntpn', 'ar.nama as nama_ar', 'js.nama as nama_js', 'm.kota', 'm.jenis as jenis_wp',
        ]);

        $sortBy = $filters['sort_by'] ?? 't.tgl_setor';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'tgl_setor' => 't.tgl_setor',
            'npwp15' => 't.npwp15',
            'fungsi' => 't.fungsi',
            'kd_map' => 't.kd_map',
            'jml_setor' => 't.jml_setor',
        ];

        $sortColumn = $allowedSorts[$sortBy] ?? 't.tgl_setor';
        $query->orderBy($sortColumn, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Query Builder Khusus untuk Streaming Export CSV
     */
    public function buildTransaksiExportQuery(array $filters, int $tahun = 2026): Builder
    {
        return $this->buildTransaksiQuery($filters, $tahun)
            ->leftJoin('pegawai as ar', function ($join) use ($tahun) {
                $join->on('m.nip_ar', '=', 'ar.nip')
                    ->where('ar.tahun', '=', $tahun)
                    ->where('ar.jabatan', '=', '5');
            })
            ->leftJoin('pegawai as js', function ($join) use ($tahun) {
                $join->on('m.nip_js', '=', 'js.nip')
                    ->where('js.tahun', '=', $tahun)
                    ->where('js.jabatan', '=', '11');
            })
            ->select([
                't.tgl_setor',
                't.npwp15',
                DB::raw('COALESCE(t.nama_wp, m.nama) as nama_wp'),
                't.fungsi',
                't.kd_map',
                't.kd_bayar',
                DB::raw("CONCAT(LPAD(t.masa1, 2, '0'), '-', LPAD(t.masa2, 2, '0')) as masa_pajak"),
                't.thn_pajak',
                't.jml_setor',
                't.ntpn',
                'ar.nama as nama_ar',
                'js.nama as nama_js',
                'm.kota',
            ])
            ->orderBy('t.tgl_setor', 'desc');
    }
}
