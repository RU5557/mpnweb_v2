<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * SptSearchService - Resolver filter SPT sesuai .airules
 * Thin Service: sanitasi, validasi tanggal, resolve sort
 * Adopsi pola WpSearchService
 */
class SptSearchService
{
    private const ALLOWED_SORTS = ['tgl_terima', 'jenis_spt', 'thn_pajak', 'nama', 'npwp', 'status_spt'];

    private const DEFAULT_SORT = 'tgl_terima';

    private const DEFAULT_ORDER = 'desc';

    public function resolveFilters(Request $request): array
    {
        return [
            'npwp' => $this->sanitizeNpwp($request->input('npwp')),
            'nama' => trim((string) $request->input('nama', '')),
            'masa1' => $this->sanitizeInt($request->input('masa1'), 1, 12),
            'masa2' => $this->sanitizeInt($request->input('masa2'), 1, 12),
            'thn_pajak' => $this->sanitizeInt($request->input('thn_pajak'), 2000, 2030),
            'pembetulan' => $this->sanitizeString($request->input('pembetulan')),
            'jenis_spt' => $this->sanitizeString($request->input('jenis_spt')),
            'status_spt' => $this->sanitizeString($request->input('status_spt')),
            'tgl_terima_mulai' => $this->sanitizeDate($request->input('tgl_terima_mulai')),
            'tgl_terima_selesai' => $this->sanitizeDate($request->input('tgl_terima_selesai')),
            'nip_ar' => $this->sanitizeString($request->input('nip_ar')),
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
        $order = strtolower($order ?? '');

        return in_array($order, ['asc', 'desc'], true) ? $order : self::DEFAULT_ORDER;
    }

    private function sanitizeNpwp(?string $npwp): string
    {
        if (empty($npwp)) {
            return '';
        }

        return preg_replace('/[^0-9]/', '', $npwp);
    }

    private function sanitizeInt($value, int $min, int $max): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $int = (int) $value;
        if ($int < $min || $int > $max) {
            return null;
        }

        return $int;
    }

    private function sanitizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trim = trim($value);

        return $trim === '' ? null : $trim;
    }

    private function sanitizeDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
    }
}
