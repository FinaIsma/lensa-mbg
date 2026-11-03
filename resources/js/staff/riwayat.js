/**
 * Riwayat Stok Bahan - Staff SPPG
 * Mengatur interaksi filter dan auto-submit pada halaman riwayat.
 */
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.querySelector('.filter-bar-form');
    if (!filterForm) return;

    // Auto-submit saat nilai dropdown filter berubah
    const autoSubmitSelects = filterForm.querySelectorAll('.js-auto-submit');
    autoSubmitSelects.forEach(function (selectEl) {
        selectEl.addEventListener('change', function () {
            filterForm.submit();
        });
    });
});

