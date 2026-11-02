<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Purchase;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth('staff')->user() ?: auth()->user();
        $idSppg = $user->id_sppg ?? null;

        $stats = [
            'total_menu' => Menu::when($idSppg, fn ($q) => $q->where('id_sppg', $idSppg))->count(),
            'total_resep' => Recipe::when($idSppg, fn ($q) => $q->whereHas('menu', fn ($mq) => $mq->where('id_sppg', $idSppg)))->count(),
            'menu_aktif' => Menu::when($idSppg, fn ($q) => $q->where('id_sppg', $idSppg))->where('is_active', true)->count(),
            'resep_aktif' => Recipe::when($idSppg, fn ($q) => $q->whereHas('menu', fn ($mq) => $mq->where('id_sppg', $idSppg)))->where('status', 'active')->count(),
            // Integrasi tugas PO Staff
            'po_draft_count' => Purchase::when($idSppg, fn ($q) => $q->where('id_sppg', $idSppg))->where('status', 'draft')->count(),
            'po_waiting_count' => Purchase::when($idSppg, fn ($q) => $q->where('id_sppg', $idSppg))->whereIn('status', ['ordered', 'received_partial'])->count(),
        ];

        // PO draft untuk kartu Tugas Hari Ini
        $draftPurchases = Purchase::with(['supplier', 'items'])
            ->when($idSppg, fn ($q) => $q->where('id_sppg', $idSppg))
            ->where('status', 'draft')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        // PO ordered / received_partial untuk kartu Pembelian Menunggu Barang
        $waitingPurchases = Purchase::with(['supplier', 'items'])
            ->when($idSppg, fn ($q) => $q->where('id_sppg', $idSppg))
            ->whereIn('status', ['ordered', 'received_partial'])
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return view('staff.dashboard', compact('user', 'stats', 'draftPurchases', 'waitingPurchases'));
    }
}
