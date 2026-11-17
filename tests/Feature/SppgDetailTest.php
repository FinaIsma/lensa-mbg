<?php

namespace Tests\Feature;

use Tests\TestCase;

class SppgDetailTest extends TestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $app['config']->set('database.default', 'pgsql');
        $app['config']->set('database.connections.pgsql.database', 'postgres');

        return $app;
    }

    public function test_halaman_detail_sppg_dapat_diakses_dan_menampilkan_data_database(): void
    {
        $response = $this->get(route('public.sppg', 5));

        $response->assertStatus(200);
        $response->assertSee('SPPG Arjosari');
        $response->assertSee('SPPG-2026-005');
        // Kepala SPPG dari DB (User::nama)
        $response->assertSee('Budi Hartanto');
        // Ahli gizi dari DB
        $response->assertSee('Riska Anggraini');
        // Sekolah binaan dari DB
        $response->assertSee('SMP NEGERI 16 MALANG');
        $response->assertSee('SDN ARJOSARI 3');
        $response->assertSee('SMKN 8 MALANG');

        // Tidak ada lagi teks CCTV
        $response->assertDontSee('CCTV');

        // Status badge di samping nama sppg cukup 'Aktif' bukan 'Dapur Aktif'
        $response->assertDontSee('Dapur Aktif');
        $response->assertSee('Aktif');
    }

    public function test_gambar_maskot_tanpa_shadow_dan_menggunakan_hover_zoom(): void
    {
        $response = $this->get(route('public.sppg', 5));

        $response->assertStatus(200);
        // Gambar maskot ada
        $response->assertSee('makan.png');
        $response->assertSee('yay.png');

        // Gausah dikasi shadow atau ngambang, cukup zoom in pas cursor kesitu
        $response->assertDontSee('animate-mascot-float');
        $response->assertDontSee('animate-mascot-shadow');
        $response->assertSee('hover:scale-');

        // Gambar dipotong 1:1 (aspect-square) dan container makan dipersempit
        $response->assertSee('aspect-square');

        // Kata-kata di bawah maskot telah dihapus
        $response->assertDontSee('Santap Bergizi Siap Saji');
        $response->assertDontSee('Tim Siaga 100%');
    }

    public function test_aktivitas_proses_memiliki_gambar_dan_fitur_membuka_ke_bawah(): void
    {
        $response = $this->get(route('public.sppg', 5));

        $response->assertStatus(200);
        // Memiliki fungsi toggle akordion ke bawah
        $response->assertSee('toggleTahapDetail');
        $response->assertSee('tahap-drawer-container');
        $response->assertSee('Tahap 01');
        $response->assertSee('Tahap 02');
        $response->assertSee('Tahap 03');
        $response->assertSee('Tahap 04');
        $response->assertSee('Bahan Segar Datang');
        $response->assertSee('Masak Matang');
        $response->assertSee('Cek Gizi');
        $response->assertSee('Meluncur ke Sekolah');

        // Gambar dokumentasi di tiap rincian tahap (preview interaktif dan modal yang bisa digeser)
        $response->assertSee('Dokumentasi Bahan Masuk');
        $response->assertSee('Dokumentasi Pengolahan');
        $response->assertSee('Dokumentasi Uji Gizi');
        $response->assertSee('Dokumentasi Logistik');
        $response->assertSee('photo-gallery-modal');
        $response->assertSee('openPhotoModal');
        $response->assertSee('Buka Galeri &amp; Geser Foto', false);
        $response->assertSee('prevModalPhoto');
        $response->assertSee('nextModalPhoto');
    }

    public function test_halaman_tetap_dapat_diakses_dengan_kode_string(): void
    {
        $response = $this->get(route('public.sppg', 'SPPG-2026-005'));

        $response->assertStatus(200);
        $response->assertSee('SPPG Arjosari');
    }
}
