<?php

namespace App\Models;

use App\Support\Keys;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'id_sppg',
        'nama',
        'kontak',
        'alamat',
        'status',
    ];

    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', Keys::SPPG_PK);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }
}
