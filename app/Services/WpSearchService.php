<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * WpSearchService - Otak validasi & normalisasi filter Masterfile WP
 * Sesuai .airules #8 thin controller & #3 hemat memory/I/O HDD
 */
class WpSearchService
{
    private const ALLOWED_SORTS = ['npwp15', 'nama', 'jenis', 'status', 'tanggal_daftar'];

    private const DEFAULT_SORT = 'nama';

    private const DEFAULT_ORDER = 'asc';

    public function resolveFilters(Request $request): array
    {
        // Sanitasi ringan, tidak pakai $request->all() mentah (hemat memory)
        return [
            'npwp' => trim((string) $request->input('npwp', '')),
            'nama' => trim((string) $request->input('nama', '')),
            'klu' => $request->input('klu'),
            'kelurahan' => $request->input('kelurahan'),
            'kecamatan' => $request->input('kecamatan'),
            'jenis' => $request->input('jenis'),
            'status' => $request->input('status'),
            'tgl_daftar_awal' => $this->sanitizeDate($request->input('tgl_daftar_awal')),
            'tgl_daftar_akhir' => $this->sanitizeDate($request->input('tgl_daftar_akhir')),
            'nip_ar' => $request->input('nip_ar'),
            'nip_js' => $request->input('nip_js'),
            'sort_by' => $this->resolveSortBy($request->input('sort_by')),
            'sort_order' => $this->resolveSortOrder($request->input('sort_order')),
        ];
    }

    public function resolveSortBy(?string $sort): string
    {
        return in_array($sort, self::ALLOWED_SORTS, true) ? $sort : self::DEFAULT_SORT;
    }

    public function resolveSortOrder(?string $order): string
    {
        return strtolower($order ?? '') === 'desc' ? 'desc' : self::DEFAULT_ORDER;
    }

    public function getSortParams(array $filters): array
    {
        return [
            'sort_by' => $filters['sort_by'] ?? self::DEFAULT_SORT,
            'sort_order' => $filters['sort_order'] ?? self::DEFAULT_ORDER,
        ];
    }

    private function sanitizeDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Validasi format Y-m-d sederhana, cegah SQL error di MariaDB 10.4
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
    }

    /**
     * Validasi rentang tanggal tidak kebalik (klinisi untuk HDD i5)
     */
    public function normalizeDateRange(?string $awal, ?string $akhir): array
    {
        if ($awal && $akhir && $awal > $akhir) {
            return [$akhir, $awal];
        }

        return [$awal, $akhir];
    }
}
