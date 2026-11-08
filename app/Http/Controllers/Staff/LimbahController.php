<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\CorrectionRequest;
use App\Models\Distribution;
use App\Models\Evidence;
use App\Models\MaterialLot;
use App\Models\ProductionBatch;
use App\Models\Staff;
use App\Models\StockMovement;
use App\Models\WasteSurplusRecord;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LimbahController extends Controller
{
    /**
     * Tampilkan laman daftar limbah & surplus, 4 kartu metrik, dan filter.
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();

        $q = trim((string) $request->input('q', ''));
        $type = trim((string) $request->input('type', ''));
        $status = trim((string) $request->input('status', ''));
        $date = $request->input('date');

        // Query riwayat limbah & surplus
        $query = WasteSurplusRecord::query()
            ->with([
                'batch.menu',
                'batch.recipe',
                'lot.material',
                'distribution.destination',
                'recorder',
                'evidences',
                'correctionRequests',
            ])
            ->where('id_sppg', $idSppg);

        if ($q !== '') {
            $query->where(function ($wQ) use ($q) {
                $wQ->where('reason', 'ilike', "%{$q}%")
                    ->orWhereHas('batch', fn ($bQ) => $bQ->where('batch_code', 'ilike', "%{$q}%")
                        ->orWhereHas('menu', fn ($mQ) => $mQ->where('nama', 'ilike', "%{$q}%")))
                    ->orWhereHas('lot', fn ($lQ) => $lQ->where('lot_number', 'ilike', "%{$q}%")
                        ->orWhere('barcode', 'ilike', "%{$q}%")
                        ->orWhereHas('material', fn ($matQ) => $matQ->where('nama', 'ilike', "%{$q}%")));
            });
        }

        if ($type !== '' && $type !== 'semua') {
            $query->where('record_type', $type);
        }

        if ($status !== '' && $status !== 'semua') {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('recorded_at', $date);
        }

        $records = $query->orderBy('recorded_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // 4 Kartu Ringkasan Metrik (Status Active)
        $activeRecords = WasteSurplusRecord::query()
            ->where('id_sppg', $idSppg)
            ->where('status', 'active');

        $stats = [
            'susut_olah' => (float) (clone $activeRecords)->where('record_type', 'PRODUCTION_LOSS')->sum('qty'),
            'makanan_sisa' => (float) (clone $activeRecords)->where('record_type', 'FOOD_WASTE')->sum('qty'),
            'surplus_porsi' => (float) (clone $activeRecords)->where('record_type', 'SURPLUS')->sum('qty'),
            'bahan_dibuang_count' => (int) (clone $activeRecords)->where('record_type', 'MATERIAL_DISPOSAL')->count(),
            'bahan_dibuang_qty' => (float) (clone $activeRecords)->where('record_type', 'MATERIAL_DISPOSAL')->sum('qty'),
        ];

        // Batch yang memenuhi syarat (status ready, distributed, atau completed)
        $eligibleBatches = ProductionBatch::query()
            ->with(['menu', 'recipe'])
            ->where('id_sppg', $idSppg)
            ->whereIn('status', ['ready', 'distributed', 'completed'])
            ->orderBy('production_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Lot bahan yang masih memiliki sisa stok (current_qty > 0)
        $eligibleLots = MaterialLot::query()
            ->with('material')
            ->where('id_sppg', $idSppg)
            ->where('status', 'available')
            ->where('current_qty', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        return view('staff.limbah.index', compact(
            'records',
            'stats',
            'eligibleBatches',
            'eligibleLots',
            'q',
            'type',
            'status',
            'date'
        ));
    }

    /**
     * Endpoint API info batch untuk live modal form (target, actual, sisa belum kirim, pengiriman).
     */
    public function getBatchInfo(int $id): JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $batch = ProductionBatch::with('menu')
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        $distributions = Distribution::with('destination')
            ->where('production_batch_id', $batch->id)
            ->get();

        $terkirim = (int) $distributions->whereIn('status', ['sent', 'received'])->sum('portions_sent');
        $actual = $batch->actual_porsi ?? 0;
        $belumKirim = max(0, $actual - $terkirim);
        $maxLoss = max(0, $batch->target_porsi - $actual);

        $distributionsData = $distributions->map(function ($dist) {
            return [
                'id' => $dist->id,
                'destination_nama' => $dist->destination?->nama_tujuan ?? 'Tujuan Pengiriman',
                'portions_sent' => $dist->portions_sent,
                'status' => $dist->status,
                'distributed_at' => $dist->distributed_at ? $dist->distributed_at->format('d/m/Y H:i') : '-',
            ];
        });

        return response()->json([
            'success' => true,
            'batch' => [
                'id' => $batch->id,
                'batch_code' => $batch->batch_code,
                'menu_nama' => $batch->menu?->nama ?? '-',
                'target_porsi' => $batch->target_porsi,
                'actual_porsi' => $actual,
                'terkirim_porsi' => $terkirim,
                'belum_kirim_porsi' => $belumKirim,
                'max_loss' => $maxLoss,
            ],
            'distributions' => $distributionsData,
        ]);
    }

    /**
     * Endpoint API info lot bahan untuk live modal form (sisa stok, satuan, tgl kedaluwarsa).
     */
    public function getLotInfo(int $id): JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $lot = MaterialLot::with('material')
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'lot' => [
                'id' => $lot->id,
                'lot_number' => $lot->lot_number,
                'barcode' => $lot->barcode,
                'material_nama' => $lot->material?->nama ?? '-',
                'satuan' => $lot->material?->satuan ?? 'unit',
                'current_qty' => (float) $lot->current_qty,
                'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('d/m/Y') : '-',
            ],
        ]);
    }

    /**
     * Simpan data limbah & surplus (dalam satu transaksi database aman).
     */
    public function store(Request $request): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $validated = $request->validate([
            'record_type' => ['required', 'string', 'in:PRODUCTION_LOSS,FOOD_WASTE,SURPLUS,MATERIAL_DISPOSAL'],
            'production_batch_id' => ['nullable', 'integer', 'exists:production_batches,id'],
            'material_lot_id' => ['nullable', 'integer', 'exists:material_lots,id'],
            'distribution_id' => ['nullable', 'integer', 'exists:distributions,id'],
            'qty' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
            'recorded_at' => ['nullable', 'date'],
            'evidence_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $recordType = $validated['record_type'];
        $qty = (float) $validated['qty'];
        $recordedAt = ! empty($validated['recorded_at']) ? Carbon::parse($validated['recorded_at']) : now();

        // Validasi Aturan Form Khusus per Tipe
        if ($recordType === 'MATERIAL_DISPOSAL') {
            if (empty($validated['material_lot_id'])) {
                return back()->withInput()->with('error', 'Lot bahan baku wajib dipilih untuk pembuangan bahan (MATERIAL_DISPOSAL).');
            }

            $lot = MaterialLot::where('id_sppg', $idSppg)->findOrFail($validated['material_lot_id']);
            if ($qty > (float) $lot->current_qty) {
                return back()->withInput()->with('error', "Jumlah dibuang ({$qty}) tidak boleh melebihi sisa stok lot ({$lot->current_qty} {$lot->material?->satuan}).");
            }
        } else {
            // Tipe Porsi: PRODUCTION_LOSS, FOOD_WASTE, SURPLUS
            if (empty($validated['production_batch_id'])) {
                return back()->withInput()->with('error', 'Batch produksi wajib dipilih untuk pencatatan porsi limbah/surplus.');
            }

            $batch = ProductionBatch::where('id_sppg', $idSppg)->findOrFail($validated['production_batch_id']);

            if ($recordType === 'SURPLUS') {
                $terkirim = (int) Distribution::where('production_batch_id', $batch->id)
                    ->whereIn('status', ['sent', 'received'])
                    ->sum('portions_sent');
                $maxSurplus = max(0, ($batch->actual_porsi ?? 0) - $terkirim);
                if ($qty > $maxSurplus) {
                    return back()->withInput()->with('error', "Jumlah surplus ({$qty} porsi) melebihi porsi yang belum terkirim ({$maxSurplus} porsi).");
                }
            }
        }

        DB::transaction(function () use ($validated, $recordType, $qty, $recordedAt, $idSppg, $staff, $request) {
            // 1. Catat ke waste_surplus_records
            $record = WasteSurplusRecord::create([
                'id_sppg' => $idSppg,
                'recorded_by' => $staff?->id,
                'record_type' => $recordType,
                'production_batch_id' => $recordType !== 'MATERIAL_DISPOSAL' ? $validated['production_batch_id'] : null,
                'material_lot_id' => $recordType === 'MATERIAL_DISPOSAL' ? $validated['material_lot_id'] : null,
                'distribution_id' => ($recordType === 'FOOD_WASTE' && ! empty($validated['distribution_id'])) ? $validated['distribution_id'] : null,
                'qty' => $qty,
                'reason' => $validated['reason'],
                'recorded_at' => $recordedAt,
                'status' => 'active',
            ]);

            // 2. Jika MATERIAL_DISPOSAL: potong stok lot & catat stock_movements
            if ($recordType === 'MATERIAL_DISPOSAL') {
                $lot = MaterialLot::where('id_sppg', $idSppg)->lockForUpdate()->findOrFail($validated['material_lot_id']);
                $newQty = max(0, (float) $lot->current_qty - $qty);
                $lot->current_qty = $newQty;
                if ($newQty == 0) {
                    $lot->status = 'depleted';
                }
                $lot->save();

                StockMovement::create([
                    'material_lot_id' => $lot->id,
                    'created_by' => $staff?->id,
                    'movement_type' => 'DISPOSAL_OUT',
                    'direction' => 'OUT',
                    'qty' => $qty,
                    'reference_type' => 'waste_surplus_records',
                    'reference_id' => $record->id,
                    'created_at' => now(),
                ]);
            }

            // 3. Simpan foto bukti jika diunggah
            if ($request->hasFile('evidence_file')) {
                $file = $request->file('evidence_file');
                $path = $file->store('evidences/limbah', 'public');

                Evidence::create([
                    'uploaded_by' => $staff?->id,
                    'evidenceable_type' => 'waste_surplus_records',
                    'evidenceable_id' => $record->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'description' => "Foto bukti limbah/surplus {$record->type_label}",
                    'is_public' => false,
                    'uploaded_at' => now(),
                ]);
            }

            // 4. Audit Trail
            AuditTrail::create([
                'id_sppg' => $idSppg,
                'actor_id' => $staff?->id,
                'action' => 'CREATE_WASTE_SURPLUS',
                'auditable_type' => 'waste_surplus_records',
                'auditable_id' => $record->id,
                'old_values' => null,
                'new_values' => [
                    'record_type' => $recordType,
                    'qty' => $qty,
                    'batch_id' => $record->production_batch_id,
                    'lot_id' => $record->material_lot_id,
                    'reason' => $record->reason,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route('staff.limbah.index')
            ->with('success', 'Catatan limbah/surplus berhasil disimpan!');
    }

    /**
     * Ajukan koreksi untuk membatalkan (void) catatan limbah yang salah catat.
     */
    public function ajukanKoreksi(Request $request): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $validated = $request->validate([
            'waste_record_id' => ['required', 'integer', 'exists:waste_surplus_records,id'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $record = WasteSurplusRecord::where('id_sppg', $idSppg)->findOrFail($validated['waste_record_id']);

        if ($record->status === 'voided') {
            return back()->with('error', 'Catatan ini sudah dalam status voided (dibatalkan).');
        }

        // Cek apakah sudah ada koreksi pending
        $hasPending = CorrectionRequest::where('id_sppg', $idSppg)
            ->where('target_table', 'waste_surplus_records')
            ->where('target_id', $record->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return back()->with('error', 'Permohonan koreksi untuk catatan ini sudah ada dan sedang menunggu peninjauan Kepala SPPG.');
        }

        $requestNo = 'CR-WST-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));

        CorrectionRequest::create([
            'id_sppg' => $idSppg,
            'requested_by' => $staff?->id,
            'request_no' => $requestNo,
            'target_table' => 'waste_surplus_records',
            'target_id' => $record->id,
            'field_name' => 'status',
            'old_value' => 'active',
            'new_value' => 'voided',
            'reason' => $validated['reason'],
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return redirect()->route('staff.limbah.index')
            ->with('success', "Permohonan koreksi pembatalan ({$requestNo}) berhasil diajukan ke Kepala SPPG.");
    }

    /**
     * Dapatkan ID SPPG dari session staff yang sedang terotentikasi.
     */
    private function resolveSppgId(): int
    {
        $staff = $this->getAuthenticatedStaff();

        return (int) ($staff?->id_sppg ?? 1);
    }

    /**
     * Dapatkan instance model Staff saat ini.
     */
    private function getAuthenticatedStaff(): ?Staff
    {
        return Auth::guard('staff')->user();
    }
}

