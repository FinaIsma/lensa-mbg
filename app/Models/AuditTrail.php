<?php

namespace App\Models;

use App\Support\Keys;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

/**
 * Cara pakai (di controller/service, di DALAM DB::transaction yang sama dengan aksinya):
 *
 *   AuditTrail::catat('confirm', $receiving, ['status' => 'draft'], ['status' => 'confirmed']);
 *   AuditTrail::catat('correction_apply', $receivingItem, ['qty_received' => 40], ['qty_received' => 45], $correction->id);
 */

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

    //penulisan

    /**
     * catat satu aktivitas. pelaku selalu user yang sedang login (bukan dari input form).
     *
     * @param string      $aksi        salah satu kunci AKTIVITAS
     * @param Model       $objek       data yang terkena aksi (nama tabelnya jadi auditable_type)
     * @param array|null  $lama        nilai sebelum (hanya kolom yang berubah)
     * @param array|null  $baru        nilai sesudah
     * @param int|null    $koreksiId   isi untuk aksi koreksi; jika $objek sendiri CorrectionRequest, otomatis terisi
     */
    public static function catat(string $aksi, Model $objek, ?array $lama = null, ?array $baru = null, ?int $koreksiId = null): self
    {
        if (! array_key_exists($aksi, self::AKTIVITAS)) {
            throw new InvalidArgumentException("Aksi audit '{$aksi}' tidak dikenal. Pilihan: " . implode(', ', array_keys(self::AKTIVITAS)));
        }

        $pelaku = Auth::id();
        if (! $pelaku) {
            throw new RuntimeException('Audit trail harus punya pelaku: user harus sedang login.');
        }

        if ($koreksiId === null && $objek->getTable() === 'correction_requests') {
            $koreksiId = (int) $objek->getKey();
        }

        return static::create([
            'correction_request_id' => $koreksiId,
            'id_user'               => $pelaku,
            'action'                => $aksi,
            'auditable_type'        => $objek->getTable(),
            'auditable_id'          => $objek->getKey(),
            'old_values'            => $lama,
            'new_values'            => $baru,
        ]);
    }

    // query untuk halaman
    //dasar query halaman: tambah nama pelaku dan nama SPPG-nya (kalau punya)
    public function scopeDaftar(Builder $q): Builder
    {
        return $q
            ->leftJoin(Keys::USER_TABLE . ' as u', 'u.' . Keys::USER_PK, '=', 'audit_trails.id_user')
            ->leftJoin(Keys::SPPG_TABLE . ' as s', 's.' . Keys::SPPG_OWNER, '=', 'audit_trails.id_user')
            ->select('audit_trails.*', 'u.nama as pelaku_nama', 's.nama_sppg as pelaku_sppg');
    }

    //filter modul
    public function scopeModul(Builder $q, ?string $modul): Builder
    {
        if (! $modul) {
            return $q;
        }
        $tabel = array_keys(array_filter(self::MODUL, fn ($nama) => $nama === $modul));

        return $q->whereIn('audit_trails.auditable_type', $tabel ?: ['-']);
    }

    //filter tanggal: hari_ini | 7_hari | 30_hari
    public function scopeRentang(Builder $q, ?string $rentang): Builder
    {
        $mulai = match ($rentang) {
            'hari_ini' => now()->startOfDay(),
            '7_hari'   => now()->subDays(6)->startOfDay(),
            '30_hari'  => now()->subDays(29)->startOfDay(),
            default    => null,
        };

        return $mulai ? $q->where('audit_trails.created_at', '>=', $mulai) : $q;
    }

    //cari nama pengguna atau kode data (PB-xxx, nomor penerimaan, nomor pembelian, nomor koreksi)
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

    // tampilan (dihitung, tidak disimpan)
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
