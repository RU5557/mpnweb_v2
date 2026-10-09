<?php

namespace App\Services;

use App\Traits\CanStreamCsv;
use Illuminate\Database\Query\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    use CanStreamCsv;

    /**
     * Export data dari Query Builder ke Streamed CSV Response
     */
    public function exportFromQuery(string $filename, array $columnsMap, Builder $query, int $chunkSize = 2000): StreamedResponse
    {
        return $this->streamCsvFromQuery($filename, $columnsMap, $query, $chunkSize);
    }
}
