<?php

namespace App\Models;

use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Receiving extends Model
{
    use HasFactory;

    protected $table = 'receivings';

    protected $fillable = [
        'purchase_id',
        'id_sppg',
        'received_by',
        'receiving_no',
        'received_at',
        'status',
        'is_locked',
        'confirmed_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'is_locked' => 'boolean',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', Keys::SPPG_PK);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceivingItem::class, 'receiving_id');
    }

    public function lots(): HasManyThrough
    {
        return $this->hasManyThrough(
            MaterialLot::class,
            ReceivingItem::class,
            'receiving_id',
            'receiving_item_id'
        );
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'evidenceable_id', 'id')
            ->where('evidenceable_type', 'receivings');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'draft' && ! $this->is_locked;
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeVoided(): bool
    {
        return $this->status === 'draft';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'confirmed' => 'Terkonfirmasi',
            'voided' => 'Dibatalkan',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'badge-draft',
            'confirmed' => 'badge-received',
            'voided' => 'badge-cancelled',
            default => 'badge-draft',
        };
    }

    /**
     * Generate receiving_no unik: RCV-YYYYMMDD-NNN
     */
    public static function generateReceivingNo(?int $idSppg = null, ?string $date = null): string
    {
        $dateObj = $date ? Carbon::parse($date) : Carbon::today();
        $dateStr = $dateObj->format('Ymd');
        $prefix = "RCV-{$dateStr}-";

        $lastNo = static::query()
            ->when($idSppg, fn ($q) => $q->where('id_sppg', $idSppg))
            ->where('receiving_no', 'like', "{$prefix}%")
            ->orderByDesc('receiving_no')
            ->value('receiving_no');

        $seq = 1;
        if ($lastNo && preg_match('/-(\d{3})$/', $lastNo, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return sprintf('%s%03d', $prefix, $seq);
    }
}
