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
    public const ROUTE = 'sppg.audit';

    /** View sidebar kepala SPPG; jika belum ada, dipakai sidebar admin sementara. */
    private const SIDEBAR = 'sppg.partials.sidebar';

    public function index(Request $request)
    {
        // SPPG milik kepala yang login (sppg.id_user = id kepala). Semua query di bawah dibatasi ke id ini.
        $idSppg = DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_OWNER, Auth::id())->value(Keys::SPPG_PK);
        abort_unless($idSppg, 403, 'Akun ini tidak terhubung ke SPPG mana pun.');
        $idSppg = (int) $idSppg;

        [$dari, $sampai] = $this->rentangTanggal($request);

        $rows = AuditTrail::daftar()
            ->sppg($idSppg)
            ->cari($request->query('q'))
            ->rentang($dari, $sampai)
            ->modul($request->query('modul'))
            ->orderByDesc('audit_trails.created_at')
            ->orderByDesc('audit_trails.id')
            ->paginate(10)
            ->withQueryString();

        $this->lengkapiKode($rows->getCollection());

        // Data untuk panel detail semua baris di halaman ini (2 query saja), supaya panel bisa dibuka tanpa reload.
        $idKoreksi = $rows->getCollection()->pluck('correction_request_id')->filter()->unique()->values()->all();
        $koreksi = $idKoreksi
            ? DB::table('correction_requests')->where('id_sppg', $idSppg)->whereIn('id', $idKoreksi)->get()->keyBy('id')
            : collect();
        $bukti = $idKoreksi
            ? DB::table('evidences')->where('evidenceable_type', 'correction_requests')
                ->whereIn('evidenceable_id', $idKoreksi)->orderBy('id')->get()->groupBy('evidenceable_id')
            : collect();

        return view('admin.audit-trail', [
            'rows'           => $rows,
            'koreksi'        => $koreksi,
            'bukti'          => $bukti,
            'dari'           => $dari,
            'sampai'         => $sampai,
            'daftarModul'    => AuditTrail::daftarModul(),
            'totalSemua'     => AuditTrail::sppg($idSppg)->count(),
            'totalHariIni'   => AuditTrail::sppg($idSppg)->where('created_at', '>=', now()->startOfDay())->count(),
            'totalPerubahan' => AuditTrail::sppg($idSppg)->where(fn ($q) => $q->whereNotNull('old_values')->orWhereNotNull('new_values'))->count(),
            'namaUser'       => Auth::user()->nama ?? 'Kepala SPPG',
            'namaSppg'       => DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_PK, $idSppg)->value('nama_sppg'),
            'routeName'      => self::ROUTE,
            'sidebar'        => view()->exists(self::SIDEBAR) ? self::SIDEBAR : 'admin.partials.sidebar',
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
            $r->kode_data = $kode[$r->auditable_type . ':' . $r->auditable_id] ?? '#' . $r->auditable_id;
        });
    }
}
