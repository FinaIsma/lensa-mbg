<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'id_user';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function sppg()
    {
        return $this->hasOne(Sppg::class, 'id_user', 'id_user');
    }

    public function requestedCorrections()
    {
        return $this->hasMany(CorrectionRequest::class, 'requested_by', 'id_user');
    }

    public function reviewedCorrections()
    {
        return $this->hasMany(CorrectionRequest::class, 'reviewed_by', 'id_user');
    }
}