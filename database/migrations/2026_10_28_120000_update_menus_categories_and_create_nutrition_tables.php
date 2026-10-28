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
        // 1. Buat tabel nutrition_types (Jenis Gizi Global)
        if (! Schema::hasTable('nutrition_types')) {
            Schema::create('nutrition_types', function (Blueprint $t) {
                $t->id();
                $t->string('nama', 100)->unique();
                $t->string('satuan', 20); // kkal / g / mg
                $t->timestamps();
            });

            DB::statement("ALTER TABLE nutrition_types ADD CONSTRAINT nutrition_types_satuan_chk CHECK (satuan IN ('kkal', 'g', 'mg'))");
        }

        // 2. Buat tabel recipe_nutritions (Nilai Gizi per Resep)
        if (! Schema::hasTable('recipe_nutritions')) {
            Schema::create('recipe_nutritions', function (Blueprint $t) {
                $t->id();
                Keys::to($t, 'recipe_id', 'recipes');
                Keys::to($t, 'nutrition_type_id', 'nutrition_types');
                $t->decimal('nilai', 10, 2);
                $t->timestamps();

                $t->unique(['recipe_id', 'nutrition_type_id']);
            });

            DB::statement('ALTER TABLE recipe_nutritions ADD CONSTRAINT recipe_nutritions_nilai_chk CHECK (nilai >= 0)');
        }

        // 3. Update tabel menus (Kategori baru & Hapus kolom gizi)
        if (Schema::hasTable('menus')) {
            // Normalkan nilai kategori lama sebelum pasang check constraint
            DB::statement("UPDATE menus SET kategori = 'makanan_siap_santap' WHERE kategori NOT IN ('makanan_siap_santap', 'paket_siap_konsumsi', 'pangan_siap_olah') OR kategori IS NULL");

            // Pasang check constraint kategori
            DB::statement("DO \$\$ DECLARE r record; BEGIN
                FOR r IN SELECT conname FROM pg_constraint
                         WHERE conrelid = 'menus'::regclass AND contype = 'c' AND pg_get_constraintdef(oid) ILIKE '%kategori%'
                LOOP EXECUTE format('ALTER TABLE menus DROP CONSTRAINT %I', r.conname); END LOOP;
            END \$\$");

            DB::statement("ALTER TABLE menus ADD CONSTRAINT menus_kategori_check CHECK (kategori IN ('makanan_siap_santap', 'paket_siap_konsumsi', 'pangan_siap_olah'))");

            // Hapus kolom gizi jika ada
            if (Schema::hasColumn('menus', 'gizi')) {
                Schema::table('menus', function (Blueprint $t) {
                    $t->dropColumn('gizi');
                });
            }
        }

        // 4. Data awal jenis gizi (Default standard nutrition types)
        $defaultTypes = [
            ['nama' => 'Energi', 'satuan' => 'kkal'],
            ['nama' => 'Protein', 'satuan' => 'g'],
            ['nama' => 'Lemak', 'satuan' => 'g'],
            ['nama' => 'Karbohidrat', 'satuan' => 'g'],
            ['nama' => 'Serat', 'satuan' => 'g'],
            ['nama' => 'Kalsium', 'satuan' => 'mg'],
            ['nama' => 'Zat Besi', 'satuan' => 'mg'],
            ['nama' => 'Vitamin C', 'satuan' => 'mg'],
        ];

        foreach ($defaultTypes as $item) {
            DB::table('nutrition_types')->updateOrInsert(
                ['nama' => $item['nama']],
                ['satuan' => $item['satuan'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_nutritions');
        Schema::dropIfExists('nutrition_types');

        if (Schema::hasTable('menus') && ! Schema::hasColumn('menus', 'gizi')) {
            Schema::table('menus', function (Blueprint $t) {
                $t->text('gizi')->nullable();
            });
        }
    }
};
