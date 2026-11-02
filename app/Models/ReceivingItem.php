<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReceivingItem extends Model
{
    use HasFactory;

    protected $table = 'receiving_items';

    protected $fillable = [
        'receiving_id',
        'purchase_item_id',
        'material_id',
        'qty_received',
        'lot_number_input',
        'expiry_date_input',
        'kondisi_dilaporkan',
    ];

    protected $casts = [
        'qty_received' => 'decimal:3',
        'expiry_date_input' => 'date',
    ];

    public function receiving(): BelongsTo
    {
        return $this->belongsTo(Receiving::class, 'receiving_id');
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function lot(): HasOne
    {
        return $this->hasOne(MaterialLot::class, 'receiving_item_id');
    }
}
