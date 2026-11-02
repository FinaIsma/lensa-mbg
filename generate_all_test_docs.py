import os
from generate_docx_module import create_docx

# Data Audit Trail
scenarios_audit = [
    [
        "1",
        "S_AUD.1",
        "Menguji otorisasi dan kontrol akses multi-tenancy pada Audit Trail",
        "1. Mengakses halaman Audit Trail tanpa login (Guest).\n2. Mengakses halaman Audit Trail menggunakan akun Staff.\n3. Mengakses halaman Audit Trail menggunakan akun Kepala SPPG.\n4. Mengakses halaman Audit Trail menggunakan akun Admin Sistem."
    ],
    [
        "2",
        "S_AUD.2",
        "Menguji fitur pencarian, filter data, dan pagination log aktivitas",
        "1. Filter riwayat audit berdasarkan modul (misal: Modul Menu).\n2. Filter riwayat audit berdasarkan rentang tanggal yang valid.\n3. Filter riwayat audit dengan rentang tanggal terbalik (tanggal dari > sampai).\n4. Pencarian log aktivitas berdasarkan kata kunci nama pengguna atau kode audit.\n5. Berpindah halaman (pagination) dengan mempertahankan query filter aktif."
    ],
    [
        "3",
        "S_AUD.3",
        "Menguji integritas, immutabilitas, dan drawer detail perubahan data (termasuk koreksi)",
        "1. Membuka panel drawer detail aktivitas mutasi biasa (perbandingan data lama & data baru).\n2. Membuka panel drawer detail aktivitas yang terhubung dengan pengajuan koreksi dan bukti lampiran.\n3. Menguji proteksi immutabilitas data log (pencegahan manipulasi update dan delete record audit trail)."
    ],
    [
        "4",
        "S_AUD.4",
        "Menguji fungsionalitas ekspor data Audit Trail ke format CSV dan laporan siap cetak (PDF)",
        "1. Mengunduh data audit trail ke format CSV.\n2. Membuka laporan resmi audit trail untuk dicetak / disimpan ke PDF.\n3. Verifikasi keamanan isolasi data saat ekspor CSV oleh akun Kepala SPPG."
    ]
]

