<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreDistributionCheck extends Model
{
    use HasFactory;

    protected $table = 'pre_distribution_checks';

    protected $fillable = [
        'production_batch_id',
        'checked_by',
        'portions_ready',
        'reported_condition',
        'note',
        'checked_at',
    ];

    protected $casts = [
        'portions_ready' => 'integer',
        'checked_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'checked_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'evidenceable_id', 'id')
            ->where('evidenceable_type', 'pre_distribution_checks');
    }
}
