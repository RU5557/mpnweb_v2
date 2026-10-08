<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyncDataSistem extends Command
{
    /**
     * Nama dan tanda tangan dari console command.
     */
    protected $signature = 'sync:data-sistem 
                            {--only=all : Pilihan target: all, ref, master, tx, spt}
                            {--thnsetor= : Filter tahun (contoh: 2026)}
                            {--blnsetor= : Filter bulan (contoh: 09 atau 9)}
                            {--maintenance : Aktifkan mode maintenance selama sync}';

    /**
     * Deskripsi console command.
     */
    protected $description = 'ETL data dari mpninfo ke mpnweb_v2 - HIGH PERFORMANCE (Table Swapping, Batching, Cursor, DB Direct Statement)';

    public function handle()
    {
        $target = $this->option('only');
        $thnSetor = $this->option('thnsetor');
        $blnSetor = $this->option('blnsetor');
        $useMaintenance = $this->option('maintenance');

        $this->info('====================================================');
        $this->info("  MEMULAI ETL DATA SINKRONISASI (Mode Target: {$target})");
        $this->info('  SUMBER: mpninfo  --->  TUJUAN: mpnweb_v2');
        if ($thnSetor || $blnSetor) {
            $infoPeriode = [];
            if ($thnSetor) {
                $infoPeriode[] = "Tahun: {$thnSetor}";
            }
            if ($blnSetor) {
                $infoPeriode[] = "Bulan: {$blnSetor}";
            }
            $this->info('  FILTER PERIODE -> '.implode(', ', $infoPeriode));
        } else {
            $this->info('  FILTER PERIODE -> FULL REFRESH (Semua Data)');
        }
        $this->info('  MODE OPTIMASI: SWAPPING + BATCH 2000 + CURSOR STREAMING + AUTOCOMMIT DISABLED');
        $this->info('====================================================');
        $startTime = microtime(true);

        // 1. Matikan Query Log Laravel untuk menghemat RAM PHP secara drastis
        DB::disableQueryLog();

        // 2. Mode Maintenance (opsional)
        if ($useMaintenance) {
            $this->comment('-> Mengaktifkan Mode Maintenance...');
            Artisan::call('down', ['--secret' => 'etl-sync-mode']);
        }

        try {
            // 3. Matikan pemeriksaan constraint & autocommit sementara di session MySQL
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            DB::statement('SET UNIQUE_CHECKS = 0;');
            DB::statement('SET AUTOCOMMIT = 0;');

            // A. Sinkronisasi Referensi (seksi, klu, map, pegawai)
            if (in_array($target, ['all', 'ref'])) {
                $this->syncSeksi();
                $this->syncKlu();
                $this->syncKdmap();
                $this->syncPegawai();
            }

            // B. Sinkronisasi Masterfile WP via Atomic Table Swapping (mpninfo.masterfile -> mpnweb_v2.mfwp)
            if (in_array($target, ['all', 'master'])) {
                $this->syncMasterfileWpBatch();
            }

            // C. Sinkronisasi Detil Transaksi WP (mpninfo.ppmpkm_drm -> mpnweb_v2.drm)
            if (in_array($target, ['all', 'tx'])) {
                $this->syncDetilTransaksiWp($thnSetor, $blnSetor);
            }

            // D. Sinkronisasi SPT Coretax (mpninfo.spt_coretax -> mpnweb_v2.spt)
            if (in_array($target, ['all', 'tx', 'spt'])) {
                $this->syncSptCoretax($thnSetor, $blnSetor);
            }

            // 4. Commit Transaksi Utama
            $this->comment('-> Menyimpan perubahan ke database (Commit Transaction)...');
            $commitStart = microtime(true);

            DB::statement('COMMIT;');

            $commitTime = round(microtime(true) - $commitStart, 2);
            $this->info("   [OK] Transaction committed ({$commitTime}s).");

            // 5. Kembalikan Setting Constraint
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            DB::statement('SET UNIQUE_CHECKS = 1;');
            DB::statement('SET AUTOCOMMIT = 1;');

            // 6. Rebuild Summary Mart (Dipicu jika target transaksi / spt)
            if (in_array($target, ['all', 'tx', 'spt'])) {
                $this->newLine();
                $this->comment('-> Memicu rekapitulasi Summary Mart Penerimaan...');

                $summaryOptions = [];
                if ($thnSetor) {
                    $summaryOptions['--thnsetor'] = $thnSetor;
                }
                if ($blnSetor) {
                    $summaryOptions['--blnsetor'] = $blnSetor;
                }

                Artisan::call('summary:rebuild', $summaryOptions, $this->output);
            } else {
                $this->newLine();
                $this->comment('-> [SKIP] Rekapitulasi Summary Mart dilewati.');
            }

            // 7. Invalidasi Cache Dashboard secara terarah (Database Store Driver)
            $this->invalidateDashboardCache($thnSetor, $blnSetor);

            $executionTime = round(microtime(true) - $startTime, 2);

            if ($useMaintenance) {
                Artisan::call('up');
                $this->comment('-> Mode Maintenance dinonaktifkan.');
            }

            $this->newLine();
            $this->info('====================================================');
            $this->info("  ETL SINKRONISASI SELESAI DALAM {$executionTime} DETIK!");
            $this->info('====================================================');

            return Command::SUCCESS;

        } catch (Exception $e) {
            DB::statement('ROLLBACK;');
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            DB::statement('SET UNIQUE_CHECKS = 1;');
            DB::statement('SET AUTOCOMMIT = 1;');

            // Bersihkan tabel temporary jika proses swapping mengalami error di tengah jalan
            DB::statement('DROP TABLE IF EXISTS mfwp_temp;');
            DB::statement('DROP TABLE IF EXISTS mfwp_old;');

            if ($useMaintenance) {
                Artisan::call('up');
            }

            $this->newLine();
            $this->error('ETL ERROR DETECTED: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    /**
     * Sumber: mpninfo.seksi
     * Tujuan: mpnweb_v2.seksi
     */
    private function syncSeksi()
    {
        $this->comment('-> Synchronizing: seksi...');
        DB::statement('TRUNCATE TABLE seksi;');
        DB::statement('
            INSERT IGNORE INTO seksi (id, kantor, tipe, nama, kode, telp) 
            SELECT id, kantor, tipe, nama, kode, telp 
            FROM mpninfo.seksi
        ');
        $this->info('   [OK] Tabel seksi synchronized.');
    }

    /**
     * Sumber: mpninfo.klu_baru
     * Tujuan: mpnweb_v2.klu
     */
    private function syncKlu()
    {
        $this->comment('-> Synchronizing: klu...');
        DB::statement('TRUNCATE TABLE klu;');
        DB::statement('
            INSERT IGNORE INTO klu (kd_klu, nm_klu, kd_kategori, nm_kategori) 
            SELECT kd_klu, nm_klu, kd_kategori, nm_kategori 
            FROM mpninfo.klu_baru
        ');
        $this->info('   [OK] Tabel klu synchronized.');
    }

    /**
     * Sumber: mpninfo.map_baru
     * Tujuan: mpnweb_v2.map
     */
    private function syncKdmap()
    {
        $this->comment('-> Synchronizing: map...');
        DB::statement('TRUNCATE TABLE map;');
        DB::statement("
            INSERT IGNORE INTO map (id, kd_map, kd_bayar, jenis_pajak, jenis_bayar, sektor_pajak) 
            SELECT id, COALESCE(kd_map, ''), COALESCE(kd_bayar, ''), jenis_pajak, jenis_bayar, sektor_pajak 
            FROM mpninfo.map_baru
        ");
        $this->info('   [OK] Tabel map synchronized.');
    }

    /**
     * Sumber: mpninfo.pegawai
     * Tujuan: mpnweb_v2.pegawai
     */
    private function syncPegawai()
    {
        $this->comment('-> Synchronizing: pegawai...');
        DB::statement('TRUNCATE TABLE pegawai;');
        DB::statement('
            INSERT IGNORE INTO pegawai (kantor, nip, nip2, nama, pangkat, seksi, jabatan, tahun, plh) 
            SELECT kantor, nip, nip2, nama, pangkat, seksi, jabatan, tahun, plh 
            FROM mpninfo.pegawai
        ');
        $this->info('   [OK] Tabel pegawai synchronized.');
    }

    /**
     * Sumber: mpninfo.masterfile
     * Tujuan: mpnweb_v2.mfwp
     * Metode: Shadow Table Swapping + Cursor Streaming + Batching 2000 (Zero Downtime).
     */
    private function syncMasterfileWpBatch()
    {
        $this->comment('-> Synchronizing: mfwp via Atomic Table Swapping (BATCH 2000)...');
        $t0 = microtime(true);

        // 1. Buat tabel temp dengan struktur & indeks persis mfwp
        DB::statement('DROP TABLE IF EXISTS mfwp_temp;');
        DB::statement('CREATE TABLE mfwp_temp LIKE mfwp;');

        $total = 0;
        $batchSize = 2000;
        $batchData = [];

        $query = DB::table('mpninfo.masterfile')->orderBy('npwp')->orderBy('kpp')->orderBy('cabang');

        foreach ($query->cursor() as $r) {
            $npwpRaw = trim((string) ($r->npwp ?? ''));
            $kppRaw = trim((string) ($r->kpp ?? ''));
            $cabangRaw = trim((string) ($r->cabang ?? ''));

            if ($npwpRaw === '' && $kppRaw === '' && $cabangRaw === '') {
                continue;
            }

            $npwp15 = str_pad($npwpRaw, 9, '0', STR_PAD_LEFT).str_pad($kppRaw, 3, '0', STR_PAD_LEFT).str_pad($cabangRaw, 3, '0', STR_PAD_LEFT);
            if ($npwp15 === '000000000000000') {
                continue;
            }

            $batchData[] = [
                'admin' => substr((string) ($r->admin ?? ''), 0, 10),
                'npwp' => substr($npwpRaw, 0, 20),
                'kpp' => substr($kppRaw, 0, 10),
                'cabang' => substr($cabangRaw, 0, 10),
                'npwp15' => $npwp15,
                'nama' => $r->nama ?? null,
                'alamat' => $r->alamat ?? null,
                'kelurahan' => substr((string) ($r->kelurahan ?? ''), 0, 50),
                'kecamatan' => substr((string) ($r->kecamatan ?? ''), 0, 100),
                'kota' => substr((string) ($r->kota ?? ''), 0, 100),
                'propinsi' => substr((string) ($r->propinsi ?? ''), 0, 100),
                'jenis' => substr((string) ($r->jenis ?? ''), 0, 50),
                'bentuk_hukum' => substr((string) ($r->bentukhukum ?? ''), 0, 50),
                'status' => substr((string) ($r->status ?? ''), 0, 50),
                'klu' => substr((string) ($r->klu ?? ''), 0, 10),
                'tanggal_daftar' => $r->tanggaldaftar ?? null,
                'tanggal_pkp' => $r->tanggalpkp ?? null,
                'tanggal_pkp_cabut' => $r->tanggalpkpcabut ?? null,
                'nik' => substr((string) ($r->nik ?? ''), 0, 30),
                'telp' => substr((string) ($r->telp ?? ''), 0, 50),
                'nip_ar' => substr((string) ($r->nipar ?? ''), 0, 30),
                'nip_eks' => substr((string) ($r->nipeks ?? ''), 0, 30),
                'nip_js' => substr((string) ($r->nipjs ?? ''), 0, 30),
                'npwp16' => $r->npwp16 ?? null,
            ];

            if (count($batchData) >= $batchSize) {
                DB::table('mfwp_temp')->insertOrIgnore($batchData);
                DB::statement('COMMIT;');
                DB::statement('SET AUTOCOMMIT = 0;');
                $total += count($batchData);
                $this->output->write("\r   [WAIT] Memproses {$total} rows ke mfwp_temp... ");
                $batchData = [];
            }
        }

        if (! empty($batchData)) {
            DB::table('mfwp_temp')->insertOrIgnore($batchData);
            DB::statement('COMMIT;');
            DB::statement('SET AUTOCOMMIT = 0;');
            $total += count($batchData);
        }

        // 2. Atomic Table Swapping (Eksklusif MariaDB - berjalan dalam milidetik)
        DB::statement('RENAME TABLE mfwp TO mfwp_old, mfwp_temp TO mfwp;');
        DB::statement('DROP TABLE IF EXISTS mfwp_old;');

        $elapsed = round(microtime(true) - $t0, 2);
        $this->output->write("\r");
        $this->info("   [OK] Tabel mfwp synchronized via Swapping ({$elapsed}s) - Total {$total} rows.                   ");
    }

    /**
     * Sumber: mpninfo.ppmpkm_drm
     * Tujuan: mpnweb_v2.drm
     */
    private function syncDetilTransaksiWp($thnSetor = null, $blnSetor = null)
    {
        $this->comment('-> Synchronizing: drm...');

        $whereConditions = [];
        $deleteConditions = [];

        if (! empty($thnSetor)) {
            $whereConditions[] = 'thnsetor = '.(int) $thnSetor;
            $deleteConditions[] = 'thn_setor = '.(int) $thnSetor;
        }
        if (! empty($blnSetor)) {
            $whereConditions[] = 'blnsetor = '.(int) $blnSetor;
            $deleteConditions[] = 'bln_setor = '.(int) $blnSetor;
        }

        if (count($deleteConditions) > 0) {
            $deleteWhereSql = ' WHERE '.implode(' AND ', $deleteConditions);
            DB::statement("DELETE FROM drm{$deleteWhereSql};");
            $this->comment('   [i] Menghapus data periode terpilih sebelum re-sync.');
        } else {
            DB::statement('TRUNCATE TABLE drm;');
            $this->comment('   [i] Melakukan TRUNCATE pada drm.');
        }

        $this->output->write('   [WAIT] Memproses salinan data transaksi...');
        $t0 = microtime(true);

        $whereSql = count($whereConditions) > 0 ? ' WHERE '.implode(' AND ', $whereConditions) : '';

        DB::statement("
            INSERT INTO drm (
                kd_kanwil, kpp_adm, npwp, kpp, cabang, no_produk_hukum, npwp15, nama_wp, no_pbk, ntpn, 
                tgl_setor, thn_setor, bln_setor, thn_pajak, masa1, masa2, jml_setor, 
                kd_map, kd_bayar, fungsi, jenis, flag_skp, id_sbr_data, tipe
            )
            SELECT 
                kdkanwil, kppadm, npwp, kpp, cabang, no_produk_hukum,
                CONCAT(LPAD(TRIM(npwp), 9, '0'), LPAD(TRIM(kpp), 3, '0'), LPAD(TRIM(cabang), 3, '0')) AS npwp15,
                nama_wp, nopbk, ntpn, tglsetor, thnsetor, blnsetor,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masapajak, '00000000')), 8, '0'), 5, 4) AS UNSIGNED) AS thn_pajak,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masapajak, '00000000')), 8, '0'), 1, 2) AS UNSIGNED) AS masa1,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masapajak, '00000000')), 8, '0'), 3, 2) AS UNSIGNED) AS masa2,
                COALESCE(jmlsetor, 0) AS jml_setor, 
                kdmap, kdbayar, fungsi, jenis, flag_skp, id_sbr_data, tipe
            FROM mpninfo.ppmpkm_drm
            {$whereSql}
        ");

        $elapsed = round(microtime(true) - $t0, 2);
        $this->output->write("\r");
        $this->info("   [OK] Tabel drm synchronized ({$elapsed}s).                   ");
    }

    /**
     * Sumber: mpninfo.spt_coretax
     * Tujuan: mpnweb_v2.spt
     */
    private function syncSptCoretax($thnSetor = null, $blnSetor = null)
    {
        $this->comment('-> Synchronizing: spt...');

        $whereConditions = [];
        $deleteConditions = [];

        if (! empty($thnSetor)) {
            $whereConditions[] = 'tahun = '.(int) $thnSetor;
            $deleteConditions[] = 'tahun = '.(int) $thnSetor;
        }
        if (! empty($blnSetor)) {
            $whereConditions[] = 'bulan = '.(int) $blnSetor;
            $deleteConditions[] = 'bulan = '.(int) $blnSetor;
        }

        if (count($deleteConditions) > 0) {
            $deleteWhereSql = ' WHERE '.implode(' AND ', $deleteConditions);
            DB::statement("DELETE FROM spt{$deleteWhereSql};");
            $this->comment('   [i] Menghapus data spt periode terpilih sebelum re-sync.');
        } else {
            DB::statement('TRUNCATE TABLE spt;');
            $this->comment('   [i] Melakukan TRUNCATE pada spt.');
        }

        $this->output->write('   [WAIT] Memproses salinan data spt...');
        $t0 = microtime(true);

        $whereSql = count($whereConditions) > 0 ? ' WHERE '.implode(' AND ', $whereConditions) : '';

        DB::statement("
            INSERT INTO spt (
                tahun, bulan, bulan_data, kanwil, kpp, npwp, nama, 
                masa1, masa2, thn_pajak, 
                jenis_spt, nomor_tanda_terima, tgl_terima, nop, 
                status_spt, pembetulan, kanal_pelaporan, 
                kd_kpp_administrasi, kpp_administrasi, kd_kpp_penerima, kpp_penerima
            )
            SELECT 
                tahun, bulan, bulan_data, kanwil, kpp, npwp, nama,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masa_pajak, '00000000')), 8, '0'), 1, 2) AS UNSIGNED) AS masa1,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masa_pajak, '00000000')), 8, '0'), 3, 2) AS UNSIGNED) AS masa2,
                CAST(SUBSTRING(LPAD(TRIM(COALESCE(masa_pajak, '00000000')), 8, '0'), 5, 4) AS UNSIGNED) AS thn_pajak,
                jenis_spt, nomor_tanda_terima, tgl_terima, nop, 
                status_spt, pembetulan, kanal_pelaporan, 
                kd_kpp_administrasi, kpp_administrasi, kd_kpp_penerima, kpp_penerima
            FROM mpninfo.spt_coretax
            {$whereSql}
        ");

        $elapsed = round(microtime(true) - $t0, 2);
        $this->output->write("\r");
        $this->info("   [OK] Tabel spt synchronized ({$elapsed}s).                   ");
    }

    /**
     * Invalidation Cache khusus untuk DashboardRepository (Sesuai Pattern Key dashboard_summary_v4_*)
     * Bekerja presisi pada Database Cache Store tanpa mengganggu cache/session lainnya.
     */
    private function invalidateDashboardCache(?string $thnSetor = null, ?string $blnSetor = null)
    {
        $this->newLine();
        $this->comment('-> Membersihkan Cache Dashboard Repository...');

        $prefix = config('cache.prefix', '');

        if (! empty($thnSetor)) {
            // Hapus cache spesifik untuk tahun yang di-sync
            $pattern = "{$prefix}dashboard_summary_v4_{$thnSetor}_%";
            $deleted = DB::table('cache')->where('key', 'LIKE', $pattern)->delete();

            // Juga hapus cache target tahunan
            Cache::forget("dashboard_target_{$thnSetor}");

            $this->info("   [OK] Cache dashboard tahun {$thnSetor} berhasil dibersihkan ({$deleted} keys).");
        } else {
            // Full refresh: Hapus seluruh cache summary dashboard v4
            $pattern = "{$prefix}dashboard_summary_v4_%";
            $deleted = DB::table('cache')->where('key', 'LIKE', $pattern)->delete();

            // Hapus semua cache target
            DB::table('cache')->where('key', 'LIKE', "{$prefix}dashboard_target_%")->delete();

            $this->info("   [OK] Seluruh cache summary dashboard berhasil dibersihkan ({$deleted} keys).");
        }
    }
}