test_cases_audit = [
    [
        "TC_AUD_001",
        "Verify otorisasi akses halaman Audit Trail oleh pengguna Guest (tanpa login)",
        "Akses URL /admin/audit-trail atau /sppg/audit-trail tanpa sesi autentikasi",
        "1. Pengguna belum melakukan login ke dalam sistem (Guest).\n2. Browser dalam mode penyamaran (Incognito).",
        "1. Buka browser.\n2. Masukkan URL: http://localhost/admin/audit-trail\n3. Tekan Enter.",
        "URL: /admin/audit-trail\nSesi: Tanpa autentikasi",
        "Sistem menolak akses dan secara otomatis mengarahkan (redirect) pengguna ke halaman Login.",
        "Pengguna berada di halaman Login.",
        "Sistem menolak akses dan mengarahkan pengguna ke halaman Login.",
        "PASS"
    ],
    [
        "TC_AUD_002",
        "Verify pembatasan hak akses role Staff ke modul Audit Trail",
        "Login sebagai Staff SPPG kemudian mencoba membuka URL halaman Audit Trail",
        "1. Sistem dapat diakses.\n2. Tersedia akun Staff yang valid dan aktif.",
        "1. Login menggunakan akun Staff.\n2. Akses URL: http://localhost/admin/audit-trail atau /sppg/audit-trail\n3. Tekan Enter.",
        "Akun: Staff SPPG valid\nURL: /admin/audit-trail",
        "Sistem menolak akses dengan menampilkan halaman HTTP 403 Forbidden (Unauthorized Access).",
        "Pengguna tidak dapat melihat data audit trail.",
        "Sistem menampilkan respon HTTP 403 Forbidden.",
        "PASS"
    ],
    [
        "TC_AUD_003",
        "Verify akses Audit Trail oleh Kepala SPPG dengan cakupan data terisolasi (Scoped Data)",
        "Login sebagai Kepala SPPG dan membuka menu Audit Trail",
        "1. Sistem dapat diakses.\n2. Tersedia akun Kepala SPPG terhubung ke unit SPPG terdaftar.",
        "1. Login sebagai Kepala SPPG.\n2. Klik menu 'Audit Trail' pada sidebar navigasi.\n3. Periksa tabel riwayat aktivitas yang dimuat.",
        "Akun: Kepala SPPG Unit SPPG 001\nURL: /sppg/audit-trail",
        "Sistem menampilkan halaman Audit Trail dengan data aktivitas yang hanya berasal dari SPPG miliknya (SPPG 001). Tidak tersedia dropdown untuk memilih SPPG lain.",
        "Pengguna melihat riwayat log spesifik unit SPPG miliknya.",
        "Sistem menampilkan halaman Audit Trail dengan data yang khusus terisolasi untuk unit SPPG 001.",
        "PASS"
    ],
    [
        "TC_AUD_004",
        "Verify akses Audit Trail oleh Admin Sistem dengan cakupan global seluruh unit SPPG",
        "Login sebagai Admin Sistem dan memeriksa ketersediaan filter SPPG",
        "1. Sistem dapat diakses.\n2. Tersedia akun Admin Sistem yang valid.",
        "1. Login sebagai Admin Sistem.\n2. Klik menu 'Audit Trail' pada sidebar.\n3. Periksa filter unit SPPG dan daftar aktivitas yang tampil.",
        "Akun: Admin Sistem\nURL: /admin/audit-trail",
        "Sistem menampilkan seluruh log audit dari semua unit SPPG serta menyediakan dropdown filter unit SPPG untuk menyaring log per unit.",
        "Pengguna memiliki kontrol supervisi audit menyeluruh.",
        "Sistem menampilkan data seluruh unit SPPG beserta dropdown pilihan unit SPPG.",
        "PASS"
    ],
    [
        "TC_AUD_005",
        "Verify filter riwayat audit berdasarkan Modul tertentu",
        "Memilih opsi modul 'Menu' pada dropdown filter modul",
        "1. Berada di halaman Audit Trail sebagai Admin atau Kepala SPPG.\n2. Data log audit dari berbagai modul tersedia.",
        "1. Buka halaman Audit Trail.\n2. Pada dropdown 'Semua Modul', pilih 'Menu'.\n3. Klik tombol 'Terapkan Filter'.",
        "Filter Modul: Menu",
        "Tabel hanya menampilkan entri audit yang berkaitan dengan modul Menu/Resep, dan total ringkasan perubahan disesuaikan.",
        "Data terfilter sesuai modul Menu.",
        "Tabel log berhasil menampilkan aktivitas mutasi data pada modul Menu saja.",
        "PASS"
    ],
    [
        "TC_AUD_006",
        "Verify filter riwayat audit berdasarkan rentang tanggal yang valid",
        "Memilih tanggal 'Dari' dan tanggal 'Sampai' yang valid",
        "1. Berada di halaman Audit Trail.\n2. Terdapat data log aktivitas pada rentang tanggal yang dipilih.",
        "1. Masukkan tanggal 'Dari': 2026-10-01.\n2. Masukkan tanggal 'Sampai': 2026-10-28.\n3. Klik tombol filter.",
        "Dari: 2026-10-01\nSampai: 2026-10-28",
        "Tabel hanya memuat log aktivitas dengan timestamp created_at antara 01/10/2026 s.d. 28/10/2026.",
        "Data terfilter sesuai rentang tanggal.",
        "Tabel menampilkan log aktivitas yang terjadi pada rentang waktu yang dipilih.",
        "PASS"
    ],
    [
        "TC_AUD_007",
        "Verify penanganan otomatis rentang tanggal terbalik (Edge Case)",
        "Memasukkan tanggal 'Dari' yang lebih baru daripada tanggal 'Sampai'",
        "1. Berada di halaman Audit Trail.",
        "1. Masukkan tanggal 'Dari': 2026-10-28.\n2. Masukkan tanggal 'Sampai': 2026-10-01.\n3. Klik tombol filter.",
        "Dari: 2026-10-28\nSampai: 2026-10-01",
        "Sistem secara cerdas menukar kedua tanggal di backend ($dari <-> $sampai) sehingga filter tetap berjalan normal tanpa error.",
        "Filter berhasil diterapkan tanpa crash.",
        "Sistem membalik rentang tanggal secara otomatis dan menampilkan data secara valid.",
        "PASS"
    ],
    [
        "TC_AUD_008",
        "Verify fitur pencarian data log berdasarkan kata kunci aktor atau kode data",
        "Memasukkan kata kunci pencarian pada kolom input 'Cari...'",
        "1. Berada di halaman Audit Trail.\n2. Data aktivitas tersedia di database.",
        "1. Masukkan kata kunci pencarian, misal: 'MN01' atau nama staf.\n2. Tekan Enter atau klik ikon cari.",
        "Query pencarian (q): 'MN01'",
        "Sistem menampilkan baris audit yang cocok dengan kode data 'MN01' atau nama aktor terkait.",
        "Data log yang relevan ditampilkan.",
        "Sistem menyaring dan hanya menampilkan log audit yang mengandung kata kunci pencarian.",
        "PASS"
    ],
    [
        "TC_AUD_009",
        "Verify drawer detail perubahan data (Old Values vs New Values)",
        "Klik tombol 'Detail' pada baris log aktivitas mutasi",
        "1. Terdapat baris audit dengan aktivitas 'Mengubah data' (update).",
        "1. Cari baris log dengan aktivitas update.\n2. Klik tombol 'Detail' di kolom paling kanan.\n3. Periksa isi panel drawer yang terbuka.",
        "Baris Audit ID: Data mutasi menu/resep",
        "Panel samping (drawer) terbuka menampilkan detail lengkap: waktu kejadian, nama pelaku, peran, unit SPPG, tabel komparasi nilai lama vs nilai baru.",
        "Pengguna dapat melihat diff perubahan data.",
        "Drawer detail terbuka dengan tabel perbandingan data lama dan data baru secara rapi.",
        "PASS"
    ],
    [
        "TC_AUD_010",
        "Verify drawer detail untuk log aktivitas yang berasal dari Koreksi Data",
        "Melihat detail aktivitas yang memiliki ID permohonan koreksi",
        "1. Terdapat riwayat koreksi data yang telah disetujui (misal koreksi penerimaan/produksi).",
        "1. Temukan baris log aktivitas yang mencantumkan label koreksi.\n2. Klik tombol 'Detail'.\n3. Periksa section Informasi Koreksi.",
        "Baris Audit ID: Data hasil koreksi",
        "Panel drawer menampilkan alasan koreksi yang diajukan serta tautan dokumen bukti lampiran pendukung.",
        "Informasi pertanggungjawaban koreksi tampil transparan.",
        "Drawer menampilkan alasan koreksi dan berkas bukti lampiran.",
        "PASS"
    ],
    [
        "TC_AUD_011",
        "Verify proteksi integritas data log (Immutability / Anti-Tamper)",
        "Mencoba melakukan update atau delete data AuditTrail secara programatik melalui Eloquent",
        "1. Record data audit trail tersedia di database.",
        "1. Eksekusi kode update/delete pada model AuditTrail melalui backend/unit test.\n2. Periksa respon model.",
        "Action: $auditTrail->delete() atau ->update()",
        "Sistem menolak mutasi dan melempar LogicException: 'Audit trail tidak boleh diubah/dihapus'. Record di database tetap utuh.",
        "Data audit trail tidak dapat dimanipulasi.",
        "Sistem melempar LogicException dan operasi manipulasi database dibatalkan.",
        "PASS"
    ],
    [
        "TC_AUD_012",
        "Verify fungsionalitas ekspor log audit ke format CSV",
        "Klik tombol 'Ekspor CSV' pada halaman Audit Trail",
        "1. Berada di halaman Audit Trail dengan atau tanpa filter aktif.",
        "1. Klik tombol 'Ekspor CSV'.\n2. Buka berkas .csv yang berhasil diunduh menggunakan aplikasi Microsoft Excel.",
        "Filter aktif: Modul Menu, Periode Oktober 2026",
        "Sistem men-download file CSV dengan encoding UTF-8 BOM, struktur kolom lengkap (No, Waktu, Kode Audit, Unit SPPG, Pengguna, Peran, Modul, Aktivitas, Nilai Lama, Nilai Baru, Alasan), serta data sesuai filter.",
        "Berkas CSV tersimpan dan terbaca rapi di Excel.",
        "File CSV berhasil diunduh, karakter rapi tanpa kerusakan encoding, dan isi sesuai filter.",
        "PASS"
    ],
    [
        "TC_AUD_013",
        "Verify fitur cetak / ekspor laporan resmi Audit Trail ke PDF",
        "Klik tombol 'Cetak PDF' pada halaman Audit Trail",
        "1. Berada di halaman Audit Trail.",
        "1. Klik tombol 'Cetak PDF'.\n2. Periksa halaman pratinjau cetak (Print Preview).",
        "Halaman Cetak: /admin/audit-trail/export/pdf",
        "Sistem membuka tab baru dengan tata letak laporan formal (kop resmi, identitas pencetak, tanggal cetak, tabel ringkasan, maksimal 1000 data) dan memicu dialog print browser.",
        "Laporan siap dicetak atau disimpan sebagai PDF.",
        "Halaman laporan resmi terbuka rapi dengan dialog print browser yang siap cetak.",
        "PASS"
    ]
]

