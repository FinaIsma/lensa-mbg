<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_items';

    protected $fillable = [
        'purchase_id',
        'material_id',
        'qty_ordered',
        'unit_price',
    ];

    protected $casts = [
        'qty_ordered' => 'decimal:3',
        'unit_price' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function receivingItems(): HasMany
    {
        return $this->hasMany(ReceivingItem::class, 'purchase_item_id');
    }

    /**
     * Subtotal = qty_ordered * unit_price (dihitung, tidak disimpan)
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->qty_ordered * (float) $this->unit_price;
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->unit_price, 0, ',', '.');
    }

    /**
     * Total qty yang sudah diterima dari penerimaan terkonfirmasi
     */
    public function getQtyReceivedTotalAttribute(): float
    {
        return (float) $this->receivingItems()
            ->whereHas('receiving', fn ($q) => $q->where('status', 'confirmed'))
            ->sum('qty_received');
    }

    /**
     * Sisa qty yang belum diterima
     */
    public function getQtyRemainingAttribute(): float
    {
        return max(0, (float) $this->qty_ordered - $this->qty_received_total);
    }
}
