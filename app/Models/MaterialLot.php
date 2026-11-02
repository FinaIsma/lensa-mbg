<?php

namespace App\Models;

use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialLot extends Model
{
    use HasFactory;

    protected $table = 'material_lots';

    protected $fillable = [
        'receiving_item_id',
        'material_id',
        'supplier_id',
        'id_sppg',
        'lot_number',
        'barcode',
        'received_date',
        'expiry_date',
        'initial_qty',
        'current_qty',
        'unit_cost',
        'status',
        'is_locked',
    ];

    protected $casts = [
        'received_date' => 'date',
        'expiry_date' => 'date',
        'initial_qty' => 'decimal:3',
        'current_qty' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    public function receivingItem(): BelongsTo
    {
        return $this->belongsTo(ReceivingItem::class, 'receiving_item_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', Keys::SPPG_PK);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'material_lot_id');
    }

    /**
     * Generate barcode lot unik: LOT-SPPG{id}-YYYYMMDD-XXX
     */
    public static function generateBarcode(?int $idSppg = null, ?string $date = null): string
    {
        $dateObj = $date ? Carbon::parse($date) : Carbon::today();
        $dateStr = $dateObj->format('Ymd');
        $sppgStr = $idSppg ? sprintf('SPPG%02d', $idSppg) : 'SPPG';
        $prefix = "LOT-{$sppgStr}-{$dateStr}-";

        $lastBarcode = static::where('barcode', 'like', "{$prefix}%")
            ->orderByDesc('barcode')
            ->value('barcode');

        $seq = 1;
        if ($lastBarcode && preg_match('/-(\d{3})$/', $lastBarcode, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return sprintf('%s%03d', $prefix, $seq);
    }
}