# Data Menu & Resep
scenarios_menu = [
    [
        "1",
        "S_MNU.1",
        "Menguji pengelolaan master data Menu (Tambah & Ubah Menu)",
        "1. Menambahkan Menu baru dengan data yang valid.\n2. Menambahkan Menu baru tanpa mengisi field wajib (nama/kategori).\n3. Memperbarui informasi data Menu yang sudah ada."
    ],
    [
        "2",
        "S_MNU.2",
        "Menguji fitur status aktif/nonaktif dan pencarian Menu",
        "1. Mengubah status keaktifan Menu (Aktif/Tidak Aktif) via tombol toggle.\n2. Mencari data Menu berdasarkan nama atau kode ID Menu.\n3. Filter daftar menu berdasarkan kategori."
    ],
    [
        "3",
        "S_RCP.1",
        "Menguji pembuatan Resep baru beserta formulasi bahan dan nilai gizi",
        "1. Menyimpan Resep baru dengan data bahan dan gizi yang valid.\n2. Menyimpan Resep dengan bahan baku yang dipilih duplikat.\n3. Menyimpan Resep dengan takaran bahan 0 atau nilai negatif.\n4. Menyimpan Resep tanpa memilih item bahan baku."
    ],
    [
        "4",
        "S_RCP.2",
        "Menguji aturan bisnis status Resep tunggal aktif (Single Active Recipe Rule)",
        "1. Otomasi pengarsipan resep lama saat menambahkan resep aktif baru pada porsi yang sama.\n2. Mengubah status resep dari arsip menjadi aktif dan memastikan resep aktif lainnya otomatis diarsipkan."
    ],
    [
        "5",
        "S_RCP.3",
        "Menguji aturan pembatasan perubahan resep yang terpakai produksi (Batch Lock) dan pembuatan versi baru",
        "1. Mengubah resep yang belum pernah digunakan dalam batch produksi.\n2. Mengubah langsung resep yang sudah pernah digunakan dalam batch produksi (Batch Lock).\n3. Membuat versi baru resep (version + 1) untuk resep yang sudah terpakai produksi."
    ],
    [
        "6",
        "S_RCP.4",
        "Menguji pengelolaan master Jenis Gizi (Nutrition Types) via modal AJAX",
        "1. Menambahkan jenis gizi baru via pop-up form AJAX.\n2. Menambahkan jenis gizi baru dengan nama yang sudah terdaftar (duplikat)."
    ]
]

