<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Support\Keys;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditTrailController extends Controller
{
    /** Nama route default */
    public const ROUTE = 'admin.audit';

    public function index(Request $request)
    {
        [$query, $idSppg, $isAdmin, $dari, $sampai, $namaSppg, $listSppg, $user] = $this->buildAuditQuery($request);

        $rows = $query
            ->orderByDesc('audit_trails.created_at')
            ->orderByDesc('audit_trails.id')
            ->paginate(10)
            ->withQueryString();

        $this->lengkapiKode($rows->getCollection());

        // Data untuk panel detail semua baris di halaman ini (2 query saja), supaya panel bisa dibuka tanpa reload.
        $idKoreksi = $rows->getCollection()->pluck('correction_request_id')->filter()->unique()->values()->all();
        $koreksiQuery = DB::table('correction_requests')->whereIn('id', $idKoreksi ?: [0]);
        if ($idSppg) {
            $koreksiQuery->where('id_sppg', $idSppg);
        }
        $koreksi = $idKoreksi ? $koreksiQuery->get()->keyBy('id') : collect();

        $bukti = $idKoreksi
            ? DB::table('evidences')->where('evidenceable_type', 'correction_requests')
                ->whereIn('evidenceable_id', $idKoreksi)->orderBy('id')->get()->groupBy('evidenceable_id')
            : collect();

        $countQuery = $idSppg ? AuditTrail::query()->sppg($idSppg) : AuditTrail::query();

        $sidebar = $isAdmin
            ? 'admin.partials.sidebar'
            : (view()->exists('KepalaSPPG.partials.sidebar') ? 'KepalaSPPG.partials.sidebar' : 'admin.partials.sidebar');

        $navbar = $isAdmin
            ? 'admin.partials.navbar'
            : (view()->exists('KepalaSPPG.partials.navbar') ? 'KepalaSPPG.partials.navbar' : 'admin.partials.navbar');

        $isSppgRoute = $request->routeIs('sppg.*') || ! $isAdmin;
        $routeName = $isSppgRoute ? 'sppg.audit' : 'admin.audit';
        $exportCsvRoute = $isSppgRoute ? 'sppg.audit.export.csv' : 'admin.audit.export.csv';
        $exportPdfRoute = $isSppgRoute ? 'sppg.audit.export.pdf' : 'admin.audit.export.pdf';

        return view('admin.audit-trail', [
            'rows' => $rows,
            'koreksi' => $koreksi,
            'bukti' => $bukti,
            'dari' => $dari,
            'sampai' => $sampai,
            'daftarModul' => AuditTrail::daftarModul(),
            'totalSemua' => (clone $countQuery)->count(),
            'totalHariIni' => (clone $countQuery)->where('created_at', '>=', now()->startOfDay())->count(),
            'totalPerubahan' => (clone $countQuery)->where(fn ($q) => $q->whereNotNull('old_values')->orWhereNotNull('new_values'))->count(),
            'namaUser' => $user->nama ?? ($isAdmin ? 'Admin' : 'Kepala SPPG'),
            'namaSppg' => $namaSppg,
            'isAdmin' => $isAdmin,
            'listSppg' => $listSppg,
            'selectedSppg' => $idSppg,
            'routeName' => $routeName,
            'exportCsvRoute' => $exportCsvRoute,
            'exportPdfRoute' => $exportPdfRoute,
            'sidebar' => $sidebar,
            'navbar' => $navbar,
        ]);
    }

    /**
     * Export data Audit Trail ke format CSV (terbuka otomatis & rapi di Microsoft Excel).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        [$query, $idSppg, $isAdmin, $dari, $sampai, $namaSppg] = $this->buildAuditQuery($request);

        $query->orderByDesc('audit_trails.created_at')->orderByDesc('audit_trails.id');

        $safeSppg = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower(trim((string) $namaSppg)));
        $timestamp = now()->format('Ymd_His');
        $fileName = "audit_trail_{$safeSppg}_{$timestamp}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM so Excel opens file with proper UTF-8 charset
            fwrite($handle, "\xEF\xBB\xBF");

            // Header kolom
            fputcsv($handle, [
                'No',
                'Waktu (WIB)',
                'Kode Audit',
                'Unit SPPG',
                'Pengguna',
                'Peran',
                'Modul',
                'Kode Data',
                'Aktivitas',
                'Data Lama (Sebelum)',
                'Data Baru (Sesudah)',
                'Alasan / Catatan Koreksi',
            ]);

            $formatJson = function (?array $data): string {
                if (empty($data)) {
                    return '-';
                }
                $lines = [];
                foreach ($data as $k => $v) {
                    $val = is_null($v) ? 'null' : (is_bool($v) ? ($v ? 'Ya' : 'Tidak') : (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string) $v));
                    $lines[] = Str::headline($k).': '.$val;
                }

                return implode('; ', $lines);
            };

            $no = 1;
            $query->chunk(500, function ($chunk) use ($handle, &$no, $formatJson) {
                $kode = AuditTrail::kodeUntuk($chunk);

                $corIds = $chunk->pluck('correction_request_id')->filter()->unique()->all();
                $alasanKoreksi = ! empty($corIds)
                    ? DB::table('correction_requests')->whereIn('id', $corIds)->pluck('reason', 'id')
                    : collect();

                foreach ($chunk as $row) {
                    $kodeData = $kode[$row->auditable_type.':'.$row->auditable_id] ?? '#'.$row->auditable_id;
                    $sppgText = $row->nama_sppg
                        ? 'SPPG'.str_pad((string) $row->id_sppg, 3, '0', STR_PAD_LEFT).' - '.$row->nama_sppg
                        : '-';

                    $peran = $row->actor_type === 'staff'
                        ? 'Staff'
                        : match ($row->pelaku_role ?? null) {
                            'kepala_sppg' => 'Kepala SPPG',
                            'admin' => 'Admin',
                            default => 'Pengguna',
                        };

                    $alasan = $row->correction_request_id ? ($alasanKoreksi[$row->correction_request_id] ?? '-') : '-';

                    fputcsv($handle, [
                        $no++,
                        $row->created_at->format('d/m/Y H:i'),
                        $row->kode_audit,
                        $sppgText,
                        $row->actor_nama ?: '(tanpa nama)',
                        $peran,
                        $row->modul,
                        $kodeData,
                        $row->aktivitas_label,
                        $formatJson($row->old_values),
                        $formatJson($row->new_values),
                        $alasan,
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, $headers);
    }

    /**
     * Tampilan Laporan Resmi Audit Trail siap cetak / simpan ke PDF via browser.
     */
    public function exportPdf(Request $request)
    {
        [$query, $idSppg, $isAdmin, $dari, $sampai, $namaSppg, $listSppg, $user] = $this->buildAuditQuery($request);

        $rows = $query->orderByDesc('audit_trails.created_at')
            ->orderByDesc('audit_trails.id')
            ->limit(1000)
            ->get();

        $this->lengkapiKode($rows);

        $idKoreksi = $rows->pluck('correction_request_id')->filter()->unique()->values()->all();
        $koreksi = ! empty($idKoreksi)
            ? DB::table('correction_requests')->whereIn('id', $idKoreksi)->get()->keyBy('id')
            : collect();

        $countQuery = $idSppg ? AuditTrail::query()->sppg($idSppg) : AuditTrail::query();

        return view('admin.audit-trail-print', [
            'rows' => $rows,
            'koreksi' => $koreksi,
            'namaSppg' => $namaSppg,
            'dari' => $dari,
            'sampai' => $sampai,
            'modul' => $request->query('modul'),
            'q' => $request->query('q'),
            'totalSemua' => (clone $countQuery)->count(),
            'totalPerubahan' => (clone $countQuery)->where(fn ($q) => $q->whereNotNull('old_values')->orWhereNotNull('new_values'))->count(),
            'dicetakOleh' => $user->nama ?? ($isAdmin ? 'Admin Sistem' : 'Kepala SPPG'),
            'waktuCetak' => now()->locale('id')->translatedFormat('l, j F Y - H.i').' WIB',
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Helper query builder bersama untuk menjamin keselarasan filter antara tampilan web, CSV, dan PDF.
     */
    private function buildAuditQuery(Request $request): array
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role === 'admin';
        $idSppg = null;

        if (! $isAdmin) {
            // Kepala SPPG: data strictly dibatasi ke SPPG miliknya sendiri
            $idSppg = DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_OWNER, Auth::id())->value(Keys::SPPG_PK);
            abort_unless($idSppg, 403, 'Akun ini tidak terhubung ke SPPG mana pun.');
            $idSppg = (int) $idSppg;
            $listSppg = collect();
        } else {
            // Admin: bisa memilih SPPG tertentu atau melihat seluruh SPPG
            $listSppg = DB::table(Keys::SPPG_TABLE)
                ->orderBy(Keys::SPPG_PK)
                ->get([Keys::SPPG_PK, 'nama_sppg']);

            if ($request->filled('sppg')) {
                $idSppg = (int) $request->query('sppg');
            }
        }

        [$dari, $sampai] = $this->rentangTanggal($request);

        $query = AuditTrail::daftar()
            ->cari($request->query('q'))
            ->rentang($dari, $sampai)
            ->modul($request->query('modul'));

        if ($idSppg) {
            $query->sppg($idSppg);
        }

        $namaSppg = $idSppg
            ? (DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_PK, $idSppg)->value('nama_sppg') ?? 'SPPG #'.$idSppg)
            : 'Seluruh SPPG';

        return [$query, $idSppg, $isAdmin, $dari, $sampai, $namaSppg, $listSppg, $user];
    }

    /** Ambil ?dari=&sampai= (Y-m-d). Tanggal tidak valid diabaikan; kalau terbalik, ditukar. */
    private function rentangTanggal(Request $request): array
    {
        $baca = function (?string $v) {
            try {
                return $v ? Carbon::createFromFormat('Y-m-d', $v)->format('Y-m-d') : null;
            } catch (\Throwable) {
                return null;
            }
        };
        $dari = $baca($request->query('dari'));
        $sampai = $baca($request->query('sampai'));
        if ($dari && $sampai && $dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [$dari, $sampai];
    }

    /** Menempelkan kode data yang mudah dibaca ke tiap baris sebagai $row->kode_data. */
    private function lengkapiKode($koleksi): void
    {
        $kode = AuditTrail::kodeUntuk($koleksi);
        $koleksi->each(function (AuditTrail $r) use ($kode) {
            $r->kode_data = $kode[$r->auditable_type.':'.$r->auditable_id] ?? '#'.$r->auditable_id;
        });
    }
}
