<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        [$dari, $sampai] = $this->rentangTanggal($request);

        $rows = AuditTrail::daftar()
            ->cari($request->query('q'))
            ->rentang($dari, $sampai)
            ->modul($request->query('modul'))
            ->orderByDesc('audit_trails.created_at')
            ->orderByDesc('audit_trails.id')
            ->paginate(10)
            ->withQueryString();

        $this->lengkapiKode($rows->getCollection());

        $idKoreksi = $rows->getCollection()->pluck('correction_request_id')->filter()->unique()->values()->all();
        $koreksi = $idKoreksi
            ? DB::table('correction_requests')->whereIn('id', $idKoreksi)->get()->keyBy('id')
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
            'totalSemua'     => AuditTrail::count(),
            'totalHariIni'   => AuditTrail::where('created_at', '>=', now()->startOfDay())->count(),
            'totalPerubahan' => AuditTrail::whereNotNull('old_values')->orWhereNotNull('new_values')->count(),
        ]);
    }

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

    private function lengkapiKode($koleksi): void
    {
        $kode = AuditTrail::kodeUntuk($koleksi);
        $koleksi->each(function (AuditTrail $r) use ($kode) {
            $r->kode_data = $kode[$r->auditable_type . ':' . $r->auditable_id] ?? '#' . $r->auditable_id;
        });
    }
}
