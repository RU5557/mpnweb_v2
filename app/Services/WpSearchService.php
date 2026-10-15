<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * WpSearchService FINAL - dengan filter AR & JSPN
 * AR: jabatan 5, JSPN: jabatan 11, tahun = tahun saat ini
 */
class WpSearchService
{
    private const ALLOWED_SORTS = ['npwp15', 'nama', 'jenis', 'status', 'tanggal_daftar'];

    private const DEFAULT_SORT = 'nama';

    private const DEFAULT_ORDER = 'asc';

    public function resolveFilters(Request $request): array
    {
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
            // AR & JSPN - support dua nama param untuk kompatibilitas
            'nip_ar' => $request->input('nip_ar'),
            'nip_js' => $request->input('nip_js') ?? $request->input('nip_jspn'),
            'nip_jspn' => $request->input('nip_jspn') ?? $request->input('nip_js'),
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

    private function sanitizeDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
    }
}
