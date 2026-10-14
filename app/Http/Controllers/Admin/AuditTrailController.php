<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    /**
     * Display the full Audit Trail log page for Admin Sistem.
     */
    public function index(Request $request): View
    {
        $modul = $request->input('modul');
        $action = $request->input('action');
        $search = $request->input('search');

        $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        if (Schema::hasTable('audit_trails') && AuditTrail::count() > 0) {
            $query = AuditTrail::with(['user', 'correctionRequest']);

            if ($modul && $modul !== 'all') {
                if ($modul === 'Distribusi') {
                    $query->whereIn('auditable_type', ['distributions', 'distribution_destinations']);
                } elseif ($modul === 'Produksi') {
                    $query->whereIn('auditable_type', ['production_batches', 'menus', 'recipes', 'materials']);
                } elseif ($modul === 'Penerimaan') {
                    $query->whereIn('auditable_type', ['receivings', 'receiving_items', 'purchases']);
                } elseif ($modul === 'Master Data') {
                    $query->whereIn('auditable_type', ['sppg', 'users']);
                }
            }

            if ($action && $action !== 'all') {
                $query->where('action', $action);
            }

            if ($search) {
                $query->where(function ($q) use ($search, $like) {
                    $q->where('action', $like, "%{$search}%")
                        ->orWhere('auditable_type', $like, "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search, $like) {
                            $uq->where('nama', $like, "%{$search}%");
                        });
                });
            }

            $rawList = $query->latest('id')->get();

            $filteredLogs = $rawList->map(function ($log) {
                $userRole = $log->user && $log->user->role === 'admin_sistem' ? 'Admin Sistem' : 'Admin SPPG';
                $oldVals = is_array($log->old_values) ? json_encode($log->old_values) : (string) $log->old_values;
                $newVals = is_array($log->new_values) ? json_encode($log->new_values) : (string) $log->new_values;

                return [
                    'id' => $log->id,
                    'log_code' => 'LOG-'.str_pad((string) $log->id, 4, '0', STR_PAD_LEFT),
                    'correction_request_id' => $log->correction_request_id,
                    'waktu' => $log->created_at ? $log->created_at->format('d M Y, H:i') : '-',
                    'pengguna' => $log->user->nama ?? 'User #'.$log->id_user,
                    'role' => $userRole,
                    'action' => $log->action,
                    'auditable_type' => $log->auditable_type,
                    'auditable_id' => $log->auditable_id,
                    'modul' => $log->modul,
                    'aktivitas' => $log->aktivitas,
                    'old_values' => $oldVals && $oldVals !== 'null' ? $oldVals : null,
                    'new_values' => $newVals && $newVals !== 'null' ? $newVals : null,
                ];
            });

            $counts = [
                'total' => AuditTrail::count(),
                'master_data' => AuditTrail::whereIn('auditable_type', ['sppg', 'users'])->count(),
                'koreksi' => AuditTrail::whereNotNull('correction_request_id')->count(),
                'user' => AuditTrail::whereNotNull('id_user')->count(),
            ];
        } else {
            $allLogs = collect([
                [
                    'id' => 1,
                    'log_code' => 'LOG-0001',
                    'correction_request_id' => null,
                    'waktu' => '24 Sep 2026, 09:40',
                    'pengguna' => 'Rafif Farrelsyah',
                    'role' => 'Admin SPPG',
                    'action' => 'confirm',
                    'auditable_type' => 'receivings',
                    'auditable_id' => 1,
                    'modul' => 'Penerimaan',
                    'aktivitas' => 'Konfirmasi Data',
                    'old_values' => '{"status":"draft"}',
                    'new_values' => '{"status":"confirmed"}',
                ],
            ]);

            $filteredLogs = $allLogs;
            $counts = [
                'total' => 1,
                'master_data' => 0,
                'koreksi' => 0,
                'user' => 1,
            ];
        }

        return view('admin.audit-trail.index', compact('filteredLogs', 'counts', 'modul', 'action', 'search'));
    }
}