test_cases_menu = [
    [
        "TC_MNU_001",
        "Verify penambahan Menu baru dengan seluruh data valid",
        "Menambahkan menu baru dengan mengisi nama menu, kategori yang sesuai, dan deskripsi",
        "1. Login sebagai Staff atau Kepala SPPG.\n2. Halaman Data Menu (/staff/menu) dapat diakses.",
        "1. Buka halaman Data Menu.\n2. Klik tombol '+ Tambah Menu'.\n3. Isi Nama Menu: 'Nasi Rawon Daging Sapi'.\n4. Pilih Kategori: 'Makanan Siap Santap'.\n5. Isi Deskripsi: 'Nasi putih, rawon daging sapi, sambal, tauge'.\n6. Centang Status Aktif.\n7. Klik tombol 'Simpan Menu'.",
        "Nama: Nasi Rawon Daging Sapi\nKategori: makanan_siap_santap\nDeskripsi: Nasi rawon lengkap\nStatus: Aktif",
        "Sistem menyimpan menu baru, menampilkan notifikasi sukses 'Data Menu baru berhasil ditambahkan', dan aktivitas tercatat di Audit Trail.",
        "Menu baru bertambah di daftar dengan kode otomatis (MNxx).",
        "Menu baru berhasil disimpan dengan kode otomatis dan notifikasi sukses muncul.",
        "PASS"
    ],
    [
        "TC_MNU_002",
        "Verify validasi form tambah Menu saat kolom wajib dikosongkan",
        "Menyimpan menu baru dengan mengosongkan field Nama Menu dan Kategori",
        "1. Modal Tambah Menu sedang terbuka.",
        "1. Kosongkan field Nama Menu.\n2. Kosongkan pilihan Kategori.\n3. Klik tombol 'Simpan Menu'.",
        "Nama: (kosong)\nKategori: (tidak dipilih)",
        "Sistem menolak penyimpanan, form tetap terbuka, dan menampilkan pesan peringatan validasi pada kolom yang belum diisi.",
        "Pengguna tetap pada form input dan data tidak tersimpan ke database.",
        "Sistem menolak form dan menampilkan pesan error validasi.",
        "PASS"
    ],
    [
        "TC_MNU_003",
        "Verify pembaruan informasi data Menu",
        "Mengedit nama dan deskripsi menu yang sudah ada",
        "1. Terdapat data Menu yang terdaftar di unit SPPG.",
        "1. Klik tombol 'Edit' (ikon pensil) pada salah satu baris menu.\n2. Ubah Nama Menu atau Deskripsi.\n3. Klik tombol 'Perbarui Menu'.",
        "Nama Baru: 'Nasi Ayam Geprek Sambal Bawang'",
        "Sistem memperbarui data menu, menampilkan notifikasi 'Data Menu berhasil diperbarui', dan perubahannya tercatat di Audit Trail.",
        "Data menu terupdate pada tabel daftar menu.",
        "Data menu berhasil diperbarui dan notifikasi sukses ditampilkan.",
        "PASS"
    ],
    [
        "TC_MNU_004",
        "Verify perubahan status aktif/nonaktif Menu via tombol Toggle",
        "Mengubah status menu dari Aktif menjadi Tidak Aktif",
        "1. Terdapat menu dengan status Aktif.",
        "1. Klik tombol toggle switch status pada baris menu yang aktif.\n2. Konfirmasi perubahan status jika dialog muncul.",
        "Menu ID: MN01\nStatus Awal: Aktif",
        "Status menu berubah menjadi 'Tidak Aktif' dengan badge abu-abu, notifikasi sukses muncul, dan tercatat di Audit Trail.",
        "Status menu berhasil diperbarui.",
        "Status menu berhasil diubah menjadi Tidak Aktif.",
        "PASS"
    ],
    [
        "TC_MNU_005",
        "Verify pencarian data Menu berdasarkan nama atau kode ID",
        "Memasukkan kata kunci pencarian pada search box data menu",
        "1. Berada di halaman daftar menu yang memiliki banyak data.",
        "1. Masukkan kata kunci 'Rawon' pada kolom pencarian.\n2. Tekan Enter.",
        "Query (q): 'Rawon'",
        "Tabel daftar menu hanya memuat menu yang memiliki nama atau deskripsi mengandung kata 'Rawon'.",
        "Daftar menu tersaring sesuai kata kunci.",
        "Sistem menampilkan hasil pencarian menu yang relevan.",
        "PASS"
    ],
    [
        "TC_RCP_001",
        "Verify penambahan Resep baru dengan formulasi bahan dan nilai gizi yang valid",
        "Menyimpan resep baru lengkap dengan item bahan, takaran, dan nilai gizi",
        "1. Sudah ada data Menu dan Master Bahan baku.\n2. Berada di halaman Data Resep (/staff/resep).",
        "1. Klik tombol '+ Tambah Resep'.\n2. Pilih Menu: 'Nasi Rawon Daging Sapi'.\n3. Pilih Ukuran Porsi: 'Besar'.\n4. Pilih Status: 'Active'.\n5. Tambahkan Bahan 1: Beras (0.12 kg).\n6. Tambahkan Bahan 2: Daging Sapi (0.08 kg).\n7. Masukkan Nilai Gizi: Energi = 520 kkal, Protein = 24 g.\n8. Klik 'Simpan Resep'.",
        "Menu: MN01\nPorsi: Besar\nBahan: Beras (0.12 kg), Daging Sapi (0.08 kg)\nGizi: Kalori 520 kkal",
        "Sistem berhasil menyimpan resep baru, menghasilkan kode resep (RSxx), menyimpan relasi bahan & gizi, serta mencatat log audit.",
        "Resep baru berstatus Active muncul pada tabel Resep.",
        "Resep berhasil tersimpan beserta detail bahan dan kandungan gizinya.",
        "PASS"
    ],
    [
        "TC_RCP_002",
        "Verify validasi pemilihan bahan baku ganda (duplikat) dalam satu resep",
        "Memilih bahan baku yang sama lebih dari satu kali pada baris formulasi bahan",
        "1. Modal Tambah/Edit Resep sedang terbuka.",
        "1. Tambah baris bahan 1: Beras putih.\n2. Tambah baris bahan 2: Beras putih.\n3. Klik tombol 'Simpan Resep'.",
        "Item Bahan: Beras putih & Beras putih (duplikat)",
        "Sistem menolak form dan menampilkan pesan error: 'Bahan tidak boleh dipilih ganda dalam satu resep.'",
        "Resep tidak tersimpan ke database.",
        "Sistem menolak penyimpanan dan menampilkan pesan error bahan duplikat.",
        "PASS"
    ],
    [
        "TC_RCP_003",
        "Verify validasi takaran bahan resep berupa angka 0 atau negatif",
        "Memasukkan angka takaran bahan <= 0",
        "1. Modal Tambah/Edit Resep sedang terbuka.",
        "1. Pilih bahan baku: Daging Ayam.\n2. Masukkan takaran qty_per_porsi: 0 atau -1.5.\n3. Klik 'Simpan Resep'.",
        "Qty per porsi: 0",
        "Sistem menolak input dan memunculkan notifikasi validasi: 'Takaran bahan harus berupa angka lebih besar dari 0.'",
        "Resep tidak tersimpan ke database.",
        "Sistem menolak input dan memunculkan pesan validasi takaran harus > 0.",
        "PASS"
    ],
    [
        "TC_RCP_004",
        "Verify aturan Single Active Recipe saat menambah resep aktif baru",
        "Membuat resep baru berstatus Active pada menu dan ukuran porsi yang sudah memiliki resep aktif",
        "1. Menu MN01 sudah memiliki Resep RS01 porsi 'Besar' berstatus 'Active'.",
        "1. Buat resep baru untuk Menu MN01, porsi 'Besar', dan pilih Status 'Active'.\n2. Masukkan bahan dan takaran.\n3. Klik 'Simpan Resep'.",
        "Menu: MN01, Porsi: Besar, Status: Active",
        "Sistem otomatis mengarsipkan (status menjadi 'archived') resep RS01, dan resep baru menjadi satu-satunya yang berstatus 'Active' untuk porsi tersebut.",
        "Hanya ada 1 resep aktif untuk kombinasi Menu & Porsi tersebut.",
        "Resep lama otomatis terarsipkan dan resep baru aktif sebagai versi terkini.",
        "PASS"
    ],
    [
        "TC_RCP_005",
        "Verify pembaruan langsung resep yang BELUM pernah dipakai dalam batch produksi",
        "Mengedit bahan dan gizi pada resep baru yang belum ada riwayat produksi",
        "1. Resep RS05 belum pernah tercatat di tabel production_batches.",
        "1. Klik tombol 'Edit' pada resep RS05.\n2. Ubah takaran bahan beras dari 0.10 menjadi 0.11 kg.\n3. Klik 'Perbarui Resep'.",
        "Resep ID: RS05 (Unused in Batch)",
        "Sistem berhasil memperbarui resep secara langsung, menampilkan notifikasi sukses, dan mencatat riwayat perubahan di Audit Trail.",
        "Data resep RS05 terupdate dengan nilai takaran baru.",
        "Data resep berhasil diperbarui secara langsung.",
        "PASS"
    ],
    [
        "TC_RCP_006",
        "Verify pencegahan edit langsung pada resep yang SUDAH digunakan dalam batch produksi (Production Batch Lock)",
        "Mencoba mengirim form pembaruan resep yang id-nya sudah terdaftar di tabel batch produksi",
        "1. Resep RS01 sudah digunakan dalam salah satu batch produksi di tabel production_batches.",
        "1. Buka drawer/modal edit untuk resep RS01.\n2. Lakukan submit perubahan data resep.",
        "Resep ID: RS01 (Used in Batch)",
        "Sistem memblokir proses update langsung dan menampilkan notifikasi: 'Resep ini sudah pernah digunakan dalam batch produksi dan tidak dapat diubah secara langsung. Silakan gunakan tombol Buat Versi Baru.'",
        "Resep RS01 tetap tidak berubah dan integritas riwayat batch produksi terlindungi.",
        "Sistem menolak pembaruan langsung dan menginstruksikan pengguna untuk membuat versi baru.",
        "PASS"
    ],
    [
        "TC_RCP_007",
        "Verify fungsionalitas 'Buat Versi Baru' (Recipe Versioning) pada resep",
        "Menggunakan aksi 'Buat Versi Baru' pada resep versi 1 untuk membuat versi 2",
        "1. Resep RS01 berada pada Versi 1 dan berstatus Active.",
        "1. Klik aksi 'Buat Versi Baru' pada resep RS01.\n2. Sesuaikan komposisi takaran bahan baku baru.\n3. Klik tombol 'Simpan Versi Baru'.",
        "Resep Asal: RS01 (v1)",
        "Sistem mengarsipkan resep v1 (status -> archived), membuat resep baru dengan version 2 dan status Active, serta mencatat audit trail aksi 'create_version'.",
        "Resep baru versi 2 aktif menggantikan versi lama.",
        "Sistem berhasil membuat resep versi 2, mengarsipkan versi 1, dan mencatatnya ke audit trail.",
        "PASS"
    ],
    [
        "TC_RCP_008",
        "Verify penambahan Master Jenis Gizi baru via AJAX pop-up",
        "Menambahkan jenis gizi baru (misal: 'Kalsium') langsung dari modal form resep",
        "1. Modal form Resep sedang terbuka.",
        "1. Klik tombol '+ Jenis Gizi Baru'.\n2. Masukkan Nama: 'Kalsium'.\n3. Pilih Satuan: 'mg'.\n4. Klik 'Simpan Jenis Gizi'.",
        "Nama: Kalsium, Satuan: mg",
        "Sistem merespons via AJAX JSON success: true, opsi 'Kalsium (mg)' langsung muncul dan terpilih otomatis di dropdown gizi resep tanpa reload halaman.",
        "Master jenis gizi baru tersimpan ke database.",
        "Jenis gizi baru berhasil ditambahkan dan langsung tersedia di dropdown.",
        "PASS"
    ],
    [
        "TC_RCP_009",
        "Verify validasi nama Jenis Gizi duplikat",
        "Menambahkan jenis gizi dengan nama yang sudah ada di database",
        "1. Jenis gizi 'Energi' sudah terdaftar di sistem.",
        "1. Buka pop-up Tambah Jenis Gizi.\n2. Masukkan Nama: 'Energi'.\n3. Pilih Satuan: 'kkal'.\n4. Klik 'Simpan Jenis Gizi'.",
        "Nama: Energi (sudah ada)",
        "Sistem mengembalikan status error validasi: 'Jenis gizi ini sudah terdaftar.' dan data tidak disimpan.",
        "Database tidak mengalami redudansi data gizi.",
        "Sistem menolak dan menampilkan pesan bahwa jenis gizi sudah terdaftar.",
        "PASS"
    ]
]

