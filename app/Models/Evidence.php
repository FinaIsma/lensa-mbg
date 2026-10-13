<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{
    use HasFactory;

    protected $table = 'evidences';

    protected $guarded = [];

    protected $casts = [
        'is_public'   => 'boolean',
        'uploaded_at' => 'datetime',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id_user');
    }

    public function evidenceable()
    {
        return $this->morphTo();
    }
}
