<?php

namespace App\Models;

use App\Support\Keys;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $table = 'materials';

    protected $fillable = [
        'id_sppg',
        'kode',
        'nama',
        'kategori',
        'satuan',
        'min_stock',
        'is_active',
    ];

    protected $casts = [
        'min_stock' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', Keys::SPPG_PK);
    }

    public function recipeItems(): HasMany
    {
        return $this->hasMany(RecipeItem::class, 'material_id');
    }
}
