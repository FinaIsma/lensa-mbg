<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\DistributionDestination;
use App\Models\Evidence;
use App\Models\Menu;
use App\Models\ProductionBatch;
use App\Models\Sppg;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicPortalController extends Controller
{
    /**
     * Halaman Utama Radar MBG & Transparansi Penerima
     */
    public function index(Request $request): View|RedirectResponse
    {
        // Jika petugas sudah login dan tidak sedang sengaja preview, arahkan ke dashboard masing-masing
        $user = auth('staff')->user() ?: auth()->user();
        if ($user && ! $request->boolean('preview')) {
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            if ($user->role === 'kepala_sppg') {
                return redirect()->route('sppg.dashboard');
            }
            if ($user->role === 'staff') {
                return redirect()->route('staff.dashboard');
            }
        }

        // Metrik Ringkasan Nasional / Daerah
        $totalPortions = Distribution::whereIn('status', ['sent', 'received'])->sum('portions_sent');
        $totalSppg = Sppg::where('status', 'aktif')->count();
        $totalDestinations = DistributionDestination::count();

        // Resolusi Sekolah / Sasaran yang Dipilih (hanya jika diminta oleh pengguna)
        $selectedDestination = null;
        $activeDistribution = null;

        if ($request->filled('destination_id')) {
            $selectedDestination = DistributionDestination::with('sppg')->find($request->destination_id);
            if ($selectedDestination && $selectedDestination->sppg) {
                if (! $request->filled('provinsi')) {
                    $request->merge(['provinsi' => $selectedDestination->sppg->provinsi]);
                }
                if (! $request->filled('kabupaten_kota')) {
                    $request->merge(['kabupaten_kota' => $selectedDestination->sppg->kabupaten_kota]);
                }
                if (! $request->filled('kecamatan')) {
                    $request->merge(['kecamatan' => $selectedDestination->sppg->kecamatan]);
                }
            }
        } elseif ($request->filled('search')) {
            $keyword = trim((string) $request->search);
            $selectedDestination = DistributionDestination::with('sppg')
                ->where('nama', 'ilike', "%{$keyword}%")
                ->orWhere('alamat', 'ilike', "%{$keyword}%")
                ->first();
            if ($selectedDestination && $selectedDestination->sppg) {
                $request->merge([
                    'provinsi' => $selectedDestination->sppg->provinsi,
                    'kabupaten_kota' => $selectedDestination->sppg->kabupaten_kota,
                    'kecamatan' => $selectedDestination->sppg->kecamatan,
                ]);
            }
        }

        // Ambil daftar sekolah/tujuan untuk dropdown pencarian
        // HANYA dimuat jika user sudah menyaring wilayah atau sudah memilih instansi
        $allDestinations = collect();
        if ($request->filled('kecamatan') || $request->filled('kabupaten_kota') || $selectedDestination) {
            $destinationsQuery = DistributionDestination::with('sppg');

            if ($request->filled('provinsi')) {
                $destinationsQuery->whereHas('sppg', function ($q) use ($request) {
                    $q->where('provinsi', 'ilike', "%{$request->provinsi}%");
                });
            }
            if ($request->filled('kabupaten_kota')) {
                $destinationsQuery->whereHas('sppg', function ($q) use ($request) {
                    $q->where('kabupaten_kota', 'ilike', "%{$request->kabupaten_kota}%");
                });
            }
            if ($request->filled('kecamatan')) {
                $destinationsQuery->whereHas('sppg', function ($q) use ($request) {
                    $q->where('kecamatan', 'ilike', "%{$request->kecamatan}%");
                });
            }

            $allDestinations = $destinationsQuery->orderBy('nama')->get();
        }

        // Filter jadwal: 'today', 'yesterday', '7days'
        $jadwal = (string) $request->query('jadwal', 'today');

        // Jika selectedDestination ditemukan, muat activeDistribution-nya
        $activeBatch = null;
        if ($selectedDestination) {
            $distQuery = Distribution::with([
                'destination.sppg',
                'batch.menu',
                'batch.recipe.nutritions.nutritionType',
                'batch.processingSteps',
                'batch.preDistributionCheck',
                'batch.materialUsages.lot.material',
                'batch.materialUsages.lot.supplier',
            ])
                ->where('destination_id', $selectedDestination->id)
                ->whereIn('status', ['sent', 'received']);

            if ($jadwal === 'yesterday') {
                $distQuery->whereDate('distributed_at', Carbon::yesterday());
            } elseif ($jadwal === '7days') {
                $distQuery->whereDate('distributed_at', Carbon::now()->subDays(7));
            } elseif ($jadwal === 'today') {
                $todayQuery = (clone $distQuery)->whereDate('distributed_at', Carbon::today());
                if ($todayQuery->exists()) {
                    $distQuery = $todayQuery;
                }
            }

            $activeDistribution = $distQuery->latest('distributed_at')->first();
            // Hanya aktifkan batch jika sekolah/tujuan ini memiliki catatan distribusi nyata
            $activeBatch = $activeDistribution?->batch;
        }

        // Ambil bukti riil database & tahapan alur rantai pasok (Petualangan Bekal)
        $publicEvidences = collect();
        $stageEvidences = [
            'tahap1' => null,
            'tahap2' => null,
            'tahap3' => null,
            'tahap4' => null,
        ];

        if ($activeDistribution && $activeBatch) {
            $batchId = $activeBatch->id;
            $distId = $activeDistribution->id;

            // Tahap 1: Evidence dari receivings bahan baku batch ini
            $receivingIds = DB::table('material_usages')
                ->join('material_lots', 'material_usages.material_lot_id', '=', 'material_lots.id')
                ->join('receiving_items', 'material_lots.receiving_item_id', '=', 'receiving_items.id')
                ->where('material_usages.production_batch_id', $batchId)
                ->pluck('receiving_items.receiving_id')
                ->filter()
                ->unique();

            if ($receivingIds->isNotEmpty()) {
                $stageEvidences['tahap1'] = Evidence::where('evidenceable_type', 'receivings')
                    ->whereIn('evidenceable_id', $receivingIds)
                    ->latest('uploaded_at')
                    ->first();
            }

            // Tahap 2: Evidence dari processing_steps batch ini
            $stepIds = $activeBatch->processingSteps->pluck('id');
            if ($stepIds->isNotEmpty()) {
                $stageEvidences['tahap2'] = Evidence::where('evidenceable_type', 'processing_steps')
                    ->whereIn('evidenceable_id', $stepIds)
                    ->latest('uploaded_at')
                    ->first();
            }

            // Tahap 3: Evidence dari pre_distribution_checks batch ini
            $qcId = $activeBatch->preDistributionCheck?->id;
            if ($qcId) {
                $stageEvidences['tahap3'] = Evidence::where('evidenceable_type', 'pre_distribution_checks')
                    ->where('evidenceable_id', $qcId)
                    ->latest('uploaded_at')
                    ->first();
            }

            // Tahap 4: Evidence dari pengiriman / penerimaan distribusi ini
            $stageEvidences['tahap4'] = Evidence::where('evidenceable_type', 'distributions')
                ->where('evidenceable_id', $distId)
                ->latest('uploaded_at')
                ->first();

            // Koleksi semua bukti foto terkait batch & distribusi untuk galeri bukti riil
            $publicEvidences = Evidence::where(function ($q) use ($batchId, $distId, $receivingIds, $stepIds, $qcId) {
                $q->where(function ($sub) use ($batchId) {
                    $sub->where('evidenceable_type', 'production_batches')
                        ->where('evidenceable_id', $batchId);
                })->orWhere(function ($sub) use ($distId) {
                    $sub->where('evidenceable_type', 'distributions')
                        ->where('evidenceable_id', $distId);
                })->when($qcId, function ($sub) use ($qcId) {
                    $sub->orWhere(function ($s) use ($qcId) {
                        $s->where('evidenceable_type', 'pre_distribution_checks')
                            ->where('evidenceable_id', $qcId);
                    });
                })->when($stepIds->isNotEmpty(), function ($sub) use ($stepIds) {
                    $sub->orWhere(function ($s) use ($stepIds) {
                        $s->where('evidenceable_type', 'processing_steps')
                            ->whereIn('evidenceable_id', $stepIds);
                    });
                })->when($receivingIds->isNotEmpty(), function ($sub) use ($receivingIds) {
                    $sub->orWhere(function ($s) use ($receivingIds) {
                        $s->where('evidenceable_type', 'receivings')
                            ->whereIn('evidenceable_id', $receivingIds);
                    });
                });
            })
                ->latest('uploaded_at')
                ->get();
            // Porsi Photo Evidence spesifik (uji mutu porsi hasil saji batch ini)
            $porsiPhotoEvidence = $stageEvidences['tahap3']
                ?? Evidence::where('evidenceable_type', 'pre_distribution_checks')->where('evidenceable_id', $qcId)->first()
                ?? Evidence::where('evidenceable_type', 'production_batches')->where('evidenceable_id', $batchId)->first()
                ?? $stageEvidences['tahap4']
                ?? $publicEvidences->first();
        } else {
            $porsiPhotoEvidence = null;
        }

        // Daftar Dapur SPPG Terverifikasi untuk direktori bawah
        $sppgList = Sppg::where('status', 'aktif')
            ->whereIn('status_verifikasi', ['terverifikasi', 'perubahan'])
            ->withCount(['activeStaffs'])
            ->take(6)
            ->get()
            ->map(function (Sppg $sppg) {
                $sevenDaysAgo = Carbon::now()->subDays(7);
                $porsiMingguan = Distribution::whereHas('destination', function ($q) use ($sppg) {
                    $q->where('id_sppg', $sppg->id_sppg);
                })
                    ->whereIn('status', ['sent', 'received'])
                    ->where('distributed_at', '>=', $sevenDaysAgo)
                    ->sum('portions_sent');

                $sekolahDilayani = DistributionDestination::where('id_sppg', $sppg->id_sppg)->count();

                $sppg->porsi_mingguan = $porsiMingguan;
                $sppg->sekolah_count = $sekolahDilayani;

                return $sppg;
            });

        return view('public.index', compact(
            'totalPortions',
            'totalSppg',
            'totalDestinations',
            'allDestinations',
            'selectedDestination',
            'activeDistribution',
            'activeBatch',
            'porsiPhotoEvidence',
            'stageEvidences',
            'publicEvidences',
            'sppgList',
            'jadwal'
        ));
    }

    /**
     * Halaman Profil & Transparansi Unit SPPG
     */
    public function sppgDetail(string $kode): View
    {
        // Cari SPPG berdasarkan ID angka atau kode string
        $id = is_numeric($kode) ? (int) $kode : (int) preg_replace('/[^0-9]/', '', $kode);

        $sppg = Sppg::where('id_sppg', $id)
            ->where('status', 'aktif')
            ->firstOrFail();

        // 7 Hari statistik
        $sevenDaysAgo = Carbon::now()->subDays(7);
        $porsiProduksi7Hari = (int) ProductionBatch::where('id_sppg', $sppg->id_sppg)
            ->where('production_date', '>=', $sevenDaysAgo)
            ->whereIn('status', ['ready', 'distributed', 'completed'])
            ->sum('actual_porsi');

        $porsiDistribusi7Hari = (int) Distribution::whereHas('destination', function ($q) use ($sppg) {
            $q->where('id_sppg', $sppg->id_sppg);
        })
            ->whereIn('status', ['sent', 'received'])
            ->where('distributed_at', '>=', $sevenDaysAgo)
            ->sum('portions_sent');

        $sekolahBinaan = DistributionDestination::where('id_sppg', $sppg->id_sppg)->get();

        // Menu Aktif & Gizi
        $activeMenus = Menu::where('id_sppg', $sppg->id_sppg)
            ->where('is_active', true)
            ->with(['recipes' => function ($q) {
                $q->where('status', 'active')->with(['nutritions.nutritionType', 'items.material']);
            }])
            ->get();

        // Riwayat Pengiriman Terbaru (hanya yang statusnya sent / received)
        $recentDistributions = Distribution::whereHas('destination', function ($q) use ($sppg) {
            $q->where('id_sppg', $sppg->id_sppg);
        })
            ->whereIn('status', ['sent', 'received'])
            ->with(['destination', 'batch.menu'])
            ->latest('distributed_at')
            ->take(10)
            ->get();

        // Galeri Foto Publik
        $publicPhotos = Evidence::where('is_public', true)
            ->whereIn('evidenceable_type', ['distributions', 'production_batches', 'pre_distribution_checks'])
            ->latest('uploaded_at')
            ->take(8)
            ->get();

        return view('public.sppg-detail', compact(
            'sppg',
            'porsiProduksi7Hari',
            'porsiDistribusi7Hari',
            'sekolahBinaan',
            'activeMenus',
            'recentDistributions',
            'publicPhotos'
        ));
    }

    /**
     * Halaman Telusur Batch Makanan (Core Traceability)
     */
    public function traceBatch(?string $batch_code = null): View
    {
        $batch = null;
        $materials = collect();
        $steps = collect();
        $qcCheck = null;
        $distributions = collect();
        $publicEvidences = collect();

        if ($batch_code) {
            $cleanCode = trim($batch_code);
            $batch = ProductionBatch::with([
                'menu',
                'recipe.nutritions.nutritionType',
                'sppg',
                'materialUsages.lot.material',
                'materialUsages.lot.supplier',
                'processingSteps' => fn ($q) => $q->orderBy('started_at', 'asc')->orderBy('id', 'asc'),
                'preDistributionCheck',
                'distributions' => fn ($q) => $q->whereIn('status', ['sent', 'received'])->with('destination'),
            ])
                ->where('batch_code', $cleanCode)
                ->whereIn('status', ['distributed', 'completed', 'ready'])
                ->first();

            if ($batch) {
                // Ambil daftar bahan & supplier unik
                $materials = $batch->materialUsages->map(function ($mu) {
                    $lot = $mu->lot ?? $mu->materialLot;

                    return [
                        'material_nama' => $lot?->material?->nama ?? 'Bahan Makanan',
                        'kategori' => $lot?->material?->kategori ?? '-',
                        'supplier_nama' => $lot?->supplier?->nama ?? 'Mitra Petani / Pemasok Lokal',
                        'received_date' => $lot?->received_date,
                        'expiry_date' => $lot?->expiry_date,
                    ];
                });

                $steps = $batch->processingSteps;
                $qcCheck = $batch->preDistributionCheck;
                $distributions = $batch->distributions;

                // Ambil foto bukti publik
                $batchId = $batch->id;
                $distIds = $distributions->pluck('id')->toArray();

                $publicEvidences = Evidence::where('is_public', true)
                    ->where(function ($q) use ($batchId, $distIds) {
                        $q->where(function ($sub) use ($batchId) {
                            $sub->where('evidenceable_type', 'production_batches')
                                ->where('evidenceable_id', $batchId);
                        })->orWhere(function ($sub) use ($distIds) {
                            $sub->where('evidenceable_type', 'distributions')
                                ->whereIn('evidenceable_id', $distIds);
                        })->orWhere(function ($sub) use ($batchId) {
                            $sub->where('evidenceable_type', 'pre_distribution_checks')
                                ->whereIn('evidenceable_id', function ($checkQuery) use ($batchId) {
                                    $checkQuery->select('id')
                                        ->from('pre_distribution_checks')
                                        ->where('production_batch_id', $batchId);
                                });
                        });
                    })
                    ->latest('uploaded_at')
                    ->get();
            }
        }

        // Ambil beberapa batch terkini untuk saran pencarian cepat
        $recentTraceableBatches = ProductionBatch::with(['menu', 'sppg'])
            ->whereIn('status', ['distributed', 'completed'])
            ->latest('production_date')
            ->take(5)
            ->get();

        return view('public.trace', compact(
            'batch_code',
            'batch',
            'materials',
            'steps',
            'qcCheck',
            'distributions',
            'publicEvidences',
            'recentTraceableBatches'
        ));
    }

    /**
     * API Autocomplete Sekolah / Instansi Penerima
     */
    public function searchDestinations(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));
        $provinsi = $request->get('provinsi');
        $kabupaten = $request->get('kabupaten_kota');
        $kecamatan = $request->get('kecamatan');

        // Jika tidak ada filter wilayah dan tidak ada query pencarian, jangan tampilkan daftar sekolah
        if (empty($provinsi) && empty($kabupaten) && empty($kecamatan) && $q === '') {
            return response()->json([]);
        }

        $query = DistributionDestination::with('sppg:id_sppg,nama_sppg,provinsi,kabupaten_kota,kecamatan');

        if (! empty($provinsi)) {
            $query->whereHas('sppg', fn ($sub) => $sub->where('provinsi', 'ilike', "%{$provinsi}%"));
        }
        if (! empty($kabupaten)) {
            $query->whereHas('sppg', fn ($sub) => $sub->where('kabupaten_kota', 'ilike', "%{$kabupaten}%"));
        }
        if (! empty($kecamatan)) {
            $query->whereHas('sppg', fn ($sub) => $sub->where('kecamatan', 'ilike', "%{$kecamatan}%"));
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'ilike', "%{$q}%")
                    ->orWhere('alamat', 'ilike', "%{$q}%");
            });
        }

        $results = $query->take(50)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'tipe' => ucfirst($item->tipe),
                'alamat' => $item->alamat,
                'penerima' => $item->jumlah_penerima,
                'sppg_nama' => $item->sppg?->nama_sppg ?? 'Dapur SPPG',
                'sppg_id' => $item->id_sppg,
                'wilayah' => ($item->sppg?->kecamatan ? $item->sppg->kecamatan.', ' : '').($item->sppg?->kabupaten_kota ?? ''),
            ];
        });

        return response()->json($results);
    }
}
