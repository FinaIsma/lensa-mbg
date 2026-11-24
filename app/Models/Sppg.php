<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sppg extends Model
{
    use HasFactory;

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
        'status_verifikasi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'id_sppg', 'id_sppg')
            ->orderByRaw("CASE WHEN status IN ('aktif', 'menunggu_persetujuan', 'menunggu') THEN 0 ELSE 1 END")
            ->latest('id');
    }

    public function staffs()
    {
        return $this->hasMany(Staff::class, 'id_sppg', 'id_sppg');
    }

    public function activeStaffs()
    {
        return $this->hasMany(Staff::class, 'id_sppg', 'id_sppg')->where('status', 'aktif');
    }

    /**
     * Resolusi URL foto kantor/gedung SPPG dari database
     */
    public function getFotoKantorUrlAttribute(): string
    {
        if (empty($this->foto_kantor_sppg)) {
            return asset('images/dapur-sppg-sample.jpg');
        }

        if (str_starts_with($this->foto_kantor_sppg, 'http://') || str_starts_with($this->foto_kantor_sppg, 'https://')) {
            return $this->foto_kantor_sppg;
        }

        if (str_starts_with($this->foto_kantor_sppg, 'storage/') || str_starts_with($this->foto_kantor_sppg, '/storage/')) {
            return asset(ltrim($this->foto_kantor_sppg, '/'));
        }

        if (str_starts_with($this->foto_kantor_sppg, 'uploads/')) {
            return asset($this->foto_kantor_sppg);
        }

        return asset('storage/'.ltrim($this->foto_kantor_sppg, '/'));
    }
}