# Widths in DXA (approx 14400 total printable width for landscape A4)
col_widths_scenario = [1200, 1400, 4200, 7600]
col_widths_testcase = [1200, 1600, 1800, 1500, 2000, 1400, 1900, 1300, 1900, 800]

sections = [
    # ---------------- MODUL 1 ----------------
    {
        "type": "heading",
        "text": "1. MODUL AUDIT TRAIL"
    },
    {
        "type": "table",
        "headers": ["Test Scenario #", "Requirement ID", "Test Scenario Description", "Test Cases"],
        "rows": scenarios_audit,
        "widths": col_widths_scenario,
        "header_bg": "1F4E79"
    },
    {
        "type": "meta",
        "data": [
            ("Project Name", "Lensa MBG"),
            ("Module Name", "Audit Trail"),
            ("Reference Document", "Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SRS) & Arsitektur Lensa MBG"),
            ("Created by", "Fina Ismatus Saniyah"),
            ("Date of creation", "30/10/2026"),
            ("Date of review", "30/10/2026")
        ]
    },
    {
        "type": "table",
        "headers": ["TEST CASE ID", "TEST SCENARIO", "TEST CASE", "PRE-CONDITION", "TEST STEPS", "TEST DATA", "EXPECTED RESULT", "POST CONDITION", "ACTUAL RESULT", "STATUS\n(PASS/FAIL)"],
        "rows": test_cases_audit,
        "widths": col_widths_testcase,
        "header_bg": "2F5597"
    },
    {
        "type": "pagebreak"
    },
    # ---------------- MODUL 2 ----------------
    {
        "type": "heading",
        "text": "2. MODUL DATA MASTER MENU DAN RESEP"
    },
    {
        "type": "table",
        "headers": ["Test Scenario #", "Requirement ID", "Test Scenario Description", "Test Cases"],
        "rows": scenarios_menu,
        "widths": col_widths_scenario,
        "header_bg": "1F4E79"
    },
    {
        "type": "meta",
        "data": [
            ("Project Name", "Lensa MBG"),
            ("Module Name", "Master Data Menu dan Resep"),
            ("Reference Document", "Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SRS) & Arsitektur Lensa MBG"),
            ("Created by", "Fina Ismatus Saniyah"),
            ("Date of creation", "30/10/2026"),
            ("Date of review", "30/10/2026")
        ]
    },
    {
        "type": "table",
        "headers": ["TEST CASE ID", "TEST SCENARIO", "TEST CASE", "PRE-CONDITION", "TEST STEPS", "TEST DATA", "EXPECTED RESULT", "POST CONDITION", "ACTUAL RESULT", "STATUS\n(PASS/FAIL)"],
        "rows": test_cases_menu,
        "widths": col_widths_testcase,
        "header_bg": "2F5597"
    }
]

