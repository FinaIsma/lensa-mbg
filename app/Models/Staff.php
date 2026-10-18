<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use Notifiable;

    protected $table = 'staff';

    protected $fillable = [
        'id_sppg',
        'nama',
        'email',
        'password',
        'no_telepon',
        'status',
        'nonaktif_at',
        'nonaktif_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getRoleAttribute(): string
    {
        return 'staff';
    }

    public function sppg()
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', 'id_sppg');
    }
}
