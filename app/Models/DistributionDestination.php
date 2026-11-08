<?php

namespace App\Models;

use App\Support\Keys;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributionDestination extends Model
{
    use HasFactory;

    protected $table = 'distribution_destinations';

    protected $fillable = [
        'id_sppg',
        'nama',
        'tipe',
        'alamat',
        'jumlah_penerima',
    ];

    protected $casts = [
        'jumlah_penerima' => 'integer',
    ];

    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', Keys::SPPG_PK);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class, 'destination_id');
    }

    public function scopeForSppg(Builder $query, int $idSppg): Builder
    {
        return $query->where('id_sppg', $idSppg);
    }

    public function getTipeLabelAttribute(): string
    {
        return match (strtolower((string) $this->tipe)) {
            'sekolah' => 'Sekolah',
            'posyandu' => 'Posyandu',
            default => 'Lainnya',
        };
    }
}

