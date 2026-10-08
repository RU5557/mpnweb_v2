@echo off
title ETL Sinkronisasi Data MPNWEB v2
:: Berpindah otomatis ke direktori project Anda
cd /d "C:\xampp\htdocs\mpnweb_v2"

:menu
cls
echo ========================================================
echo       PANEL SINKRONISASI DATA ETL (MPNINFO -^> MPNWEB_V2)
echo ========================================================
echo.
echo  [1] Semua Tabel (Full Refresh + Rebuild Summary Mart)
echo  [2] Transaksi DRM ^& SPT Coretax (Bisa Filter Periode)
echo  [3] Data SPT Coretax Saja (Bisa Filter Periode)
echo  [4] Masterfile WP (MFWP - Zero Downtime Swapping)
echo  [5] Tabel Referensi (Seksi, KLU, MAP, Pegawai)
echo  [6] Keluar
echo.
echo * Catatan: Menggunakan teknik Table Swapping (Zero Downtime),
echo           Rebuild Summary Mart (opsi 1-3), dan Invalidation
echo           Cache spesifik di Database Store.
echo ========================================================
set /p pilihan="Pilih opsi menu [1-6]: "

if "%pilihan%"=="1" goto sync_all
if "%pilihan%"=="2" goto sync_tx
if "%pilihan%"=="3" goto sync_spt
if "%pilihan%"=="4" goto sync_master
if "%pilihan%"=="5" goto sync_ref
if "%pilihan%"=="6" goto keluar

echo.
echo [ERROR] Pilihan tidak valid! Silakan tekan tombol apa saja untuk mencoba lagi.
pause >nul
goto menu

:: ========================================================
:: OPSI 1: SEMUA TABEL (ALL)
:: ========================================================
:sync_all
echo.
echo [+] Memproses Sinkronisasi SEMUA Tabel (Referensi, MFWP, Transaksi DRM ^& SPT)...
php artisan sync:data-sistem --only=all
echo.
pause
goto menu

:: ========================================================
:: OPSI 2: DETIL TRANSAKSI DRM & SPT CORETAX (WITH PERIOD FILTER)
:: ========================================================
:sync_tx
echo.
echo --------------------------------------------------------
echo  FILTER PERIODE TRANSAKSI DRM ^& SPT (Kosongkan jika Full Refresh)
echo --------------------------------------------------------
set /p thn="Masukkan Tahun Setor/Pajak (Contoh: 2026 / tekan Enter untuk Semua): "
set /p bln="Masukkan Bulan Setor/Pajak (Contoh: 09 / tekan Enter untuk Semua): "

set cmd_args=--only=tx
if not "%thn%"=="" set cmd_args=%cmd_args% --thnsetor=%thn%
if not "%bln%"=="" set cmd_args=%cmd_args% --blnsetor=%bln%

echo.
echo [+] Memproses Sinkronisasi Transaksi DRM ^& SPT Coretax...
php artisan sync:data-sistem %cmd_args%
echo.
pause
goto menu

:: ========================================================
:: OPSI 3: SPT CORETAX SAJA (WITH PERIOD FILTER)
:: ========================================================
:sync_spt
echo.
echo --------------------------------------------------------
echo  FILTER PERIODE SPT CORETAX (Kosongkan jika Full Refresh)
echo --------------------------------------------------------
set /p thn="Masukkan Tahun Pajak (Contoh: 2026 / tekan Enter untuk Semua): "
set /p bln="Masukkan Bulan Pajak (Contoh: 09 / tekan Enter untuk Semua): "

set cmd_args=--only=spt
if not "%thn%"=="" set cmd_args=%cmd_args% --thnsetor=%thn%
if not "%bln%"=="" set cmd_args=%cmd_args% --blnsetor=%bln%

echo.
echo [+] Memproses Sinkronisasi Data SPT Coretax...
php artisan sync:data-sistem %cmd_args%
echo.
pause
goto menu

:: ========================================================
:: OPSI 4: MASTERFILE WP (MFWP)
:: ========================================================
:sync_master
echo.
echo [+] Memproses Sinkronisasi Masterfile WP (mfwp) via Swapping...
php artisan sync:data-sistem --only=master
echo.
pause
goto menu

:: ========================================================
:: OPSI 5: TABEL REFERENSI
:: ========================================================
:sync_ref
echo.
echo [+] Memproses Sinkronisasi Tabel Referensi (seksi, klu, map, pegawai)...
php artisan sync:data-sistem --only=ref
echo.
pause
goto menu

:keluar
exit