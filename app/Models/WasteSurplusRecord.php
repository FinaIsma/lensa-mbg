<?php

namespace App\Models;

use App\Support\Keys;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WasteSurplusRecord extends Model
{
    use HasFactory;

    protected $table = 'waste_surplus_records';

    protected $fillable = [
        'production_batch_id',
        'material_lot_id',
        'distribution_id',
        'id_sppg',
        'recorded_by',
        'record_type',
        'qty',
        'reason',
        'recorded_at',
        'status',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'recorded_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(MaterialLot::class, 'material_lot_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'recorded_by');
    }

    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', Keys::SPPG_PK);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'evidenceable_id', 'id')
            ->where('evidenceable_type', 'waste_surplus_records');
    }
}

