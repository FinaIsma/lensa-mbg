<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectionRequest extends Model
{
    protected $table = 'correction_requests';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_sppg',
        'requested_by',
        'reviewed_by',
        'request_no',
        'target_table',
        'target_id',
        'field_name',
        'old_value',
        'new_value',
        'reason',
        'status',
        'requested_at',
        'reviewed_at',
        'review_note',
        'applied_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', 'id_sppg');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by', 'id_user');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id_user');
    }

    /**
     * Map database target_table to user friendly Indonesian label.
     */
    public function getJenisDataAttribute(): string
    {
        return match ($this->target_table) {
            'distributions', 'distribution_destinations' => 'Distribusi',
            'production_batches', 'materials', 'material_usages', 'menus', 'recipes' => 'Produksi',
            'receivings', 'receiving_items', 'purchases', 'purchase_items' => 'Penerimaan',
            default => ucfirst(str_replace('_', ' ', (string) $this->target_table)),
        };
    }

    /**
     * Map database status to user friendly Indonesian status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved', 'applied' => 'Disetujui',
            'rejected' => 'Ditolak',
            'pending', 'waiting' => 'Menunggu',
            default => ucfirst((string) $this->status),
        };
    }
}
