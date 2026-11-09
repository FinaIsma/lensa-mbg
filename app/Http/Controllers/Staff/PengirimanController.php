<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Distribution;
use App\Models\DistributionDestination;
use App\Models\Evidence;
use App\Models\ProductionBatch;
use App\Models\Staff;
use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PengirimanController extends Controller
{
    /**
     * Tampilkan halaman utama Distribusi > Pengiriman.
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();

        $q = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));
        $tipe = strtolower(trim((string) $request->input('tipe', '')));
        $destinationId = $request->input('destination_id');
        $date = $request->input('date');

        // Query Dasar Pengiriman untuk SPPG ini
        $baseQuery = Distribution::query()
            ->with(['batch.menu', 'destination', 'recorder', 'evidences'])
            ->whereHas('destination', function ($destQuery) use ($idSppg) {
                $destQuery->where('id_sppg', $idSppg);
            });

        // 3 Statistik Metrik Pengiriman
        $stats = [
            'total_pengiriman' => (clone $baseQuery)->count(),
            'dalam_proses' => (clone $baseQuery)->whereIn('status', ['planned', 'sent'])->count(),
            'total_porsi_terima' => (clone $baseQuery)->where('status', 'received')->sum('portions_sent'),
        ];

        // Filter Pencarian (Kode Batch, Nama Menu, atau Nama Tujuan)
        if ($q !== '') {
            $baseQuery->where(function ($sub) use ($q) {
                $sub->whereHas('batch', function ($bQ) use ($q) {
                    $bQ->where('batch_code', 'ilike', "%{$q}%")
                        ->orWhere('batch_code', 'like', "%{$q}%")
                        ->orWhereHas('menu', function ($mQ) use ($q) {
                            $mQ->where('nama', 'ilike', "%{$q}%")
                                ->orWhere('nama', 'like', "%{$q}%");
                        });
                })->orWhereHas('destination', function ($dQ) use ($q) {
                    $dQ->where('nama', 'ilike', "%{$q}%")
                        ->orWhere('nama', 'like', "%{$q}%");
                });
            });
        }

        // Filter Tipe Sasaran (Sekolah, Posyandu, Lainnya)
        if ($tipe !== '' && in_array($tipe, ['sekolah', 'posyandu', 'lainnya'], true)) {
            $baseQuery->whereHas('destination', function ($dQ) use ($tipe) {
                $dQ->where('tipe', $tipe);
            });
        }

        // Filter Status Pengiriman
        if ($status !== '' && in_array($status, ['planned', 'sent', 'received', 'cancelled'], true)) {
            $baseQuery->where('status', $status);
        }

        // Filter Berdasarkan Tujuan
        if (! empty($destinationId)) {
            $baseQuery->where('destination_id', $destinationId);
        }

        // Filter Berdasarkan Tanggal
        if (! empty($date)) {
            $baseQuery->whereDate('distributed_at', $date);
        }

        $distributions = $baseQuery->orderByDesc('id')->paginate(10)->withQueryString();

        // Daftar Batch yang berstatus 'ready' atau 'distributed' dan memiliki sisa porsi siap kirim
        $rawBatches = ProductionBatch::query()
            ->with(['menu', 'distributions'])
            ->where('id_sppg', $idSppg)
            ->whereIn('status', ['ready', 'distributed'])
            ->orderByDesc('production_date')
            ->orderByDesc('id')
            ->get();

        $batches = $rawBatches->filter(function ($b) {
            return $b->sisa_porsi_siap_kirim > 0;
        })->values();

        // Daftar Master Tujuan untuk SPPG ini
        $destinations = DistributionDestination::query()
            ->where('id_sppg', $idSppg)
            ->orderBy('nama')
            ->get();

        return view('staff.pengiriman.index', compact(
            'distributions',
            'stats',
            'batches',
            'destinations',
            'q',
            'tipe',
            'status',
            'destinationId',
            'date'
        ));
    }

    /**
     * Dapatkan detail info kuota batch secara real-time via AJAX.
     */
    public function getBatchInfo(int $id): JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $batch = ProductionBatch::query()
            ->with(['menu', 'distributions'])
            ->where('id_sppg', $idSppg)
            ->find($id);

        if (! $batch) {
            return response()->json([
                'success' => false,
                'message' => 'Batch produksi tidak ditemukan.',
            ], 404);
        }

        $allocated = (int) $batch->distributions
            ->where('status', '!=', 'cancelled')
            ->sum('portions_sent');

        $actual = (int) ($batch->actual_porsi ?? 0);
        $sisa = max(0, $actual - $allocated);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $batch->id,
                'batch_code' => $batch->batch_code,
                'menu_nama' => $batch->menu?->nama ?? '-',
                'production_date' => $batch->production_date ? Carbon::parse($batch->production_date)->format('d M Y') : '-',
                'target_porsi' => (int) ($batch->target_porsi ?? 0),
                'actual_porsi' => $actual,
                'allocated_porsi' => $allocated,
                'sisa_porsi' => $sisa,
                'status' => $batch->status,
            ],
        ]);
    }

    /**
     * Dapatkan detail lengkap satu pengiriman untuk Slide-over Drawer via AJAX.
     */
    public function show(int $id): JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $distribution = Distribution::query()
            ->with([
                'batch.menu',
                'batch.distributions',
                'destination',
                'recorder',
                'evidences.uploader',
            ])
            ->whereHas('destination', function ($destQuery) use ($idSppg) {
                $destQuery->where('id_sppg', $idSppg);
            })
            ->find($id);

        if (! $distribution) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengiriman tidak ditemukan.',
            ], 404);
        }

        $batch = $distribution->batch;
        $allocatedBatch = $batch
            ? (int) $batch->distributions->where('status', '!=', 'cancelled')->sum('portions_sent')
            : 0;
        $actualBatch = (int) ($batch?->actual_porsi ?? 0);
        $sisaBatch = max(0, $actualBatch - $allocatedBatch);

        // Timeline Step: 1 = Planned, 2 = Sent, 3 = Received (or 0 for Cancelled)
        $timelineStep = match ($distribution->status) {
            'planned' => 1,
            'sent' => 2,
            'received' => 3,
            'cancelled' => 0,
            default => 1,
        };

        $evidences = $distribution->evidences->map(function ($ev) {
            return [
                'id' => $ev->id,
                'file_url' => Storage::url($ev->file_path),
                'file_type' => $ev->file_type,
                'description' => $ev->description,
                'uploader' => $ev->uploader?->nama ?? 'Staff',
                'uploaded_at' => $ev->uploaded_at ? Carbon::parse($ev->uploaded_at)->translatedFormat('d M Y H:i') : '-',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $distribution->id,
                'portions_sent' => (int) $distribution->portions_sent,
                'status' => $distribution->status,
                'status_label' => $distribution->status_label,
                'status_badge_class' => $distribution->status_badge_class,
                'distributed_at' => $distribution->distributed_at ? Carbon::parse($distribution->distributed_at)->translatedFormat('d M Y, H:i') : '-',
                'distributed_at_raw' => $distribution->distributed_at ? Carbon::parse($distribution->distributed_at)->format('Y-m-d\TH:i') : '',
                'created_at' => $distribution->created_at ? Carbon::parse($distribution->created_at)->translatedFormat('d M Y, H:i') : '-',
                'recorder_name' => $distribution->recorder?->nama ?? 'Staff',
                'timeline_step' => $timelineStep,
                'destination' => [
                    'id' => $distribution->destination?->id,
                    'nama' => $distribution->destination?->nama ?? '-',
                    'tipe' => $distribution->destination?->tipe_label ?? '-',
                    'alamat' => $distribution->destination?->alamat ?? '-',
                    'target_penerima' => (int) ($distribution->destination?->jumlah_penerima ?? 0),
                ],
                'batch' => [
                    'id' => $batch?->id,
                    'batch_code' => $batch?->batch_code ?? '-',
                    'menu_nama' => $batch?->menu?->nama ?? '-',
                    'target_porsi' => (int) ($batch?->target_porsi ?? 0),
                    'actual_porsi' => $actualBatch,
                    'allocated_porsi' => $allocatedBatch,
                    'sisa_porsi' => $sisaBatch,
                    'status' => $batch?->status ?? '-',
                ],
                'evidences' => $evidences,
            ],
        ]);
    }

    /**
     * Simpan alokasi dan pencatatan pengiriman baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $request->validate([
            'production_batch_id' => 'required|exists:production_batches,id',
            'destination_id' => 'required|exists:distribution_destinations,id',
            'portions_sent' => 'required|integer|min:1',
            'distributed_at' => 'required|date',
            'status' => 'required|in:planned,sent',
            'evidence_file' => 'nullable|file|image|max:5120',
            'notes' => 'nullable|string|max:500',
        ], [
            'production_batch_id.required' => 'Pilih batch produksi yang akan dikirim.',
            'destination_id.required' => 'Pilih tujuan distribusi penerima porsi.',
            'portions_sent.required' => 'Masukkan jumlah porsi yang dikirim.',
            'portions_sent.min' => 'Jumlah porsi dikirim minimal 1 porsi.',
            'distributed_at.required' => 'Tentukan waktu pengiriman.',
            'evidence_file.image' => 'Bukti pengiriman harus berupa file gambar (JPG/PNG).',
            'evidence_file.max' => 'Ukuran file gambar maksimal 5MB.',
        ]);

        $batch = ProductionBatch::query()
            ->where('id_sppg', $idSppg)
            ->findOrFail($request->input('production_batch_id'));

        $destination = DistributionDestination::query()
            ->where('id_sppg', $idSppg)
            ->findOrFail($request->input('destination_id'));

        $portionsSent = (int) $request->input('portions_sent');
        $sisaPorsi = $batch->sisa_porsi_siap_kirim;

        if ($portionsSent > $sisaPorsi) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Jumlah porsi yang diminta ({$portionsSent}) melebihi sisa porsi siap kirim dari batch {$batch->batch_code} (tersedia: {$sisaPorsi} porsi).");
        }

        DB::beginTransaction();
        try {
            $distribution = Distribution::create([
                'production_batch_id' => $batch->id,
                'destination_id' => $destination->id,
                'recorded_by' => $staff?->id,
                'portions_sent' => $portionsSent,
                'status' => $request->input('status'),
                'distributed_at' => Carbon::parse($request->input('distributed_at')),
            ]);

            // Jika status langsung 'sent', ubah status batch ke 'distributed' jika batch berstatus 'ready'
            if ($distribution->status === 'sent' && $batch->status === 'ready') {
                $batch->update(['status' => 'distributed']);
            }

            // Simpan foto bukti jika diunggah
            if ($request->hasFile('evidence_file')) {
                $file = $request->file('evidence_file');
                $path = $file->store('evidences/pengiriman', 'public');

                Evidence::create([
                    'uploaded_by' => $staff?->id,
                    'evidenceable_type' => 'distributions',
                    'evidenceable_id' => $distribution->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'description' => $request->input('notes') ?: "Bukti keberangkatan pengiriman #{$distribution->id} ke {$destination->nama}",
                    'is_public' => false,
                    'uploaded_at' => now(),
                ]);
            }

            // Audit Trail
            AuditTrail::catat(
                'create',
                $distribution,
                null,
                [
                    'destination' => $destination->nama,
                    'portions_sent' => $portionsSent,
                    'batch_code' => $batch->batch_code,
                    'status' => $distribution->status,
                ],
                null,
                $idSppg
            );

            DB::commit();

            return redirect()->route('staff.pengiriman.index')
                ->with('success', "Pengiriman porsi ke {$destination->nama} berhasil dicatat.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pengiriman: '.$e->getMessage());
        }
    }

    /**
     * Ubah data pengiriman (hanya diizinkan untuk status 'planned').
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $distribution = Distribution::query()
            ->with(['batch', 'destination'])
            ->whereHas('destination', fn ($q) => $q->where('id_sppg', $idSppg))
            ->findOrFail($id);

        if ($distribution->status !== 'planned') {
            return redirect()->back()
                ->with('error', 'Hanya pengiriman berstatus Direncanakan (Planned) yang dapat diubah.');
        }

        $request->validate([
            'destination_id' => 'required|exists:distribution_destinations,id',
            'portions_sent' => 'required|integer|min:1',
            'distributed_at' => 'required|date',
        ]);

        $batch = $distribution->batch;
        $newPortions = (int) $request->input('portions_sent');

        // Kuota batch yang tersedia di luar porsi sebelumnya
        $allocatedOthers = (int) $batch->distributions()
            ->where('id', '!=', $distribution->id)
            ->where('status', '!=', 'cancelled')
            ->sum('portions_sent');

        $maxAvailable = max(0, (int) $batch->actual_porsi - $allocatedOthers);

        if ($newPortions > $maxAvailable) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Porsi melebihi sisa kapasitas batch (maksimal tersedia: {$maxAvailable} porsi).");
        }

        $destination = DistributionDestination::query()
            ->where('id_sppg', $idSppg)
            ->findOrFail($request->input('destination_id'));

        $oldData = [
            'destination_id' => $distribution->destination_id,
            'portions_sent' => $distribution->portions_sent,
        ];

        $distribution->update([
            'destination_id' => $destination->id,
            'portions_sent' => $newPortions,
            'distributed_at' => Carbon::parse($request->input('distributed_at')),
        ]);

        AuditTrail::catat(
            'update',
            $distribution,
            $oldData,
            [
                'destination' => $destination->nama,
                'portions_sent' => $newPortions,
            ],
            null,
            $idSppg
        );

        return redirect()->route('staff.pengiriman.index')
            ->with('success', 'Data pengiriman berhasil diperbarui.');
    }

    /**
     * Tandai pengiriman sebagai Terkirim/Dalam Perjalanan (planned -> sent).
     */
    public function markAsSent(Request $request, int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $distribution = Distribution::query()
            ->with(['batch', 'destination'])
            ->whereHas('destination', fn ($q) => $q->where('id_sppg', $idSppg))
            ->findOrFail($id);

        if ($distribution->status !== 'planned') {
            return redirect()->back()
                ->with('error', 'Hanya pengiriman berstatus Direncanakan yang dapat diberangkatkan.');
        }

        $request->validate([
            'distributed_at' => 'nullable|date',
            'evidence_file' => 'nullable|file|image|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $departureTime = $request->filled('distributed_at')
                ? Carbon::parse($request->input('distributed_at'))
                : now();

            $distribution->update([
                'status' => 'sent',
                'distributed_at' => $departureTime,
            ]);

            // Transisi status batch: Jika batch berstatus 'ready', beralih ke 'distributed'
            if ($distribution->batch && $distribution->batch->status === 'ready') {
                $distribution->batch->update(['status' => 'distributed']);
            }

            // Simpan foto bukti jika disertakan
            if ($request->hasFile('evidence_file')) {
                $file = $request->file('evidence_file');
                $path = $file->store('evidences/pengiriman', 'public');

                Evidence::create([
                    'uploaded_by' => $staff?->id,
                    'evidenceable_type' => 'distributions',
                    'evidenceable_id' => $distribution->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'description' => $request->input('notes') ?: "Bukti keberangkatan pengiriman #{$distribution->id}",
                    'is_public' => false,
                    'uploaded_at' => now(),
                ]);
            }

            AuditTrail::catat(
                'confirm',
                $distribution,
                ['status' => 'planned'],
                ['status' => 'sent', 'distributed_at' => $departureTime->toDateTimeString()],
                null,
                $idSppg
            );

            DB::commit();

            return redirect()->route('staff.pengiriman.index')
                ->with('success', "Pengiriman #{$distribution->id} berhasil ditandai Dalam Perjalanan.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal memberangkatkan pengiriman: '.$e->getMessage());
        }
    }

    /**
     * Tandai pengiriman sebagai Diterima oleh pihak tujuan (sent -> received).
     */
    public function markAsReceived(Request $request, int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $distribution = Distribution::query()
            ->with(['batch.distributions', 'destination'])
            ->whereHas('destination', fn ($q) => $q->where('id_sppg', $idSppg))
            ->findOrFail($id);

        if ($distribution->status !== 'sent') {
            return redirect()->back()
                ->with('error', 'Hanya pengiriman berstatus Dalam Perjalanan yang dapat ditandai Diterima.');
        }

        $request->validate([
            'evidence_file' => 'nullable|file|image|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $distribution->update([
                'status' => 'received',
            ]);

            // Simpan foto bukti serah terima jika disertakan
            if ($request->hasFile('evidence_file')) {
                $file = $request->file('evidence_file');
                $path = $file->store('evidences/pengiriman', 'public');

                Evidence::create([
                    'uploaded_by' => $staff?->id,
                    'evidenceable_type' => 'distributions',
                    'evidenceable_id' => $distribution->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'description' => $request->input('notes') ?: "Bukti serah terima pengiriman #{$distribution->id} di lokasi {$distribution->destination?->nama}",
                    'is_public' => false,
                    'uploaded_at' => now(),
                ]);
            }

            // Aturan Modul 10: Jika semua pengiriman batch (selain cancelled) telah received, batch menjadi completed
            $batch = $distribution->batch;
            if ($batch) {
                $pendingDistributions = $batch->distributions()
                    ->where('id', '!=', $distribution->id)
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'received')
                    ->count();

                if ($pendingDistributions === 0 && $batch->status === 'distributed') {
                    $batch->update(['status' => 'completed']);
                }
            }

            AuditTrail::catat(
                'confirm',
                $distribution,
                ['status' => 'sent'],
                ['status' => 'received'],
                null,
                $idSppg
            );

            DB::commit();

            return redirect()->route('staff.pengiriman.index')
                ->with('success', "Pengiriman #{$distribution->id} berhasil dikonfirmasi telah diterima.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal memproses konfirmasi penerimaan: '.$e->getMessage());
        }
    }

    /**
     * Batalkan pengiriman (hanya untuk status 'planned').
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $distribution = Distribution::query()
            ->with(['batch', 'destination'])
            ->whereHas('destination', fn ($q) => $q->where('id_sppg', $idSppg))
            ->findOrFail($id);

        if ($distribution->status !== 'planned') {
            return redirect()->back()
                ->with('error', 'Hanya pengiriman berstatus Direncanakan yang dapat dibatalkan.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|min:5|max:500',
        ], [
            'cancellation_reason.required' => 'Alasan pembatalan wajib diisi.',
            'cancellation_reason.min' => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        $reason = trim((string) $request->input('cancellation_reason'));

        DB::beginTransaction();
        try {
            $distribution->update([
                'status' => 'cancelled',
            ]);

            AuditTrail::catat(
                'cancel',
                $distribution,
                ['status' => 'planned'],
                ['status' => 'cancelled', 'reason' => $reason],
                null,
                $idSppg
            );

            DB::commit();

            return redirect()->route('staff.pengiriman.index')
                ->with('success', "Pengiriman #{$distribution->id} berhasil dibatalkan.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal membatalkan pengiriman: '.$e->getMessage());
        }
    }

    /**
     * Dapatkan staff yang sedang login.
     */
    protected function getAuthenticatedStaff(): ?Staff
    {
        $user = Auth::guard('staff')->user();
        if ($user instanceof Staff) {
            return $user;
        }

        if (Auth::check()) {
            return Staff::where('id_user', Auth::id())->first();
        }

        return null;
    }

    /**
     * Selesaikan id_sppg aktif.
     */
    protected function resolveSppgId(): int
    {
        $staff = $this->getAuthenticatedStaff();
        $idSppg = $staff?->id_sppg;

        if (! $idSppg && Auth::check()) {
            $idSppg = DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_OWNER, Auth::id())->value(Keys::SPPG_PK);
        }

        return (int) ($idSppg ?? 1);
    }
}
