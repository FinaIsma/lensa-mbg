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
        'confirm'            => 'Mengonfirmasi',
        'void'               => 'Membatalkan (void)',
        'cancel'             => 'Membatalkan',
        'correction_request' => 'Mengajukan koreksi',
        'correction_approve' => 'Menyetujui koreksi',
        'correction_reject'  => 'Menolak koreksi',
        'correction_apply'   => 'Menerapkan koreksi',
        'sppg_verify'           => 'Memverifikasi SPPG',
        'sppg_reject'           => 'Menolak pendaftaran SPPG',
        'sppg_activate'         => 'Mengaktifkan SPPG',
        'sppg_deactivate'       => 'Menonaktifkan SPPG',
        'staff_replace'         => 'Mengganti staff',
        'kepala_change_request' => 'Mengajukan pergantian kepala',
        'kepala_change_approve' => 'Menyetujui pergantian kepala',
        'kepala_change_reject'  => 'Menolak pergantian kepala',
    ];

    /** Nama tabel objek (auditable_type) => nama modul untuk tampilan dan filter. Tambahkan jika ada tabel baru. */
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
        'sppg'                     => 'SPPG',
        'kepala_change_requests'   => 'SPPG',
        'staff'                    => 'Staff',
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
     * @param string      $aksi        salah satu kunci AKTIVITAS
     * @param Model       $objek       data yang terkena aksi (nama tabelnya jadi auditable_type)
     * @param array|null  $lama        nilai sebelum (hanya kolom yang berubah)
     * @param array|null  $baru        nilai sesudah
     * @param int|null    $koreksiId   isi untuk aksi koreksi; jika $objek sendiri CorrectionRequest, otomatis terisi
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
            throw new InvalidArgumentException("Aksi audit '{$aksi}' tidak dikenal. Pilihan: " . implode(', ', array_keys(self::AKTIVITAS)));
        }

        [$tipe, $aktorId, $aktorNama, $sppgPelaku] = self::pelaku();

        return static::create([
            'correction_request_id' => $koreksiId,
            'actor_type'            => $tipe,
            'actor_id'              => $aktorId,
            'actor_nama'            => $aktorNama,     // salinan nama saat aksi, tidak berubah walau akun diganti
            'id_sppg'               => $idSppg ?? $sppgPelaku,
            'action'                => $aksi,
            'auditable_type'        => $tabel,
            'auditable_id'          => $id,
            'old_values'            => $lama,
            'new_values'            => $baru,
        ]);
    }

    // ------------------------------------------------------------------ query untuk halaman

    /** Dasar query halaman: menambahkan peran pelaku bertipe user (admin / kepala_sppg) sebagai pelaku_role. */
    public function scopeDaftar(Builder $q): Builder
    {
        return $q
            ->leftJoin(Keys::USER_TABLE . ' as u', function ($j) {
                $j->on('u.' . Keys::USER_PK, '=', 'audit_trails.actor_id')->where('audit_trails.actor_type', '=', 'user');
            })
            ->select('audit_trails.*', 'u.role as pelaku_role');
    }

    /** Batasi ke satu SPPG (dipakai halaman kepala SPPG). */
    public function scopeSppg(Builder $q, int $idSppg): Builder
    {
        return $q->where('audit_trails.id_sppg', $idSppg);
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

    /** Cari nama pengguna atau kode data (PB-xxx, nomor penerimaan, nomor pembelian, nomor koreksi). */
    public function scopeCari(Builder $q, ?string $kata): Builder
    {
        $kata = trim((string) $kata);
        if ($kata === '') {
            return $q;
        }
        $like = '%' . $kata . '%';

        return $q->where(function (Builder $w) use ($like) {
            $w->where('audit_trails.actor_nama', 'ilike', $like)
              ->orWhere(fn ($x) => $x->where('audit_trails.auditable_type', Keys::SPPG_TABLE)
                  ->whereRaw("'SPPG' || LPAD(audit_trails.auditable_id::text, 3, '0') ILIKE ?", [$like]))
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

    // ------------------------------------------------------------------ tampilan (dihitung, tidak disimpan)

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
        return 'AUD-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    /** "Sana - Kepala SPPG", "Dewi - Staff", "Nanda - Admin". */
    public function getPelakuAttribute(): string
    {
        $peran = $this->actor_type === 'staff'
            ? 'Staff'
            : match ($this->pelaku_role ?? null) {
                'kepala_sppg' => 'Kepala SPPG',
                'admin'       => 'Admin',
                default       => 'Pengguna',
            };

        return ($this->actor_nama ?: '(tanpa nama)') . ' - ' . $peran;
    }

    /** Warna lencana aktivitas: ok | bad | info. */
    public function getNadaAttribute(): string
    {
        return match ($this->action) {
            'confirm', 'correction_approve', 'correction_apply', 'sppg_verify', 'sppg_activate', 'kepala_change_approve' => 'ok',
            'void', 'cancel', 'correction_reject', 'sppg_reject', 'sppg_deactivate', 'kepala_change_reject'             => 'bad',
            default                                             => 'info',
        };
    }

    /**
     * Kode data yang mudah dibaca (nomor penerimaan, kode batch, dst.) untuk banyak baris sekaligus (tanpa N+1).
     * Hasil: ['receivings:12' => 'RCV-20261012-001', ...]. Tipe tanpa kode jatuh ke "#id" di pemanggil.
     */
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
            Keys::SPPG_TABLE      => fn ($ids) => DB::table(Keys::SPPG_TABLE)->whereIn(Keys::SPPG_PK, $ids)
                                        ->select(Keys::SPPG_PK . ' as k', DB::raw("'SPPG' || LPAD(" . Keys::SPPG_PK . "::text, 3, '0') as v")),
            'staff'               => fn ($ids) => DB::table('staff')->whereIn('id', $ids)->select('id as k', DB::raw("'STF' || LPAD(id::text, 3, '0') as v")),
            'kepala_change_requests' => fn ($ids) => DB::table('kepala_change_requests')->whereIn('id', $ids)
                                        ->select('id as k', DB::raw("'KPL' || LPAD(id::text, 3, '0') as v")),
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
