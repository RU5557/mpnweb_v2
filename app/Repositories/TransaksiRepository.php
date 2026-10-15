<?php

namespace App\Repositories;

use App\Models\Drm;
use App\Models\Klu;
use App\Models\Mfwp;
use App\Models\Pegawai;
use App\Models\Seksi;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransaksiRepository
{
    public function getFilterDropdownOptions(int $tahun): array
    {
        $ttl = 21600;

        return [
            'listFungsi' => Cache::remember('trx_fungsi_v8', $ttl, fn () => Drm::whereNotNull('fungsi')->where('fungsi', '!=', '')->distinct()->orderBy('fungsi')->pluck('fungsi')),
            'listKecamatan' => Cache::remember('trx_kec_v8', $ttl, fn () => Mfwp::whereNotNull('kecamatan')->where('kecamatan', '!=', '')->distinct()->orderBy('kecamatan')->limit(500)->pluck('kecamatan')),
            'listJenisWp' => Cache::remember('trx_jenis_v8', $ttl, fn () => Mfwp::whereNotNull('jenis')->where('jenis', '!=', '')->distinct()->orderBy('jenis')->pluck('jenis')),
            'listSektor' => Cache::remember('trx_sektor_v8', $ttl, fn () => Klu::whereNotNull('nm_kategori')->where('nm_kategori', '!=', '')->distinct()->orderBy('nm_kategori')->pluck('nm_kategori')),
            'listAr' => Cache::remember("trx_ar_{$tahun}_v8", $ttl, fn () => Pegawai::where('jabatan', '5')->where('tahun', $tahun)->orderBy('nama')->get(['nip', 'nama', 'seksi'])),
            'listSeksi' => Cache::remember('trx_seksi_v8', $ttl, fn () => Seksi::where('nama', 'LIKE', '%pengawasan%')->orderBy('id')->get(['id', 'nama', 'kode'])),
            'listMap' => Cache::remember('trx_map_v8', $ttl, fn () => Drm::whereNotNull('kd_map')->where('kd_map', '!=', '')->distinct()->orderBy('kd_map')->limit(300)->pluck('kd_map')),
            'listBayar' => Cache::remember('trx_bayar_v8', $ttl, fn () => Drm::whereNotNull('kd_bayar')->where('kd_bayar', '!=', '')->distinct()->orderBy('kd_bayar')->limit(300)->pluck('kd_bayar')),
        ];
    }

    public function buildTransaksiQuery(array $filters, int $tahun = 2026): Builder
    {
        $query = DB::table('drm as t')->leftJoin('mfwp as m', 't.npwp15', '=', 'm.npwp15');

        if (! empty($filters['npwp'])) {
            $clean = preg_replace('/[^0-9]/', '', (string) $filters['npwp']);
            if ($clean !== '') {
                $query->where(fn ($q) => $q->where('t.npwp15', 'LIKE', "{$clean}%")->orWhere('t.npwp', 'LIKE', "{$clean}%"));
            }
        }
        if (! empty($filters['nama'])) {
            $nama = trim((string) $filters['nama']);
            if ($nama !== '') {
                $words = array_filter(explode(' ', $nama));
                $hasShort = false;
                foreach ($words as $w) {
                    if (mb_strlen($w) < 4) {
                        $hasShort = true;
                        break;
                    }
                }
                if ($hasShort || count($words) == 0) {
                    $query->where('t.nama_wp', 'LIKE', "%{$nama}%");
                } else {
                    $query->whereRaw('MATCH(t.nama_wp) AGAINST(? IN BOOLEAN MODE)', ['+'.implode(' +', $words).'*']);
                }
            }
        }
        if (! empty($filters['fungsi'])) {
            $f = $filters['fungsi'];
            if (is_array($f)) {
                $query->whereIn('t.fungsi', $f);
            } else {
                $query->where('t.fungsi', trim((string) $f));
            }
        }
        if (! empty($filters['kd_map'])) {
            $query->where('t.kd_map', trim((string) $filters['kd_map']));
        }
        if (! empty($filters['kd_bayar'])) {
            $query->where('t.kd_bayar', trim((string) $filters['kd_bayar']));
        }
        if (! empty($filters['masa1'])) {
            $query->where('t.masa1', (int) $filters['masa1']);
        }
        if (! empty($filters['masa2'])) {
            $query->where('t.masa2', (int) $filters['masa2']);
        }
        if (! empty($filters['thn_pajak'])) {
            $query->where('t.thn_pajak', (int) $filters['thn_pajak']);
        }
        $tglAwal = $filters['tgl_setor_awal'] ?? $filters['tgl_setor_start'] ?? null;
        $tglAkhir = $filters['tgl_setor_akhir'] ?? $filters['tgl_setor_end'] ?? null;
        if (! empty($tglAwal) && ! empty($tglAkhir)) {
            $query->whereBetween('t.tgl_setor', [$tglAwal, $tglAkhir]);
        } elseif (! empty($tglAwal)) {
            $query->where('t.tgl_setor', '>=', $tglAwal);
        } elseif (! empty($tglAkhir)) {
            $query->where('t.tgl_setor', '<=', $tglAkhir);
        }
        if (! empty($filters['ntpn'])) {
            $query->where('t.ntpn', 'LIKE', '%'.trim((string) $filters['ntpn']).'%');
        }
        if (! empty($filters['no_produk_hukum'])) {
            $query->where('t.no_produk_hukum', 'LIKE', '%'.trim((string) $filters['no_produk_hukum']).'%');
        }
        if (! empty($filters['kecamatan'])) {
            $query->where('m.kecamatan', $filters['kecamatan']);
        }
        if (! empty($filters['jenis_wp'])) {
            $query->where('m.jenis', $filters['jenis_wp']);
        }
        if (! empty($filters['sektor'])) {
            $query->whereExists(fn ($q) => $q->select(DB::raw(1))->from('klu')->whereColumn('klu.kd_klu', 'm.klu')->where('klu.nm_kategori', $filters['sektor']));
        }

        $nipAr = $filters['nip_ar'] ?? null;
        $seksiId = $filters['seksi_id'] ?? null;
        if (! empty($nipAr) && empty($seksiId)) {
            if (is_array($nipAr)) {
                $query->whereIn('m.nip_ar', $nipAr);
            } else {
                $query->where('m.nip_ar', $nipAr);
            }
        } elseif (empty($nipAr) && ! empty($seksiId)) {
            $seksi = Seksi::find($seksiId);
            if ($seksi) {
                $seksiNama = $seksi->nama;
                $seksiIdStr = (string) $seksi->id;
                $lastWord = trim(substr($seksiNama, strrpos($seksiNama, ' ') ?: 0));
                $nips = Pegawai::where('tahun', $tahun)->where('jabatan', '5')
                    ->where(fn ($q) => $q->where('seksi', $seksiIdStr)->orWhere('seksi', $seksiNama)->orWhere('seksi', 'LIKE', '%'.$seksiNama.'%')->orWhere(fn ($qq) => $qq->where('seksi', 'LIKE', '%'.$lastWord)->where('seksi', 'LIKE', '%Pengawasan%')))
                    ->pluck('nip')->toArray();
                if (! empty($nips)) {
                    $query->whereIn('m.nip_ar', $nips);
                } else {
                    $query->whereRaw('1=0');
                }
            }
        } elseif (! empty($nipAr) && ! empty($seksiId)) {
            $seksi = Seksi::find($seksiId);
            if ($seksi) {
                $seksiNama = $seksi->nama;
                $seksiIdStr = (string) $seksi->id;
                $lastWord = trim(substr($seksiNama, strrpos($seksiNama, ' ') ?: 0));
                $nipList = is_array($nipAr) ? $nipAr : [$nipAr];
                $validNips = Pegawai::where('tahun', $tahun)->where('jabatan', '5')->whereIn('nip', $nipList)
                    ->where(fn ($q) => $q->where('seksi', $seksiIdStr)->orWhere('seksi', $seksiNama)->orWhere('seksi', 'LIKE', '%'.$seksiNama.'%')->orWhere(fn ($qq) => $qq->where('seksi', 'LIKE', '%'.$lastWord)->where('seksi', 'LIKE', '%Pengawasan%')))
                    ->pluck('nip')->toArray();
                if (! empty($validNips)) {
                    $query->whereIn('m.nip_ar', $validNips);
                } else {
                    $query->whereRaw('1=0');
                }
            } else {
                if (is_array($nipAr)) {
                    $query->whereIn('m.nip_ar', $nipAr);
                } else {
                    $query->where('m.nip_ar', $nipAr);
                }
            }
        }

        return $query;
    }

    /**
     * UBAH: totalExact (COUNT) diganti jadi totalJumlah (SUM jml_setor)
     */
    public function searchTransaksiCursorPaginated(array $filters, int $perPage = 20, int $tahun = 2026): array
    {
        $baseQuery = $this->buildTransaksiQuery($filters, $tahun);

        // SUM jml_setor, bukan COUNT
        $totalJumlah = (clone $baseQuery)->sum('t.jml_setor');
        $totalCount = (clone $baseQuery)->count(); // tetap hitung count untuk info menampilkan X data, tapi tidak dipakai sebagai totalExact

        $query = $baseQuery
            ->leftJoin('pegawai as ar', fn ($j) => $j->on('m.nip_ar', '=', 'ar.nip')->where('ar.tahun', '=', $tahun)->where('ar.jabatan', '=', '5'))
            ->leftJoin('pegawai as js', fn ($j) => $j->on('m.nip_js', '=', 'js.nip')->where('js.tahun', '=', $tahun)->where('js.jabatan', '=', '11'))
            ->leftJoin('klu as k2', 'm.klu', '=', 'k2.kd_klu')
            ->select(['t.id', 't.tgl_setor', 't.npwp', 't.npwp15', 't.nama_wp', 't.fungsi', 't.kd_map', 't.kd_bayar', 't.masa1', 't.masa2', 't.thn_pajak', 't.jml_setor', 't.ntpn', 't.no_produk_hukum', 'm.nama as nama_master', 'm.kecamatan', 'm.jenis as jenis_wp', 'm.klu', 'k2.nm_kategori as sektor', 'ar.nama as nama_ar', 'ar.seksi as seksi_ar', 'js.nama as nama_js']);

        $sortBy = $filters['sort_by'] ?? 'tgl_setor';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $allowed = ['tgl_setor' => 't.tgl_setor', 'npwp15' => 't.npwp15', 'jml_setor' => 't.jml_setor', 'kd_map' => 't.kd_map', 'fungsi' => 't.fungsi'];
        $sortCol = $allowed[$sortBy] ?? 't.tgl_setor';
        $paginator = $query->orderBy($sortCol, $sortOrder)->orderBy('t.id', 'asc')->cursorPaginate($perPage);

        return ['results' => $paginator, 'totalJumlah' => $totalJumlah, 'totalCount' => $totalCount];
    }

    // SUM cache, bukan COUNT
    public function getTotalJumlahCached(array $filters, int $tahun = 2026): int
    {
        $key = 'trx_sum_jml_'.md5(json_encode($filters).$tahun);

        return Cache::remember($key, 600, fn () => $this->buildTransaksiQuery($filters, $tahun)->sum('t.jml_setor'));
    }

    public function buildTransaksiExportQuery(array $filters, int $tahun = 2026): Builder
    {
        return $this->buildTransaksiQuery($filters, $tahun)
            ->leftJoin('pegawai as ar', fn ($j) => $j->on('m.nip_ar', '=', 'ar.nip')->where('ar.tahun', '=', $tahun)->where('ar.jabatan', '=', '5'))
            ->leftJoin('pegawai as js', fn ($j) => $j->on('m.nip_js', '=', 'js.nip')->where('js.tahun','=',$tahun)->where('js.jabatan','=','11'))
            ->leftJoin('klu as k2','m.klu','=','k2.kd_klu')
            ->select(['t.tgl_setor', 't.npwp', 't.npwp15', DB::raw('COALESCE(t.nama_wp, m.nama) as nama_wp'), 't.fungsi', 't.kd_map', 't.kd_bayar', 't.masa1', 't.masa2', 't.thn_pajak', 't.jml_setor', 't.ntpn', 't.no_produk_hukum', 'm.kecamatan', 'm.jenis as jenis_wp', 'k2.nm_kategori as sektor', 'ar.nama as nama_ar', 'ar.seksi as seksi'])
            ->orderBy('t.tgl_setor','desc');
    }
}
