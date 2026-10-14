<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTrail extends Model
{
    protected $table = 'audit_trails';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'correction_request_id',
        'id_user',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function correctionRequest(): BelongsTo
    {
        return $this->belongsTo(CorrectionRequest::class, 'correction_request_id', 'id');
    }

    /**
     * Map auditable_type to module name.
     */
    public function getModulAttribute(): string
    {
        return match ($this->auditable_type) {
            'distributions', 'distribution_destinations' => 'Distribusi',
            'production_batches', 'menus', 'recipes', 'materials' => 'Produksi',
            'receivings', 'receiving_items', 'purchases' => 'Penerimaan',
            'sppg', 'users' => 'Master Data',
            default => 'Koreksi',
        };
    }

    /**
     * Map action to Indonesian activity text.
     */
    public function getAktivitasAttribute(): string
    {
        return match ($this->action) {
            'confirm' => 'Konfirmasi Data',
            'correction_request' => 'Mengajukan koreksi',
            'correction_approve' => 'Menyetujui koreksi',
            'correction_reject' => 'Menolak koreksi',
            'correction_apply' => 'Menerapkan koreksi',
            'create' => 'Menambah data',
            'update' => 'Mengubah data',
            'delete' => 'Menghapus data',
            default => ucfirst(str_replace('_', ' ', (string) $this->action)),
        };
    }
}
