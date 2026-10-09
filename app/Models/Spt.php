<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spt extends Model
{
    use HasFactory;

    protected $table = 'spt';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'tahun',
        'bulan',
        'bulan_data',
        'kanwil',
        'kpp',
        'npwp',
        'nama',
        'masa1',
        'masa2',
        'thn_pajak',
        'jenis_spt',
        'nomor_tanda_terima',
        'tgl_terima',
        'nop',
        'status_spt',
        'pembetulan',
        'kanal_pelaporan',
        'kd_kpp_administrasi',
        'kpp_administrasi',
        'kd_kpp_penerima',
        'kpp_penerima',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'bulan' => 'integer',
            'masa1' => 'integer',
            'masa2' => 'integer',
            'thn_pajak' => 'integer',
            'tgl_terima' => 'date',
        ];
    }
}
