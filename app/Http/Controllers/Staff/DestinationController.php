<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Distribution;
use App\Models\DistributionDestination;
use App\Models\Staff;
use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DestinationController extends Controller
{
    /**
     * Tampilkan halaman utama Data Master Tujuan Distribusi.
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();
        $this->ensureSampleDestinations($idSppg);

        $q = trim((string) $request->input('q', ''));
        $tipe = trim((string) $request->input('tipe', ''));

        // Query Dasar dengan agregasi:
        // - Jumlah pengiriman: count dari distribusi selain cancelled
        // - Total porsi terkirim: sum portions_sent dari distribusi selain cancelled
        $query = DistributionDestination::query()
            ->where('id_sppg', $idSppg)
            ->withCount(['distributions as jumlah_pengiriman' => function ($sq) {
                $sq->where('status', '!=', 'cancelled');
            }])
            ->withSum(['distributions as total_porsi_terkirim' => function ($sq) {
                $sq->where('status', '!=', 'cancelled');
            }], 'portions_sent');

        // Filter Pencarian (Nama Tujuan atau Alamat)
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'ilike', "%{$q}%")
                    ->orWhere('nama', 'like', "%{$q}%")
                    ->orWhere('alamat', 'ilike', "%{$q}%")
                    ->orWhere('alamat', 'like', "%{$q}%");
            });
        }

        // Filter Tipe: Semua / sekolah / posyandu / lainnya
        if ($tipe !== '' && ! in_array(strtolower($tipe), ['', 'semua', 'semua tipe'], true)) {
            $query->where('tipe', strtolower($tipe));
        }

        $destinations = $query->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // 3 Kartu Statistik sesuai Pedoman Lensa MBG
        $totalTujuan = DistributionDestination::where('id_sppg', $idSppg)->count();
        $totalPenerima = (int) DistributionDestination::where('id_sppg', $idSppg)->sum('jumlah_penerima');
        $totalPengiriman = Distribution::whereHas('destination', function ($d) use ($idSppg) {
            $d->where('id_sppg', $idSppg);
        })->where('status', '!=', 'cancelled')->count();

        $stats = [
            'total_tujuan' => $totalTujuan,
            'total_penerima' => $totalPenerima,
            'total_pengiriman' => $totalPengiriman,
        ];

        return view('staff.tujuan.index', compact('destinations', 'stats', 'q', 'tipe'));
    }

    /**
     * Simpan data tujuan distribusi baru.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'tipe' => ['required', 'string', 'in:sekolah,posyandu,lainnya'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'jumlah_penerima' => ['required', 'integer', 'min:0'],
        ], [
            'nama.required' => 'Nama tujuan distribusi wajib diisi.',
            'nama.max' => 'Nama tujuan maksimal 150 karakter.',
            'tipe.required' => 'Tipe tujuan distribusi wajib dipilih.',
            'tipe.in' => 'Tipe tujuan harus salah satu dari: Sekolah, Posyandu, atau Lainnya.',
            'jumlah_penerima.required' => 'Jumlah penerima wajib diisi.',
            'jumlah_penerima.integer' => 'Isi dengan angka bulat, minimal 0.',
            'jumlah_penerima.min' => 'Isi dengan angka bulat, minimal 0.',
        ]);

        $destination = DistributionDestination::create([
            'id_sppg' => $idSppg,
            'nama' => trim($validated['nama']),
            'tipe' => strtolower($validated['tipe']),
            'alamat' => $validated['alamat'] ? trim($validated['alamat']) : null,
            'jumlah_penerima' => (int) $validated['jumlah_penerima'],
        ]);

        AuditTrail::catat('create', $destination, null, [
            'nama' => $destination->nama,
            'tipe' => $destination->tipe,
            'jumlah_penerima' => $destination->jumlah_penerima,
        ], null, $idSppg);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Tujuan distribusi '{$destination->nama}' berhasil ditambahkan.",
                'data' => $destination,
            ]);
        }

        return redirect()->route('staff.destinations.index')
            ->with('success', "Tujuan distribusi '{$destination->nama}' berhasil ditambahkan.");
    }

    /**
     * Ambil data detail tujuan beserta riwayat pengiriman untuk panel detail/drawer (JSON).
     */
    public function show(int $id): JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $destination = DistributionDestination::where('id_sppg', $idSppg)
            ->withCount(['distributions as jumlah_pengiriman' => function ($sq) {
                $sq->where('status', '!=', 'cancelled');
            }])
            ->withSum(['distributions as total_porsi_terkirim' => function ($sq) {
                $sq->where('status', '!=', 'cancelled');
            }], 'portions_sent')
            ->findOrFail($id);

        $distributions = $destination->distributions()
            ->with(['batch.menu'])
            ->orderByDesc('distributed_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function ($dist) {
                return [
                    'id' => $dist->id,
                    'batch_code' => $dist->batch?->batch_code ?? '-',
                    'menu_nama' => $dist->batch?->menu?->nama ?? 'Menu MBG',
                    'portions_sent' => (int) $dist->portions_sent,
                    'status' => $dist->status,
                    'status_label' => match ($dist->status) {
                        'planned' => 'Rencana',
                        'sent' => 'Terkirim',
                        'received' => 'Diterima',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst((string) $dist->status),
                    },
                    'distributed_at' => $dist->distributed_at
                        ? Carbon::parse($dist->distributed_at)->translatedFormat('d M Y, H:i')
                        : ($dist->created_at ? Carbon::parse($dist->created_at)->translatedFormat('d M Y, H:i') : '-'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $destination->id,
                'nama' => $destination->nama,
                'tipe' => $destination->tipe,
                'tipe_label' => $destination->tipe_label,
                'alamat' => $destination->alamat ?? '-',
                'jumlah_penerima' => (int) $destination->jumlah_penerima,
                'jumlah_pengiriman' => (int) ($destination->jumlah_pengiriman ?? 0),
                'total_porsi_terkirim' => (int) ($destination->total_porsi_terkirim ?? 0),
                'created_at' => $destination->created_at ? Carbon::parse($destination->created_at)->translatedFormat('d M Y') : '-',
                'distributions' => $distributions,
            ],
        ]);
    }

    /**
     * Perbarui data tujuan distribusi.
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $idSppg = $this->resolveSppgId();
        $destination = DistributionDestination::where('id_sppg', $idSppg)->findOrFail($id);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'tipe' => ['required', 'string', 'in:sekolah,posyandu,lainnya'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'jumlah_penerima' => ['required', 'integer', 'min:0'],
        ], [
            'nama.required' => 'Nama tujuan distribusi wajib diisi.',
            'nama.max' => 'Nama tujuan maksimal 150 karakter.',
            'tipe.required' => 'Tipe tujuan distribusi wajib dipilih.',
            'tipe.in' => 'Tipe tujuan harus salah satu dari: Sekolah, Posyandu, atau Lainnya.',
            'jumlah_penerima.required' => 'Jumlah penerima wajib diisi.',
            'jumlah_penerima.integer' => 'Isi dengan angka bulat, minimal 0.',
            'jumlah_penerima.min' => 'Isi dengan angka bulat, minimal 0.',
        ]);

        $oldData = [
            'nama' => $destination->nama,
            'tipe' => $destination->tipe,
            'alamat' => $destination->alamat,
            'jumlah_penerima' => $destination->jumlah_penerima,
        ];

        $destination->update([
            'nama' => trim($validated['nama']),
            'tipe' => strtolower($validated['tipe']),
            'alamat' => $validated['alamat'] ? trim($validated['alamat']) : null,
            'jumlah_penerima' => (int) $validated['jumlah_penerima'],
        ]);

        AuditTrail::catat('update', $destination, $oldData, [
            'nama' => $destination->nama,
            'tipe' => $destination->tipe,
            'jumlah_penerima' => $destination->jumlah_penerima,
        ], null, $idSppg);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data tujuan '{$destination->nama}' berhasil diperbarui.",
                'data' => $destination,
            ]);
        }

        return redirect()->route('staff.destinations.index')
            ->with('success', "Data tujuan '{$destination->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus tujuan distribusi jika belum pernah digunakan dalam pengiriman.
     */
    public function destroy(int $id): RedirectResponse|JsonResponse
    {
        $idSppg = $this->resolveSppgId();
        $destination = DistributionDestination::where('id_sppg', $idSppg)->findOrFail($id);

        if ($destination->distributions()->exists()) {
            $msg = 'Tujuan distribusi ini sudah memiliki riwayat pengiriman dan tidak dapat dihapus.';
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $nama = $destination->nama;
        $destination->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Tujuan distribusi '{$nama}' berhasil dihapus.",
            ]);
        }

        return redirect()->route('staff.destinations.index')
            ->with('success', "Tujuan distribusi '{$nama}' berhasil dihapus.");
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

        return (int) ($idSppg ?? 1);
    }

    /**
     * Pastikan minimal ada data sampel jika tabel masih kosong.
     */
    protected function ensureSampleDestinations(int $idSppg): void
    {
        if (DistributionDestination::where('id_sppg', $idSppg)->exists()) {
            return;
        }

        $samples = [
            ['nama' => 'SDN 1 Sukamaju', 'tipe' => 'sekolah', 'alamat' => 'Jl. Pendidikan No. 12, Sukamaju', 'jumlah_penerima' => 320],
            ['nama' => 'SMPN 2 Sukamaju', 'tipe' => 'sekolah', 'alamat' => 'Jl. Merdeka No. 45, Sukamaju', 'jumlah_penerima' => 280],
            ['nama' => 'PAUD Melati', 'tipe' => 'sekolah', 'alamat' => 'Gang Melati No. 8, Sukamaju', 'jumlah_penerima' => 80],
            ['nama' => 'Posyandu Mawar', 'tipe' => 'posyandu', 'alamat' => 'Balai RW 03, Sukamaju', 'jumlah_penerima' => 60],
        ];

        foreach ($samples as $sample) {
            DistributionDestination::create(array_merge($sample, ['id_sppg' => $idSppg]));
        }
    }
}
