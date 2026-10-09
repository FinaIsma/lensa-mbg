<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sppg extends Model
{
    protected $table = 'sppg';

    protected $primaryKey = 'id_sppg';

    protected $fillable = [
        'id_user',
        'nama_sppg',
        'no_telepon',
        'alamat_sppg',
        'provinsi',
        'kabupaten_kota',
        'kecamatan',
        'foto_ktp',
        'foto_kantor_sppg',
        'foto_surat_resmi',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}