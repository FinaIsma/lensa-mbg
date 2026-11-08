<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Distribution extends Model
{
    use HasFactory;

    protected $table = 'distributions';

    protected $fillable = [
        'production_batch_id',
        'destination_id',
        'recorded_by',
        'portions_sent',
        'status',
        'distributed_at',
    ];

    protected $casts = [
        'distributed_at' => 'datetime',
        'portions_sent' => 'integer',
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(DistributionDestination::class, 'destination_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'recorded_by');
    }
}

