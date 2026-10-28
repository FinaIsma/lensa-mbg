<?php

namespace App\Models;

use App\Support\Keys;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    public const KATEGORI = [
        'makanan_siap_santap' => 'Makanan Siap Santap',
        'paket_siap_konsumsi' => 'Paket Siap Konsumsi',
        'pangan_siap_olah' => 'Pangan Siap Olah',
    ];

    protected $fillable = [
        'id_sppg',
        'nama',
        'kategori',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI[$this->kategori] ?? ($this->kategori ?: '-');
    }

    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', Keys::SPPG_PK);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'menu_id');
    }

    public function getFormattedIdAttribute(): string
    {
        return 'MN' . str_pad((string) $this->id, 2, '0', STR_PAD_LEFT);
    }
}
