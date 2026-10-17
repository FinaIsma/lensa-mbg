<?php

use App\Support\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'suppliers',
            'materials',
            'menus',
            'distribution_destinations',
            'recipes',
            'recipe_items',
            'purchases',
            'purchase_items',
            'receivings',
            'receiving_items',
            'material_lots',
            'stock_movements',
            'production_batches',
            'material_usages',
            'processing_steps',
            'pre_distribution_checks',
            'distributions',
            'waste_surplus_records',
            'evidences',
            'correction_requests',
            'audit_trails',
        ];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE \"{$table}\" ENABLE ROW LEVEL SECURITY");
        }
    }

    public function down(): void
    {
        $tables = [
            'suppliers',
            'materials',
            'menus',
            'distribution_destinations',
            'recipes',
            'recipe_items',
            'purchases',
            'purchase_items',
            'receivings',
            'receiving_items',
            'material_lots',
            'stock_movements',
            'production_batches',
            'material_usages',
            'processing_steps',
            'pre_distribution_checks',
            'distributions',
            'waste_surplus_records',
            'evidences',
            'correction_requests',
            'audit_trails',
        ];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE \"{$table}\" DISABLE ROW LEVEL SECURITY");
        }
    }
};
