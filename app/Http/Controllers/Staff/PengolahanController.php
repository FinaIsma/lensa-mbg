<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Evidence;
use App\Models\ProcessingStep;
use App\Models\ProductionBatch;
use App\Models\Staff;
use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengolahanController extends Controller
{
    /**
     * Tampilkan daftar batch yang sedang dalam tahap pengolahan (status confirmed & processing).
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();

        $dateFilter = $request->input('date');
        $batchIdFilter = $request->input('batch_id');

        $query = ProductionBatch::query()
            ->with([
                'menu',
                'recipe',
                'creator',
                'processingSteps' => function ($q) {
                    $q->orderBy('started_at', 'asc')->with(['recorder', 'evidences']);
                },
            ])
            ->where('id_sppg', $idSppg)
            ->whereIn('status', ['confirmed', 'processing']);

        if ($dateFilter) {
            $query->whereDate('production_date', $dateFilter);
        }

        if ($batchIdFilter) {
            $query->where('id', $batchIdFilter);
        }

        $batches = $query->orderBy('production_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Statistik Pengolahan
        $stats = [
            'siap_diolah' => ProductionBatch::where('id_sppg', $idSppg)->where('status', 'confirmed')->count(),
            'sedang_diproses' => ProductionBatch::where('id_sppg', $idSppg)->where('status', 'processing')->count(),
            'tahap_selesai_hari_ini' => ProcessingStep::whereHas('batch', fn ($q) => $q->where('id_sppg', $idSppg))
                ->where('status', 'done')
                ->whereDate('ended_at', Carbon::today())
                ->count(),
            'total_aktif' => ProductionBatch::where('id_sppg', $idSppg)->whereIn('status', ['confirmed', 'processing'])->count(),
        ];

        // Daftar batch aktif untuk dropdown filter
        $activeBatchOptions = ProductionBatch::where('id_sppg', $idSppg)
            ->whereIn('status', ['confirmed', 'processing'])
            ->with('menu')
            ->orderBy('batch_code')
            ->get();

        return view('staff.pengolahan.index', compact(
            'batches',
            'stats',
            'activeBatchOptions',
            'dateFilter',
            'batchIdFilter'
        ));
    }

    /**
     * Tambah tahap pengolahan baru pada batch (status confirmed atau processing).
     * Aturan: Tahap pertama pada batch berstatus 'confirmed' secara otomatis mengubah status batch jadi 'processing'.
     */
    public function storeStep(Request $request): RedirectResponse|JsonResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $validated = $request->validate([
            'production_batch_id' => ['required', 'exists:production_batches,id'],
            'step_name' => ['required', 'string', 'max:150'],
            'started_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'evidence_file' => ['nullable', 'file', 'image', 'max:10240'],
        ]);

        try {
            $step = DB::transaction(function () use ($validated, $idSppg, $staff, $request) {
                $batch = ProductionBatch::where('id_sppg', $idSppg)
                    ->whereIn('status', ['confirmed', 'processing'])
                    ->findOrFail($validated['production_batch_id']);

                $step = ProcessingStep::create([
                    'production_batch_id' => $batch->id,
                    'recorded_by' => $staff?->id,
                    'step_name' => trim($validated['step_name']),
                    'started_at' => Carbon::parse($validated['started_at']),
                    'ended_at' => null,
                    'status' => 'in_progress',
                    'note' => $validated['note'] ?? null,
                ]);

                // Simpan bukti foto jika ada
                if ($request->hasFile('evidence_file')) {
                    $file = $request->file('evidence_file');
                    $path = $file->store('evidences/processing', 'public');

                    Evidence::create([
                        'uploaded_by' => $staff?->id,
                        'evidenceable_type' => 'processing_steps',
                        'evidenceable_id' => $step->id,
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'description' => "Foto bukti tahap: {$step->step_name}",
                        'is_public' => true,
                        'uploaded_at' => now(),
                    ]);
                }

                // Logika: Tahap pertama pada batch confirmed mengubah batch menjadi processing
                if ($batch->status === 'confirmed') {
                    $batch->status = 'processing';
                    $batch->save();
                }

                AuditTrail::catat('create', $step, null, [
                    'step_name' => $step->step_name,
                    'batch_code' => $batch->batch_code,
                    'started_at' => $step->started_at->format('Y-m-d H:i'),
                ]);

                return $step;
            });

            $msg = "Tahap \"{$step->step_name}\" berhasil ditambahkan ke alur pengolahan.";
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Selesaikan tahap pengolahan: isi ended_at (>= started_at), status -> done.
     */
    public function completeStep(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $validated = $request->validate([
            'ended_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'evidence_file' => ['nullable', 'file', 'image', 'max:10240'],
        ]);

        try {
            DB::transaction(function () use ($id, $validated, $idSppg, $staff, $request) {
                $step = ProcessingStep::whereHas('batch', fn ($q) => $q->where('id_sppg', $idSppg))
                    ->findOrFail($id);

                if ($step->status === 'done') {
                    throw new \InvalidArgumentException('Tahap ini sudah ditandai selesai sebelumnya.');
                }

                $endedAt = Carbon::parse($validated['ended_at']);
                if ($endedAt->lt($step->started_at)) {
                    throw new \InvalidArgumentException('Waktu selesai tidak boleh lebih awal dari waktu mulai.');
                }

                $step->ended_at = $endedAt;
                $step->status = 'done';

                if (! empty($validated['note'])) {
                    $step->note = $step->note ? ($step->note."\nCatatan Selesai: ".$validated['note']) : $validated['note'];
                }

                $step->save();

                // Simpan foto bukti jika diunggah saat penyelesaian
                if ($request->hasFile('evidence_file')) {
                    $file = $request->file('evidence_file');
                    $path = $file->store('evidences/processing', 'public');

                    Evidence::create([
                        'uploaded_by' => $staff?->id,
                        'evidenceable_type' => 'processing_steps',
                        'evidenceable_id' => $step->id,
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'description' => "Foto bukti penyelesaian tahap: {$step->step_name}",
                        'is_public' => true,
                        'uploaded_at' => now(),
                    ]);
                }

                AuditTrail::catat('update', $step, ['status' => 'in_progress'], [
                    'status' => 'done',
                    'ended_at' => $endedAt->format('Y-m-d H:i'),
                ]);
            });

            $msg = 'Tahap pengolahan berhasil diselesaikan.';
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Helper ambil staf aktif
     */
    protected function getAuthenticatedStaff(): ?Staff
    {
        $user = Auth::guard('staff')->user();
        if ($user instanceof Staff) {
            return $user;
        }

        $authUser = Auth::user();
        if ($authUser) {
            $staff = Staff::where('email', $authUser->email)->first();
            if ($staff) {
                return $staff;
            }
        }

        return Staff::first();
    }

    /**
     * Helper ambil id SPPG aktif
     */
    protected function resolveSppgId(): int
    {
        $staff = $this->getAuthenticatedStaff();
        $idSppg = $staff?->id_sppg;

        if (! $idSppg && Auth::check()) {
            $idSppg = DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_OWNER, Auth::id())->value(Keys::SPPG_PK);
        }

        if (! $idSppg) {
            $idSppg = DB::table(Keys::SPPG_TABLE)->orderBy(Keys::SPPG_PK)->value(Keys::SPPG_PK);
        }

        abort_unless($idSppg, 403, 'Akun ini tidak terhubung ke SPPG mana pun.');

        return (int) $idSppg;
    }
}
