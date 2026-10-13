<?php

namespace Database\Seeders;

use App\Support\Keys;
use DateTimeImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data dummy Lensa MBG: satu skenario utuh dari supplier sampai audit trail, supaya semua laman
 * punya data untuk ditampilkan tanpa menunggu laman input selesai dikodekan.
 *
 * Jalankan : php artisan db:seed --class=LensaDummySeeder
 * Hapus    : php artisan db:seed --class=LensaDummyCleanSeeder
 *
 * - Memakai SPPG berstatus 'aktif' pertama (atau SPPG pertama) beserta user pemiliknya; tidak membuat sppg/users baru.
 * - Semua tanggal RELATIF terhadap hari ini, jadi lot "mendekati expired" dan "expired" selalu ada.
 * - Stok konsisten: current_qty setiap lot = SUM(stock_movements), seperti aturan aplikasi.
 * - Menolak jalan jika tabel suppliers sudah berisi (supaya tidak menimpa data nyata).
 */
class LensaDummySeeder extends Seeder
{
    private DateTimeImmutable $today;
    private $sppg;
    private $user;

    /** @var array<string,int> kode bahan => id material */
    private array $mat = [];
    /** @var array<string,array> kunci lot => data lot (id, cost, qty, ...) */
    private array $lots = [];
    /** @var array<string,array<string,array<string,float>>> menu => ukuran => bahan => qty per porsi */
    private array $rate = [];
    /** @var array<string,array<string,int>> menu => ukuran => id resep */
    private array $recipe = [];
    private array $menu = [];
    private array $dest = [];
    private array $sup = [];

    private int $poSeq = 0;
    private int $lotSeq = 0;
    private int $batchSeq = 0;
    private int $corSeq = 0;

    /** Tabel yang hanya punya created_at (tanpa updated_at). */
    private array $noTs = ['stock_movements', 'audit_trails'];

    public function run(): void
    {
        $this->today = new DateTimeImmutable('today');

        // SPPG: pilih yang berstatus 'aktif' lebih dulu, jika tidak ada pakai SPPG pertama.
        $this->sppg = null;
        if (Schema::hasColumn(Keys::SPPG_TABLE, 'status')) {
            $this->sppg = DB::table(Keys::SPPG_TABLE)->where('status', 'aktif')->orderBy(Keys::SPPG_PK)->value(Keys::SPPG_PK);
        }
        $this->sppg = $this->sppg ?: DB::table(Keys::SPPG_TABLE)->orderBy(Keys::SPPG_PK)->value(Keys::SPPG_PK);

        if (! $this->sppg) {
            $this->command->error('Tabel ' . Keys::SPPG_TABLE . ' masih kosong. Daftarkan minimal 1 SPPG dulu.');
            return;
        }

        // User yang tercatat sebagai pembuat data = user pemilik SPPG itu (kolom Keys::SPPG_OWNER di tabel SPPG).
        $this->user = DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_PK, $this->sppg)->value(Keys::SPPG_OWNER)
            ?: DB::table(Keys::USER_TABLE)->orderBy(Keys::USER_PK)->value(Keys::USER_PK);

        if (! $this->user) {
            $this->command->error('Tabel ' . Keys::USER_TABLE . ' masih kosong.');
            return;
        }

        if (DB::table('suppliers')->exists()) {
            $this->command->warn('Tabel suppliers sudah berisi. Seeder dibatalkan agar data yang ada tidak tercampur. '
                . 'Jalankan LensaDummyCleanSeeder dulu jika ingin mengulang dari nol.');
            return;
        }

        DB::transaction(function () {
            $this->master();
            $this->procurement();
            $this->production();
            $this->corrections();
        });

