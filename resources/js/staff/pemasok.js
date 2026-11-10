/**
 * Interaction, Drawer Detail, and Modal Management for Modul Data Pemasok - Staff SPPG
 */

document.addEventListener('DOMContentLoaded', function () {
    const drawerDetail = document.getElementById('drawerDetailPemasok');
    const drawerBackdrop = document.getElementById('pemasokDrawerBackdrop');
    const modalTambah = document.getElementById('modalTambahPemasok');
    const modalEdit = document.getElementById('modalEditPemasok');
    let currentDetailSupplier = null;
    let activeDetailRequestId = 0;

    function syncBodyScrollLock() {
        const hasOpen = document.querySelector('#drawerDetailPemasok.open, #modalTambahPemasok.show, #modalEditPemasok.show');
        document.body.style.overflow = hasOpen ? 'hidden' : '';
    }

    // ==========================================
    // 1. PANEL DETAIL (SLIDE-OVER DRAWER)
    // ==========================================
    window.openDetailPanelPemasok = async function (id) {
        if (!drawerDetail) return;

        const requestId = ++activeDetailRequestId;
        currentDetailSupplier = null;

        // Reset display while loading
        const namaEl = document.getElementById('detail_nama');
        const chipEl = document.getElementById('detail_status_chip');
        const createdEl = document.getElementById('detail_created_at');
        const kontakEl = document.getElementById('detail_kontak');
        const alamatEl = document.getElementById('detail_alamat');
        const jumlahPoEl = document.getElementById('detail_jumlah_pembelian');
        const totalNilaiEl = document.getElementById('detail_total_nilai');
        const poCountEl = document.getElementById('detail_po_count');
        const poTbodyEl = document.getElementById('detail_po_tbody');

        if (namaEl) namaEl.textContent = 'Memuat...';
        if (createdEl) createdEl.textContent = '-';
        if (kontakEl) kontakEl.textContent = '-';
        if (alamatEl) alamatEl.textContent = '-';
        if (jumlahPoEl) jumlahPoEl.textContent = '0 PO';
        if (totalNilaiEl) totalNilaiEl.textContent = 'Rp 0';
        if (poCountEl) poCountEl.textContent = '-';
        if (chipEl) {
            chipEl.innerHTML = `
                <span class="badge-status badge-draft">
                    <span class="status-dot"></span>
                    Memuat...
                </span>
            `;
        }
        if (poTbodyEl) {
            poTbodyEl.innerHTML = '<tr><td colspan="4" class="drawer-po-empty">Mengambil data dari server...</td></tr>';
        }

        drawerDetail.classList.add('open');
        if (drawerBackdrop) drawerBackdrop.classList.add('open');
        syncBodyScrollLock();

        try {
            const res = await fetch(`/staff/pemasok/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!res.ok) throw new Error('Gagal mengambil data detail pemasok');

            const result = await res.json();
            if (requestId !== activeDetailRequestId) return;

            const data = result.data;
            currentDetailSupplier = data;

            if (namaEl) namaEl.textContent = data.nama || '-';
            if (createdEl) createdEl.textContent = data.created_at || '-';
            if (kontakEl) kontakEl.textContent = data.kontak || '-';
            if (alamatEl) alamatEl.textContent = data.alamat || '-';
            if (jumlahPoEl) jumlahPoEl.textContent = `${data.jumlah_pembelian ?? 0} PO`;
            if (totalNilaiEl) totalNilaiEl.textContent = data.formatted_total_nilai || 'Rp 0';

            // Status Chip: Badge hijau Aktif / abu-abu Nonaktif
            if (chipEl) {
                const isActive = (data.status || '').toLowerCase() === 'active';
                chipEl.innerHTML = `
                    <span class="badge-status ${isActive ? 'badge-received' : 'badge-draft'}">
                        <span class="status-dot"></span>
                        ${isActive ? 'Aktif' : 'Nonaktif'}
                    </span>
                `;
            }

            // Render Tabel Riwayat PO (nomor sebagai tautan ke Pembelian, tanggal, status, total)
            if (poTbodyEl) {
                const purchases = data.purchases || [];
                if (poCountEl) poCountEl.textContent = `${purchases.length} transaksi`;

                if (purchases.length === 0) {
                    poTbodyEl.innerHTML = `
                        <tr>
                            <td colspan="4" class="drawer-po-empty">
                                Belum ada riwayat PO non-draft untuk pemasok ini.
                            </td>
                        </tr>
                    `;
                } else {
                    poTbodyEl.innerHTML = purchases.map((po) => {
                        const targetUrl = po.purchase_url || `/staff/pembelian?detail_id=${po.id}`;
                        const badgeClass = po.status_badge_class || 'badge-ordered';
                        return `
                            <tr>
                                <td>
                                    <a href="${escapeHtml(targetUrl)}" class="drawer-po-link" title="Buka Detail Pembelian">
                                        ${escapeHtml(po.purchase_no)}
                                    </a>
                                </td>
                                <td style="color: #475569; white-space: nowrap;">
                                    ${escapeHtml(po.purchase_date)}
                                </td>
                                <td>
                                    <span class="badge-status ${escapeHtml(badgeClass)}">
                                        <span class="status-dot"></span>
                                        ${escapeHtml(po.status_label || po.status)}
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #071E49; white-space: nowrap;">
                                    ${escapeHtml(po.formatted_total_cost)}
                                </td>
                            </tr>
                        `;
                    }).join('');
                }
            }
        } catch (err) {
            if (requestId !== activeDetailRequestId) return;
            console.error(err);
            if (namaEl) namaEl.textContent = 'Gagal memuat';
            if (poTbodyEl) {
                poTbodyEl.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px 12px; color: #DC2626; font-size: 13px;">Gagal memuat riwayat pembelian.</td></tr>';
            }
        }
    };

    window.openDetailPanel = window.openDetailPanelPemasok;

    window.closeDetailPanelPemasok = function () {
        activeDetailRequestId++;
        if (drawerDetail) drawerDetail.classList.remove('open');
        if (drawerBackdrop) drawerBackdrop.classList.remove('open');
        syncBodyScrollLock();
    };

    // Tombol Ubah di dalam Panel Detail
    const detailBtnEdit = document.getElementById('detail_btn_edit');
    if (detailBtnEdit) {
        detailBtnEdit.addEventListener('click', function () {
            if (currentDetailSupplier) {
                const supplierToEdit = currentDetailSupplier;
                closeDetailPanelPemasok();
                openModalEditPemasok(supplierToEdit);
            }
        });
    }

    // ==========================================
    // 2. MODAL TAMBAH & EDIT PEMASOK
    // ==========================================
    window.openModalTambahPemasok = function () {
        if (!modalTambah) return;
        const form = document.getElementById('formTambahPemasok');
        if (form) form.reset();
        clearModalErrors(modalTambah);
        modalTambah.classList.add('show');
        syncBodyScrollLock();
    };

    window.closeModalTambahPemasok = function () {
        if (!modalTambah) return;
        modalTambah.classList.remove('show');
        syncBodyScrollLock();
    };

    window.openModalEditPemasok = function (supplier) {
        if (!modalEdit || !supplier) return;
        clearModalErrors(modalEdit);

        const form = document.getElementById('formEditPemasok');
        if (form) {
            form.action = `/staff/pemasok/${supplier.id}`;
            const idInput = document.getElementById('edit_pemasok_id');
            const namaInput = document.getElementById('edit_nama');
            const kontakInput = document.getElementById('edit_kontak');
            const alamatInput = document.getElementById('edit_alamat');
            const statusSelect = document.getElementById('edit_status');

            if (idInput) idInput.value = supplier.id;
            if (namaInput) namaInput.value = supplier.nama || '';
            if (kontakInput) kontakInput.value = supplier.kontak || '';
            if (alamatInput) alamatInput.value = supplier.alamat || '';
            if (statusSelect) statusSelect.value = supplier.status || 'active';
        }

        modalEdit.classList.add('show');
        syncBodyScrollLock();
    };

    window.closeModalEditPemasok = function () {
        if (!modalEdit) return;
        modalEdit.classList.remove('show');
        syncBodyScrollLock();
    };

    // Close on backdrop click
    [modalTambah, modalEdit].forEach((modal) => {
        if (!modal) return;
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('show');
                syncBodyScrollLock();
            }
        });
    });

    // Close open drawer or modal on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (modalEdit && modalEdit.classList.contains('show')) {
                closeModalEditPemasok();
                return;
            }
            if (modalTambah && modalTambah.classList.contains('show')) {
                closeModalTambahPemasok();
                return;
            }
            if (drawerDetail && drawerDetail.classList.contains('open')) {
                closeDetailPanelPemasok();
            }
        }
    });

    // Helper: Clear validation errors inside modal
    function clearModalErrors(modal) {
        if (!modal) return;
        modal.querySelectorAll('.field-error-msg').forEach((el) => el.remove());
        modal.querySelectorAll('.pemasok-form-input, .pemasok-form-textarea, .pemasok-select').forEach((el) => {
            el.style.borderColor = '#CBD5E1';
        });
    }

    // Helper: Escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
