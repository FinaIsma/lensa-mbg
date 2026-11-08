<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessingStep extends Model
{
    use HasFactory;

    protected $table = 'processing_steps';

    protected $fillable = [
        'production_batch_id',
        'recorded_by',
        'step_name',
        'started_at',
        'ended_at',
        'status',
        'note',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'recorded_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'evidenceable_id', 'id')
            ->where('evidenceable_type', 'processing_steps');
    }

    /**
     * Hitung durasi kerja jika sudah berakhir
     */
    public function getDurationHumanAttribute(): ?string
    {
        if (! $this->started_at || ! $this->ended_at) {
            return null;
        }

        $diffMinutes = $this->started_at->diffInMinutes($this->ended_at);
        $hours = intdiv($diffMinutes, 60);
        $minutes = $diffMinutes % 60;

        if ($hours > 0) {
            return "{$hours} jam {$minutes} mnt";
        }

        return "{$minutes} menit";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'Sedang Berjalan',
            'done' => 'Selesai',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'badge-ordered', // warna kuning/amber
            'done' => 'badge-received', // warna hijau
            default => 'badge-draft',
        };
    }
}
