<?php

namespace App\Models;

use App\Support\Keys;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

class AuditTrail extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'audit_trails';

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public const AKTIVITAS = [
        'confirm'            => 'Mengonfirmasi',
        'void'               => 'Membatalkan (void)',
        'cancel'             => 'Membatalkan',
        'correction_request' => 'Mengajukan koreksi',
        'correction_approve' => 'Menyetujui koreksi',
        'correction_reject'  => 'Menolak koreksi',
        'correction_apply'   => 'Menerapkan koreksi',
    ];

    public const MODUL = [
        'purchases'                => 'Pembelian',
        'purchase_items'           => 'Pembelian',
        'receivings'               => 'Penerimaan Bahan',
        'receiving_items'          => 'Penerimaan Bahan',
        'material_lots'            => 'Stok',
        'stock_movements'          => 'Stok',
        'production_batches'       => 'Produksi',
        'material_usages'          => 'Produksi',
        'processing_steps'         => 'Produksi',
        'pre_distribution_checks'  => 'Produksi',
        'distributions'            => 'Distribusi',
        'waste_surplus_records'    => 'Waste & Surplus',
        'correction_requests'      => 'Koreksi',
        'menus'                    => 'Menu',
        'recipes'                  => 'Menu',
        'recipe_items'             => 'Menu',
        'suppliers'                => 'Pemasok',
        'materials'                => 'Bahan',
        'distribution_destinations' => 'Tujuan Distribusi',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit trail tidak boleh diubah.'));
        static::deleting(fn () => throw new LogicException('Audit trail tidak boleh dihapus.'));
    }

    /**
     * @param string      $aksi        salah satu kunci AKTIVITAS
     * @param Model       $objek       data yang terkena aksi (nama tabelnya jadi auditable_type)
     * @param array|null  $lama        nilai sebelum (hanya kolom yang berubah)
     * @param array|null  $baru        nilai sesudah
     * @param int|null    $koreksiId   isi untuk aksi koreksi; jika $objek sendiri CorrectionRequest, otomatis terisi
     */
    public static function catat(string $aksi, Model $objek, ?array $lama = null, ?array $baru = null, ?int $koreksiId = null): self
    {
        if ($koreksiId === null && $objek->getTable() === 'correction_requests') {
            $koreksiId = (int) $objek->getKey();
        }

        return static::catatPada($aksi, $objek->getTable(), (int) $objek->getKey(), $lama, $baru, $koreksiId);
    }

    /**
     * @param Model $koreksi baris correction_requests
     */
    public static function catatKoreksi(string $aksi, Model $koreksi, ?array $lama = null, ?array $baru = null): self
    {
        if (! str_starts_with($aksi, 'correction_')) {
            throw new InvalidArgumentException('catatKoreksi hanya untuk aksi correction_*. Untuk aksi lain pakai catat().');
        }

        $f = $koreksi->field_name;
        [$lamaDefault, $baruDefault] = match ($aksi) {
            'correction_approve' => [['status' => 'pending'], ['status' => 'approved']],
            'correction_reject'  => [['status' => 'pending'], ['status' => 'rejected']],
            default              => [[$f => $koreksi->old_value], [$f => $koreksi->new_value]],
        };

        return static::catatPada($aksi, $koreksi->target_table, (int) $koreksi->target_id, $lama ?? $lamaDefault, $baru ?? $baruDefault, (int) $koreksi->getKey());
    }

    private static function catatPada(string $aksi, string $tabel, int $id, ?array $lama, ?array $baru, ?int $koreksiId): self
    {
        if (! array_key_exists($aksi, self::AKTIVITAS)) {
            throw new InvalidArgumentException("Aksi audit '{$aksi}' tidak dikenal. Pilihan: " . implode(', ', array_keys(self::AKTIVITAS)));
        }

        $pelaku = Auth::id();
        if (! $pelaku) {
            throw new RuntimeException('Audit trail harus punya pelaku: user harus sedang login.');
        }

        return static::create([
            'correction_request_id' => $koreksiId,
            'id_user'               => $pelaku,
            'action'                => $aksi,
            'auditable_type'        => $tabel,
            'auditable_id'          => $id,
            'old_values'            => $lama,
            'new_values'            => $baru,
        ]);
    }

    public function scopeDaftar(Builder $q): Builder
    {
        return $q
            ->leftJoin(Keys::USER_TABLE . ' as u', 'u.' . Keys::USER_PK, '=', 'audit_trails.id_user')
            ->leftJoin(Keys::SPPG_TABLE . ' as s', 's.' . Keys::SPPG_OWNER, '=', 'audit_trails.id_user')
            ->select('audit_trails.*', 'u.nama as pelaku_nama', 's.nama_sppg as pelaku_sppg');
    }

    public function scopeModul(Builder $q, ?string $modul): Builder
    {
        if (! $modul) {
            return $q;
        }
        $tabel = array_keys(array_filter(self::MODUL, fn ($nama) => $nama === $modul));

        return $q->whereIn('audit_trails.auditable_type', $tabel ?: ['-']);
    }

    /** Filter tanggal dari-sampai (format Y-m-d, dua-duanya boleh kosong). Batas dihitung satu hari penuh. */
    public function scopeRentang(Builder $q, ?string $dari, ?string $sampai): Builder
    {
        if ($dari) {
            $q->where('audit_trails.created_at', '>=', Carbon::parse($dari)->startOfDay());
        }
        if ($sampai) {
            $q->where('audit_trails.created_at', '<=', Carbon::parse($sampai)->endOfDay());
        }

        return $q;
    }

    /** Cari nama pengguna atau kode data (PB-xxx, nomor penerimaan, nomor pembelian, nomor koreksi). */
    public function scopeCari(Builder $q, ?string $kata): Builder
    {
        $kata = trim((string) $kata);
        if ($kata === '') {
            return $q;
        }
        $like = '%' . $kata . '%';

        return $q->where(function (Builder $w) use ($like) {
            $w->whereIn('audit_trails.id_user', fn ($s) => $s->select(Keys::USER_PK)->from(Keys::USER_TABLE)->where('nama', 'ilike', $like))
              ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'receivings')
                  ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('receivings')->where('receiving_no', 'ilike', $like)))
              ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'receiving_items')
                  ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('ri.id')->from('receiving_items as ri')
                      ->join('receivings as r', 'r.id', '=', 'ri.receiving_id')->where('r.receiving_no', 'ilike', $like)))
              ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'production_batches')
                  ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('production_batches')->where('batch_code', 'ilike', $like)))
              ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'purchases')
                  ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('purchases')->where('purchase_no', 'ilike', $like)))
              ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'purchase_items')
                  ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('pi.id')->from('purchase_items as pi')
                      ->join('purchases as p', 'p.id', '=', 'pi.purchase_id')->where('p.purchase_no', 'ilike', $like)))
              ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'correction_requests')
                  ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('correction_requests')->where('request_no', 'ilike', $like)));
        });
    }

    public static function daftarModul(): array
    {
        return array_values(array_unique(self::MODUL));
    }

    public function getAktivitasLabelAttribute(): string
    {
        return self::AKTIVITAS[$this->action] ?? $this->action;
    }

    public function getModulAttribute(): string
    {
        return self::MODUL[$this->auditable_type] ?? $this->auditable_type;
    }

    public function getKodeAuditAttribute(): string
    {
        return 'AUD-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getPelakuAttribute(): string
    {
        return ($this->pelaku_nama ?? '(user dihapus)') . ' - ' . ($this->pelaku_sppg ?? 'Admin');
    }

    public function getNadaAttribute(): string
    {
        return match ($this->action) {
            'confirm', 'correction_approve', 'correction_apply' => 'ok',
            'void', 'cancel', 'correction_reject'               => 'bad',
            default                                             => 'info',
        };
    }

    public static function kodeUntuk(Collection $rows): array
    {
        $sumber = [
            'receivings'          => fn ($ids) => DB::table('receivings')->whereIn('id', $ids)->select('id as k', 'receiving_no as v'),
            'receiving_items'     => fn ($ids) => DB::table('receiving_items as ri')->join('receivings as r', 'r.id', '=', 'ri.receiving_id')
                                        ->whereIn('ri.id', $ids)->select('ri.id as k', 'r.receiving_no as v'),
            'production_batches'  => fn ($ids) => DB::table('production_batches')->whereIn('id', $ids)->select('id as k', 'batch_code as v'),
            'purchases'           => fn ($ids) => DB::table('purchases')->whereIn('id', $ids)->select('id as k', 'purchase_no as v'),
            'purchase_items'      => fn ($ids) => DB::table('purchase_items as pi')->join('purchases as p', 'p.id', '=', 'pi.purchase_id')
                                        ->whereIn('pi.id', $ids)->select('pi.id as k', 'p.purchase_no as v'),
            'correction_requests' => fn ($ids) => DB::table('correction_requests')->whereIn('id', $ids)->select('id as k', 'request_no as v'),
        ];

        $hasil = [];
        foreach ($rows->groupBy('auditable_type') as $tipe => $grup) {
            if (! isset($sumber[$tipe])) {
                continue;
            }
            foreach ($sumber[$tipe]($grup->pluck('auditable_id')->unique()->all())->get() as $baris) {
                $hasil[$tipe . ':' . $baris->k] = $baris->v;
            }
        }

        return $hasil;
    }
}
