<?php

namespace App\Services;

use Illuminate\Http\Request;

class ErrorLogService
{
    public function buildLogContent(\Throwable $e, Request $request, ?string $customMessage = null): string
    {
        $timestamp = now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s T');
        $content = "=========================================\n";
        $content .= " MPNWEB SYSTEM ERROR LOG - DJP Executive\n";
        $content .= "=========================================\n";
        $content .= "Timestamp (WIB) : {$timestamp}\n";
        $content .= "URL : {$request->fullUrl()}\n";
        $content .= "Method : {$request->method()}\n";
        $content .= "IP : {$request->ip()}\n";
        $content .= "File : {$e->getFile()} (Line {$e->getLine()})\n";
        $content .= 'Message : '.($customMessage ?? $e->getMessage())."\n";
        $content .= "-----------------------------------------\n";
        $content .= "STACK TRACE:\n".$e->getTraceAsString()."\n";
        $content .= "=========================================\n";

        return $content;
    }

    public function getBase64(string $logContent): string
    {
        return base64_encode($logContent);
    }

    public function getFileName(string $prefix = 'Error_Log_MPNWEB'): string
    {
        return $prefix.'_'.now()->format('Ymd_His').'.txt';
    }

    public function generateForView(\Throwable $e, Request $request, ?string $customMessage = null): array
    {
        $content = $this->buildLogContent($e, $request, $customMessage);

        return [
            'timestamp' => now(),
            'logContent' => $content,
            'logBase64' => $this->getBase64($content),
            'logFileName' => $this->getFileName(),
        ];
    }

    public function generateMaintenanceLog(Request $request, string $reason = 'Maintenance'): array
    {
        $content = "MPNWEB MAINTENANCE 503\nTime: ".now()->format('Y-m-d H:i:s')."\nURL: ".$request->fullUrl()."\nReason: $reason\n";

        return [
            'timestamp' => now(),
            'logContent' => $content,
            'logBase64' => $this->getBase64($content),
            'logFileName' => $this->getFileName('Maintenance_MPNWEB'),
        ];
    }
}
