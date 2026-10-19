<?php

namespace App\Http\Controllers\KepalaSPPG;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\CorrectionRequest;
use App\Models\Sppg;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CorrectionRequestController extends Controller
{
    /**
     * Tampilkan daftar pengajuan koreksi khusus untuk staff dari SPPG yang dipimpin.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $sppg = $user->sppg ?? Sppg::where('id_user', $user->id_user)->first();
        $idSppg = $sppg?->id_sppg;

        [$dari, $sampai] = $this->rentangTanggal($request);

        // Jika user Kepala SPPG belum tertaut ke SPPG
        if (! $idSppg) {
            $emptyPaginator = new LengthAwarePaginator([], 0, 7, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return view('KepalaSPPG.correction-request', [
                'rows' => $emptyPaginator,
                'totalCount' => 0,
                'pendingCount' => 0,
                'approvedCount' => 0,
                'rejectedCount' => 0,
                'staffList' => collect(),
                'daftarJenisData' => CorrectionRequest::daftarJenisData(),
                'dari' => $dari,
                'sampai' => $sampai,
                'sppg' => null,
            ]);
        }

        // Query dibatasi ketat HANYA untuk SPPG yang dipimpin oleh Kepala SPPG yang login
        $baseQuery = CorrectionRequest::where('id_sppg', $idSppg);

        // Dynamic counts untuk 4 stat card di atas (hanya staff di SPPG ini)
        $totalCount = (clone $baseQuery)->count();
        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $baseQuery)->whereIn('status', ['approved', 'applied'])->count();
        $rejectedCount = (clone $baseQuery)->where('status', 'rejected')->count();

        // Query data tabel dengan relasi dan filter
        $query = (clone $baseQuery)->with(['sppg.user', 'requester', 'reviewer', 'evidences'])
            ->cari($request->query('q'))
            ->jenisData($request->query('jenis_data'))
            ->statusFilter($request->query('status'))
            ->rentang($dari, $sampai);

        // Filter spesifik staff pemohon (jika dipilih di dropdown filter)
        if ($request->filled('staff')) {
            $query->where('requested_by', $request->query('staff'));
        }

        $query->orderBy('requested_at', 'desc')->orderBy('id', 'desc');

        // Paginate 7 baris per halaman
        $rows = $query->paginate(7)->withQueryString();

        CorrectionRequest::lengkapiKodeTarget($rows->getCollection());

        // Ambil daftar staff yang bertugas di SPPG ini untuk opsi dropdown filter
        $staffList = Staff::where('id_sppg', $idSppg)->orderBy('nama', 'asc')->get();
        $daftarJenisData = CorrectionRequest::daftarJenisData();

        return view('KepalaSPPG.correction-request', [
            'rows' => $rows,
            'totalCount' => $totalCount,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'staffList' => $staffList,
            'daftarJenisData' => $daftarJenisData,
            'dari' => $dari,
            'sampai' => $sampai,
            'sppg' => $sppg,
        ]);
    }

    /**
     * Setujui pengajuan koreksi (hanya boleh jika berasal dari staff di SPPG kepala ini).
     */
    public function approve(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $sppg = $user->sppg ?? Sppg::where('id_user', $user->id_user)->first();
        $idSppg = $sppg?->id_sppg;

        $koreksi = CorrectionRequest::findOrFail($id);

        // Otorisasi: Hanya kepala dari SPPG terkait yang berhak menyetujui
        if (! $idSppg || (int) $koreksi->id_sppg !== (int) $idSppg) {
            abort(403, 'Akses ditolak: Anda hanya berwenang menyetujui pengajuan koreksi dari staff SPPG Anda sendiri.');
        }

        if ($koreksi->status !== 'pending') {
            return redirect()->back()->with('info', "Pengajuan koreksi {$koreksi->kode_pengajuan} sudah diproses sebelumnya.");
        }

        $koreksi->update([
            'status' => 'approved',
            'reviewed_by' => $user->id_user,
            'reviewed_at' => now(),
            'review_note' => $request->input('note', 'Pengajuan koreksi disetujui.'),
        ]);

        // Catat otomatis ke Audit Trail
        AuditTrail::catatKoreksi('correction_approve', $koreksi);

        return redirect()->back()->with('success', "Pengajuan koreksi {$koreksi->kode_pengajuan} berhasil disetujui.");
    }

    /**
     * Tolak pengajuan koreksi (hanya boleh jika berasal dari staff di SPPG kepala ini).
     */
    public function reject(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $sppg = $user->sppg ?? Sppg::where('id_user', $user->id_user)->first();
        $idSppg = $sppg?->id_sppg;

        $koreksi = CorrectionRequest::findOrFail($id);

        // Otorisasi: Hanya kepala dari SPPG terkait yang berhak menolak
        if (! $idSppg || (int) $koreksi->id_sppg !== (int) $idSppg) {
            abort(403, 'Akses ditolak: Anda hanya berwenang menolak pengajuan koreksi dari staff SPPG Anda sendiri.');
        }

        if ($koreksi->status !== 'pending') {
            return redirect()->back()->with('info', "Pengajuan koreksi {$koreksi->kode_pengajuan} sudah diproses sebelumnya.");
        }

        $note = trim((string) $request->input('note'));

        $koreksi->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id_user,
            'reviewed_at' => now(),
            'review_note' => $note ?: 'Pengajuan koreksi ditolak.',
        ]);

        // Catat otomatis ke Audit Trail
        AuditTrail::catatKoreksi('correction_reject', $koreksi);

        return redirect()->back()->with('success', "Pengajuan koreksi {$koreksi->kode_pengajuan} telah ditolak.");
    }

    private function rentangTanggal(Request $request): array
    {
        $dariRaw = $request->query('dari');
        $sampaiRaw = $request->query('sampai');

        $dari = null;
        $sampai = null;

        if ($dariRaw) {
            try {
                $dari = Carbon::parse($dariRaw)->startOfDay()->format('Y-m-d');
            } catch (\Throwable) {
                $dari = null;
            }
        }

        if ($sampaiRaw) {
            try {
                $sampai = Carbon::parse($sampaiRaw)->endOfDay()->format('Y-m-d');
            } catch (\Throwable) {
                $sampai = null;
            }
        }

        if ($dari && $sampai && $dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [$dari, $sampai];
    }
}
