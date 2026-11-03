/**
 * Riwayat Stok Bahan - Staff SPPG
 * Mengatur interaksi filter dan slide-over right drawer detail mutasi stok.
 */
document.addEventListener('DOMContentLoaded', function () {
    // 1. Auto-submit filter form
    const filterForm = document.querySelector('.filter-bar-form');
    if (filterForm) {
        const autoSubmitSelects = filterForm.querySelectorAll('.js-auto-submit');
        autoSubmitSelects.forEach(function (selectEl) {
            selectEl.addEventListener('change', function () {
                filterForm.submit();
            });
        });
    }

    // 2. Right Drawer Elements
    const drawer = document.getElementById('movementDetailDrawer');
    const backdrop = document.getElementById('movementDrawerBackdrop');

    function openMovementDrawer(data) {
        if (!drawer || !backdrop) return;

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val || '-';
        };

        setVal('drawerWaktu', data.waktu);
        setVal('drawerJenisMutasi', data.movement_type_label);

        const jmlEl = document.getElementById('drawerJumlah');
        if (jmlEl) {
            jmlEl.textContent = data.qty_formatted || '-';
            jmlEl.className = 'detail-item-value riwayat-cell-qty ' +
                (data.direction === 'IN' ? 'riwayat-qty-in' : 'riwayat-qty-out');
        }

        setVal('drawerPetugas', data.petugas);
        setVal('drawerArahLengkap', data.direction === 'IN' ? 'Barang Masuk (IN)' : 'Barang Keluar (OUT)');

        const badgeEl = document.getElementById('drawerDirectionBadge');
        if (badgeEl) {
            badgeEl.className = 'badge-status ' + (data.direction_class || '');
        }
        setVal('drawerDirectionText', data.direction_label);

        setVal('drawerNamaBahan', data.material_nama);
        setVal('drawerKategoriBahan', (data.material_kategori || '-') + ' (' + (data.material_satuan || '') + ')');
        setVal('drawerBarcodeLot', data.barcode);
        setVal('drawerSisaStok', data.current_qty);
        setVal('drawerExpiryDate', data.expiry_date);
        setVal('drawerSupplier', data.supplier);

        setVal('drawerDocType', data.doc_label);
        setVal('drawerDocCode', data.doc_code);

        const docLink = document.getElementById('drawerDocLink');
        if (docLink) {
            if (data.doc_url) {
                docLink.href = data.doc_url;
                docLink.style.display = 'inline-flex';
            } else {
                docLink.style.display = 'none';
            }
        }

        drawer.classList.add('open');
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeMovementDrawer() {
        if (drawer) drawer.classList.remove('open');
        if (backdrop) backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    // Event delegation: Klik tombol mata detail di tabel & tombol tutup
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-btn-movement-detail');
        if (btn) {
            e.preventDefault();
            try {
                const raw = btn.getAttribute('data-movement');
                if (raw) {
                    const data = JSON.parse(raw);
                    openMovementDrawer(data);
                }
            } catch (err) {
                console.error('Gagal parsing data mutasi:', err);
            }
            return;
        }

        if (e.target.closest('.js-btn-close-drawer') || e.target === backdrop) {
            e.preventDefault();
            closeMovementDrawer();
        }
    });

    // Tutup drawer dengan tombol Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeMovementDrawer();
        }
    });
});
