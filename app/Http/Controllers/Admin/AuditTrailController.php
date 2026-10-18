<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Support\Keys;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Halaman Audit Trail untuk KEPALA SPPG: hanya menampilkan audit milik SPPG-nya sendiri.
 * (Admin tidak punya halaman ini. Folder/namespace boleh dipindah, mis. ke App\Http\Controllers\Sppg.)
 * Pasang di group route kepala SPPG (middleware auth + role:kepala_sppg) dengan nama route 'sppg.audit'
 * atau ubah konstanta ROUTE di bawah.
 */
class AuditTrailController extends Controller
{
    /** Nama route halaman ini (dipakai form filter & tombol reset). */
    public const ROUTE = 'admin.audit';

    /** View sidebar kepala SPPG; jika belum ada, dipakai sidebar admin sementara. */
    private const SIDEBAR = 'sppg.partials.sidebar';

    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role === 'admin';
        $idSppg = null;

        if (! $isAdmin) {
            $idSppg = DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_OWNER, Auth::id())->value(Keys::SPPG_PK);
            abort_unless($idSppg, 403, 'Akun ini tidak terhubung ke SPPG mana pun.');
            $idSppg = (int) $idSppg;
        }

        [$dari, $sampai] = $this->rentangTanggal($request);

        $query = AuditTrail::daftar()
            ->cari($request->query('q'))
            ->rentang($dari, $sampai)
            ->modul($request->query('modul'));

        if ($idSppg) {
            $query->sppg($idSppg);
        }

        $rows = $query
            ->orderByDesc('audit_trails.created_at')
            ->orderByDesc('audit_trails.id')
            ->paginate(10)
            ->withQueryString();

        $this->lengkapiKode($rows->getCollection());

        // Data untuk panel detail semua baris di halaman ini (2 query saja), supaya panel bisa dibuka tanpa reload.
        $idKoreksi = $rows->getCollection()->pluck('correction_request_id')->filter()->unique()->values()->all();
        $koreksiQuery = DB::table('correction_requests')->whereIn('id', $idKoreksi ?: [0]);
        if ($idSppg) {
            $koreksiQuery->where('id_sppg', $idSppg);
        }
        $koreksi = $idKoreksi ? $koreksiQuery->get()->keyBy('id') : collect();

        $bukti = $idKoreksi
            ? DB::table('evidences')->where('evidenceable_type', 'correction_requests')
                ->whereIn('evidenceable_id', $idKoreksi)->orderBy('id')->get()->groupBy('evidenceable_id')
            : collect();

        $countQuery = $idSppg ? AuditTrail::sppg($idSppg) : AuditTrail::query();

        $namaSppg = $idSppg
            ? DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_PK, $idSppg)->value('nama_sppg')
            : 'Seluruh SPPG';

        $sidebar = $isAdmin
            ? 'admin.partials.sidebar'
            : (view()->exists(self::SIDEBAR) ? self::SIDEBAR : 'admin.partials.sidebar');

        return view('admin.audit-trail', [
            'rows' => $rows,
            'koreksi' => $koreksi,
            'bukti' => $bukti,
            'dari' => $dari,
            'sampai' => $sampai,
            'daftarModul' => AuditTrail::daftarModul(),
            'totalSemua' => (clone $countQuery)->count(),
            'totalHariIni' => (clone $countQuery)->where('created_at', '>=', now()->startOfDay())->count(),
            'totalPerubahan' => (clone $countQuery)->where(fn ($q) => $q->whereNotNull('old_values')->orWhereNotNull('new_values'))->count(),
            'namaUser' => $user->nama ?? ($isAdmin ? 'Admin' : 'Kepala SPPG'),
            'namaSppg' => $namaSppg,
            'routeName' => self::ROUTE,
            'sidebar' => $sidebar,
        ]);
    }

    /** Ambil ?dari=&sampai= (Y-m-d). Tanggal tidak valid diabaikan; kalau terbalik, ditukar. */
    private function rentangTanggal(Request $request): array
    {
        $baca = function (?string $v) {
            try {
                return $v ? Carbon::createFromFormat('Y-m-d', $v)->format('Y-m-d') : null;
            } catch (\Throwable) {
                return null;
            }
        };
        $dari = $baca($request->query('dari'));
        $sampai = $baca($request->query('sampai'));
        if ($dari && $sampai && $dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [$dari, $sampai];
    }

    /** Menempelkan kode data yang mudah dibaca ke tiap baris sebagai $row->kode_data. */
    private function lengkapiKode($koleksi): void
    {
        $kode = AuditTrail::kodeUntuk($koleksi);
        $koleksi->each(function (AuditTrail $r) use ($kode) {
            $r->kode_data = $kode[$r->auditable_type.':'.$r->auditable_id] ?? '#'.$r->auditable_id;
        });
    }
}