docx_file = "Test_Scenario_dan_Test_Case_Lensa_MBG.docx"
create_docx(docx_file, "DOKUMEN TEST SCENARIO & TEST CASE - LENSA MBG", sections)

# Also generate HTML file that opens directly in Word
html_content = f'''<!DOCTYPE html>
<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
<meta charset="utf-8">
<title>Dokumen Test Scenario dan Test Case Lensa MBG</title>
<!--[if gte mso 9]>
<xml>
<w:WordDocument>
<w:View>Print</w:View>
<w:Zoom>100</w:Zoom>
<w:DoNotOptimizeForBrowser/>
</w:WordDocument>
</xml>
<![endif]-->
<style>
@page {{
    size: 29.7cm 21.0cm; /* Landscape A4 */
    margin: 1.5cm 1.5cm 1.5cm 1.5cm;
    mso-page-orientation: landscape;
}}
body {{
    font-family: 'Calibri', 'Arial', sans-serif;
    font-size: 10pt;
    color: #222;
}}
h1 {{
    text-align: center;
    font-size: 16pt;
    color: #1F4E79;
    margin-bottom: 20px;
}}
h2 {{
    font-size: 13pt;
    color: #1F4E79;
    border-bottom: 2px solid #1F4E79;
    padding-bottom: 4px;
    margin-top: 30px;
    margin-bottom: 12px;
}}
.meta-box {{
    margin-bottom: 15px;
    font-size: 9.5pt;
    line-height: 1.6;
    background-color: #F2F4F7;
    padding: 10px 15px;
    border-left: 4px solid #1F4E79;
}}
.meta-box strong {{
    display: inline-block;
    width: 170px;
    color: #333;
}}
table {{
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 25px;
}}
th, td {{
    border: 1px solid #999;
    padding: 6px 8px;
    font-size: 8.5pt;
    vertical-align: top;
}}
th {{
    background-color: #2F5597;
    color: #ffffff;
    font-weight: bold;
    text-align: center;
}}
tr:nth-child(even) td {{
    background-color: #FAFAFA;
}}
.status-pass {{
    background-color: #E2F0D9 !important;
    color: #385723;
    font-weight: bold;
    text-align: center;
}}
.status-fail {{
    background-color: #FCE4D6 !important;
    color: #C00000;
    font-weight: bold;
    text-align: center;
}}
.center {{
    text-align: center;
}}
.page-break {{
    page-break-before: always;
}}
</style>
</head>
<body>
<h1>DOKUMEN TEST SCENARIO & TEST CASE<br>SISTEM LENSA MBG</h1>

<h2>1. MODUL AUDIT TRAIL</h2>

<table>
<tr>
    <th style="width: 8%;">Test Scenario #</th>
    <th style="width: 12%;">Requirement ID</th>
    <th style="width: 32%;">Test Scenario Description</th>
    <th style="width: 48%;">Test Cases</th>
</tr>
'''

