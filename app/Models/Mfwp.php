<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mfwp extends Model
{
    protected $table = 'mfwp';

    protected $primaryKey = 'npwp15';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'npwp15',
        'admin',
        'npwp',
        'kpp',
        'cabang',
        'nama',
        'alamat',
        'kelurahan',
        'kecamatan',
        'kota',
        'propinsi',
        'jenis',
        'bentuk_hukum',
        'status',
        'klu',
        'tanggal_daftar',
        'tanggal_pkp',
        'tanggal_pkp_cabut',
        'nik',
        'telp',
        'nip_ar',
        'nip_eks',
        'nip_js',
        'npwp16',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_daftar' => 'date',
            'tanggal_pkp' => 'date',
            'tanggal_pkp_cabut' => 'date',
        ];
    }

    public function ar(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nip_ar', 'nip');
    }

    public function js(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nip_js', 'nip');
    }

    public function kluData(): BelongsTo
    {
        return $this->belongsTo(Klu::class, 'klu', 'kd_klu');
    }
}
