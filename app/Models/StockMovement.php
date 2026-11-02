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
}
