<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use HasFactory;

    protected $table = 'recipes';

    protected $fillable = [
        'menu_id',
        'ukuran_porsi',
        'version',
        'status',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecipeItem::class, 'recipe_id');
    }

    public function nutritions(): HasMany
    {
        return $this->hasMany(RecipeNutrition::class, 'recipe_id');
    }

    /**
     * Cek apakah resep ini sudah pernah digunakan dalam batch produksi
     */
    public function isUsedInBatch(): bool
    {
        return \Illuminate\Support\Facades\DB::table('production_batches')
            ->where('recipe_id', $this->id)
            ->exists();
    }

    public function getFormattedIdAttribute(): string
    {
        return 'RS' . str_pad((string) $this->id, 2, '0', STR_PAD_LEFT);
    }

    public function getBahanListAttribute(): string
    {
        return $this->items
            ->map(fn (RecipeItem $item) => $item->material?->nama)
            ->filter()
            ->unique()
            ->values()
            ->implode(', ') ?: '-';
    }
}
