<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeNutrition extends Model
{
    use HasFactory;

    protected $table = 'recipe_nutritions';

    protected $fillable = [
        'recipe_id',
        'nutrition_type_id',
        'nilai',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    public function nutritionType(): BelongsTo
    {
        return $this->belongsTo(NutritionType::class, 'nutrition_type_id');
    }
}
