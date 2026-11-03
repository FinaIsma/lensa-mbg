<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\Staff;
use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LotController extends Controller
{
    /**
     * Tampilkan daftar lot bahan baku (FEFO urutan default).
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();

        $q = trim((string) $request->input('q', ''));
        $materialId = $request->input('material_id');
        $status = trim((string) $request->input('status', ''));
        $expiryFilter = trim((string) $request->input('expiry', ''));

        $today = Carbon::today();
        $sevenDaysAhead = Carbon::today()->addDays(7);

        $query = MaterialLot::query()
            ->with(['material', 'supplier', 'receivingItem.receiving'])
            ->where('id_sppg', $idSppg);

        // Pencarian (Barcode, Nomor Lot Kemasan, Nama Bahan, Nama Pemasok)
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('barcode', 'ilike', "%{$q}%")
                    ->orWhere('lot_number', 'ilike', "%{$q}%")
                    ->orWhereHas('material', fn ($mq) => $mq->where('nama', 'ilike', "%{$q}%"))
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('nama', 'ilike', "%{$q}%"));
            });
        }

        // Filter Bahan
        if (! empty($materialId)) {
            $query->where('material_id', $materialId);
        }

        // Filter Status (available, depleted, voided)
        if ($status !== '' && in_array($status, ['available', 'depleted', 'voided'], true)) {
            $query->where('status', $status);
        }

        // Filter Kedaluwarsa
        if ($expiryFilter === 'expired') {
            $query->whereDate('expiry_date', '<', $today);
        } elseif ($expiryFilter === 'near_expired') {
            $query->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', $sevenDaysAhead);
        } elseif ($expiryFilter === 'safe') {
            $query->whereDate('expiry_date', '>', $sevenDaysAhead);
        }

        // Default urutan FEFO (First Expired, First Out)
        $lots = $query->orderBy('expiry_date', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        // 4 Kartu Statistik sesuai pedoman desain
        $totalLots = MaterialLot::where('id_sppg', $idSppg)->count();

        $stokTersediaCount = MaterialLot::where('id_sppg', $idSppg)
            ->where('status', 'available')
            ->where('current_qty', '>', 0)
            ->whereDate('expiry_date', '>=', $today)
            ->count();

        $mendekatiKedaluwarsaCount = MaterialLot::where('id_sppg', $idSppg)
            ->where('status', 'available')
            ->where('current_qty', '>', 0)
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate('expiry_date', '<=', $sevenDaysAhead)
            ->count();

        $kedaluwarsaCount = MaterialLot::where('id_sppg', $idSppg)
            ->where('current_qty', '>', 0)
            ->whereDate('expiry_date', '<', $today)
            ->count();

        $stats = [
            'total_lots' => $totalLots,
            'stok_tersedia' => $stokTersediaCount,
            'mendekati_kedaluwarsa' => $mendekatiKedaluwarsaCount,
            'kedaluwarsa' => $kedaluwarsaCount,
        ];

        // Daftar bahan untuk dropdown filter
        $materials = Material::where('id_sppg', $idSppg)
            ->orderBy('nama')
            ->get();

        $activeTab = $request->input('tab');
        if (! $activeTab) {
            $activeTab = ($request->has('page') || $request->filled('q') || $request->filled('material_id') || $request->filled('status') || $request->filled('expiry'))
                ? 'table'
                : 'scan';
        }

        return view('staff.lots.index', compact(
            'lots',
            'stats',
            'materials',
            'q',
            'materialId',
            'status',
            'expiryFilter',
            'activeTab'
        ));
    }

    /**
     * Tampilkan detail lot beserta kartu stok (buku mutasi) dan penelusuran.
     */
    public function show(Request $request, int $id): JsonResponse|View
    {
        $idSppg = $this->resolveSppgId();

        $lot = MaterialLot::query()
            ->with([
                'material',
                'supplier',
                'receivingItem.receiving.purchase',
                'receivingItem.receiving.receiver',
                'movements.creator',
            ])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        // Hitung saldo berjalan kartu stok dari riwayat movements (kronologis)
        $movementsChronological = $lot->movements()->orderBy('created_at', 'asc')->orderBy('id', 'asc')->get();
        $runningBalance = 0;
        $movementsWithBalance = [];

        foreach ($movementsChronological as $m) {
            if ($m->direction === 'IN') {
                $runningBalance += (float) $m->qty;
            } else {
                $runningBalance -= (float) $m->qty;
            }

            $movementsWithBalance[] = [
                'id' => $m->id,
                'movement_type' => $m->movement_type,
                'direction' => $m->direction,
                'qty' => (float) $m->qty,
                'balance_after' => $runningBalance,
                'reference_type' => $m->reference_type,
                'reference_id' => $m->reference_id,
                'creator_name' => $m->creator?->nama ?? 'Staff',
                'created_at' => $m->created_at ? $m->created_at->format('d-m-Y H:i:s') : '-',
            ];
        }

        // Urutkan balik ke terbaru untuk tampilan tabel
        $movementsDisplay = array_reverse($movementsWithBalance);

        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
            return response()->json([
                'success' => true,
                'lot' => [
                    'id' => $lot->id,
                    'barcode' => $lot->barcode,
                    'lot_number' => $lot->lot_number,
                    'material_nama' => $lot->material?->nama ?? '-',
                    'material_satuan' => $lot->material?->satuan ?? 'kg',
                    'material_kategori' => $lot->material?->kategori ?? '-',
                    'supplier_nama' => $lot->supplier?->nama ?? '-',
                    'initial_qty' => (float) $lot->initial_qty,
                    'current_qty' => (float) $lot->current_qty,
                    'unit_cost' => (float) $lot->unit_cost,
                    'formatted_unit_cost' => 'Rp '.number_format($lot->unit_cost, 0, ',', '.'),
                    'received_date' => $lot->received_date ? $lot->received_date->format('d-m-Y') : '-',
                    'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('d-m-Y') : '-',
                    'expiry_status' => $lot->expiry_status,
                    'expiry_status_label' => $lot->expiry_status_label,
                    'expiry_badge_class' => $lot->expiry_badge_class,
                    'status' => $lot->status,
                    'status_label' => $lot->status_label,
                    'status_badge_class' => $lot->status_badge_class,
                    'receiving_no' => $lot->receivingItem?->receiving?->receiving_no ?? '-',
                    'receiving_id' => $lot->receivingItem?->receiving?->id,
                    'purchase_no' => $lot->receivingItem?->receiving?->purchase?->purchase_no ?? '-',
                    'purchase_id' => $lot->receivingItem?->receiving?->purchase?->id,
                ],
                'movements' => $movementsDisplay,
            ]);
        }

        return view('staff.lots.show', compact('lot', 'movementsDisplay'));
    }

    /**
     * Cari lot langsung lewat kode barcode (mendukung USB Barcode Scanner & Kamera).
     */
    public function findByBarcode(Request $request, ?string $barcode = null): JsonResponse
    {
        $idSppg = $this->resolveSppgId();
        $code = trim((string) ($barcode ?? $request->query('code', '')));

        if ($code === '') {
            return response()->json([
                'success' => false,
                'message' => 'Kode barcode tidak boleh kosong.',
            ], 400);
        }

        $lot = MaterialLot::query()
            ->with(['material', 'supplier', 'receivingItem.receiving'])
            ->where('id_sppg', $idSppg)
            ->where(function ($sub) use ($code) {
                $sub->where('barcode', $code)
                    ->orWhere('lot_number', $code);
            })
            ->first();

        if (! $lot) {
            return response()->json([
                'success' => false,
                'code' => $code,
                'message' => "Data lot dengan kode '{$code}' tidak ditemukan pada unit SPPG ini.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'lot_id' => $lot->id,
            'barcode' => $lot->barcode,
            'material_nama' => $lot->material?->nama,
            'current_qty' => (float) $lot->current_qty,
            'satuan' => $lot->material?->satuan,
            'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('d-m-Y') : '-',
            'status' => $lot->status_label,
            'detail_url' => route('staff.lots.show', $lot->id),
        ]);
    }

    /**
     * Cetak label stiker barcode lot satuan.
     */
    public function print(Request $request, int $id): View
    {
        $idSppg = $this->resolveSppgId();

        $lot = MaterialLot::query()
            ->with(['material', 'supplier', 'receivingItem.receiving'])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        $sppg = DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_PK, $idSppg)->first();

        return view('staff.lots.print', compact('lot', 'sppg'));
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

        if (! $idSppg) {
            $idSppg = DB::table(Keys::SPPG_TABLE)->orderBy(Keys::SPPG_PK)->value(Keys::SPPG_PK);
        }

        abort_unless($idSppg, 403, 'Akun ini tidak terhubung ke SPPG mana pun.');

        return (int) $idSppg;
    }
}
