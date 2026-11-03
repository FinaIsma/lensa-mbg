<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'stock_movements';

    protected $fillable = [
        'material_lot_id',
        'created_by',
        'movement_type',
        'direction',
        'qty',
        'reference_type',
        'reference_id',
        'created_at',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'created_at' => 'datetime',
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(MaterialLot::class, 'material_lot_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Label manusiawi untuk jenis mutasi stok.
     */
    public function getMovementTypeLabelAttribute(): string
    {
        return match ($this->movement_type) {
            'RECEIVE_IN' => 'Penerimaan Bahan',
            'PRODUCTION_OUT' => 'Pemakaian Produksi',
            'DISPOSAL_OUT' => 'Pembuangan / Limbah',
            'CORRECTION_IN' => 'Koreksi Masuk',
            'CORRECTION_OUT' => 'Koreksi Keluar',
            'VOID_REVERSAL' => 'Pembalikan (Void)',
            default => ucwords(strtolower(str_replace('_', ' ', (string) $this->movement_type))),
        };
    }

    /**
     * Kelas badge arah mutasi (IN hijau, OUT merah).
     */
    public function getDirectionBadgeClassAttribute(): string
    {
        return $this->direction === 'IN' ? 'badge-in' : 'badge-out';
    }
}
