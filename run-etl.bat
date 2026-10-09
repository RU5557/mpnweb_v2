@echo off
title ETL Sinkronisasi Data MPNWEB v2
cd /d "C:\xampp\htdocs\mpnweb_v2"

:menu
cls
echo ========================================================
echo       PANEL SINKRONISASI DATA ETL (MPNINFO -^> MPNWEB_V2)
echo ========================================================
echo.
echo  [1] HARIAN   : Gabungan Pembayaran DRM ^& SPT Coretax
echo  [2] HARIAN   : Pembayaran DRM Saja (Termasuk Rebuild Penjagaan ^& PKM)
echo  [3] HARIAN   : Laporan SPT Coretax Saja
echo  [4] MINGGUAN : Masterfile WP (MFWP - Zero Downtime Swapping)
echo  [5] TAHUNAN  : Tabel Referensi (Seksi, KLU, MAP, Pegawai)
echo  [6] FULL SYNC: Semua Tabel + Rebuild Summary Mart, Penjagaan ^& PKM
echo  [7] Keluar
echo.
echo * Catatan: Opsi 1, 2, dan 3 mendukung Filter Periode Tahun ^& Bulan.
echo ========================================================
set /p pilihan="Pilih opsi menu [1-7]: "

if "%pilihan%"=="1" goto sync_tx
if "%pilihan%"=="2" goto sync_drm
if "%pilihan%"=="3" goto sync_spt
if "%pilihan%"=="4" goto sync_master
if "%pilihan%"=="5" goto sync_ref
if "%pilihan%"=="6" goto sync_all
if "%pilihan%"=="7" goto keluar

echo.
echo [ERROR] Pilihan tidak valid! Silakan tekan tombol apa saja untuk mencoba lagi.
pause >nul
goto menu

:: ========================================================
:: OPSI 1: HARIAN - GABUNGAN DRM & SPT CORETAX
:: ========================================================
:sync_tx
echo.
echo --------------------------------------------------------
echo  FILTER PERIODE TRANSAKSI DRM ^& SPT (Kosongkan jika Full Refresh)
echo --------------------------------------------------------
set /p thn="Masukkan Tahun Setor/Pajak (Contoh: 2026 / Enter untuk Semua): "
set /p bln="Masukkan Bulan Setor/Pajak (Contoh: 09 / Enter untuk Semua): "

set cmd_args=--only=tx
if not "%thn%"=="" set cmd_args=%cmd_args% --thnsetor=%thn%
if not "%bln%"=="" set cmd_args=%cmd_args% --blnsetor=%bln%

echo.
echo [+] Memproses Sinkronisasi HARIAN (DRM ^& SPT Coretax + Rebuild Summary)...
php artisan sync:data-sistem %cmd_args%
echo.
pause
goto menu

:: ========================================================
:: OPSI 2: HARIAN - DRM SAJA (PEMBAYARAN, PENJAGAAN & PKM)
:: ========================================================
:sync_drm
echo.
echo --------------------------------------------------------
echo  FILTER PERIODE PEMBAYARAN DRM (Kosongkan jika Full Refresh)
echo --------------------------------------------------------
set /p thn="Masukkan Tahun Setor (Contoh: 2026 / Enter untuk Semua): "
set /p bln="Masukkan Bulan Setor (Contoh: 09 / Enter untuk Semua): "

set cmd_args=--only=drm
if not "%thn%"=="" set cmd_args=%cmd_args% --thnsetor=%thn%
if not "%bln%"=="" set cmd_args=%cmd_args% --blnsetor=%bln%

echo.
echo [+] Memproses Sinkronisasi HARIAN (DRM Saja ^& Rebuild Penjagaan + PKM)...
php artisan sync:data-sistem %cmd_args%
echo.
pause
goto menu

:: ========================================================
:: OPSI 3: HARIAN - SPT CORETAX SAJA (PELAPORAN)
:: ========================================================
:sync_spt
echo.
echo --------------------------------------------------------
echo  FILTER PERIODE LAPORAN SPT (Kosongkan jika Full Refresh)
echo --------------------------------------------------------
set /p thn="Masukkan Tahun Pajak (Contoh: 2026 / Enter untuk Semua): "
set /p bln="Masukkan Bulan Pajak (Contoh: 09 / Enter untuk Semua): "

set cmd_args=--only=spt
if not "%thn%"=="" set cmd_args=%cmd_args% --thnsetor=%thn%
if not "%bln%"=="" set cmd_args=%cmd_args% --blnsetor=%bln%

echo.
echo [+] Memproses Sinkronisasi HARIAN (SPT Coretax Saja)...
php artisan sync:data-sistem %cmd_args%
echo.
pause
goto menu

:: ========================================================
:: OPSI 4: MINGGUAN - MASTERFILE WP (MFWP)
:: ========================================================
:sync_master
echo.
echo [+] Memproses Sinkronisasi MINGGUAN (Masterfile WP via Swapping)...
php artisan sync:data-sistem --only=master
echo.
pause
goto menu

:: ========================================================
:: OPSI 5: TAHUNAN - TABEL REFERENSI
:: ========================================================
:sync_ref
echo.
echo [+] Memproses Sinkronisasi TAHUNAN (Seksi, KLU, MAP, Pegawai)...
php artisan sync:data-sistem --only=ref
echo.
pause
goto menu

:: ========================================================
:: OPSI 6: FULL SYNC - SEMUA TABEL
:: ========================================================
:sync_all
echo.
echo [+] Memproses FULL SYNC (Semua Tabel + Rebuild All Summary)...
php artisan sync:data-sistem --only=all
echo.
pause
goto menu

:keluar
exit