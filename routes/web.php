<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PenjagaanController;
use App\Http\Controllers\PkmPemeriksaanController;
use App\Http\Controllers\PkmPenagihanController;
use App\Http\Controllers\PkmPengawasanController;
use App\Http\Controllers\SptSearchController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\WpSearchController;
use App\Http\Middleware\AdminAuthMiddleware;
use Illuminate\Support\Facades\Route;

// Route untuk download langsung log error dari session tanpa perlu simpan file di server
Route::get('/download-current-error-log', function () {
    $logContent = session('error_log_content', 'Log error tidak ditemukan atau session telah kadaluarsa.');
    $filename = 'Error_Log_MPNWEB_'.date('Ymd_His').'.txt';

    return response($logContent, 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        'Cache-Control' => 'no-cache, must-revalidate',
    ]);
})->name('error.log.download');

/*
|--------------------------------------------------------------------------
| Redirect Root (/)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('penerimaan.dashboard');
});

/*
|--------------------------------------------------------------------------
| Modul Publik (Bisa diakses tanpa login)
|--------------------------------------------------------------------------
*/
Route::prefix('penerimaan')->name('penerimaan.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export-detil', [DashboardController::class, 'exportDetil'])->name('dashboard.export-detil');

    // Grouping Route Penjagaan
    Route::prefix('penjagaan')->name('penjagaan.')->group(function () {
        Route::get('/bulanan', [PenjagaanController::class, 'bulanan'])->name('bulanan');
        Route::get('/bulanan/export-detil', [PenjagaanController::class, 'exportBulananCsv'])->name('bulanan.export-detil');

        Route::get('/harian', [PenjagaanController::class, 'harian'])->name('harian');
        Route::get('/harian/export-detil', [PenjagaanController::class, 'exportHarianCsv'])->name('harian.export-detil');

        Route::get('/vs-bulan-lalu', [PenjagaanController::class, 'vsBulanLalu'])->name('vs-bulan-lalu');
        Route::get('/vs-bulan-lalu/export-detil', [PenjagaanController::class, 'exportVsBulanLaluCsv'])->name('vs-bulan-lalu.export-detil');
    });
});

/*
|--------------------------------------------------------------------------
| Modul PKM (Pengawasan, Pemeriksaan, Penagihan)
|--------------------------------------------------------------------------
*/
Route::prefix('pkm')->name('pkm.')->group(function () {
    // 1. PKM Pengawasan
    Route::get('/pengawasan', [PkmPengawasanController::class, 'index'])->name('pengawasan');
    Route::get('/pengawasan/export-detil', [PkmPengawasanController::class, 'exportDetil'])->name('pengawasan.export-detil');

    // 2. PKM Pemeriksaan
    Route::get('/pemeriksaan', [PkmPemeriksaanController::class, 'index'])->name('pemeriksaan');
    Route::get('/pemeriksaan/export-detil', [PkmPemeriksaanController::class, 'exportDetil'])->name('pemeriksaan.export-detil');

    // 3. PKM Penagihan
    Route::get('/penagihan', [PkmPenagihanController::class, 'index'])->name('penagihan');
    Route::get('/penagihan/export-detil', [PkmPenagihanController::class, 'exportDetil'])->name('penagihan.export-detil');
});

/*
|--------------------------------------------------------------------------
| Autentikasi Admin & Panel Admin
|--------------------------------------------------------------------------
*/
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware(AdminAuthMiddleware::class)->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/target', [AdminController::class, 'updateTarget'])->name('target.update');
    Route::post('/rolling-text', [AdminController::class, 'updateRollingText'])->name('rolling-text.update');
});

/*
|--------------------------------------------------------------------------
| Modul Pencarian WP, Transaksi & SPT
|--------------------------------------------------------------------------
*/
Route::prefix('pencarian')->name('pencarian.')->group(function () {
    // 1. Masterfile WP
    Route::get('/masterfile', [WpSearchController::class, 'searchMasterfile'])->name('masterfile');
    Route::get('/masterfile/export', [WpSearchController::class, 'exportMasterfileCsv'])->name('masterfile.export');

    // 2. Detil Transaksi / Penerimaan
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi');
    Route::get('/transaksi/export', [TransaksiController::class, 'exportCsv'])->name('transaksi.export');

    // 3. Tanda Terima SPT (Coretax)
    Route::get('/spt', [SptSearchController::class, 'index'])->name('spt');
    Route::get('/spt/export', [SptSearchController::class, 'exportCsv'])->name('spt.export');
});
