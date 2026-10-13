<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $rows = AuditTrail::daftar()
            ->cari($request->query('q'))
            ->rentang($request->query('tanggal'))
            ->modul($request->query('modul'))
            ->orderByDesc('audit_trails.created_at')
            ->orderByDesc('audit_trails.id')
            ->paginate(10)
            ->withQueryString();

        $this->lengkapiKode($rows->getCollection());

        // Panel detail di sisi kanan: ?detail={id}
        $detail = $koreksi = null;
        $bukti = collect();
        if ($id = (int) $request->query('detail')) {
            $detail = AuditTrail::daftar()->where('audit_trails.id', $id)->first();
            if ($detail) {
                $this->lengkapiKode(collect([$detail]));
                if ($detail->correction_request_id) {
                    $koreksi = DB::table('correction_requests')->where('id', $detail->correction_request_id)->first();
                    $bukti = DB::table('evidences')
                        ->where('evidenceable_type', 'correction_requests')
                        ->where('evidenceable_id', $detail->correction_request_id)
                        ->orderBy('id')
                        ->get();
                }
            }
        }

        return view('admin.audit.index', [
            'rows'        => $rows,
            'detail'      => $detail,
            'koreksi'     => $koreksi,
            'bukti'       => $bukti,
            'daftarModul' => AuditTrail::daftarModul(),
            'totalSemua'  => AuditTrail::count(),
            'totalHariIni' => AuditTrail::where('created_at', '>=', now()->startOfDay())->count(),
            'totalPerubahan' => AuditTrail::whereNotNull('old_values')->orWhereNotNull('new_values')->count(),
        ]);
    }

    private function lengkapiKode($koleksi): void
    {
        $kode = AuditTrail::kodeUntuk($koleksi);
        $koleksi->each(function (AuditTrail $r) use ($kode) {
            $r->kode_data = $kode[$r->auditable_type . ':' . $r->auditable_id] ?? '#' . $r->auditable_id;
        });
    }
}
