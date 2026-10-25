<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Model StaffManagement
 * Mewakili entitas manajemen data staff SPPG pada tabel 'staff'.
 */
class StaffManagement extends Staff
{
    /**
     * Nama tabel database yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'staff';

    /**
     * Scope query untuk staff berstatus aktif.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope query untuk staff menunggu persetujuan / belum diverifikasi.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeMenunggu($query)
    {
        return $query->whereIn('status', ['menunggu', 'menunggu_persetujuan']);
    }

    /**
     * Scope query untuk staff nonaktif.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeNonaktif($query)
    {
        return $query->where('status', 'nonaktif');
    }
}