for row in scenarios_audit:
    tc_formatted = row[3].replace('\n', '<br>')
    html_content += f'''<tr>
    <td class="center"><b>{row[0]}</b></td>
    <td class="center"><b>{row[1]}</b></td>
    <td>{row[2]}</td>
    <td>{tc_formatted}</td>
</tr>'''

html_content += '''
</table>

<div class="meta-box">
    <div><strong>Project Name:</strong> Lensa MBG</div>
    <div><strong>Module Name:</strong> Audit Trail</div>
    <div><strong>Reference Document:</strong> Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SRS) & Arsitektur Lensa MBG</div>
    <div><strong>Created by:</strong> Fina Ismatus Saniyah</div>
    <div><strong>Date of creation:</strong> 30/10/2026</div>
    <div><strong>Date of review:</strong> 30/10/2026</div>
</div>

<table>
<tr>
    <th style="width: 8%;">TEST CASE ID</th>
    <th style="width: 11%;">TEST SCENARIO</th>
    <th style="width: 11%;">TEST CASE</th>
    <th style="width: 10%;">PRE-CONDITION</th>
    <th style="width: 13%;">TEST STEPS</th>
    <th style="width: 10%;">TEST DATA</th>
    <th style="width: 13%;">EXPECTED RESULT</th>
    <th style="width: 9%;">POST CONDITION</th>
    <th style="width: 11%;">ACTUAL RESULT</th>
    <th style="width: 4%;">STATUS</th>
</tr>
'''

