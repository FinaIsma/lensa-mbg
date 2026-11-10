/**
 * Interaction and Modal Management for Modul Data Bahan Baku - Staff SPPG
 */

document.addEventListener('DOMContentLoaded', function () {
    const modalTambah = document.getElementById('modalTambahBahan');
    const modalEdit = document.getElementById('modalEditBahan');
    const modalToggle = document.getElementById('modalToggleStatusBahan');
    const drawerDetail = document.getElementById('drawerDetailBahan');
    const drawerBackdrop = document.getElementById('bahanDrawerBackdrop');

    let currentDetailMaterial = null;
    let activeDetailRequestId = 0;

    function syncBodyScrollLock() {
        const hasOpen = document.querySelector('#drawerDetailBahan.open, #modalTambahBahan.show, #modalEditBahan.show, #modalToggleStatusBahan.show');
        document.body.style.overflow = hasOpen ? 'hidden' : '';
    }

    // ==========================================
    // 1. PANEL DETAIL BAHAN (SLIDE-OVER DRAWER)
    // ==========================================
    window.openDetailPanelBahan = async function (id) {
        if (!drawerDetail) return;

        const requestId = ++activeDetailRequestId;
        currentDetailMaterial = null;

        // Reset display
        const kodeEl = document.getElementById('detail_bahan_kode');
        const namaEl = document.getElementById('detail_bahan_nama');
        const kategoriEl = document.getElementById('detail_bahan_kategori');
        const satuanEl = document.getElementById('detail_bahan_satuan');
        const aktifChipEl = document.getElementById('detail_bahan_aktif_chip');
        const indChipEl = document.getElementById('detail_bahan_indikator_chip');
        const totalStokEl = document.getElementById('detail_bahan_total_stok');
        const minStockEl = document.getElementById('detail_bahan_min_stock');
        const lotCountEl = document.getElementById('detail_bahan_lot_count');
        const lotTbody = document.getElementById('detail_bahan_lot_tbody');

        if (kodeEl) kodeEl.textContent = '...';
        if (namaEl) namaEl.textContent = 'Memuat data...';
        if (kategoriEl) kategoriEl.textContent = '-';
        if (satuanEl) satuanEl.textContent = '-';
        if (totalStokEl) totalStokEl.textContent = '-';
        if (minStockEl) minStockEl.textContent = '-';
        if (lotCountEl) lotCountEl.textContent = '-';
        if (aktifChipEl) {
            aktifChipEl.innerHTML = '<span class="badge-status badge-draft"><span class="status-dot"></span>Memuat...</span>';
        }
        if (indChipEl) indChipEl.innerHTML = '';
        if (lotTbody) {
            lotTbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #94A3B8; padding: 24px 0;">Memuat riwayat lot...</td></tr>';
        }

        drawerDetail.classList.add('open');
        if (drawerBackdrop) drawerBackdrop.classList.add('open');
        syncBodyScrollLock();

        try {
            const res = await fetch(`/staff/bahan/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) throw new Error('Gagal memuat detail bahan');
            const resJson = await res.json();
            if (requestId !== activeDetailRequestId) return;

            const data = resJson.data;
            currentDetailMaterial = data;

            if (kodeEl) kodeEl.textContent = data.kode || '-';
            if (namaEl) namaEl.textContent = data.nama || '-';
            if (kategoriEl) kategoriEl.textContent = data.kategori || '-';
            if (satuanEl) satuanEl.textContent = data.satuan || '-';

            if (aktifChipEl) {
                aktifChipEl.innerHTML = data.is_active
                    ? '<span class="badge-status badge-received"><span class="status-dot"></span>Aktif</span>'
                    : '<span class="badge-status badge-draft"><span class="status-dot"></span>Nonaktif</span>';
            }

            if (indChipEl) {
                indChipEl.innerHTML = data.indikator === 'Aman'
                    ? '<span class="badge-status badge-safe"><span class="status-dot"></span>Aman</span>'
                    : '<span class="badge-status badge-cancelled"><span class="status-dot"></span>Di bawah minimum</span>';
            }

            if (totalStokEl) totalStokEl.textContent = data.formatted_total_stok || '0';
            if (minStockEl) minStockEl.textContent = data.formatted_min_stock || '0';

            // Render Lots
            const lots = data.lots || [];
            if (lotCountEl) lotCountEl.textContent = `${lots.length} Lot Tercatat`;

            if (lotTbody) {
                if (lots.length === 0) {
                    lotTbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #94A3B8; padding: 24px 0;">Belum ada lot persediaan untuk bahan ini.</td></tr>';
                } else {
                    lotTbody.innerHTML = lots.map((lot) => {
                        const statusClass = lot.status === 'available' ? 'chip-aman' : 'chip-nonaktif';
                        const statusLabel = lot.status === 'available'
                            ? 'Tersedia'
                            : (lot.status === 'depleted' ? 'Habis' : (lot.status === 'voided' ? 'Dibatalkan' : (lot.status || '-')));
                        const expiredBadge = lot.is_expired
                            ? '<span style="color: #DC2626; font-size: 10.5px; font-weight: 600;">(Kedaluwarsa)</span>'
                            : '';

                        return `
                            <tr>
                                <td style="font-weight: 600; color: #071E49;">${escapeHtml(lot.lot_number || '-')}</td>
                                <td style="color: #475569;">${escapeHtml(lot.supplier_nama || '-')}</td>
                                <td style="color: #64748B;">${escapeHtml(lot.received_date || '-')}</td>
                                <td style="color: ${lot.is_expired ? '#DC2626' : '#64748B'};">
                                    ${escapeHtml(lot.expiry_date || '-')} ${expiredBadge}
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #0F172A;">
                                    ${escapeHtml(`${lot.current_qty} ${data.satuan || ''}`.trim())}
                                </td>
                                <td style="text-align: center;">
                                    <span class="chip-status ${statusClass}" style="padding: 2px 8px; font-size: 11px;">
                                        ${escapeHtml(statusLabel)}
                                    </span>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }
            }
        } catch (e) {
            if (requestId !== activeDetailRequestId) return;
            console.error(e);
            if (namaEl) namaEl.textContent = 'Gagal memuat';
            if (lotTbody) {
                lotTbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #DC2626; padding: 20px 0;">Gagal memuat data dari server.</td></tr>';
            }
        }
    };

    window.closeDetailPanelBahan = function () {
        activeDetailRequestId++;
        if (drawerDetail) drawerDetail.classList.remove('open');
        if (drawerBackdrop) drawerBackdrop.classList.remove('open');
        syncBodyScrollLock();
    };

    window.openModalEditBahanFromDetail = function () {
        if (currentDetailMaterial) {
            const materialToEdit = currentDetailMaterial;
            closeDetailPanelBahan();
            openModalEditBahan(materialToEdit);
        }
    };

    // ==========================================
    // 2. MODAL TAMBAH BAHAN
    // ==========================================
    window.openModalTambahBahan = function () {
        const modal = document.getElementById('modalTambahBahan');
        if (!modal) return;
        const form = document.getElementById('formTambahBahan');
        if (form) form.reset();
        clearModalErrors(modal);
        modal.classList.add('show');
        syncBodyScrollLock();
    };

    window.closeModalTambahBahan = function () {
        const modal = document.getElementById('modalTambahBahan');
        if (!modal) return;
        modal.classList.remove('show');
        syncBodyScrollLock();
    };

    // ==========================================
    // 3. MODAL EDIT BAHAN
    // ==========================================
    window.openModalEditBahan = async function (materialOrId) {
        const modal = document.getElementById('modalEditBahan');
        if (!modal || !materialOrId) return;
        clearModalErrors(modal);

        let data = null;

        // Jika berupa object
        if (typeof materialOrId === 'object' && materialOrId !== null) {
            data = materialOrId;
        } else {
            // Cek data attributes di tombol terlebih dahulu untuk performa cepat tanpa loading
            const btn = document.querySelector(`.btn-bahan-edit[data-id="${materialOrId}"]`);
            if (btn && btn.dataset.nama !== undefined) {
                data = {
                    id: materialOrId,
                    kode: btn.dataset.kode,
                    nama: btn.dataset.nama,
                    kategori: btn.dataset.kategori,
                    satuan: btn.dataset.satuan,
                    min_stock: btn.dataset.minStock,
                    is_active: btn.dataset.isActive === '1',
                };
            } else {
                // Fetch dari server
                try {
                    const res = await fetch(`/staff/bahan/${materialOrId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    if (!res.ok) throw new Error('Gagal mengambil data bahan baku');
                    const json = await res.json();
                    data = json.data;
                } catch (e) {
                    console.error(e);
                    alert('Gagal mengambil data bahan baku dari server.');
                    return;
                }
            }
        }

        const form = document.getElementById('formEditBahan');
        if (form && data) {
            form.action = `/staff/bahan/${data.id}`;

            const idInput = document.getElementById('edit_bahan_id');
            const kodeInput = document.getElementById('edit_kode');
            const namaInput = document.getElementById('edit_nama');
            const kategoriSelect = document.getElementById('edit_kategori');
            const satuanSelect = document.getElementById('edit_satuan');
            const minStockInput = document.getElementById('edit_min_stock');
            const statusSelect = document.getElementById('edit_status');

            if (idInput) idInput.value = data.id;
            if (kodeInput) kodeInput.value = data.kode || '';
            if (namaInput) namaInput.value = data.nama || '';
            if (kategoriSelect) kategoriSelect.value = data.kategori || '';
            if (satuanSelect) satuanSelect.value = data.satuan || 'KG';
            if (minStockInput) minStockInput.value = data.min_stock ?? 0;
            if (statusSelect) statusSelect.value = data.is_active ? '1' : '0';
        }

        modal.classList.add('show');
        syncBodyScrollLock();
    };

    window.closeModalEditBahan = function () {
        const modal = document.getElementById('modalEditBahan');
        if (!modal) return;
        modal.classList.remove('show');
        syncBodyScrollLock();
    };

    // ==========================================
    // 4. MODAL TOGGLE STATUS (NONAKTIFKAN / AKTIFKAN)
    // ==========================================
    window.confirmToggleStatusBahan = function (id, nama, isActive) {
        const modal = document.getElementById('modalToggleStatusBahan');
        if (!modal) return;

        const form = document.getElementById('formToggleStatusBahan');
        const titleEl = document.getElementById('toggleStatusModalTitle');
        const subEl = document.getElementById('toggleStatusModalSubtitle');
        const msgEl = document.getElementById('toggleStatusMessage');
        const submitBtn = document.getElementById('btnToggleStatusSubmit');
        const iconBox = document.getElementById('toggleStatusIconBox');

        if (form) form.action = `/staff/bahan/${id}/toggle-status`;

        if (isActive) {
            if (titleEl) titleEl.textContent = 'Nonaktifkan Bahan?';
            if (subEl) subEl.textContent = 'Konfirmasi penonaktifan bahan';
            if (msgEl) msgEl.textContent = `Apakah Anda yakin ingin menonaktifkan bahan baku "${nama}"? Bahan yang dinonaktifkan tidak akan muncul pada daftar pemilihan belanja (PO).`;
            if (submitBtn) {
                submitBtn.textContent = 'Ya, Nonaktifkan';
                submitBtn.style.backgroundColor = '#DC2626';
            }
            if (iconBox) {
                iconBox.className = 'bahan-icon-box icon-red';
                iconBox.innerHTML = `
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                    </svg>
                `;
            }
        } else {
            if (titleEl) titleEl.textContent = 'Aktifkan Kembali Bahan?';
            if (subEl) subEl.textContent = 'Konfirmasi pengaktifan bahan';
            if (msgEl) msgEl.textContent = `Apakah Anda yakin ingin mengaktifkan kembali bahan baku "${nama}"? Bahan akan kembali aktif dan dapat dipilih dalam perencanaan resep dan pengadaan.`;
            if (submitBtn) {
                submitBtn.textContent = 'Ya, Aktifkan Kembali';
                submitBtn.style.backgroundColor = '#16A34A';
            }
            if (iconBox) {
                iconBox.className = 'bahan-icon-box icon-green';
                iconBox.innerHTML = `
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                `;
            }
        }

        modal.classList.add('show');
        syncBodyScrollLock();
    };

    window.closeModalToggleStatusBahan = function () {
        const modal = document.getElementById('modalToggleStatusBahan');
        if (!modal) return;
        modal.classList.remove('show');
        syncBodyScrollLock();
    };

    // Close modal on backdrop click
    [modalTambah, modalEdit, modalToggle].forEach((modal) => {
        if (!modal) return;
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('show');
                syncBodyScrollLock();
            }
        });
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            let closedModal = false;
            [modalTambah, modalEdit, modalToggle].forEach((modal) => {
                if (modal && modal.classList.contains('show')) {
                    modal.classList.remove('show');
                    closedModal = true;
                }
            });
            if (closedModal) {
                syncBodyScrollLock();
                return;
            }
            if (drawerDetail && drawerDetail.classList.contains('open')) {
                closeDetailPanelBahan();
            }
        }
    });

    // Helper: Clear validation errors inside modal
    function clearModalErrors(modal) {
        if (!modal) return;
        modal.querySelectorAll('.field-error-msg').forEach((el) => el.remove());
        modal.querySelectorAll('.bahan-form-input, .bahan-select').forEach((el) => {
            el.style.borderColor = '#CBD5E1';
        });
    }

    // Helper: Escape HTML
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }
});
