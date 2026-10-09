<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SummaryPkm extends Model
{
    protected $table = 'summary_pkm';

    protected $fillable = [
        'thn_setor',
        'bln_setor',
        'npwp15',
        'fungsi',
        'flag_skp',
        'total_setor',
        'total_transaksi',
    ];

    protected $casts = [
        'thn_setor' => 'integer',
        'bln_setor' => 'integer',
        'total_setor' => 'decimal:2',
        'total_transaksi' => 'integer',
    ];
}