for r in test_cases_audit:
    status_class = "status-pass" if r[9] == "PASS" else "status-fail"
    html_content += f'''<tr>
    <td class="center"><b>{r[0]}</b></td>
    <td>{r[1]}</td>
    <td>{r[2]}</td>
    <td>{r[3].replace(chr(10), "<br>")}</td>
    <td>{r[4].replace(chr(10), "<br>")}</td>
    <td>{r[5].replace(chr(10), "<br>")}</td>
    <td>{r[6].replace(chr(10), "<br>")}</td>
    <td>{r[7]}</td>
    <td>{r[8]}</td>
    <td class="{status_class}">{r[9]}</td>
</tr>'''

html_content += '''
</table>

<div class="page-break"></div>

<h2>2. MODUL DATA MASTER MENU DAN RESEP</h2>

<table>
<tr>
    <th style="width: 8%;">Test Scenario #</th>
    <th style="width: 12%;">Requirement ID</th>
    <th style="width: 32%;">Test Scenario Description</th>
    <th style="width: 48%;">Test Cases</th>
</tr>
'''

for row in scenarios_menu:
    tc_formatted = row[3].replace('\n', '<br>')
    html_content += f'''<tr>
    <td class="center"><b>{row[0]}</b></td>
    <td class="center"><b>{row[1]}</b></td>
    <td>{row[2]}</td>
    <td>{tc_formatted}</td>
</tr>'''

html_content += '''
</table>

<div class="meta-box">
    <div><strong>Project Name:</strong> Lensa MBG</div>
    <div><strong>Module Name:</strong> Master Data Menu dan Resep</div>
    <div><strong>Reference Document:</strong> Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SRS) & Arsitektur Lensa MBG</div>
    <div><strong>Created by:</strong> Fina Ismatus Saniyah</div>
    <div><strong>Date of creation:</strong> 30/10/2026</div>
    <div><strong>Date of review:</strong> 30/10/2026</div>
</div>

<table>
<tr>
    <th style="width: 8%;">TEST CASE ID</th>
    <th style="width: 11%;">TEST SCENARIO</th>
    <th style="width: 11%;">TEST CASE</th>
    <th style="width: 10%;">PRE-CONDITION</th>
    <th style="width: 13%;">TEST STEPS</th>
    <th style="width: 10%;">TEST DATA</th>
    <th style="width: 13%;">EXPECTED RESULT</th>
    <th style="width: 9%;">POST CONDITION</th>
    <th style="width: 11%;">ACTUAL RESULT</th>
    <th style="width: 4%;">STATUS</th>
</tr>
'''

for r in test_cases_menu:
    status_class = "status-pass" if r[9] == "PASS" else "status-fail"
    html_content += f'''<tr>
    <td class="center"><b>{r[0]}</b></td>
    <td>{r[1]}</td>
    <td>{r[2]}</td>
    <td>{r[3].replace(chr(10), "<br>")}</td>
    <td>{r[4].replace(chr(10), "<br>")}</td>
    <td>{r[5].replace(chr(10), "<br>")}</td>
    <td>{r[6].replace(chr(10), "<br>")}</td>
    <td>{r[7]}</td>
    <td>{r[8]}</td>
    <td class="{status_class}">{r[9]}</td>
</tr>'''

html_content += '''
</table>
</body>
</html>
'''

doc_file = "Test_Scenario_dan_Test_Case_Lensa_MBG.doc"
with open(doc_file, "w", encoding="utf-8") as f:
    f.write(html_content)

print(f"File {doc_file} created successfully ({os.path.getsize(doc_file)} bytes)")