        $this->command->info('Data dummy Lensa MBG selesai dibuat untuk SPPG id=' . $this->sppg . '.');
    }

    // =========================================================================================
    // 1. DATA MASTER
    // =========================================================================================
    private function master(): void
    {
        // [kunci, nama, status]
        foreach ([
            ['TM', 'CV Tani Makmur', 'active'],
            ['BP', 'UD Berkah Pangan', 'active'],
            ['SS', 'PT Samudra Segar', 'active'],
            ['SJ', 'Toko Sembako Jaya', 'inactive'],
        ] as [$k, $nama, $status]) {
            $this->sup[$k] = $this->ins('suppliers', [
                'id_sppg' => $this->sppg, 'nama' => $nama, 'kontak' => '0812-' . random_int(1000, 9999) . '-' . random_int(1000, 9999),
                'alamat' => 'Jl. Pasar Induk No. ' . random_int(1, 40), 'status' => $status,
            ]);
        }

        // [kode, nama, kategori, satuan, min_stock]  (Wortel sengaja min_stock tinggi => muncul "stok kritis")
        foreach ([
            ['BRS', 'Beras putih', 'Karbohidrat', 'kg', 50],
            ['AYM', 'Daging ayam', 'Protein hewani', 'kg', 30],
            ['TLR', 'Telur ayam', 'Protein hewani', 'kg', 20],
            ['IKN', 'Ikan nila fillet', 'Protein hewani', 'kg', 15],
            ['TMP', 'Tempe', 'Protein nabati', 'kg', 10],
            ['WRT', 'Wortel', 'Sayur', 'kg', 30],
            ['BUC', 'Buncis', 'Sayur', 'kg', 10],
            ['MNY', 'Minyak goreng', 'Bumbu & minyak', 'liter', 15],
            ['BBM', 'Bumbu dapur', 'Bumbu & minyak', 'kg', 5],
        ] as [$kode, $nama, $kat, $satuan, $min]) {
            $this->mat[$kode] = $this->ins('materials', [
                'id_sppg' => $this->sppg, 'kode' => $kode, 'nama' => $nama, 'kategori' => $kat,
                'satuan' => $satuan, 'min_stock' => $min, 'is_active' => true,
            ]);
        }

        foreach ([
            ['M1', 'Nasi Ayam Goreng, Wortel Buncis', 'Kalori ±650 kkal, protein 28 g, lemak 20 g, karbohidrat 82 g'],
            ['M2', 'Nasi Ikan Bakar, Tumis Buncis', 'Kalori ±600 kkal, protein 30 g, lemak 15 g, karbohidrat 80 g'],
            ['M3', 'Nasi Telur Balado, Tempe Goreng', 'Kalori ±640 kkal, protein 26 g, lemak 22 g, karbohidrat 78 g'],
        ] as [$k, $nama, $gizi]) {
            $this->menu[$k] = $this->ins('menus', [
                'id_sppg' => $this->sppg, 'nama' => $nama, 'kategori' => 'Makan siang',
                'deskripsi' => 'Menu makan bergizi gratis: ' . strtolower($nama) . '.', 'gizi' => $gizi, 'is_active' => true,
            ]);
        }

        // Resep per ukuran. qty per porsi (kg / liter).
        $resep = [
            'M1' => [
                'besar' => ['BRS' => 0.12, 'AYM' => 0.10, 'WRT' => 0.03, 'BUC' => 0.03, 'MNY' => 0.015, 'BBM' => 0.01],
                'kecil' => ['BRS' => 0.09, 'AYM' => 0.07, 'WRT' => 0.02, 'BUC' => 0.02, 'MNY' => 0.010, 'BBM' => 0.008],
            ],
            'M2' => [
                'besar' => ['BRS' => 0.12, 'IKN' => 0.09, 'BUC' => 0.04, 'MNY' => 0.015, 'BBM' => 0.01],
                'kecil' => ['BRS' => 0.09, 'IKN' => 0.065, 'BUC' => 0.03, 'MNY' => 0.010, 'BBM' => 0.008],
            ],
            'M3' => [
                'besar' => ['BRS' => 0.12, 'TLR' => 0.08, 'TMP' => 0.05, 'MNY' => 0.015, 'BBM' => 0.01],
                'kecil' => ['BRS' => 0.09, 'TLR' => 0.06, 'TMP' => 0.035, 'MNY' => 0.010, 'BBM' => 0.008],
            ],
        ];
        foreach ($resep as $m => $ukuranList) {
            foreach ($ukuranList as $ukuran => $items) {
                $rid = $this->ins('recipes', [
                    'menu_id' => $this->menu[$m], 'ukuran_porsi' => $ukuran, 'version' => 1, 'status' => 'active',
                ]);
                $this->recipe[$m][$ukuran] = $rid;
                foreach ($items as $matKey => $qty) {
                    $satuan = $matKey === 'MNY' ? 'liter' : 'kg';
                    $this->ins('recipe_items', [
                        'recipe_id' => $rid, 'material_id' => $this->mat[$matKey], 'qty_per_porsi' => $qty, 'satuan' => $satuan,
                    ]);
                    $this->rate[$m][$ukuran][$matKey] = $qty;
                }
            }
        }

        foreach ([
            ['SDN1', 'SDN 1 Sukamaju', 'sekolah', 320],
            ['SMP2', 'SMPN 2 Sukamaju', 'sekolah', 280],
            ['PAUD', 'PAUD Melati', 'sekolah', 80],
            ['POS', 'Posyandu Mawar', 'posyandu', 60],
        ] as [$k, $nama, $tipe, $jumlah]) {
            $this->dest[$k] = $this->ins('distribution_destinations', [
                'id_sppg' => $this->sppg, 'nama' => $nama, 'tipe' => $tipe,
                'alamat' => 'Kecamatan Sukamaju', 'jumlah_penerima' => $jumlah,
            ]);
        }
    }

    // =========================================================================================
    // 2. PENGADAAN -> PENERIMAAN -> LOT -> STOCK MOVEMENT (RECEIVE_IN)
    // =========================================================================================
    private function procurement(): void
    {
        // buy(supplier, hari PO relatif, item[ [bahan, qty, harga, expiry relatif, kunci lot] ], diterima?)

        // Lot sudah EXPIRED (expiry 2 hari lalu), supplier nonaktif.
        $this->buy('SJ', -20, [['TMP', 20, 14000, -2, 'TMP#0']]);

        $this->buy('TM', -9, [
            ['BRS', 200, 13000, 120, 'BRS#1'],
            ['WRT', 40, 9000, 2, 'WRT#1'],     // mendekati expired
            ['BUC', 40, 12000, 6, 'BUC#1'],
            ['MNY', 60, 17000, 200, 'MNY#1'],
            ['BBM', 20, 30000, 60, 'BBM#1'],
        ]);

        // Dua lot ayam dengan expiry berbeda => contoh urutan FEFO (AYM#1 lebih dulu dari AYM#2).
        $this->buy('BP', -6, [
            ['AYM', 120, 38000, 5, 'AYM#1'],
            ['TLR', 60, 28000, 14, 'TLR#1'],   // expiry salah ketik (+14), nanti dikoreksi menjadi +12
        ]);

        $this->buy('SS', -5, [['IKN', 60, 45000, 3, 'IKN#1']]);

        $this->buy('BP', -3, [
            ['AYM', 80, 39000, 9, 'AYM#2'],
            ['TMP', 40, 14000, 4, 'TMP#1'],
        ]);

        // Purchase yang BELUM diterima (status ordered, tanpa receiving).
        $this->buy('TM', -1, [['MNY', 100, 17000, 0, '-']], false);
    }

    private function buy(string $supKey, int $day, array $items, bool $receive = true): void
    {
        $seq = ++$this->poSeq;
        $poId = $this->ins('purchases', [
            'supplier_id' => $this->sup[$supKey], 'id_sppg' => $this->sppg, 'created_by' => $this->user,
            'purchase_no' => sprintf('PO-%s-%03d', $this->ymd($day), $seq),
            'purchase_date' => $this->d($day), 'status' => $receive ? 'received' : 'ordered',
            'created_at' => $this->t($day), 'updated_at' => $this->t($day),
        ]);

        $piIds = [];
        foreach ($items as [$matKey, $qty, $price]) {
            $piIds[] = $this->ins('purchase_items', [
                'purchase_id' => $poId, 'material_id' => $this->mat[$matKey], 'qty_ordered' => $qty, 'unit_price' => $price,
                'created_at' => $this->t($day),
            ]);
        }

        if (! $receive) {
            return;
        }

        $rd = $day + 1; // barang datang sehari setelah PO
        $rcvNo = sprintf('RCV-%s-%03d', $this->ymd($rd), $seq);
        $rcvId = $this->ins('receivings', [
            'purchase_id' => $poId, 'id_sppg' => $this->sppg, 'received_by' => $this->user,
            'receiving_no' => $rcvNo, 'received_at' => $this->t($rd, '09:15:00'),
            'status' => 'confirmed', 'is_locked' => true, 'confirmed_at' => $this->t($rd, '09:40:00'),
            'created_at' => $this->t($rd, '09:15:00'),
        ]);

        $this->ins('evidences', [
            'uploaded_by' => $this->user, 'evidenceable_type' => 'receivings', 'evidenceable_id' => $rcvId,
            'file_path' => 'evidence/receiving/' . $rcvNo . '-barang.jpg', 'file_type' => 'image/jpeg',
            'description' => 'Foto bahan saat diterima', 'is_public' => false, 'uploaded_at' => $this->t($rd, '09:20:00'),
        ]);
        $this->ins('evidences', [
            'uploaded_by' => $this->user, 'evidenceable_type' => 'receivings', 'evidenceable_id' => $rcvId,
            'file_path' => 'evidence/receiving/' . $rcvNo . '-surat-jalan.pdf', 'file_type' => 'application/pdf',
            'description' => 'Surat jalan', 'is_public' => false, 'uploaded_at' => $this->t($rd, '09:22:00'),
        ]);

        foreach ($items as $i => [$matKey, $qty, $price, $expOffset, $lotKey]) {
            $riId = $this->ins('receiving_items', [
                'receiving_id' => $rcvId, 'purchase_item_id' => $piIds[$i], 'material_id' => $this->mat[$matKey],
                'qty_received' => $qty, 'lot_number_input' => 'LT-' . $matKey . '-' . $this->ymd($rd),
                'expiry_date_input' => $this->d($expOffset), 'kondisi_dilaporkan' => 'Kondisi baik, kemasan utuh',
                'created_at' => $this->t($rd, '09:15:00'),
            ]);

            $lotId = $this->ins('material_lots', [
                'receiving_item_id' => $riId, 'material_id' => $this->mat[$matKey], 'supplier_id' => $this->sup[$supKey],
                'id_sppg' => $this->sppg, 'lot_number' => 'LT-' . $matKey . '-' . $this->ymd($rd),
                'barcode' => sprintf('LOT-%s-%03d', $this->ymd($rd), ++$this->lotSeq),
                'received_date' => $this->d($rd), 'expiry_date' => $this->d($expOffset),
                'initial_qty' => $qty, 'current_qty' => 0, 'unit_cost' => $price, // current_qty terisi lewat RECEIVE_IN
                'status' => 'available', 'is_locked' => true, 'created_at' => $this->t($rd, '09:40:00'),
            ]);

            $this->lots[$lotKey] = ['id' => $lotId, 'cost' => $price, 'qty' => 0.0, 'ri' => $riId, 'mat' => $matKey];
            $this->move($lotId, 'RECEIVE_IN', 'IN', $qty, 'receiving_items', $riId, $rd, '09:40:00');
        }

        $this->audit('confirm', 'receivings', $rcvId, ['status' => 'draft'], ['status' => 'confirmed'], $rd, '09:40:00');
    }

    // =========================================================================================
    // 3. PRODUKSI -> PENGOLAHAN -> HASIL -> DISTRIBUSI -> WASTE/SURPLUS
    // =========================================================================================
    private function production(): void
    {
        // ---- B1: Menu 1 porsi besar, SELESAI penuh (traceability lengkap) ----
        $b1 = $this->batch('M1', 'besar', -4, 300, 'completed', [
            'BRS' => 'BRS#1', 'AYM' => 'AYM#1', 'WRT' => 'WRT#1', 'BUC' => 'BUC#1', 'MNY' => 'MNY#1', 'BBM' => 'BBM#1',
        ]);
        $this->steps($b1, -4, true);
        $this->result($b1, -4, 295, 'Makanan matang sempurna, dikemas dalam food tray, suhu terjaga.', true);
        $d1 = $this->dist($b1, 'SDN1', 180, -4, '11:15:00', 'received', true);
        $this->dist($b1, 'SMP2', 115, -4, '11:40:00', 'received', false);
        $this->waste('PRODUCTION_LOSS', $b1, null, null, 5, 'Beberapa porsi tumpah saat pengemasan', -4);
        $this->waste('FOOD_WASTE', $b1, null, $d1, 8, 'Sisa makanan dikembalikan sekolah (tidak habis)', -3);

        // ---- B2: Menu 1 porsi kecil, selesai, ADA surplus, tanpa bukti pengolahan ----
        $b2 = $this->batch('M1', 'kecil', -4, 150, 'completed', [
            'BRS' => 'BRS#1', 'AYM' => 'AYM#1', 'WRT' => 'WRT#1', 'BUC' => 'BUC#1', 'MNY' => 'MNY#1', 'BBM' => 'BBM#1',
        ]);
        $this->steps($b2, -4, false);
        $this->result($b2, -4, 150, 'Kondisi baik.', false);
        $this->dist($b2, 'PAUD', 80, -4, '11:50:00', 'received', false);
        $this->dist($b2, 'POS', 60, -4, '12:10:00', 'received', false);
        $this->waste('SURPLUS', $b2, null, null, 10, 'Porsi cadangan tidak terdistribusi', -4);

        // ---- B3: Menu 2 porsi besar, SIAP DISTRIBUSI (belum ada distribusi) ----
        $b3 = $this->batch('M2', 'besar', -2, 200, 'ready', [
            'BRS' => 'BRS#1', 'IKN' => 'IKN#1', 'BUC' => 'BUC#1', 'MNY' => 'MNY#1', 'BBM' => 'BBM#1',
        ]);
        $this->steps($b3, -2, false);
        $this->result($b3, -2, 198, 'Ikan bakar matang merata.', false);
        $this->waste('PRODUCTION_LOSS', $b3, null, null, 2, 'Dua porsi rusak saat proses', -2);

        // ---- B4: Menu 3 porsi besar, SEDANG DIPROSES (satu tahap masih berjalan) ----
        $b4 = $this->batch('M3', 'besar', -1, 250, 'processing', [
            'BRS' => 'BRS#1', 'TLR' => 'TLR#1', 'TMP' => 'TMP#1', 'MNY' => 'MNY#1', 'BBM' => 'BBM#1',
        ]);
        $this->stepRows($b4, -1, [
            ['Persiapan bahan', '05:00:00', '06:00:00', 'done'],
            ['Pemasakan', '06:00:00', '09:00:00', 'done'],
            ['Pengemasan', '09:00:00', null, 'in_progress'],
        ]);

        // ---- B5: Menu 2 porsi kecil, DRAFT (pemilihan lot belum lengkap, stok BELUM berkurang) ----
        $this->batch('M2', 'kecil', 0, 120, 'draft', ['BRS' => 'BRS#1', 'IKN' => 'IKN#1']);

        // ---- Disposal bahan: lot tempe expired dibuang 14 kg, sisa 6 kg (tetap muncul di Expiry Alert) ----
        $this->waste('MATERIAL_DISPOSAL', null, 'TMP#0', null, 14, 'Tempe melewati tanggal kedaluwarsa', -1);
    }

    /** Membuat batch + pemakaian lot. Hanya batch non-draft yang mengurangi stok (di-"konfirmasi"). */
    private function batch(string $m, string $ukuran, int $day, int $target, string $status, array $alloc): int
    {
        $confirmed = $status !== 'draft';
        $id = $this->ins('production_batches', [
            'menu_id' => $this->menu[$m], 'recipe_id' => $this->recipe[$m][$ukuran], 'id_sppg' => $this->sppg,
            'created_by' => $this->user, 'batch_code' => sprintf('BTC-%s-%03d', $this->ymd($day), ++$this->batchSeq),
            'production_date' => $this->d($day), 'target_porsi' => $target, 'actual_porsi' => null, 'total_cost' => null,
            'status' => $status, 'is_locked' => $confirmed, 'created_at' => $this->t($day, '04:30:00'),
        ]);

        $total = 0.0;
        foreach ($this->rate[$m][$ukuran] as $matKey => $perPorsi) {
            if (! isset($alloc[$matKey])) {
                continue;
            }
            $qty = round($perPorsi * $target, 3);
            $total += $this->consume($id, $alloc[$matKey], $qty, $day, $confirmed);
        }

        if ($confirmed) {
            DB::table('production_batches')->where('id', $id)->update(['total_cost' => round($total, 2)]);
            $this->audit('confirm', 'production_batches', $id, ['status' => 'draft'], ['status' => 'confirmed'], $day, '05:00:00');
        }

        return $id;
    }

    private function consume(int $batchId, string $lotKey, float $qty, int $day, bool $confirmed): float
    {
        $lot = $this->lots[$lotKey];
        $uid = $this->ins('material_usages', [
            'production_batch_id' => $batchId, 'material_lot_id' => $lot['id'], 'qty_used' => $qty,
            'unit_cost_snapshot' => $confirmed ? $lot['cost'] : null, 'created_at' => $this->t($day, '04:45:00'),
        ]);

        if ($confirmed) {
            $this->move($lot['id'], 'PRODUCTION_OUT', 'OUT', $qty, 'material_usages', $uid, $day, '05:00:00');
        }

        return round($qty * $lot['cost'], 2);
    }

    private function steps(int $batchId, int $day, bool $withEvidence): void
    {
        $ids = $this->stepRows($batchId, $day, [
            ['Persiapan bahan', '05:00:00', '06:00:00', 'done'],
            ['Pemasakan', '06:00:00', '09:00:00', 'done'],
            ['Pengemasan', '09:00:00', '10:30:00', 'done'],
        ]);

        if ($withEvidence) {
            foreach ([1 => 'pemasakan', 2 => 'pengemasan'] as $i => $label) {
                $this->ins('evidences', [
                    'uploaded_by' => $this->user, 'evidenceable_type' => 'processing_steps', 'evidenceable_id' => $ids[$i],
                    'file_path' => 'evidence/processing/batch' . $batchId . '-' . $label . '.jpg', 'file_type' => 'image/jpeg',
                    'description' => 'Foto tahap ' . $label, 'is_public' => true, 'uploaded_at' => $this->t($day, '09:30:00'),
                ]);
            }
        }
    }

    private function stepRows(int $batchId, int $day, array $rows): array
    {
        $ids = [];
        foreach ($rows as [$nama, $start, $end, $status]) {
            $ids[] = $this->ins('processing_steps', [
                'production_batch_id' => $batchId, 'recorded_by' => $this->user, 'step_name' => $nama,
                'started_at' => $this->t($day, $start), 'ended_at' => $end ? $this->t($day, $end) : null,
                'status' => $status, 'note' => null, 'created_at' => $this->t($day, $start),
            ]);
        }
        return $ids;
    }

    /** Hasil produksi (pre-distribution) + mengisi actual_porsi di batch. */
    private function result(int $batchId, int $day, int $ready, string $kondisi, bool $withEvidence): void
    {
        $pid = $this->ins('pre_distribution_checks', [
            'production_batch_id' => $batchId, 'checked_by' => $this->user, 'portions_ready' => $ready,
            'reported_condition' => $kondisi, 'note' => null, 'checked_at' => $this->t($day, '10:45:00'),
            'created_at' => $this->t($day, '10:45:00'),
        ]);
        DB::table('production_batches')->where('id', $batchId)->update(['actual_porsi' => $ready]);

        if ($withEvidence) {
            $this->ins('evidences', [
                'uploaded_by' => $this->user, 'evidenceable_type' => 'pre_distribution_checks', 'evidenceable_id' => $pid,
                'file_path' => 'evidence/pre-distribution/batch' . $batchId . '.jpg', 'file_type' => 'image/jpeg',
                'description' => 'Foto hasil produksi siap kirim', 'is_public' => true, 'uploaded_at' => $this->t($day, '10:50:00'),
            ]);
        }
    }

    private function dist(int $batchId, string $destKey, int $portions, int $day, string $time, string $status, bool $withEvidence): int
    {
        $id = $this->ins('distributions', [
            'production_batch_id' => $batchId, 'destination_id' => $this->dest[$destKey], 'recorded_by' => $this->user,
            'portions_sent' => $portions, 'status' => $status, 'distributed_at' => $this->t($day, $time),
            'created_at' => $this->t($day, $time),
        ]);

        if ($withEvidence) {
            $this->ins('evidences', [
                'uploaded_by' => $this->user, 'evidenceable_type' => 'distributions', 'evidenceable_id' => $id,
                'file_path' => 'evidence/distribution/dist' . $id . '.jpg', 'file_type' => 'image/jpeg',
                'description' => 'Foto serah terima di lokasi', 'is_public' => true, 'uploaded_at' => $this->t($day, $time),
            ]);
        }

        return $id;
    }

    /** Catatan waste/surplus. MATERIAL_DISPOSAL juga membuat DISPOSAL_OUT di stock_movements. */
    private function waste(string $type, ?int $batchId, ?string $lotKey, ?int $distId, float $qty, string $reason, int $day): int
    {
        $lot = $lotKey ? $this->lots[$lotKey] : null;
        $id = $this->ins('waste_surplus_records', [
            'production_batch_id' => $batchId, 'material_lot_id' => $lot['id'] ?? null, 'distribution_id' => $distId,
            'id_sppg' => $this->sppg, 'recorded_by' => $this->user, 'record_type' => $type, 'qty' => $qty,
            'reason' => $reason, 'recorded_at' => $this->t($day, '14:00:00'), 'status' => 'active',
            'created_at' => $this->t($day, '14:00:00'),
        ]);

        if ($type === 'MATERIAL_DISPOSAL') {
            $this->move($lot['id'], 'DISPOSAL_OUT', 'OUT', $qty, 'waste_surplus_records', $id, $day, '14:00:00');
        }

        return $id;
    }

    // =========================================================================================
    // 4. CORRECTION REQUEST + AUDIT TRAIL
    // =========================================================================================
    private function corrections(): void
    {
        // C1 - PENDING (menunggu Admin Sistem), lengkap dengan alasan + bukti.
        $c1 = $this->correction('receiving_items', $this->lots['AYM#1']['ri'], 'qty_received', '120', '118',
            'Setelah ditimbang ulang di gudang, berat ayam hanya 118 kg (ada susut 2 kg saat pengiriman).', 'pending', -1, null);
        $this->corrEvidence($c1, 'timbangan-ulang.jpg', -1);
        $this->audit('correction_request', 'receiving_items', $this->lots['AYM#1']['ri'],
            ['qty_received' => '120'], ['qty_received' => '118'], -1, '13:00:00', $c1);

        // C2 - APPLIED: expiry telur salah ketik (+14), dikoreksi menjadi +12. Perubahan benar-benar diterapkan.
        $c2 = $this->correction('receiving_items', $this->lots['TLR#1']['ri'], 'expiry_date_input', $this->d(14), $this->d(12),
            'Tanggal kedaluwarsa pada kemasan telur tertulis ' . $this->d(12) . ', salah ketik saat penerimaan.', 'applied', -4,
            'Sesuai foto kemasan. Disetujui.');
        $this->corrEvidence($c2, 'foto-kemasan-telur.jpg', -4);
        DB::table('receiving_items')->where('id', $this->lots['TLR#1']['ri'])->update(['expiry_date_input' => $this->d(12)]);
        DB::table('material_lots')->where('id', $this->lots['TLR#1']['id'])->update(['expiry_date' => $this->d(12)]);
        $this->audit('correction_request', 'receiving_items', $this->lots['TLR#1']['ri'],
            ['expiry_date_input' => $this->d(14)], ['expiry_date_input' => $this->d(12)], -4, '10:00:00', $c2);
        $this->audit('correction_approve', 'receiving_items', $this->lots['TLR#1']['ri'],
            ['status' => 'pending'], ['status' => 'approved'], -4, '11:00:00', $c2);
        $this->audit('correction_apply', 'receiving_items', $this->lots['TLR#1']['ri'],
            ['expiry_date_input' => $this->d(14)], ['expiry_date_input' => $this->d(12)], -4, '11:05:00', $c2);

        // C3 - REJECTED: ingin mengubah target porsi batch yang sudah terkunci.
        $b1 = (int) DB::table('production_batches')->orderBy('id')->value('id');
        $c3 = $this->correction('production_batches', $b1, 'target_porsi', '300', '310',
            'Ada permintaan tambahan 10 porsi dari sekolah.', 'rejected', -3,
            'Batch sudah selesai dan terkunci. Buat batch baru untuk tambahan porsi.');
        $this->corrEvidence($c3, 'surat-permintaan.pdf', -3, 'application/pdf');
        $this->audit('correction_request', 'production_batches', $b1, ['target_porsi' => '300'], ['target_porsi' => '310'], -3, '09:00:00', $c3);
        $this->audit('correction_reject', 'production_batches', $b1, ['status' => 'pending'], ['status' => 'rejected'], -3, '10:30:00', $c3);
    }

    private function correction(string $table, int $targetId, string $field, string $old, string $new, string $reason,
                                string $status, int $day, ?string $note): int
    {
        $reviewed = $status !== 'pending';
        return $this->ins('correction_requests', [
            'id_sppg' => $this->sppg, 'requested_by' => $this->user, 'reviewed_by' => $reviewed ? $this->user : null,
            'request_no' => sprintf('COR-%s-%03d', $this->ymd($day), ++$this->corSeq),
            'target_table' => $table, 'target_id' => $targetId, 'field_name' => $field,
            'old_value' => $old, 'new_value' => $new, 'reason' => $reason, 'status' => $status,
            'requested_at' => $this->t($day, '09:00:00'),
            'reviewed_at' => $reviewed ? $this->t($day, '11:00:00') : null,
            'review_note' => $note,
            'applied_at' => $status === 'applied' ? $this->t($day, '11:05:00') : null,
            'created_at' => $this->t($day, '09:00:00'),
        ]);
    }

    private function corrEvidence(int $corId, string $file, int $day, string $type = 'image/jpeg'): void
    {
        $this->ins('evidences', [
            'uploaded_by' => $this->user, 'evidenceable_type' => 'correction_requests', 'evidenceable_id' => $corId,
            'file_path' => 'evidence/correction/' . $file, 'file_type' => $type,
            'description' => 'Bukti pendukung koreksi', 'is_public' => false, 'uploaded_at' => $this->t($day, '09:05:00'),
        ]);
    }

    // =========================================================================================
    // HELPER
    // =========================================================================================

    /** Satu-satunya jalur perubahan stok: catat movement, lalu perbarui current_qty lot. */
    private function move(int $lotId, string $type, string $dir, float $qty, string $refType, int $refId, int $day, string $time): void
    {
        $this->ins('stock_movements', [
            'material_lot_id' => $lotId, 'created_by' => $this->user, 'movement_type' => $type, 'direction' => $dir,
            'qty' => $qty, 'reference_type' => $refType, 'reference_id' => $refId, 'created_at' => $this->t($day, $time),
        ]);

        foreach ($this->lots as $key => $lot) {
            if ($lot['id'] === $lotId) {
                $this->lots[$key]['qty'] = round($lot['qty'] + ($dir === 'IN' ? $qty : -$qty), 3);
                DB::table('material_lots')->where('id', $lotId)->update([
                    'current_qty' => $this->lots[$key]['qty'],
                    'status' => $this->lots[$key]['qty'] <= 0 ? 'depleted' : 'available',
                ]);
                return;
            }
        }
    }

    private function audit(string $action, string $type, int $id, array $old, array $new, int $day, string $time, ?int $corId = null): void
    {
        $this->ins('audit_trails', [
            'correction_request_id' => $corId, 'id_user' => $this->user, 'action' => $action,
            'auditable_type' => $type, 'auditable_id' => $id,
            'old_values' => json_encode($old), 'new_values' => json_encode($new), 'created_at' => $this->t($day, $time),
        ]);
    }

    private function ins(string $table, array $row): int
    {
        if (! in_array($table, $this->noTs, true)) {
            $now = $this->t(0, '07:00:00');
            $row += ['created_at' => $now, 'updated_at' => $row['created_at'] ?? $now];
        }

        return (int) DB::table($table)->insertGetId($row);
    }

    private function d(int $offset): string
    {
        return $this->today->modify(sprintf('%+d days', $offset))->format('Y-m-d');
    }

    private function t(int $offset, string $time = '08:00:00'): string
    {
        return $this->d($offset) . ' ' . $time;
    }

    private function ymd(int $offset): string
    {
        return $this->today->modify(sprintf('%+d days', $offset))->format('Ymd');
    }
}
