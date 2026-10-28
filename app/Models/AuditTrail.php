<?php

namespace App\Models;

use App\Support\Keys;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

/**
 * Jejak aktivitas penting. Ditulis OTOMATIS oleh kode lewat AuditTrail::catat(), tidak pernah diisi manual oleh pengguna.
 * Tabel ini hanya boleh ditambah: update dan delete ditolak.
 *
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

    /** Nilai kolom `action` (sesuai enum di migration) => label tampilan. */
    public const AKTIVITAS = [
        'confirm' => 'Mengonfirmasi',
        'void' => 'Membatalkan (void)',
        'cancel' => 'Membatalkan',
        'correction_request' => 'Mengajukan koreksi',
        'correction_approve' => 'Menyetujui koreksi',
        'correction_reject' => 'Menolak koreksi',
        'correction_apply' => 'Menerapkan koreksi',
        'sppg_verify' => 'Memverifikasi SPPG',
        'sppg_reject' => 'Menolak pendaftaran SPPG',
        'sppg_activate' => 'Mengaktifkan SPPG',
        'sppg_deactivate' => 'Menonaktifkan SPPG',
        'staff_replace' => 'Mengganti staff',
        'kepala_change_request' => 'Mengajukan pergantian kepala',
        'kepala_change_approve' => 'Menyetujui pergantian kepala',
        'kepala_change_reject' => 'Menolak pergantian kepala',
    ];

    /** Nama tabel objek (auditable_type) => nama modul untuk tampilan dan filter. Tambahkan jika ada tabel baru. */
    public const MODUL = [
        'purchases' => 'Pembelian',
        'purchase_items' => 'Pembelian',
        'receivings' => 'Penerimaan Bahan',
        'receiving_items' => 'Penerimaan Bahan',
        'material_lots' => 'Stok',
        'stock_movements' => 'Stok',
        'production_batches' => 'Produksi',
        'material_usages' => 'Produksi',
        'processing_steps' => 'Produksi',
        'pre_distribution_checks' => 'Produksi',
        'distributions' => 'Distribusi',
        'waste_surplus_records' => 'Waste & Surplus',
        'correction_requests' => 'Koreksi',
        'menus' => 'Menu',
        'recipes' => 'Menu',
        'recipe_items' => 'Menu',
        'suppliers' => 'Pemasok',
        'materials' => 'Bahan',
        'distribution_destinations' => 'Tujuan Distribusi',
        'sppg' => 'SPPG',
        'kepala_change_requests' => 'SPPG',
        'staff' => 'Staff',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit trail tidak boleh diubah.'));
        static::deleting(fn () => throw new LogicException('Audit trail tidak boleh dihapus.'));
    }

    // ------------------------------------------------------------------ penulisan

    /**
     * Catat satu aktivitas. Pelaku selalu user yang sedang login (bukan dari input form).
     *
     * @param  string  $aksi  salah satu kunci AKTIVITAS
     * @param  Model  $objek  data yang terkena aksi (nama tabelnya jadi auditable_type)
     * @param  array|null  $lama  nilai sebelum (hanya kolom yang berubah)
     * @param  array|null  $baru  nilai sesudah
     * @param  int|null  $koreksiId  isi untuk aksi koreksi; jika $objek sendiri CorrectionRequest, otomatis terisi
     */
    public static function catat(string $aksi, Model $objek, ?array $lama = null, ?array $baru = null, ?int $koreksiId = null, ?int $idSppg = null): self
    {
        if ($koreksiId === null && $objek->getTable() === 'correction_requests') {
            $koreksiId = (int) $objek->getKey();
        }

        // SPPG yang terkena: argumen > kolom id_sppg pada objek > objek itu sendiri jika tabel sppg > SPPG milik pelaku.
        $idSppg ??= $objek->getAttribute('id_sppg')
            ?? ($objek->getTable() === Keys::SPPG_TABLE ? $objek->getKey() : null);

        return static::catatPada($aksi, $objek->getTable(), (int) $objek->getKey(), $lama, $baru, $koreksiId, $idSppg ? (int) $idSppg : null);
    }

    /**
     * Khusus aksi koreksi: dicatat pada DATA YANG DIKOREKSI (target_table + target_id dari pengajuan), lengkap dengan
     * penghubung ke pengajuannya, sehingga halaman audit menampilkan modul/kode data aslinya plus alasan dan bukti.
     * Nilai lama/baru terisi otomatis kalau tidak diberikan:
     *   correction_request / correction_apply : [field_name => old_value] -> [field_name => new_value]
     *   correction_approve / correction_reject: status pending -> approved / rejected
     *
     * @param  Model  $koreksi  baris correction_requests
     */
    public static function catatKoreksi(string $aksi, Model $koreksi, ?array $lama = null, ?array $baru = null): self
    {
        if (! str_starts_with($aksi, 'correction_')) {
            throw new InvalidArgumentException('catatKoreksi hanya untuk aksi correction_*. Untuk aksi lain pakai catat().');
        }

        $f = $koreksi->field_name;
        [$lamaDefault, $baruDefault] = match ($aksi) {
            'correction_approve' => [['status' => 'pending'], ['status' => 'approved']],
            'correction_reject' => [['status' => 'pending'], ['status' => 'rejected']],
            default => [[$f => $koreksi->old_value], [$f => $koreksi->new_value]],
        };

        return static::catatPada(
            $aksi, $koreksi->target_table, (int) $koreksi->target_id,
            $lama ?? $lamaDefault, $baru ?? $baruDefault, (int) $koreksi->getKey(), (int) $koreksi->id_sppg
        );
    }

    /**
     * Pelaku = yang sedang login: guard 'staff' (tabel staff) atau guard bawaan (tabel users: admin / kepala SPPG).
     * Mengembalikan [actor_type, actor_id, actor_nama, id_sppg_pelaku].
     */
    private static function pelaku(): array
    {
        if (config('auth.guards.staff') && Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();

            return ['staff', (int) $staff->getKey(), $staff->nama, (int) $staff->id_sppg];
        }

        if (Auth::check()) {
            $user = Auth::user();
            $idSppg = DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_OWNER, $user->getKey())->value(Keys::SPPG_PK);

            return ['user', (int) $user->getKey(), $user->nama, $idSppg ? (int) $idSppg : null];
        }

        throw new RuntimeException('Audit trail harus punya pelaku: user atau staff harus sedang login.');
    }

    private static function catatPada(string $aksi, string $tabel, int $id, ?array $lama, ?array $baru, ?int $koreksiId, ?int $idSppg): self
    {
        if (! array_key_exists($aksi, self::AKTIVITAS)) {
            throw new InvalidArgumentException("Aksi audit '{$aksi}' tidak dikenal. Pilihan: ".implode(', ', array_keys(self::AKTIVITAS)));
        }

        [$tipe, $aktorId, $aktorNama, $sppgPelaku] = self::pelaku();

        return static::create([
            'correction_request_id' => $koreksiId,
            'actor_type' => $tipe,
            'actor_id' => $aktorId,
            'actor_nama' => $aktorNama,     // salinan nama saat aksi, tidak berubah walau akun diganti
            'id_sppg' => $idSppg ?? $sppgPelaku,
            'action' => $aksi,
            'auditable_type' => $tabel,
            'auditable_id' => $id,
            'old_values' => $lama,
            'new_values' => $baru,
            'created_at' => now(),
        ]);
    }

    // ------------------------------------------------------------------ query untuk halaman

    /** Dasar query halaman: menambahkan peran pelaku bertipe user (admin / kepala_sppg) dan nama SPPG. */
    public function scopeDaftar(Builder $q): Builder
    {
        return $q
            ->leftJoin(Keys::USER_TABLE.' as u', function ($j) {
                $j->on('u.'.Keys::USER_PK, '=', 'audit_trails.actor_id')->where('audit_trails.actor_type', '=', 'user');
            })
            ->leftJoin(Keys::SPPG_TABLE.' as s', 's.'.Keys::SPPG_PK, '=', 'audit_trails.id_sppg')
            ->select('audit_trails.*', 'u.role as pelaku_role', 's.nama_sppg');
    }

    /** Batasi ke satu SPPG (dipakai halaman kepala SPPG). */
    public function scopeSppg(Builder $q, int $idSppg): Builder
    {
        return $q->where('audit_trails.id_sppg', $idSppg);
    }

    /** Khusus Kepala SPPG: strictly batasi ke SPPG miliknya DAN sembunyikan semua aktivitas Admin Sistem. */
    public function scopeUntukKepalaSppg(Builder $q, int $idSppg): Builder
    {
        return $q->where('audit_trails.id_sppg', $idSppg)
            ->where(function (Builder $w) {
                $w->where('audit_trails.actor_type', 'staff')
                    ->orWhere(function (Builder $w2) {
                        $w2->where('audit_trails.actor_type', 'user')
                            ->whereNotExists(function ($sub) {
                                $sub->selectRaw('1')
                                    ->from(Keys::USER_TABLE)
                                    ->whereColumn(Keys::USER_TABLE.'.'.Keys::USER_PK, 'audit_trails.actor_id')
                                    ->whereIn(Keys::USER_TABLE.'.role', ['admin', 'admin_sistem']);
                            });
                    });
            });
    }

    /** Filter berdasarkan peran aktor (admin sistem, sppg / semua tim sppg, kepala sppg, staff). */
    public function scopePeran(Builder $q, ?string $role): Builder
    {
        if (! $role) {
            return $q;
        }

        return match ($role) {
            'admin' => $q->where('audit_trails.actor_type', 'user')
                ->whereExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from(Keys::USER_TABLE)
                        ->whereColumn(Keys::USER_TABLE.'.'.Keys::USER_PK, 'audit_trails.actor_id')
                        ->whereIn(Keys::USER_TABLE.'.role', ['admin', 'admin_sistem']);
                }),
            'sppg' => $q->where(function (Builder $w) {
                $w->where('audit_trails.actor_type', 'staff')
                    ->orWhere(function (Builder $w2) {
                        $w2->where('audit_trails.actor_type', 'user')
                            ->whereExists(function ($sub) {
                                $sub->selectRaw('1')
                                    ->from(Keys::USER_TABLE)
                                    ->whereColumn(Keys::USER_TABLE.'.'.Keys::USER_PK, 'audit_trails.actor_id')
                                    ->where(Keys::USER_TABLE.'.role', 'kepala_sppg');
                            });
                    });
            }),
            'kepala_sppg' => $q->where('audit_trails.actor_type', 'user')
                ->whereExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from(Keys::USER_TABLE)
                        ->whereColumn(Keys::USER_TABLE.'.'.Keys::USER_PK, 'audit_trails.actor_id')
                        ->where(Keys::USER_TABLE.'.role', 'kepala_sppg');
                }),
            'staff' => $q->where('audit_trails.actor_type', 'staff'),
            default => $q,
        };
    }

    /** Filter modul: 'Produksi' mencakup beberapa tabel sekaligus. */
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

    /** Cari nama pengguna atau kode data (PB-xxx, nomor penerimaan, nomor pembelian, nomor koreksi, lot, dll). */
    public function scopeCari(Builder $q, ?string $kata): Builder
    {
        $kata = trim((string) $kata);
        if ($kata === '') {
            return $q;
        }
        $like = '%'.$kata.'%';

        return $q->where(function (Builder $w) use ($like) {
            $w->where('audit_trails.actor_nama', 'ilike', $like)
                // SPPG
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', Keys::SPPG_TABLE)
                    ->whereRaw("'SPPG' || LPAD(audit_trails.auditable_id::text, 3, '0') ILIKE ?", [$like]))
                ->orWhere(fn ($x) => $x->whereIn('audit_trails.id_sppg', fn ($s) => $s->select(Keys::SPPG_PK)->from(Keys::SPPG_TABLE)->where('nama_sppg', 'ilike', $like)))
                // Staff & Kepala Change
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'staff')
                    ->whereRaw("'STF' || LPAD(audit_trails.auditable_id::text, 3, '0') ILIKE ?", [$like]))
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'kepala_change_requests')
                    ->whereRaw("'KPL' || LPAD(audit_trails.auditable_id::text, 3, '0') ILIKE ?", [$like]))
                // Penerimaan
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'receivings')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('receivings')->where('receiving_no', 'ilike', $like)))
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'receiving_items')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('ri.id')->from('receiving_items as ri')
                        ->join('receivings as r', 'r.id', '=', 'ri.receiving_id')->where('r.receiving_no', 'ilike', $like)))
                // Pembelian
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'purchases')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('purchases')->where('purchase_no', 'ilike', $like)))
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'purchase_items')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('pi.id')->from('purchase_items as pi')
                        ->join('purchases as p', 'p.id', '=', 'pi.purchase_id')->where('p.purchase_no', 'ilike', $like)))
                // Produksi & Tahapan
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'production_batches')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('production_batches')->where('batch_code', 'ilike', $like)))
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'material_usages')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('mu.id')->from('material_usages as mu')
                        ->join('production_batches as pb', 'pb.id', '=', 'mu.production_batch_id')->where('pb.batch_code', 'ilike', $like)))
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'processing_steps')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('ps.id')->from('processing_steps as ps')
                        ->join('production_batches as pb', 'pb.id', '=', 'ps.production_batch_id')->where('pb.batch_code', 'ilike', $like)))
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'pre_distribution_checks')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('pdc.id')->from('pre_distribution_checks as pdc')
                        ->join('production_batches as pb', 'pb.id', '=', 'pdc.production_batch_id')->where('pb.batch_code', 'ilike', $like)))
                // Stok
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'material_lots')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('material_lots')
                        ->where('lot_number', 'ilike', $like)->orWhere('barcode', 'ilike', $like)))
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'stock_movements')
                    ->whereRaw("'MOV' || LPAD(audit_trails.auditable_id::text, 4, '0') ILIKE ?", [$like]))
                // Distribusi
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'distributions')
                    ->whereRaw("'DIS' || LPAD(audit_trails.auditable_id::text, 4, '0') ILIKE ?", [$like]))
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'distribution_destinations')
                    ->whereRaw("'DST' || LPAD(audit_trails.auditable_id::text, 3, '0') ILIKE ?", [$like])
                    ->orWhereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('distribution_destinations')->where('nama', 'ilike', $like)))
                // Waste & Surplus
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'waste_surplus_records')
                    ->whereRaw("'WST' || LPAD(audit_trails.auditable_id::text, 4, '0') ILIKE ?", [$like]))
                // Koreksi
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'correction_requests')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('correction_requests')->where('request_no', 'ilike', $like)))
                // Menu & Resep
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'menus')
                    ->whereRaw("'MNU' || LPAD(audit_trails.auditable_id::text, 3, '0') ILIKE ?", [$like])
                    ->orWhereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('menus')->where('nama', 'ilike', $like)))
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'recipes')
                    ->whereRaw("'RCP' || LPAD(audit_trails.auditable_id::text, 3, '0') ILIKE ?", [$like]))
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'recipe_items')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('ri.id')->from('recipe_items as ri')
                        ->join('recipes as r', 'r.id', '=', 'ri.recipe_id')->whereRaw("'RCP' || LPAD(r.id::text, 3, '0') ILIKE ?", [$like])))
                // Pemasok & Bahan
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'suppliers')
                    ->whereRaw("'SUP' || LPAD(audit_trails.auditable_id::text, 3, '0') ILIKE ?", [$like])
                    ->orWhereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('suppliers')->where('nama', 'ilike', $like)))
                ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', 'materials')
                    ->whereIn('audit_trails.auditable_id', fn ($s) => $s->select('id')->from('materials')->where('kode', 'ilike', $like)->orWhere('nama', 'ilike', $like)));
        });
    }

    // ------------------------------------------------------------------ tampilan (dihitung, tidak disimpan)

    public function user()
    {
        return $this->belongsTo(User::class, 'actor_id', Keys::USER_PK);
    }

    public function sppgUnit()
    {
        return $this->belongsTo(Sppg::class, 'id_sppg', Keys::SPPG_PK);
    }

    public function getAktivitasAttribute(): string
    {
        return $this->aktivitas_label;
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

    /** "AUD-0042": nomor tampilan, diturunkan dari id. */
    public function getKodeAuditAttribute(): string
    {
        return 'AUD-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    /** "Sana - Kepala SPPG", "Dewi - Staff", "Nanda - Admin". */
    public function getPelakuAttribute(): string
    {
        $peran = $this->actor_type === 'staff'
            ? 'Staff'
            : match ($this->pelaku_role ?? null) {
                'kepala_sppg' => 'Kepala SPPG',
                'admin' => 'Admin',
                default => 'Pengguna',
            };

        return ($this->actor_nama ?: '(tanpa nama)').' - '.$peran;
    }

    /** Warna lencana aktivitas: ok | bad | info. */
    public function getNadaAttribute(): string
    {
        return match ($this->action) {
            'confirm', 'correction_approve', 'correction_apply', 'sppg_verify', 'sppg_activate', 'kepala_change_approve' => 'ok',
            'void', 'cancel', 'correction_reject', 'sppg_reject', 'sppg_deactivate', 'kepala_change_reject' => 'bad',
            default => 'info',
        };
    }

    /**
     * Kode data yang mudah dibaca (nomor penerimaan, kode batch, dst.) untuk banyak baris sekaligus (tanpa N+1).
     * Hasil: ['receivings:12' => 'RCV-20261012-001', ...]. Tipe tanpa kode jatuh ke "#id" di pemanggil.
     */
    public static function kodeUntuk(Collection $rows): array
    {
        $sumber = [
            // Penerimaan
            'receivings' => fn ($ids) => DB::table('receivings')->whereIn('id', $ids)->select('id as k', 'receiving_no as v'),
            'receiving_items' => fn ($ids) => DB::table('receiving_items as ri')->join('receivings as r', 'r.id', '=', 'ri.receiving_id')
                ->whereIn('ri.id', $ids)->select('ri.id as k', 'r.receiving_no as v'),

            // Pembelian
            'purchases' => fn ($ids) => DB::table('purchases')->whereIn('id', $ids)->select('id as k', 'purchase_no as v'),
            'purchase_items' => fn ($ids) => DB::table('purchase_items as pi')->join('purchases as p', 'p.id', '=', 'pi.purchase_id')
                ->whereIn('pi.id', $ids)->select('pi.id as k', 'p.purchase_no as v'),

            // Produksi
            'production_batches' => fn ($ids) => DB::table('production_batches')->whereIn('id', $ids)->select('id as k', 'batch_code as v'),
            'material_usages' => fn ($ids) => DB::table('material_usages as mu')->join('production_batches as pb', 'pb.id', '=', 'mu.production_batch_id')
                ->whereIn('mu.id', $ids)->select('mu.id as k', 'pb.batch_code as v'),
            'processing_steps' => fn ($ids) => DB::table('processing_steps as ps')->join('production_batches as pb', 'pb.id', '=', 'ps.production_batch_id')
                ->whereIn('ps.id', $ids)->select('ps.id as k', 'pb.batch_code as v'),
            'pre_distribution_checks' => fn ($ids) => DB::table('pre_distribution_checks as pdc')->join('production_batches as pb', 'pb.id', '=', 'pdc.production_batch_id')
                ->whereIn('pdc.id', $ids)->select('pdc.id as k', 'pb.batch_code as v'),

            // Stok
            'material_lots' => fn ($ids) => DB::table('material_lots')->whereIn('id', $ids)
                ->select('id as k', DB::raw("COALESCE(lot_number, barcode, 'LOT' || LPAD(id::text, 4, '0')) as v")),
            'stock_movements' => fn ($ids) => DB::table('stock_movements')->whereIn('id', $ids)
                ->select('id as k', DB::raw("'MOV' || LPAD(id::text, 4, '0') as v")),

            // Distribusi
            'distributions' => fn ($ids) => DB::table('distributions')->whereIn('id', $ids)
                ->select('id as k', DB::raw("'DIS' || LPAD(id::text, 4, '0') as v")),
            'distribution_destinations' => fn ($ids) => DB::table('distribution_destinations')->whereIn('id', $ids)
                ->select('id as k', DB::raw("'DST' || LPAD(id::text, 3, '0') as v")),

            // Waste & Surplus
            'waste_surplus_records' => fn ($ids) => DB::table('waste_surplus_records')->whereIn('id', $ids)
                ->select('id as k', DB::raw("'WST' || LPAD(id::text, 4, '0') as v")),

            // Koreksi
            'correction_requests' => fn ($ids) => DB::table('correction_requests')->whereIn('id', $ids)->select('id as k', 'request_no as v'),

            // Menu & Resep
            'menus' => fn ($ids) => DB::table('menus')->whereIn('id', $ids)
                ->select('id as k', DB::raw("'MNU' || LPAD(id::text, 3, '0') as v")),
            'recipes' => fn ($ids) => DB::table('recipes')->whereIn('id', $ids)
                ->select('id as k', DB::raw("'RCP' || LPAD(id::text, 3, '0') as v")),
            'recipe_items' => fn ($ids) => DB::table('recipe_items as ri')->join('recipes as r', 'r.id', '=', 'ri.recipe_id')
                ->whereIn('ri.id', $ids)->select('ri.id as k', DB::raw("'RCP' || LPAD(r.id::text, 3, '0') as v")),

            // Master Data (Pemasok & Bahan)
            'suppliers' => fn ($ids) => DB::table('suppliers')->whereIn('id', $ids)
                ->select('id as k', DB::raw("'SUP' || LPAD(id::text, 3, '0') as v")),
            'materials' => fn ($ids) => DB::table('materials')->whereIn('id', $ids)
                ->select('id as k', DB::raw("COALESCE(kode, 'MAT' || LPAD(id::text, 3, '0')) as v")),

            // Unit & Pengguna
            Keys::SPPG_TABLE => fn ($ids) => DB::table(Keys::SPPG_TABLE)->whereIn(Keys::SPPG_PK, $ids)
                ->select(Keys::SPPG_PK.' as k', DB::raw("'SPPG' || LPAD(".Keys::SPPG_PK."::text, 3, '0') as v")),
            'staff' => fn ($ids) => DB::table('staff')->whereIn('id', $ids)->select('id as k', DB::raw("'STF' || LPAD(id::text, 3, '0') as v")),
            'kepala_change_requests' => fn ($ids) => DB::table('kepala_change_requests')->whereIn('id', $ids)
                ->select('id as k', DB::raw("'KPL' || LPAD(id::text, 3, '0') as v")),
        ];

        $hasil = [];
        foreach ($rows->groupBy('auditable_type') as $tipe => $grup) {
            if (! isset($sumber[$tipe])) {
                continue;
            }
            foreach ($sumber[$tipe]($grup->pluck('auditable_id')->unique()->all())->get() as $baris) {
                $hasil[$tipe.':'.$baris->k] = $baris->v;
            }
        }

        return $hasil;
    }
}
