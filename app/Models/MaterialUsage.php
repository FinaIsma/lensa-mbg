<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialUsage extends Model
{
    use HasFactory;

    protected $table = 'material_usages';

    protected $fillable = [
        'production_batch_id',
        'material_lot_id',
        'qty_used',
        'unit_cost_snapshot',
    ];

    protected $casts = [
        'qty_used' => 'decimal:3',
        'unit_cost_snapshot' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(MaterialLot::class, 'material_lot_id');
    }

    public function getMaterialAttribute(): ?Material
    {
        return $this->lot?->material;
    }

    /**
     * Biaya baris = qty_used * unit_cost_snapshot (atau lot->unit_cost saat draft)
     */
    public function getRowCostAttribute(): float
    {
        $cost = (float) ($this->unit_cost_snapshot ?? $this->lot?->unit_cost ?? 0);

        return round(((float) $this->qty_used) * $cost, 2);
    }
}
