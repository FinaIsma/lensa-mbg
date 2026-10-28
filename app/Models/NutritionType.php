<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NutritionType extends Model
{
    use HasFactory;

    protected $table = 'nutrition_types';

    protected $fillable = [
        'nama',
        'satuan',
    ];

    public function recipeNutritions(): HasMany
    {
        return $this->hasMany(RecipeNutrition::class, 'nutrition_type_id');
    }
}
