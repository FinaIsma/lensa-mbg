import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/auth/login.css',
                'resources/css/auth/register.css',
                'resources/css/admin/dashboard.css',
                'resources/css/admin/sppg-management.css',
                'resources/css/admin/audit-trail.css',
                'resources/css/admin/sidebar-navbar.css',
                'resources/css/KepalaSPPG/dashboard.css',
                'resources/css/KepalaSPPG/sidebar-navbar.css',
                'resources/css/KepalaSPPG/correction-request.css',
                'resources/css/KepalaSPPG/staff-management.css',
                'resources/css/KepalaSPPG/profile-modals.css',
                'resources/css/staff/sidebar-navbar.css',
                'resources/css/staff/menu-resep.css',
                'resources/css/staff/purchases.css',
                'resources/css/staff/riwayat.css',
                'resources/css/staff/lots.css',
                'resources/css/staff/lots-print.css',
                'resources/css/staff/pemasok.css',
                'resources/css/staff/bahan.css',
                'resources/css/staff/tujuan.css',
                'resources/css/staff/produksi.css',
                'resources/css/staff/hasil.css',
                'resources/css/staff/limbah.css',
                'resources/css/staff/pengiriman.css',
                'resources/js/app.js',
                'resources/js/auth/login.js',
                'resources/js/auth/register.js',
                'resources/js/admin/dashboard.js',
                'resources/js/admin/sppg-management.js',
                'resources/js/admin/audit-trail.js',
                'resources/js/KepalaSPPG/dashboard.js',
                'resources/js/KepalaSPPG/correction-request.js',
                'resources/js/KepalaSPPG/staff-management.js',
                'resources/js/KepalaSPPG/profile-modals.js',
                'resources/js/staff/menu-resep.js',
                'resources/js/staff/riwayat.js',
                'resources/js/staff/lots.js',
                'resources/js/staff/lots-print.js',
                'resources/js/staff/pemasok.js',
                'resources/js/staff/bahan.js',
                'resources/js/staff/hasil.js',
                'resources/js/staff/limbah.js',
                'resources/js/staff/tujuan.js',
                'resources/js/staff/pengiriman.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
