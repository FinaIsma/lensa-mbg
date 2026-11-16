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

    public const FORM_TYPES = [
        'makanan_siap_santap' => 'Makanan Siap Santap',
        'paket_siap_konsumsi' => 'Paket Siap Konsumsi',
        'pangan_siap_olah' => 'Pangan Siap Olah',
        'uji_sampel_rutin' => 'Uji Sampel Rutin',
    ];

    public const DEFAULT_PHYSICAL_MATRIX = [
        'rasa' => ['label' => 'Rasa', 'status' => 'sesuai', 'keterangan' => 'Gurih, bumbu meresap, normal khas resep'],
        'aroma' => ['label' => 'Aroma', 'status' => 'segar', 'keterangan' => 'Segar, wangi masakan khas rempah, bebas bau apek'],
        'warna' => ['label' => 'Warna & Visual', 'status' => 'cerah', 'keterangan' => 'Cerah, bersih, tidak pucat, bebas kontaminan asing'],
        'tekstur' => ['label' => 'Tekstur & Kematangan', 'status' => 'matang', 'keterangan' => 'Matang sempurna, empuk, tidak keras/mentah'],
        'suhu' => ['label' => 'Suhu Sajian', 'status' => 'hangat', 'keterangan' => 'Hangat (≥60°C) higienis'],
        'kemasan' => ['label' => 'Kemasan & Higienitas', 'status' => 'rapat', 'keterangan' => 'Tertutup rapat, baki bersih food-grade'],
    ];

    protected $fillable = [
        'production_batch_id',
        'checked_by',
        'portions_ready',
        'reported_condition',
        'note',
        'checked_at',
        'form_type',
        'nutritionist_name',
        'nutritionist_id',
        'consume_by',
        'estimated_distribution_at',
        'physical_test_matrix',
        'physical_test_status',
    ];

    protected $casts = [
        'portions_ready' => 'integer',
        'checked_at' => 'datetime',
        'consume_by' => 'datetime',
        'estimated_distribution_at' => 'datetime',
        'physical_test_matrix' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'checked_by');
    }

    public function nutritionist(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'nutritionist_id');
    }

    public function getEffectiveNutritionistNameAttribute(): string
    {
        return $this->nutritionist_name ?: ($this->nutritionist?->nama ?? 'Tenaga Gizi');
    }

    public function getFormTypeLabelAttribute(): string
    {
        return self::FORM_TYPES[$this->form_type] ?? ucfirst(str_replace('_', ' ', $this->form_type ?? 'makanan_siap_santap'));
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'evidenceable_id', 'id')
            ->where('evidenceable_type', 'pre_distribution_checks');
    }
}
