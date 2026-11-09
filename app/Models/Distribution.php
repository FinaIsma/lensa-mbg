<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Distribution extends Model
{
    use HasFactory;

    protected $table = 'distributions';

    protected $fillable = [
        'production_batch_id',
        'destination_id',
        'recorded_by',
        'portions_sent',
        'status',
        'distributed_at',
    ];

    protected $casts = [
        'distributed_at' => 'datetime',
        'portions_sent' => 'integer',
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(DistributionDestination::class, 'destination_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'recorded_by');
    }

    public function evidences(): MorphMany
    {
        return $this->morphMany(Evidence::class, 'evidenceable');
    }

    public function wasteRecords(): HasMany
    {
        return $this->hasMany(WasteSurplusRecord::class, 'distribution_id');
    }

    public function scopeForSppg(Builder $query, int $sppgId): Builder
    {
        return $query->whereHas('destination', function ($q) use ($sppgId) {
            $q->where('id_sppg', $sppgId);
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'planned' => 'Direncanakan',
            'sent' => 'Dalam Pengiriman',
            'received' => 'Diterima',
            'cancelled' => 'Dibatalkan',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'planned' => 'badge-planned',
            'sent' => 'badge-sent',
            'received' => 'badge-received',
            'cancelled' => 'badge-cancelled',
            default => 'badge-planned',
        };
    }

    public function getIsOverQuotaAttribute(): bool
    {
        $target = (int) ($this->destination?->jumlah_penerima ?? 0);
        if ($target <= 0) {
            return false;
        }

        return (int) $this->portions_sent > $target;
    }
}
