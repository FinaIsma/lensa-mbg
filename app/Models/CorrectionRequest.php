<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectionRequest extends Model
{
    protected $table = 'correction_requests';

    protected $primaryKey = 'id';

    public const JENIS_DATA = [
        'distributions'             => 'Distribusi',
        'distribution_destinations' => 'Distribusi',
        'production_batches'        => 'Produksi',
        'materials'                 => 'Produksi',
        'material_usages'           => 'Produksi',
        'processing_steps'          => 'Produksi',
        'pre_distribution_checks'   => 'Produksi',
        'menus'                     => 'Menu',
        'recipes'                   => 'Menu',
        'recipe_items'              => 'Menu',
        'receivings'                => 'Penerimaan',
        'receiving_items'           => 'Penerimaan',
        'purchases'                 => 'Pengadaan',
        'purchase_items'            => 'Pengadaan',
        'material_lots'             => 'Stok',
        'stock_movements'           => 'Stok',
        'waste_surplus_records'     => 'Waste & Surplus',
    ];

    protected $fillable = [
        'id_sppg',
        'requested_by',
        'reviewed_by',
        'request_no',
        'target_table',
        'target_id',
        'field_name',
        'old_value',
        'new_value',
        'reason',
        'status',
        'requested_at',
        'reviewed_at',
        'review_note',
        'applied_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function sppg(): BelongsTo
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', 'id_sppg');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by', 'id_user');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id_user');
    }

    public function evidences()
    {
        return $this->hasMany(Evidence::class, 'evidenceable_id', 'id')
            ->where('evidenceable_type', 'correction_requests');
    }

    public function auditTrails()
    {
        return $this->hasMany(AuditTrail::class, 'correction_request_id', 'id');
    }

    // Scopes
    public function scopeCari(Builder $q, ?string $kata): Builder
    {
        $kata = trim((string) $kata);
        if ($kata === '') {
            return $q;
        }
        $like = '%' . $kata . '%';

        return $q->where(function (Builder $w) use ($like) {
            $w->where('request_no', 'ilike', $like)
              ->orWhere('field_name', 'ilike', $like)
              ->orWhere('reason', 'ilike', $like)
              ->orWhereHas('sppg', fn ($sq) => $sq->where('nama_sppg', 'ilike', $like))
              ->orWhereHas('requester', fn ($uq) => $uq->where('nama', 'ilike', $like));
        });
    }

    public function scopeSppgFilter(Builder $q, ?string $idSppg): Builder
    {
        if (! $idSppg) {
            return $q;
        }
        return $q->where('id_sppg', $idSppg);
    }

    public function scopeJenisData(Builder $q, ?string $jenis): Builder
    {
        if (! $jenis) {
            return $q;
        }
        $tables = array_keys(array_filter(self::JENIS_DATA, fn ($val) => strcasecmp($val, $jenis) === 0));
        return $q->whereIn('target_table', $tables ?: ['-']);
    }

    public function scopeStatusFilter(Builder $q, ?string $status): Builder
    {
        if (! $status) {
            return $q;
        }

        return match (strtolower($status)) {
            'pending', 'belum diverifikasi', 'menunggu review' => $q->where('status', 'pending'),
            'approved', 'terverifikasi', 'disetujui'            => $q->whereIn('status', ['approved', 'applied']),
            'rejected', 'ditolak'                               => $q->where('status', 'rejected'),
            default                                             => $q->where('status', $status),
        };
    }

    public function scopeRentang(Builder $q, ?string $dari, ?string $sampai): Builder
    {
        if ($dari) {
            $q->where('requested_at', '>=', Carbon::parse($dari)->startOfDay());
        }
        if ($sampai) {
            $q->where('requested_at', '<=', Carbon::parse($sampai)->endOfDay());
        }
        return $q;
    }

    // Accessors
    public function getKodePengajuanAttribute(): string
    {
        if ($this->request_no && str_starts_with($this->request_no, 'CR-')) {
            return $this->request_no;
        }
        return 'CR-' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }

    public function getKodeSppgAttribute(): string
    {
        return 'SPPG' . str_pad((string) $this->id_sppg, 3, '0', STR_PAD_LEFT);
    }

    public function getJenisDataAttribute(): string
    {
        return self::JENIS_DATA[$this->target_table] ?? ucfirst(str_replace('_', ' ', $this->target_table));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'             => 'Belum Diverifikasi',
            'approved', 'applied' => 'Terverifikasi',
            'rejected'            => 'Ditolak',
            default               => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending'             => 'badge-pending',
            'approved', 'applied' => 'badge-approved',
            'rejected'            => 'badge-rejected',
            default               => 'badge-default',
        };
    }

    public function getTampilOldValueAttribute(): string
    {
        return $this->formatNilai($this->old_value);
    }

    public function getTampilNewValueAttribute(): string
    {
        return $this->formatNilai($this->new_value);
    }

    public function getTargetDataDisplayAttribute(): string
    {
        return $this->target_data_code ?? $this->kode_pengajuan;
    }

    private function formatNilai(?string $val): string
    {
        if ($val === null || $val === '') {
            return '-';
        }
        $valTrim = trim($val);
        if (str_starts_with($valTrim, 'Rp') || str_starts_with($valTrim, 'rp')) {
            return $valTrim;
        }
        if (is_numeric($valTrim)) {
            $num = (float) $valTrim;
            if ($num >= 1000) {
                $field = strtolower($this->field_name ?? '');
                if (preg_match('/(nominal|harga|total|biaya|nilai|cost|price|budget|dana|porsi|portion)/i', $field)) {
                    if (str_contains($field, 'porsi') || str_contains($field, 'portion')) {
                        return number_format($num, 0, ',', '.') . ' porsi';
                    }
                    return 'Rp ' . number_format($num, 0, ',', '.');
                }
            }
        }
        return (string) $val;
    }

    public static function daftarJenisData(): array
    {
        return ['Distribusi', 'Produksi', 'Penerimaan', 'Pengadaan', 'Stok', 'Waste & Surplus', 'Menu'];
    }

    public static function lengkapiKodeTarget(Collection $items): void
    {
        $grouped = $items->groupBy('target_table');
        $resolved = [];

        foreach ($grouped as $table => $subItems) {
            $ids = $subItems->pluck('target_id')->unique()->filter()->all();
            if (empty($ids)) {
                continue;
            }

            try {
                if ($table === 'receivings') {
                    $rows = DB::table('receivings')->whereIn('id', $ids)->select('id', 'receiving_no as code')->get();
                } elseif ($table === 'receiving_items') {
                    $rows = DB::table('receiving_items as ri')
                        ->join('receivings as r', 'r.id', '=', 'ri.receiving_id')
                        ->whereIn('ri.id', $ids)->select('ri.id', 'r.receiving_no as code')->get();
                } elseif ($table === 'production_batches') {
                    $rows = DB::table('production_batches')->whereIn('id', $ids)->select('id', 'batch_code as code')->get();
                } elseif ($table === 'purchases') {
                    $rows = DB::table('purchases')->whereIn('id', $ids)->select('id', 'purchase_no as code')->get();
                } elseif ($table === 'purchase_items') {
                    $rows = DB::table('purchase_items as pi')
                        ->join('purchases as p', 'p.id', '=', 'pi.purchase_id')
                        ->whereIn('pi.id', $ids)->select('pi.id', 'p.purchase_no as code')->get();
                } elseif ($table === 'distributions') {
                    $rows = DB::table('distributions')->whereIn('id', $ids)->select('id', 'distribution_no as code')->get();
                } else {
                    $rows = collect();
                }

                foreach ($rows as $r) {
                    $resolved[$table . ':' . $r->id] = $r->code;
                }
            } catch (\Throwable) {
                // Keep default
            }
        }

        $items->each(function (CorrectionRequest $item) use ($resolved) {
            $item->target_data_code = $resolved[$item->target_table . ':' . $item->target_id] ?? $item->kode_pengajuan;
        });
    }
}
