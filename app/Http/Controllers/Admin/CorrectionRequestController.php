<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CorrectionRequestController extends Controller
{
    /**
     * Display the full Correction Request management page for Admin Sistem.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $jenisData = $request->input('jenis_data');
        $search = $request->input('search');

        $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        if (Schema::hasTable('correction_requests') && CorrectionRequest::count() > 0) {
            $query = CorrectionRequest::with(['sppg', 'requester', 'reviewer']);

            if ($status && $status !== 'all') {
                if ($status === 'Menunggu') {
                    $query->whereIn('status', ['pending', 'waiting', 'Menunggu']);
                } elseif ($status === 'Disetujui') {
                    $query->whereIn('status', ['approved', 'applied', 'Disetujui']);
                } elseif ($status === 'Ditolak') {
                    $query->whereIn('status', ['rejected', 'Ditolak']);
                } else {
                    $query->where('status', $status);
                }
            }

            if ($jenisData && $jenisData !== 'all') {
                if ($jenisData === 'Distribusi') {
                    $query->whereIn('target_table', ['distributions', 'distribution_destinations']);
                } elseif ($jenisData === 'Produksi') {
                    $query->whereIn('target_table', ['production_batches', 'materials', 'material_usages', 'menus', 'recipes']);
                } elseif ($jenisData === 'Penerimaan') {
                    $query->whereIn('target_table', ['receivings', 'receiving_items', 'purchases', 'purchase_items']);
                }
            }

            if ($search) {
                $query->where(function ($q) use ($search, $like) {
                    $q->where('request_no', $like, "%{$search}%")
                        ->orWhere('field_name', $like, "%{$search}%")
                        ->orWhere('reason', $like, "%{$search}%")
                        ->orWhere('old_value', $like, "%{$search}%")
                        ->orWhere('new_value', $like, "%{$search}%")
                        ->orWhereHas('sppg', function ($sq) use ($search, $like) {
                            $sq->where('nama_sppg', $like, "%{$search}%");
                        })
                        ->orWhereHas('requester', function ($rq) use ($search, $like) {
                            $rq->where('nama', $like, "%{$search}%");
                        });
                });
            }

            $rawList = $query->latest()->get();

            $filteredRequests = $rawList->map(function ($cr) {
                return [
                    'id' => $cr->request_no ?? 'CR-'.str_pad((string) $cr->id, 3, '0', STR_PAD_LEFT),
                    'sppg_nama' => $cr->sppg->nama_sppg ?? 'SPPG Wilayah',
                    'pemohon' => $cr->requester->nama ?? 'Pengguna SPPG',
                    'reviewer' => $cr->reviewer->nama ?? '-',
                    'jenis_data' => $cr->jenis_data,
                    'target_table' => $cr->target_table,
                    'field_name' => $cr->field_name,
                    'old_value' => $cr->old_value,
                    'new_value' => $cr->new_value,
                    'tanggal' => $cr->created_at ? $cr->created_at->format('d M Y, H:i') : '-',
                    'status' => $cr->status_label,
                    'ringkasan' => "Koreksi {$cr->field_name} ({$cr->old_value} → {$cr->new_value})",
                    'alasan' => $cr->reason ?? '-',
                    'review_note' => $cr->review_note ?? '-',
                    'prioritas' => 'Sedang',
                ];
            });

            $counts = [
                'total' => CorrectionRequest::count(),
                'menunggu' => CorrectionRequest::whereIn('status', ['pending', 'waiting', 'Menunggu'])->count(),
                'disetujui' => CorrectionRequest::whereIn('status', ['approved', 'applied', 'Disetujui'])->count(),
                'ditolak' => CorrectionRequest::whereIn('status', ['rejected', 'Ditolak'])->count(),
            ];
        } else {
            // Fallback sample data if empty
            $allRequests = collect([
                [
                    'id' => 'COR-20261012-001',
                    'sppg_nama' => 'SPPG Sawojajar',
                    'pemohon' => 'Yu Ji-min',
                    'reviewer' => 'Nanda (Admin)',
                    'jenis_data' => 'Penerimaan',
                    'target_table' => 'receiving_items',
                    'field_name' => 'qty_received',
                    'old_value' => '120',
                    'new_value' => '118',
                    'tanggal' => '12 Okt 2026, 09:00',
                    'status' => 'Disetujui',
                    'ringkasan' => 'Setelah ditimbang ulang di gudang, berat ayam hanya 118 kg',
                    'alasan' => 'Ada susut timbangan 2 kg saat pengiriman',
                    'review_note' => 'Pengajuan koreksi disetujui.',
                    'prioritas' => 'Tinggi',
                ],
                [
                    'id' => 'COR-20261009-002',
                    'sppg_nama' => 'SPPG Batu',
                    'pemohon' => 'Kim Je-eun',
                    'reviewer' => 'Nanda (Admin)',
                    'jenis_data' => 'Penerimaan',
                    'target_table' => 'receiving_items',
                    'field_name' => 'expiry_date_input',
                    'old_value' => '2026-10-27',
                    'new_value' => '2026-10-25',
                    'tanggal' => '09 Okt 2026, 09:00',
                    'status' => 'Disetujui',
                    'ringkasan' => 'Tanggal kedaluwarsa pada kemasan telur tertulis 2026-10-25',
                    'alasan' => 'Salah ketik saat penerimaan barang',
                    'review_note' => 'Sesuai foto kemasan. Disetujui.',
                    'prioritas' => 'Sedang',
                ],
                [
                    'id' => 'COR-20261010-003',
                    'sppg_nama' => 'SPPG Sawojajar',
                    'pemohon' => 'Yu Ji-min',
                    'reviewer' => 'Nanda (Admin)',
                    'jenis_data' => 'Produksi',
                    'target_table' => 'production_batches',
                    'field_name' => 'target_porsi',
                    'old_value' => '300',
                    'new_value' => '310',
                    'tanggal' => '10 Okt 2026, 09:00',
                    'status' => 'Ditolak',
                    'ringkasan' => 'Ada permintaan tambahan 10 porsi dari sekolah',
                    'alasan' => 'Penambahan kuota penerima',
                    'review_note' => 'Batch sudah selesai dan terkunci. Buat batch baru untuk tambahan porsi.',
                    'prioritas' => 'Rendah',
                ],
            ]);

            $filteredRequests = $allRequests->when($status && $status !== 'all', function ($collection) use ($status) {
                return $collection->where('status', $status);
            })->when($jenisData && $jenisData !== 'all', function ($collection) use ($jenisData) {
                return $collection->where('jenis_data', $jenisData);
            });

            $counts = [
                'total' => $allRequests->count(),
                'menunggu' => $allRequests->where('status', 'Menunggu')->count(),
                'disetujui' => $allRequests->where('status', 'Disetujui')->count(),
                'ditolak' => $allRequests->where('status', 'Ditolak')->count(),
            ];
        }

        return view('admin.correction-requests.index', compact('filteredRequests', 'counts', 'status', 'jenisData', 'search'));
    }
}
