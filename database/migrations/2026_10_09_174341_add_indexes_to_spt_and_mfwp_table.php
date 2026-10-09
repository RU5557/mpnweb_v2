<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spt', function (Blueprint $table) {
            $table->index(['npwp', 'tgl_terima'], 'idx_spt_npwp_tgl');
            $table->index(['jenis_spt', 'status_spt', 'thn_pajak'], 'idx_spt_filter_spt');
            $table->index('nomor_tanda_terima', 'idx_spt_bpe');
        });

        Schema::table('mfwp', function (Blueprint $table) {
            $table->index(['nip_ar', 'npwp15'], 'idx_mfwp_ar_npwp');
        });
    }

    public function down(): void
    {
        Schema::table('spt', function (Blueprint $table) {
            $table->dropIndex('idx_spt_npwp_tgl');
            $table->dropIndex('idx_spt_filter_spt');
            $table->dropIndex('idx_spt_bpe');
        });

        Schema::table('mfwp', function (Blueprint $table) {
            $table->dropIndex('idx_mfwp_ar_npwp');
        });
    }
};
