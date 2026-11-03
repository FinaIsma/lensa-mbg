<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\Staff;
use App\Models\StockMovement;
use App\Support\Keys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    /**
     * Tampilkan buku catatan mutasi stok bahan (Riwayat Stock Movement).
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();

        $q = trim((string) $request->input('q', ''));
        $materialId = $request->input('material_id');
        $lotId = $request->input('lot_id');
        $movementType = trim((string) $request->input('movement_type', ''));
        $direction = trim((string) $request->input('direction', ''));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Query dasar dibatasi isolasi id_sppg lewat material_lots
        $baseQuery = StockMovement::query()
            ->whereHas('lot', function ($lotQuery) use ($idSppg) {
                $lotQuery->where('id_sppg', $idSppg);
            });

        // 1. Filter Pencarian Bebas (Barcode, Nomor Lot, Nama Bahan, Kode Bahan, Nama Staff)
        if ($q !== '') {
            $baseQuery->where(function ($sub) use ($q) {
                $sub->whereHas('lot', function ($lq) use ($q) {
                    $lq->where('barcode', 'ilike', "%{$q}%")
                        ->orWhere('lot_number', 'ilike', "%{$q}%")
                        ->orWhereHas('material', fn ($mq) => $mq->where('nama', 'ilike', "%{$q}%")->orWhere('kode', 'ilike', "%{$q}%"));
                })->orWhereHas('creator', function ($cq) use ($q) {
                    $cq->where('nama', 'ilike', "%{$q}%");
                });
            });
        }

        // 2. Filter Bahan
        if (! empty($materialId)) {
            $baseQuery->whereHas('lot', fn ($lq) => $lq->where('material_id', $materialId));
        }

        // 3. Filter Lot Spesifik
        if (! empty($lotId)) {
            $baseQuery->where('material_lot_id', $lotId);
        }

        // 4. Filter Jenis Mutasi
        $validTypes = ['RECEIVE_IN', 'PRODUCTION_OUT', 'DISPOSAL_OUT', 'CORRECTION_IN', 'CORRECTION_OUT', 'VOID_REVERSAL'];
        if ($movementType !== '' && in_array($movementType, $validTypes, true)) {
            $baseQuery->where('movement_type', $movementType);
        }

        // 5. Filter Arah Mutasi (IN / OUT)
        if ($direction !== '' && in_array($direction, ['IN', 'OUT'], true)) {
            $baseQuery->where('direction', $direction);
        }

        // 6. Filter Rentang Tanggal (WIB)
        if (! empty($startDate)) {
            $baseQuery->whereDate('created_at', '>=', $startDate);
        }
        if (! empty($endDate)) {
            $baseQuery->whereDate('created_at', '<=', $endDate);
        }

        // Ringkasan 4 Angka Sesuai Filter Aktif (Total IN, Total OUT, Selisih, Total Transaksi)
        $totalIn = (float) (clone $baseQuery)->where('direction', 'IN')->sum('qty');
        $totalOut = (float) (clone $baseQuery)->where('direction', 'OUT')->sum('qty');
        $netDiff = $totalIn - $totalOut;
        $totalTransaksi = (clone $baseQuery)->count();

        $stats = [
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'net_diff' => $netDiff,
            'total_transaksi' => $totalTransaksi,
        ];

        // Daftar dropdown bahan untuk filter
        $materials = Material::where('id_sppg', $idSppg)
            ->orderBy('nama')
            ->get();

        // Daftar dropdown lot untuk filter
        $lotsQuery = MaterialLot::where('id_sppg', $idSppg)
            ->with('material')
            ->orderByDesc('id');
        if (! empty($materialId)) {
            $lotsQuery->where('material_id', $materialId);
        }
        $availableLots = $lotsQuery->limit(100)->get();

        // Paginated 10 baris, Urut waktu terbaru (DESC)
        $movements = (clone $baseQuery)
            ->with(['lot.material', 'lot.supplier', 'creator'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // Resolusi dokumen referensi tanpa N+1 query
        $referencesMap = $this->resolveReferences($movements->items());
        foreach ($movements->items() as $item) {
            $key = $item->reference_type.':'.$item->reference_id;
            $item->setAttribute('doc_ref', $referencesMap[$key] ?? null);
        }

        return view('staff.riwayat.index', compact(
            'movements',
            'stats',
            'materials',
            'availableLots',
            'q',
            'materialId',
            'lotId',
            'movementType',
            'direction',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Resolusi dokumen referensi secara efisien (tanpa N+1 queries).
     *
     * @param  array<StockMovement>  $movements
     * @return array<string, array{code: string, url: ?string, label: string}>
     */
    protected function resolveReferences(array $movements): array
    {
        $grouped = [];
        foreach ($movements as $m) {
            if ($m->reference_type && $m->reference_id) {
                $grouped[$m->reference_type][] = $m->reference_id;
            }
        }

        $resolved = [];

        // 1. receiving_items -> cari nomor penerimaan
        if (! empty($grouped['receiving_items'])) {
            $ids = array_unique($grouped['receiving_items']);
            $rows = DB::table('receiving_items as ri')
                ->join('receivings as r', 'r.id', '=', 'ri.receiving_id')
                ->whereIn('ri.id', $ids)
                ->select('ri.id as ref_id', 'r.id as receiving_id', 'r.receiving_no')
                ->get();

            foreach ($rows as $row) {
                $resolved["receiving_items:{$row->ref_id}"] = [
                    'code' => $row->receiving_no,
                    'url' => route('staff.receivings.show', $row->receiving_id),
                    'label' => 'Penerimaan',
                ];
            }
        }

        // 2. material_usages -> cari kode batch produksi
        if (! empty($grouped['material_usages'])) {
            $ids = array_unique($grouped['material_usages']);
            $rows = DB::table('material_usages as mu')
                ->join('production_batches as pb', 'pb.id', '=', 'mu.production_batch_id')
                ->whereIn('mu.id', $ids)
                ->select('mu.id as ref_id', 'pb.id as batch_id', 'pb.batch_code')
                ->get();

            foreach ($rows as $row) {
                $resolved["material_usages:{$row->ref_id}"] = [
                    'code' => $row->batch_code,
                    'url' => null, // Belum ada route batch show di modul awal
                    'label' => 'Batch Produksi',
                ];
            }
        }

        // 3. waste_surplus_records -> catatan limbah / pembuangan
        if (! empty($grouped['waste_surplus_records'])) {
            $ids = array_unique($grouped['waste_surplus_records']);
            $rows = DB::table('waste_surplus_records')
                ->whereIn('id', $ids)
                ->select('id as ref_id', 'record_type', 'reason')
                ->get();

            foreach ($rows as $row) {
                $resolved["waste_surplus_records:{$row->ref_id}"] = [
                    'code' => 'WST-'.str_pad((string) $row->ref_id, 4, '0', STR_PAD_LEFT),
                    'url' => null,
                    'label' => 'Limbah '.($row->record_type === 'MATERIAL_DISPOSAL' ? 'Bahan' : 'Porsi'),
                ];
            }
        }

        // 4. correction_requests -> nomor pengajuan koreksi
        if (! empty($grouped['correction_requests'])) {
            $ids = array_unique($grouped['correction_requests']);
            $rows = DB::table('correction_requests')
                ->whereIn('id', $ids)
                ->select('id as ref_id', 'request_no')
                ->get();

            foreach ($rows as $row) {
                $resolved["correction_requests:{$row->ref_id}"] = [
                    'code' => $row->request_no,
                    'url' => null,
                    'label' => 'Koreksi Disetujui',
                ];
            }
        }

        return $resolved;
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
