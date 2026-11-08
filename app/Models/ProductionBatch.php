<?php

namespace App\Models;

use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductionBatch extends Model
{
    use HasFactory;

    protected $table = 'production_batches';

    protected $fillable = [
        'menu_id',
        'recipe_id',
        'id_sppg',
        'created_by',
        'batch_code',
        'production_date',
        'target_porsi',
        'actual_porsi',
        'total_cost',
        'status',
        'is_locked',
    ];

    protected $casts = [
        'production_date' => 'date',
        'target_porsi' => 'integer',
        'actual_porsi' => 'integer',
        'total_cost' => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    /**
     * Relasi ke Menu
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Relasi ke Resep (snapshot versi aktif saat dibuat)
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    /**
     * Relasi ke SPPG
     */
    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', Keys::SPPG_PK);
    }

    /**
     * Relasi ke Staff pembuat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Relasi ke pemakaian lot bahan
     */
    public function materialUsages(): HasMany
    {
        return $this->hasMany(MaterialUsage::class, 'production_batch_id');
    }

    /**
     * Relasi ke tahap pengolahan (urut berdasarkan started_at asc)
     */
    public function processingSteps(): HasMany
    {
        return $this->hasMany(ProcessingStep::class, 'production_batch_id')
            ->orderBy('started_at', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Relasi ke hasil produksi (pre-distribution check)
     */
    public function preDistributionCheck(): HasOne
    {
        return $this->hasOne(PreDistributionCheck::class, 'production_batch_id');
    }

    /**
     * Bukti foto terkait batch
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'evidenceable_id', 'id')
            ->where('evidenceable_type', 'production_batches');
    }

    /**
     * Susut produksi = target_porsi - actual_porsi
     */
    public function getSusutProduksiAttribute(): ?int
    {
        if ($this->actual_porsi === null) {
            return null;
        }

        return max(0, $this->target_porsi - $this->actual_porsi);
    }

    /**
     * Biaya per porsi = total_cost / actual_porsi (terhitung, kosong sebelum ada hasil)
     */
    public function getBiayaPerPorsiAttribute(): ?float
    {
        if (! $this->actual_porsi || $this->actual_porsi <= 0 || ! $this->total_cost) {
            return null;
        }

        return round((float) $this->total_cost / (float) $this->actual_porsi, 2);
    }

    /**
     * Estimasi biaya per porsi berdasarkan target (untuk batch confirmed sebelum ada actual_porsi)
     */
    public function getEstimasiBiayaPerPorsiAttribute(): ?float
    {
        if (! $this->target_porsi || $this->target_porsi <= 0 || ! $this->total_cost) {
            return null;
        }

        return round((float) $this->total_cost / (float) $this->target_porsi, 2);
    }

    /**
     * Label status bahasa Indonesia yang rapi
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'confirmed' => 'Terkonfirmasi',
            'processing' => 'Pengolahan',
            'ready' => 'Siap Distribusi',
            'distributed' => 'Didistribusikan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Kelas badge warna status:
     * - draft: abu-abu
     * - confirmed, processing, ready, distributed: kuning / amber
     * - completed: hijau
     * - cancelled: merah
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'badge-draft',
            'confirmed', 'processing', 'ready', 'distributed' => 'badge-ordered', // warna kuning/amber
            'completed' => 'badge-received', // warna hijau
            'cancelled' => 'badge-cancelled', // warna merah
            default => 'badge-draft',
        };
    }

    /**
     * Tahap yang disarankan per kategori menu
     */
    public function getSuggestedStepsAttribute(): array
    {
        $kategori = $this->menu?->kategori;

        return match ($kategori) {
            'makanan_siap_santap' => ['Persiapan bahan', 'Pencucian', 'Pemasakan', 'Pengemasan'],
            'paket_siap_konsumsi' => ['Pemeriksaan produk', 'Pengemasan paket'],
            'pangan_siap_olah' => ['Persiapan bahan', 'Pengolahan awal', 'Pengemasan'],
            default => ['Persiapan bahan', 'Pencucian', 'Pemasakan', 'Pengemasan'],
        };
    }

    /**
     * Generate format batch code unik: BTH-YYYYMMDD-NNN
     */
    public static function generateBatchCode(?int $idSppg = null, ?string $date = null): string
    {
        $dateObj = $date ? Carbon::parse($date) : Carbon::today();
        $dateStr = $dateObj->format('Ymd');
        $prefix = "BTH-{$dateStr}-";

        $lastCode = static::query()
            ->when($idSppg, fn ($q) => $q->where('id_sppg', $idSppg))
            ->where('batch_code', 'like', "{$prefix}%")
            ->orderByDesc('batch_code')
            ->value('batch_code');

        $seq = 1;
        if ($lastCode && preg_match('/-(\d{3})$/', $lastCode, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return sprintf('%s%03d', $prefix, $seq);
    }
}
