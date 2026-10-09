<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SummaryPenjagaan extends Model
{
    protected $table = 'summary_penjagaan';

    protected $fillable = [
        'thn_setor',
        'bln_setor',
        'tgl_setor',
        'fungsi',
        'total_setor',
        'total_transaksi',
    ];

    protected $casts = [
        'thn_setor' => 'integer',
        'bln_setor' => 'integer',
        'tgl_setor' => 'date',
        'total_setor' => 'decimal:2',
        'total_transaksi' => 'integer',
    ];
}
