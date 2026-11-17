<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Evidence;
use App\Models\PreDistributionCheck;
use App\Models\ProductionBatch;
use App\Models\Staff;
use App\Models\WasteSurplusRecord;
use App\Services\SupabaseStorageService;
use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HasilController extends Controller
{
    /**
     * Tampilkan daftar hasil produksi, 4 KPI metrik, dan filter.
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();

        $q = trim((string) $request->input('q', ''));
        $dateFilter = $request->input('date');
        $status = trim((string) $request->input('status', ''));

        // Query batch yang telah memiliki pencatatan hasil
        $query = ProductionBatch::query()
            ->with([
                'menu',
                'recipe',
                'creator',
                'preDistributionCheck.checker',
                'preDistributionCheck.nutritionist',
                'preDistributionCheck.evidences.uploader',
            ])
            ->where('id_sppg', $idSppg)
            ->whereHas('preDistributionCheck');

        if ($q !== '') {
            $query->where(function ($bQ) use ($q) {
                $bQ->where('batch_code', 'ilike', "%{$q}%")
                    ->orWhereHas('menu', fn ($mQ) => $mQ->where('nama', 'ilike', "%{$q}%"));
            });
        }

        if ($dateFilter) {
            $query->whereDate('production_date', $dateFilter);
        }

        if ($status === 'susut') {
            $query->whereColumn('actual_porsi', '<', 'target_porsi');
        } elseif ($status === 'sesuai') {
            $query->whereColumn('actual_porsi', '>=', 'target_porsi');
        } elseif ($status === 'hari_ini') {
            $today = Carbon::today();
            $query->where(function ($subQ) use ($today) {
                $subQ->whereDate('production_date', $today)
                    ->orWhereHas('preDistributionCheck', fn ($cQ) => $cQ->whereDate('checked_at', $today));
            });
        }

        $batches = $query->orderBy('production_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Batch IDs yang sudah mencatat susut (PRODUCTION_LOSS)
        $recordedLossBatchIds = WasteSurplusRecord::query()
            ->where('id_sppg', $idSppg)
            ->where('record_type', 'PRODUCTION_LOSS')
            ->whereIn('production_batch_id', $batches->pluck('id'))
            ->pluck('production_batch_id')
            ->flip()
            ->toArray();

        // Batch yang memenuhi syarat untuk dicatat hasil (sesuai aturan tabel):
        // Batch berstatus 'processing' yang belum punya hasil dan semua tahap kerjanya 'done'
        $eligibleBatches = ProductionBatch::query()
            ->where('id_sppg', $idSppg)
            ->where('status', 'processing')
            ->doesntHave('preDistributionCheck')
            ->whereHas('processingSteps')
            ->whereDoesntHave('processingSteps', function ($sQ) {
                $sQ->where('status', '!=', 'done');
            })
            ->with(['menu', 'recipe', 'processingSteps'])
            ->orderBy('production_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // 4 KPI Cards
        $totalPorsiJadi = (int) $batches->sum(fn ($b) => $b->actual_porsi ?? $b->preDistributionCheck?->portions_ready ?? 0);
        $totalTargetPorsi = (int) $batches->sum('target_porsi');
        $persentaseKeberhasilan = $totalTargetPorsi > 0
            ? round(($totalPorsiJadi / $totalTargetPorsi) * 100, 1)
            : 0;

        $totalSusut = (int) $batches->sum(function ($b) {
            $actual = $b->actual_porsi ?? $b->preDistributionCheck?->portions_ready ?? 0;

            return max(0, $b->target_porsi - $actual);
        });

        $stats = [
            'siap_dicatat' => $eligibleBatches->count(),
            'total_porsi_jadi' => $totalPorsiJadi,
            'persentase_keberhasilan' => $persentaseKeberhasilan,
            'total_susut' => $totalSusut,
        ];

        // Daftar staff aktif SPPG untuk pilihan Tenaga Ahli Gizi
        $staffList = Staff::query()
            ->where('id_sppg', $idSppg)
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        $formTypes = PreDistributionCheck::FORM_TYPES;
        $defaultPhysicalMatrix = PreDistributionCheck::DEFAULT_PHYSICAL_MATRIX;

        return view('staff.hasil.index', compact(
            'batches',
            'eligibleBatches',
            'stats',
            'recordedLossBatchIds',
            'q',
            'dateFilter',
            'status',
            'staffList',
            'formTypes',
            'defaultPhysicalMatrix'
        ));
    }

    /**
     * Endpoint info batch untuk live calculation modal.
     */
    public function getBatchInfo(int $id): JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $batch = ProductionBatch::with(['menu', 'recipe'])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'batch' => [
                'id' => $batch->id,
                'batch_code' => $batch->batch_code,
                'production_date' => $batch->production_date ? $batch->production_date->format('d/m/Y') : '-',
                'menu_nama' => $batch->menu?->nama ?? '-',
                'menu_kategori' => $batch->menu?->kategori ?? 'makanan_siap_santap',
                'ukuran_porsi' => ucfirst($batch->recipe?->ukuran_porsi ?? 'Standar'),
                'target_porsi' => $batch->target_porsi,
            ],
        ]);
    }

    /**
     * Simpan hasil produksi (Pre-Distribution Check) sesuai aturan form.
     */
    public function store(Request $request): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        // Sanitasi nutritionist_id: cegah kegagalan validasi jika dikirim non-numeric (seperti 'custom', '', atau 'null')
        if ($request->has('nutritionist_id')) {
            $rawNutritionistId = $request->input('nutritionist_id');
            if ($rawNutritionistId === 'custom' || $rawNutritionistId === '' || $rawNutritionistId === 'null' || ! is_numeric($rawNutritionistId) || (int) $rawNutritionistId <= 0) {
                $request->merge(['nutritionist_id' => null]);
            } else {
                $request->merge(['nutritionist_id' => (int) $rawNutritionistId]);
            }
        }

        $validated = $request->validate([
            'production_batch_id' => ['required', 'integer', 'exists:production_batches,id'],
            'portions_ready' => ['required', 'integer', 'min:0'],
            'reported_condition' => ['required', 'string', 'max:1000'],
            'form_type' => ['required', 'string', 'max:50'],
            'nutritionist_name' => ['nullable', 'string', 'max:150'],
            'nutritionist_id' => ['nullable', 'integer', 'exists:staff,id'],
            'checked_at' => ['required', 'date'],
            'estimated_distribution_at' => ['nullable', 'date'],
            'consume_by' => ['required', 'date', 'after:checked_at'],
            'physical_test_status' => ['nullable', 'string', 'in:passed,warning,failed'],
            'physical_test_matrix' => ['nullable', 'array'],
            'note' => ['nullable', 'string', 'max:1000'],
            'evidence_file' => ['required', 'file', 'image', 'max:5120'], // minimal 1 foto, maks 5MB
            'is_public' => ['nullable', 'boolean'],
        ], [
            'production_batch_id.required' => 'Batch produksi wajib dipilih.',
            'portions_ready.required' => 'Jumlah porsi siap saji wajib diisi.',
            'portions_ready.min' => 'Jumlah porsi siap saji minimal 0.',
            'reported_condition.required' => 'Kondisi makanan wajib diisi.',
            'form_type.required' => 'Kategori jenis form wajib dipilih.',
            'checked_at.required' => 'Jam pengecekan / uji fisik wajib diisi.',
            'consume_by.required' => 'Waktu kritis (batas maksimal dimakan) wajib diisi demi keamanan pangan.',
            'consume_by.after' => 'Batas maksimal dimakan harus lebih lambat dari jam pengecekan / uji fisik.',
            'evidence_file.required' => 'Wajib mengunggah minimal satu foto bukti porsi siap kirim.',
            'evidence_file.image' => 'Bukti harus berupa file gambar (JPG, JPEG, PNG, atau WEBP).',
            'evidence_file.max' => 'Ukuran foto maksimal adalah 5MB.',
        ]);

        $batch = ProductionBatch::where('id_sppg', $idSppg)->findOrFail($validated['production_batch_id']);

        // Validasi status batch: harus processing dan belum punya hasil
        if ($batch->status !== 'processing' || $batch->preDistributionCheck()->exists()) {
            return back()->with('error', 'Batch ini tidak dalam status pengolahan atau sudah memiliki catatan hasil.')
                ->withInput();
        }

        // Validasi semua tahap pengolahan harus sudah done
        $steps = $batch->processingSteps;
        if ($steps->isEmpty() || ! $steps->every(fn ($s) => $s->status === 'done')) {
            return back()->with('error', 'Semua tahapan kerja pengolahan untuk batch ini harus berstatus selesai (done).')
                ->withInput();
        }

        // Resolusi Nama Ahli Gizi
        $nutritionistName = trim((string) ($validated['nutritionist_name'] ?? ''));
        if ($nutritionistName === '' && ! empty($validated['nutritionist_id'])) {
            $nStaff = Staff::find($validated['nutritionist_id']);
            $nutritionistName = $nStaff?->nama ?? '';
        }
        if ($nutritionistName === '') {
            $nutritionistName = $staff?->nama ?? 'Tenaga Pelaksana Gizi';
        }

        $checkedAt = Carbon::parse($validated['checked_at']);
        $consumeBy = Carbon::parse($validated['consume_by']);
        $estimatedDistAt = ! empty($validated['estimated_distribution_at'])
            ? Carbon::parse($validated['estimated_distribution_at'])
            : null;

        $physicalMatrix = $validated['physical_test_matrix'] ?? PreDistributionCheck::DEFAULT_PHYSICAL_MATRIX;
        $physicalStatus = $validated['physical_test_status'] ?? 'passed';

        // Peringatan: Jika portions_ready melebihi target, sistem TIDAK memblokir
        $isExcess = $validated['portions_ready'] > $batch->target_porsi;
        $excessQty = $isExcess ? ($validated['portions_ready'] - $batch->target_porsi) : 0;

        DB::transaction(function () use (
            $validated,
            $batch,
            $staff,
            $request,
            $idSppg,
            $isExcess,
            $excessQty,
            $nutritionistName,
            $checkedAt,
            $consumeBy,
            $estimatedDistAt,
            $physicalMatrix,
            $physicalStatus
        ) {
            $note = $validated['note'] ?? null;
            if ($isExcess) {
                $excessNote = "[Peringatan Capaian: Porsi siap ({$validated['portions_ready']}) melebihi target ({$batch->target_porsi}), surplus +{$excessQty} porsi]";
                $note = $note ? ($note.' '.$excessNote) : $excessNote;
            }

            // 1. Buat catatan pre_distribution_checks lengkap dengan waktu kritis, ahli gizi, dan matriks uji fisik
            $check = PreDistributionCheck::create([
                'production_batch_id' => $batch->id,
                'checked_by' => $staff?->id,
                'form_type' => $validated['form_type'],
                'portions_ready' => $validated['portions_ready'],
                'reported_condition' => $validated['reported_condition'],
                'nutritionist_name' => $nutritionistName,
                'nutritionist_id' => $validated['nutritionist_id'] ?? null,
                'checked_at' => $checkedAt,
                'consume_by' => $consumeBy,
                'estimated_distribution_at' => $estimatedDistAt,
                'physical_test_matrix' => $physicalMatrix,
                'physical_test_status' => $physicalStatus,
                'note' => $note,
            ]);

            // 2. Simpan foto bukti porsi siap saji
            if ($request->hasFile('evidence_file')) {
                $file = $request->file('evidence_file');
                $path = app(SupabaseStorageService::class)->uploadEvidence($file, 'hasil');

                Evidence::create([
                    'uploaded_by' => $staff?->id,
                    'evidenceable_type' => 'pre_distribution_checks',
                    'evidenceable_id' => $check->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'description' => "Foto porsi siap kirim untuk batch {$batch->batch_code}",
                    'is_public' => $request->boolean('is_public', false),
                    'uploaded_at' => now(),
                ]);
            }

            // 3. Perbarui status batch menjadi 'ready' (Siap Distribusi)
            $batch->actual_porsi = min($validated['portions_ready'], $batch->target_porsi);
            $batch->status = 'ready';
            $batch->save();

            // 4. Audit Trail
            AuditTrail::catat(
                'update',
                $batch,
                ['status' => 'processing', 'actual_porsi' => null],
                [
                    'status' => 'ready',
                    'actual_porsi' => $batch->actual_porsi,
                    'portions_ready' => $check->portions_ready,
                    'reported_condition' => $check->reported_condition,
                    'consume_by' => $check->consume_by?->toDateTimeString(),
                    'nutritionist_name' => $check->nutritionist_name,
                    'is_excess' => $isExcess,
                ],
                null,
                $idSppg
            );
        });

        // Hitung susut (tidak boleh negatif, minimal 0)
        $susut = max(0, $batch->target_porsi - $validated['portions_ready']);

        if ($susut > 0) {
            return redirect()->route('staff.hasil.index')
                ->with('success', "Hasil produksi batch {$batch->batch_code} berhasil disimpan ({$validated['portions_ready']} porsi siap). Terdapat susut sebanyak {$susut} porsi.")
                ->with('prompt_susut_batch_id', $batch->id)
                ->with('prompt_susut_batch_code', $batch->batch_code)
                ->with('prompt_susut_qty', $susut);
        }

        if ($isExcess) {
            return redirect()->route('staff.hasil.index')
                ->with('warning', "Peringatan: Hasil produksi batch {$batch->batch_code} tersimpan dengan {$validated['portions_ready']} porsi siap saji (melebihi target awal {$batch->target_porsi} porsi sebanyak +{$excessQty} porsi). Susut tercatat: 0 porsi.");
        }

        return redirect()->route('staff.hasil.index')
            ->with('success', "Hasil produksi batch {$batch->batch_code} berhasil disimpan! Total {$validated['portions_ready']} porsi siap saji sesuai 100% target produksi (tanpa susut).");
    }

    /**
     * Catat susut ke modul Limbah (tipe PRODUCTION_LOSS).
     */
    public function storeSusut(Request $request): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $validated = $request->validate([
            'production_batch_id' => ['required', 'integer', 'exists:production_batches,id'],
            'qty' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', 'string', 'max:1000'],
            'evidence_file' => ['nullable', 'file', 'image', 'max:5120'],
        ], [
            'production_batch_id.required' => 'Batch produksi wajib ditentukan.',
            'qty.required' => 'Jumlah porsi susut wajib diisi.',
            'qty.min' => 'Jumlah susut harus lebih dari 0.',
            'reason.required' => 'Penyebab/alasan susut wajib diisi.',
        ]);

        $batch = ProductionBatch::where('id_sppg', $idSppg)->findOrFail($validated['production_batch_id']);

        DB::transaction(function () use ($validated, $batch, $staff, $request, $idSppg) {
            $record = WasteSurplusRecord::create([
                'production_batch_id' => $batch->id,
                'id_sppg' => $idSppg,
                'recorded_by' => $staff?->id,
                'record_type' => 'PRODUCTION_LOSS',
                'qty' => $validated['qty'],
                'reason' => $validated['reason'],
                'recorded_at' => now(),
                'status' => 'active',
            ]);

            if ($request->hasFile('evidence_file')) {
                $file = $request->file('evidence_file');
                $path = app(SupabaseStorageService::class)->uploadEvidence($file, 'waste');

                Evidence::create([
                    'uploaded_by' => $staff?->id,
                    'evidenceable_type' => 'waste_surplus_records',
                    'evidenceable_id' => $record->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'description' => "Foto bukti susut produksi batch {$batch->batch_code}",
                    'is_public' => false,
                    'uploaded_at' => now(),
                ]);
            }

            AuditTrail::catat(
                'create',
                $record,
                null,
                [
                    'batch_code' => $batch->batch_code,
                    'record_type' => 'PRODUCTION_LOSS',
                    'qty' => $record->qty,
                    'reason' => $record->reason,
                ],
                null,
                $idSppg
            );
        });

        return redirect()->route('staff.hasil.index')
            ->with('success', "Susut produksi sebanyak {$validated['qty']} porsi untuk batch {$batch->batch_code} berhasil dicatat ke data limbah.");
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
