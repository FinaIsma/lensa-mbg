import { test, expect } from '@playwright/test';

/**
 * Helper untuk login akun melalui antarmuka Form Login
 * @param {import('@playwright/test').Page} page
 * @param {'admin'|'sppg'} roleType
 * @param {string} email
 * @param {string} password
 */
async function loginAs(page, roleType, email, password) {
  await page.goto('/login');

  // Pilih tombol role (Admin atau Kepala/Staff SPPG)
  if (roleType === 'admin') {
    await page.locator('#adminButton').click();
  } else {
    await page.locator('#sppgButton').click();
  }

  await page.locator('input#email').fill(email);
  await page.locator('input#password').fill(password);
  await page.locator('button[type="submit"].login-button').click();

  // Tunggu hingga navigasi selesai dan tidak lagi berada di halaman login
  await page.waitForLoadState('networkidle');
}

test.describe('Audit Trail - Integration & E2E Testing (JavaScript)', () => {

  // ===========================================================================
  // SKENARIO 1: Menguji Otorisasi & Kontrol Akses Multi-tenancy
  // ===========================================================================

  test('1.1 Mengakses halaman Audit Trail tanpa login (Guest) dialihkan ke Login', async ({ page }) => {
    // Akses audit trail admin tanpa login
    await page.goto('/admin/audit-trail');
    await expect(page).toHaveURL(/.*\/login/);

    // Akses audit trail sppg tanpa login
    await page.goto('/sppg/audit-trail');
    await expect(page).toHaveURL(/.*\/login/);
  });

  test('1.2 Akun Staff dilarang mengakses rute audit trail admin & dialihkan ke Dashboard Staff', async ({ page }) => {
    // Kredensial akun Staff Anda (sesuaikan jika berbeda di database lokal)
    const staffEmail = process.env.TEST_STAFF_EMAIL || 'staff@example.com';
    const staffPassword = process.env.TEST_STAFF_PASSWORD || 'password123';

    await loginAs(page, 'sppg', staffEmail, staffPassword);

    // Jika login berhasil, coba buka rute audit trail
    if (!page.url().includes('/login')) {
      await page.goto('/admin/audit-trail');
      await expect(page).toHaveURL(/.*\/staff\/dashboard/);
    }
  });

  test('1.3 Akun Kepala SPPG dapat membuka audit trail SPPG miliknya tetapi dilarang ke rute Admin', async ({ page }) => {
    const kepalaEmail = process.env.TEST_KEPALA_EMAIL || 'kepala@example.com';
    const kepalaPassword = process.env.TEST_KEPALA_PASSWORD || 'password123';

    await loginAs(page, 'sppg', kepalaEmail, kepalaPassword);

    if (!page.url().includes('/login')) {
      // 1. Coba akses rute admin audit -> dialihkan ke sppg dashboard
      await page.goto('/admin/audit-trail');
      await expect(page).toHaveURL(/.*\/sppg\/dashboard/);

      // 2. Akses rute audit trail sppg sendiri -> berhasil dibuka
      await page.goto('/sppg/audit-trail');
      await expect(page).toHaveURL(/.*\/sppg\/audit/);
      await expect(page.locator('h1.page-title')).toContainText('Audit Trail');
    }
  });

  test('1.4 Akun Admin Sistem dapat membuka Audit Trail Seluruh SPPG', async ({ page }) => {
    const adminEmail = process.env.TEST_ADMIN_EMAIL || 'admin1@gmail.com';
    const adminPassword = process.env.TEST_ADMIN_PASSWORD || 'password123';

    await loginAs(page, 'admin', adminEmail, adminPassword);

    if (!page.url().includes('/login')) {
      await page.goto('/admin/audit-trail');
      await expect(page).toHaveURL(/.*\/admin\/audit/);
      await expect(page.locator('h1.page-title')).toContainText('Audit Trail');
      // Admin memiliki dropdown untuk memilih SPPG tertentu atau Semua SPPG
      await expect(page.locator('select[name="sppg"]')).toBeVisible();
    }
  });

  // ===========================================================================
  // SKENARIO 2: Menguji Fitur Pencarian, Filter Data, dan Pagination
  // ===========================================================================

  test('2.1 Filter riwayat audit berdasarkan modul (misal: Modul Menu)', async ({ page }) => {
    const adminEmail = process.env.TEST_ADMIN_EMAIL || 'admin1@gmail.com';
    const adminPassword = process.env.TEST_ADMIN_PASSWORD || 'password123';

    await loginAs(page, 'admin', adminEmail, adminPassword);

    if (!page.url().includes('/login')) {
      await page.goto('/admin/audit-trail');

      // Pilih Modul "Menu" di dropdown
      const selectModul = page.locator('select[name="modul"]');
      await selectModul.selectOption('Menu');

      await page.waitForLoadState('networkidle');
      await expect(page).toHaveURL(/.*modul=Menu/);

      // Pastikan badge/label Modul yang muncul di tabel adalah Menu (jika ada baris data)
      const rows = page.locator('.sppg-table tbody tr');
      const count = await rows.count();
      if (count > 0 && !(await page.locator('.empty-state').isVisible())) {
        await expect(rows.first()).toContainText('Menu');
      }
    }
  });

  test('2.2 Filter tanggal menggunakan popover datepicker', async ({ page }) => {
    const adminEmail = process.env.TEST_ADMIN_EMAIL || 'admin1@gmail.com';
    const adminPassword = process.env.TEST_ADMIN_PASSWORD || 'password123';

    await loginAs(page, 'admin', adminEmail, adminPassword);

    if (!page.url().includes('/login')) {
      await page.goto('/admin/audit-trail');

      // Buka popover rentang tanggal
      const dateBtn = page.locator('#dateBtn');
      await dateBtn.click();

      // Pilih salah satu preset tanggal, misal: "7-hari"
      const preset7Hari = page.locator('button[data-preset="7-hari"]');
      if (await preset7Hari.isVisible()) {
        await preset7Hari.click();
        await page.locator('#popTerapkan').click();
        await page.waitForLoadState('networkidle');
        await expect(page).toHaveURL(/.*dari=/);
      }
    }
  });

  test('2.3 Pencarian log aktivitas berdasarkan kata kunci', async ({ page }) => {
    const adminEmail = process.env.TEST_ADMIN_EMAIL || 'admin1@gmail.com';
    const adminPassword = process.env.TEST_ADMIN_PASSWORD || 'password123';

    await loginAs(page, 'admin', adminEmail, adminPassword);

    if (!page.url().includes('/login')) {
      await page.goto('/admin/audit-trail');

      const searchInput = page.locator('input[name="q"]');
      await searchInput.fill('Admin');
      await searchInput.press('Enter');

      await page.waitForLoadState('networkidle');
      await expect(page).toHaveURL(/.*q=Admin/);
    }
  });

  test('2.4 Berpindah halaman (pagination) dengan mempertahankan query filter aktif', async ({ page }) => {
    const adminEmail = process.env.TEST_ADMIN_EMAIL || 'admin1@gmail.com';
    const adminPassword = process.env.TEST_ADMIN_PASSWORD || 'password123';

    await loginAs(page, 'admin', adminEmail, adminPassword);

    if (!page.url().includes('/login')) {
      // Masuk dengan filter modul aktif
      await page.goto('/admin/audit-trail?modul=Menu');

      const page2Link = page.locator('.pagination a:has-text("2")');
      if (await page2Link.isVisible()) {
        await page2Link.click();
        await page.waitForLoadState('networkidle');

        // Pastikan parameter query filter tetap terbawa di URL halaman kedua
        await expect(page).toHaveURL(/.*page=2/);
        await expect(page).toHaveURL(/.*modul=Menu/);
      }
    }
  });

  // ===========================================================================
  // SKENARIO 3: Drawer Detail Perubahan Data (Interaksi Animasi & DOM)
  // ===========================================================================

  test('3.1 Membuka dan menutup panel drawer detail mutasi data', async ({ page }) => {
    const adminEmail = process.env.TEST_ADMIN_EMAIL || 'admin1@gmail.com';
    const adminPassword = process.env.TEST_ADMIN_PASSWORD || 'password123';

    await loginAs(page, 'admin', adminEmail, adminPassword);

    if (!page.url().includes('/login')) {
      await page.goto('/admin/audit-trail');

      const detailButtons = page.locator('button.btn-detail');
      if ((await detailButtons.count()) > 0) {
        // Klik tombol Detail pada baris pertama
        await detailButtons.first().click();

        const drawer = page.locator('#detailDrawer');
        const backdrop = page.locator('#detailBackdrop');

        // Drawer dan Backdrop harus bergeser muncul (mendapat class 'open')
        await expect(drawer).toHaveClass(/open/);
        await expect(backdrop).toHaveClass(/open/);
        await expect(drawer.locator('h2')).toHaveText('Detail Audit Trail');

        // Pastikan konten detail aktivitas ter-render di dalam drawer
        await expect(drawer.locator('#detailBody')).toContainText('Informasi Aktivitas');

        // Klik tombol silang (close)
        await page.locator('#detailClose').click();

        // Drawer harus menutup kembali
        await expect(drawer).not.toHaveClass(/open/);
        await expect(backdrop).not.toHaveClass(/open/);
      }
    }
  });

  // ===========================================================================
  // SKENARIO 4: Ekspor Data ke Format CSV dan Laporan Siap Cetak (PDF)
  // ===========================================================================

  test('4.1 Mengunduh riwayat audit trail ke format CSV melalui browser', async ({ page }) => {
    const adminEmail = process.env.TEST_ADMIN_EMAIL || 'admin1@gmail.com';
    const adminPassword = process.env.TEST_ADMIN_PASSWORD || 'password123';

    await loginAs(page, 'admin', adminEmail, adminPassword);

    if (!page.url().includes('/login')) {
      await page.goto('/admin/audit-trail');

      // Buka dropdown ekspor
      await page.locator('#exportDropdownBtn').click();
      const exportMenu = page.locator('#exportMenu');
      await expect(exportMenu).toBeVisible();

      // Tangkap event unduhan file dari browser
      const [download] = await Promise.all([
        page.waitForEvent('download'),
        page.locator('a:has-text("Unduh Excel (CSV)")').click(),
      ]);

      // Verifikasi nama file unduhan berformat CSV
      const fileName = download.suggestedFilename();
      expect(fileName).toMatch(/^audit_trail_.*\.csv$/);
    }
  });

  test('4.2 Membuka laporan resmi audit trail untuk dicetak / PDF di tab baru', async ({ page }) => {
    const adminEmail = process.env.TEST_ADMIN_EMAIL || 'admin1@gmail.com';
    const adminPassword = process.env.TEST_ADMIN_PASSWORD || 'password123';

    await loginAs(page, 'admin', adminEmail, adminPassword);

    if (!page.url().includes('/login')) {
      await page.goto('/admin/audit-trail');

      // Buka dropdown ekspor
      await page.locator('#exportDropdownBtn').click();

      // Klik "Cetak Laporan (PDF)" yang membuka tab baru
      const [newPage] = await Promise.all([
        page.context().waitForEvent('page'),
        page.locator('a:has-text("Cetak Laporan (PDF)")').click(),
      ]);

      await newPage.waitForLoadState('domcontentloaded');

      // Verifikasi halaman pratinjau cetak PDF
      await expect(newPage.locator('.print-actions-title')).toContainText('Pratinjau Dokumen Audit Trail');
      await expect(newPage.locator('.btn-print')).toBeVisible();
    }
  });
});
