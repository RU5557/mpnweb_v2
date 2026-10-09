<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

trait CanStreamCsv
{
    /**
     * Stream CSV Response dari Query Builder menggunakan Chunking
     */
    protected function streamCsvFromQuery(string $filename, array $columnsMap, $queryBuilder, int $chunkSize = 3000): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($columnsMap, $queryBuilder, $chunkSize) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');

            // UTF-8 BOM untuk Microsoft Excel
            fwrite($file, "\xEF\xBB\xBF");

            // Header CSV
            fputcsv($file, array_values($columnsMap));

            $hasData = false;

            try {
                // Gunakan chunk() untuk efisiensi RAM
                $queryBuilder->chunk($chunkSize, function ($rows) use ($file, $columnsMap, &$hasData) {
                    foreach ($rows as $row) {
                        $hasData = true;
                        $rowData = [];
                        foreach (array_keys($columnsMap) as $dbColumn) {
                            $rowData[] = $row->{$dbColumn} ?? '';
                        }
                        fputcsv($file, $rowData);
                    }

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                });
            } catch (Throwable $e) {
                // Log error atau abaikan jika koneksi terputus di tengah jalan
                report($e);
            }

            if (! $hasData) {
                fputcsv($file, ['Tidak ada data ditemukan']);
            }

            fclose($file);
        }, 200, $headers);
    }
}
