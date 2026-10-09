<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drm extends Model
{
    // Nama tabel di database
    protected $table = 'drm';

    // Primary key
    protected $primaryKey = 'id';

    // Tabel ini tidak menggunakan timestamps bawaan Laravel (created_at & updated_at)
    public $timestamps = false;

    // Kolom yang dapat diisi secara massal (Mass Assignment)
    protected $guarded = ['id'];

    // Format tipe data kolom (Casting)
    protected $casts = [
        'tgl_setor' => 'date',
        'thn_setor' => 'integer',
        'bln_setor' => 'integer',
        'thn_pajak' => 'integer',
        'masa1' => 'integer',
        'masa2' => 'integer',
        'jml_setor' => 'integer',
    ];
}
