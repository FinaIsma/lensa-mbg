<?php

namespace App\Models;

use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = [
        'supplier_id',
        'id_sppg',
        'created_by',
        'purchase_no',
        'purchase_date',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', Keys::SPPG_PK);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function receivings(): HasMany
    {
        return $this->hasMany(Receiving::class, 'purchase_id');
    }

    /**
     * Total biaya nota dihitung dinamis dari item, tidak disimpan di DB.
     */
    public function getTotalCostAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum(fn (PurchaseItem $item) => (float) $item->qty_ordered * (float) $item->unit_price);
        }

        return (float) ($this->items()->selectRaw('SUM(qty_ordered * unit_price) as total')->value('total') ?? 0);
    }

    public function getFormattedTotalCostAttribute(): string
    {
        return 'Rp '.number_format($this->total_cost, 0, ',', '.');
    }

    public function getTotalItemsCountAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return $this->items->count();
        }

        return $this->items()->count();
    }

    /**
     * Label status sesuai mockup.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'ordered' => 'Menunggu Cek',
            'received_partial' => 'Sebagian Diterima',
            'received' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Kelas badge warna status sesuai mockup.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'badge-draft',
            'ordered' => 'badge-ordered',
            'received_partial' => 'badge-partial',
            'received' => 'badge-received',
            'cancelled' => 'badge-cancelled',
            default => 'badge-draft',
        };
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeOrdered(): bool
    {
        return $this->status === 'draft';
    }

    public function hasConfirmedReceivings(): bool
    {
        return $this->receivings()->where('status', 'confirmed')->exists();
    }

    public function canBeCancelled(): bool
    {
        return ! in_array($this->status, ['cancelled', 'received'], true) && ! $this->hasConfirmedReceivings();
    }

    public function canReceive(): bool
    {
        return in_array($this->status, ['ordered', 'received_partial'], true);
    }

    /**
     * Generate format nomor PO: PO-YYYYMMDD-XXX
     */
    public static function generatePurchaseNo(?int $idSppg = null, ?string $date = null): string
    {
        $dateObj = $date ? Carbon::parse($date) : Carbon::today();
        $dateStr = $dateObj->format('Ymd');
        $prefix = "PO-{$dateStr}-";

        $lastNo = static::query()
            ->when($idSppg, fn ($q) => $q->where('id_sppg', $idSppg))
            ->where('purchase_no', 'like', "{$prefix}%")
            ->orderByDesc('purchase_no')
            ->value('purchase_no');

        $seq = 1;
        if ($lastNo && preg_match('/-(\d{3})$/', $lastNo, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return sprintf('%s%03d', $prefix, $seq);
    }
}
