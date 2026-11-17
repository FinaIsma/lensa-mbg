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
        'is_public' => 'boolean',
        'uploaded_at' => 'datetime',
    ];

    protected $appends = ['url'];

    /**
     * Get the accessible public or local URL for the evidence file.
     */
    public function getUrlAttribute(): string
    {
        if (empty($this->file_path)) {
            return '';
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return asset('storage/'.ltrim($this->file_path, '/'));
    }

    public function uploader()
    {
        return $this->belongsTo(Staff::class, 'uploaded_by', 'id');
    }

    public function evidenceable()
    {
        return $this->morphTo();
    }
}
