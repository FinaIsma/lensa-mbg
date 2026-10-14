<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\CorrectionRequest;
use App\Models\Sppg;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        [$dari, $sampai] = $this->rentangTanggal($request);

        // Dynamic counts for top stat cards
        $totalCount = CorrectionRequest::count();
        $pendingCount = CorrectionRequest::where('status', 'pending')->count();
        $approvedCount = CorrectionRequest::whereIn('status', ['approved', 'applied'])->count();
        $rejectedCount = CorrectionRequest::where('status', 'rejected')->count();

        // Query with filters
        $query = CorrectionRequest::with(['sppg.user', 'requester', 'reviewer', 'evidences'])
            ->cari($request->query('q'))
            ->sppgFilter($request->query('sppg'))
            ->jenisData($request->query('jenis_data'))
            ->statusFilter($request->query('status'))
            ->rentang($dari, $sampai)
            ->orderBy('requested_at', 'desc')
            ->orderBy('id', 'desc');

        // Paginate 7 items per page as requested by UI design
        $rows = $query->paginate(7)->withQueryString();

        CorrectionRequest::lengkapiKodeTarget($rows->getCollection());

        $sppgs = Sppg::orderBy('id_sppg', 'asc')->get();
        $daftarJenisData = CorrectionRequest::daftarJenisData();

        return view('admin.correction-request', [
            'rows' => $rows,
            'totalCount' => $totalCount,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'sppgs' => $sppgs,
            'daftarJenisData' => $daftarJenisData,
            'dari' => $dari,
            'sampai' => $sampai,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $koreksi = CorrectionRequest::findOrFail($id);

        if ($koreksi->status !== 'pending') {
            return redirect()->back()->with('info', "Pengajuan koreksi {$koreksi->kode_pengajuan} sudah diproses sebelumnya.");
        }

        $koreksi->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_note' => $request->input('note', 'Pengajuan koreksi disetujui.'),
        ]);

        // Catat otomatis ke Audit Trail
        AuditTrail::catatKoreksi('correction_approve', $koreksi);

        return redirect()->back()->with('success', "Pengajuan koreksi {$koreksi->kode_pengajuan} berhasil disetujui.");
    }

    public function reject(Request $request, $id)
    {
        $koreksi = CorrectionRequest::findOrFail($id);

        if ($koreksi->status !== 'pending') {
            return redirect()->back()->with('info', "Pengajuan koreksi {$koreksi->kode_pengajuan} sudah diproses sebelumnya.");
        }

        $note = trim((string) $request->input('note'));

        $koreksi->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_note' => $note ?: 'Pengajuan koreksi ditolak.',
        ]);

        // Catat otomatis ke Audit Trail
        AuditTrail::catatKoreksi('correction_reject', $koreksi);

        return redirect()->back()->with('success', "Pengajuan koreksi {$koreksi->kode_pengajuan} telah ditolak.");
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
}
