# Project Requirements: Lensa MBG (Sistem Transparansi MBG)

## 1. Ringkasan Proyek & Tech Stack
Lensa MBG adalah sistem informasi berbasis web untuk mendukung transparansi dan keterlacakan (*traceability*) proses pengolahan Makan Bergizi Gratis (MBG). Sistem ini berfokus pada pelacakan material lot (bahan baku) dari supplier hingga ke production batch dan distribusi.

### Tech Stack Utama
- **Backend:** Laravel 11
- **Frontend:** HTML, CSS, Tailwind CSS, Blade / Livewire
- **Database:** supabase

---

## 2. Struktur Database (Schema & Models)

### 1. Model: `User` (Tabel: `users`)
Mengelola data pengguna sistem.
- `id_user` (Bigint, PK, Auto Increment)
- `nama` (String)
- `email` (String, Unique)
- `password` (String)
- `role` (Enum: 'Admin Sistem', 'Admin SPPG', 'Penerima MBG')

### 2. Model: `Sppg` (Tabel: `sppgs`)
Mengelola data titik unit SPPG (Satuan Pelayanan Program Gizi).
- `id_sppg` (String, PK, e.g., 'SPPG001')
- `nama_pegawai` (String)
- `email` (String, Unique)
- `nama_sppg` (String)
- `status_verif` (Enum: 'Terverifikasi', 'Belum Diverifikasi', 'Ditolak')
- `status_aktif` (Enum: 'Aktif', 'Belum Aktif', 'Nonaktif')
- Timestamp (created_at, updated_at)

### 3. Model Pendukung Operasional (Garis Besar Hubungan Data)
Agen AI wajib menyiapkan relasi database untuk entitas berikut sesuai proposal:
- `Supplier`, `Material`, `MaterialLot` (Mencatat lot number, expiry date, dan barcode otomatis).
- `Purchase`, `Receiving` (Pencatatan pengadaan bahan).
- `RecipeBook / BOM` (Bill of Materials untuk menu pangan).
- `ProductionBatch`, `Distribution`, `FoodWasteSurplus`.
- `CorrectionRequest`, `AuditTrail`.

---

## 3. Fitur Utama Halaman "SPPG Management" (Akses: Admin Sistem)
Berdasarkan screenshot UI yang diunggah, halaman ini wajib mengimplementasikan fungsi dinamis berikut:

### A. Tampilan Atas: Statistik Cards (Dinamis dari Database)
Menampilkan 4 kartu indikator performa utama dengan data *real-time*:
1. **Total SPPG:** Menghitung total data pada tabel `sppgs`.
2. **Belum Verifikasi:** Menghitung baris data `sppgs` dengan `status_verif = 'Belum Diverifikasi'`.
3. **Status Aktif:** Menghitung baris data `sppgs` dengan `status_aktif = 'Aktif'`.
4. **Status Nonaktif:** Menghitung baris data `sppgs` dengan `status_aktif = 'Nonaktif'`.

### B. Komponen Kontrol: Pencarian & Filter
1. **Input Search:** Text input "Cari nama, email atau tempat SPPG" yang memfilter data tabel berdasarkan kolom `nama_pegawai`, `email`, atau `nama_sppg`.
2. **Dropdown Filter:** Pilihan "Status Verif" untuk memfilter data berdasarkan status verifikasi.
3. **Button "Hapus Filter":** Mereset input pencarian dan dropdown ke kondisi semula.

### C. Data Table & Tombol Aksi
Menampilkan 7 baris data per halaman (Pagination) dengan kolom:
- `IDSPPG` | `Nama Pegawai` | `Email` | `SPPG` | `Status Verif` | `Status` | `Aksi`
- **Aturan Warna Badge:**
  - *Terverifikasi / Aktif:* Hijau tekstur kontras.
  - *Belum Diverifikasi / Belum Aktif:* Kuning/Oranye tekstur kontras.
  - *Ditolak / Nonaktif:* Merah tekstur kontras.
- **Tombol Aksi Dinamis:**
  - Jika `status_verif = 'Belum Diverifikasi'`, tombol utama yang muncul adalah **Verifikasi** (ikon catatan/persetujuan).
  - Jika status sudah 'Terverifikasi' atau 'Ditolak', tombol yang muncul adalah **Detail** (ikon mata).

---

## 4. Aturan Pengendalian Sistem (System Constraints)
Agen AI harus menerapkan aturan *backend* berikut demi menjaga validitas data MBG:
1. **Expiry & FEFO Validation:** Sistem wajib menolak penggunaan `MaterialLot` yang sudah kedaluwarsa dan memprioritaskan metode FEFO (*First Expired, First Out*) saat produksi.
2. **Stock Movement:** Jumlah stok tidak boleh diubah manual via input angka langsung, melainkan harus kalkulasi otomatis dari transaksi masuk/keluar.
3. **Data Locking & Correction Workflow:** Data transaksi operasional yang sudah dikonfirmasi akan langsung dikunci (*locked*). Segala bentuk perubahan data salah harus melalui pengajuan `CorrectionRequest` dan disetujui oleh Admin Sistem.
4. **No Hard Delete:** Dilarang keras menghapus data transaksi secara permanen (`->delete()`). Gunakan mekanisme soft-delete atau mengubah status menjadi *Cancelled* atau *Voided*.
5. **Audit Trail:** Setiap aktivitas krusial (siapa, kapan, data sebelum, dan data sesudah) wajib dicatat otomatis di tabel `audit_trails` memakai *server timestamp*.
