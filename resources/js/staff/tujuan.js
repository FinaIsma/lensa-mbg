/**
 * Interaction, Drawer Detail, and Modal Management for Modul Data Master Tujuan - Staff SPPG
 */

document.addEventListener('DOMContentLoaded', function () {
    const drawerDetail = document.getElementById('drawerDetailTujuan');
    const modalTambah = document.getElementById('modalTambahTujuan');
    const modalEdit = document.getElementById('modalEditTujuan');
    const modalDelete = document.getElementById('modalDeleteTujuan');
    let currentDetailTujuan = null;

    // Helper: Escape HTML to avoid XSS
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ==========================================
    // 1. PANEL DETAIL (SLIDE-OVER DRAWER)
    // ==========================================
    window.openDetailTujuan = async function (id) {
        if (!drawerDetail) return;

        // Reset elements to loading state
        const namaEl = document.getElementById('detail_nama');
        const tipeChipEl = document.getElementById('detail_tipe_chip');
        const createdEl = document.getElementById('detail_created_at');
        const alamatEl = document.getElementById('detail_alamat');
        const penerimaEl = document.getElementById('detail_jumlah_penerima');
        const pengirimanEl = document.getElementById('detail_jumlah_pengiriman');
        const porsiEl = document.getElementById('detail_total_porsi');
        const distCountEl = document.getElementById('detail_dist_count');
        const distTbodyEl = document.getElementById('detail_dist_tbody');

        if (namaEl) namaEl.textContent = 'Memuat...';
        if (distTbodyEl) {
            distTbodyEl.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 24px 0; color: #94A3B8; font-size: 13px;">Mengambil data dari server...</td></tr>';
        }

        drawerDetail.classList.add('show');
        document.body.style.overflow = 'hidden';

        try {
            const res = await fetch(`/staff/tujuan/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!res.ok) throw new Error('Gagal mengambil data detail tujuan');

            const result = await res.json();
            const data = result.data;
            currentDetailTujuan = data;

            if (namaEl) namaEl.textContent = data.nama || '-';
            if (createdEl) createdEl.textContent = `Terdaftar: ${data.created_at || '-'}`;
            if (alamatEl) alamatEl.textContent = data.alamat || '-';
            if (penerimaEl) penerimaEl.textContent = `${Number(data.jumlah_penerima || 0).toLocaleString('id-ID')} Orang / Porsi`;
            if (pengirimanEl) pengirimanEl.textContent = `${Number(data.jumlah_pengiriman || 0).toLocaleString('id-ID')} Kali`;
            if (porsiEl) porsiEl.textContent = `${Number(data.total_porsi_terkirim || 0).toLocaleString('id-ID')} Porsi`;

            // Badge Tipe
            if (tipeChipEl) {
                const tipeLower = (data.tipe || 'lainnya').toLowerCase();
                let badgeClass = 'badge-tipe-lainnya';
                if (tipeLower === 'sekolah') badgeClass = 'badge-tipe-sekolah';
                else if (tipeLower === 'posyandu') badgeClass = 'badge-tipe-posyandu';

                tipeChipEl.innerHTML = `
                    <span class="badge-tipe ${badgeClass}">
                        ${escapeHtml(data.tipe_label || data.tipe)}
                    </span>
                `;
            }

            // Render Riwayat Distribusi
            if (distTbodyEl) {
                const distributions = data.distributions || [];
                if (distCountEl) distCountEl.textContent = `${distributions.length} riwayat`;

                if (distributions.length === 0) {
                    distTbodyEl.innerHTML = `
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 24px 0; color: #94A3B8; font-size: 13px;">
                                Belum ada riwayat pengiriman untuk tujuan ini.
                            </td>
                        </tr>
                    `;
                } else {
                    distTbodyEl.innerHTML = distributions.map((dist) => {
                        let statusColor = '#64748B';
                        let statusBg = '#F1F5F9';
                        if (dist.status === 'received') {
                            statusColor = '#047857';
                            statusBg = '#ECFDF5';
                        } else if (dist.status === 'sent') {
                            statusColor = '#1D4ED8';
                            statusBg = '#EFF6FF';
                        } else if (dist.status === 'planned') {
                            statusColor = '#B45309';
                            statusBg = '#FEF3C7';
                        } else if (dist.status === 'cancelled') {
                            statusColor = '#B91C1C';
                            statusBg = '#FEF2F2';
                        }

                        return `
                            <tr>
                                <td style="font-weight: 600; color: #071E49;">
                                    ${escapeHtml(dist.batch_code)}
                                </td>
                                <td style="color: #334155;">
                                    ${escapeHtml(dist.menu_nama)}
                                </td>
                                <td style="font-weight: 600; color: #0F172A; text-align: center;">
                                    ${Number(dist.portions_sent).toLocaleString('id-ID')}
                                </td>
                                <td style="color: #64748B; font-size: 12px; white-space: nowrap;">
                                    ${escapeHtml(dist.distributed_at)}
                                </td>
                                <td>
                                    <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 600; background-color: ${statusBg}; color: ${statusColor};">
                                        ${escapeHtml(dist.status_label)}
                                    </span>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }
            }

            // Hook edit button from drawer
            const btnEditFromDrawer = document.getElementById('detail_btn_edit');
            if (btnEditFromDrawer) {
                btnEditFromDrawer.onclick = function () {
                    closeDetailPanel();
                    openModalEdit(data.id, data.nama, data.tipe, data.alamat, data.jumlah_penerima);
                };
            }
        } catch (err) {
            console.error('Error openDetailTujuan:', err);
            if (namaEl) namaEl.textContent = 'Gagal memuat';
            if (distTbodyEl) {
                distTbodyEl.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #EF4444; padding: 16px;">${escapeHtml(err.message)}</td></tr>`;
            }
        }
    };

    window.closeDetailPanel = function () {
        if (!drawerDetail) return;
        drawerDetail.classList.remove('show');
        document.body.style.overflow = '';
        currentDetailTujuan = null;
    };

    // ==========================================
    // 2. MODAL TAMBAH TUJUAN
    // ==========================================
    window.openModalTambah = function () {
        if (!modalTambah) return;
        const form = document.getElementById('formTambahTujuan');
        if (form) form.reset();
        modalTambah.classList.add('show');
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            const input = document.getElementById('tambah_nama');
            if (input) input.focus();
        }, 100);
    };

    window.closeModalTambah = function () {
        if (!modalTambah) return;
        modalTambah.classList.remove('show');
        document.body.style.overflow = '';
    };

    // ==========================================
    // 3. MODAL EDIT TUJUAN
    // ==========================================
    window.openModalEdit = function (id, nama, tipe, alamat, jumlahPenerima) {
        if (!modalEdit) return;

        const form = document.getElementById('formEditTujuan');
        if (form) {
            form.action = `/staff/tujuan/${id}`;
        }

        const inputId = document.getElementById('edit_id');
        const inputNama = document.getElementById('edit_nama');
        const selectTipe = document.getElementById('edit_tipe');
        const inputAlamat = document.getElementById('edit_alamat');
        const inputPenerima = document.getElementById('edit_jumlah_penerima');

        if (inputId) inputId.value = id;
        if (inputNama) inputNama.value = nama || '';
        if (selectTipe) selectTipe.value = (tipe || 'sekolah').toLowerCase();
        if (inputAlamat) inputAlamat.value = (alamat && alamat !== '-') ? alamat : '';
        if (inputPenerima) inputPenerima.value = jumlahPenerima || 0;

        modalEdit.classList.add('show');
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            if (inputNama) inputNama.focus();
        }, 100);
    };

    window.closeModalEdit = function () {
        if (!modalEdit) return;
        modalEdit.classList.remove('show');
        document.body.style.overflow = '';
    };

    // ==========================================
    // 4. MODAL DELETE TUJUAN
    // ==========================================
    window.confirmDelete = function (id, nama) {
        if (!modalDelete) return;

        const form = document.getElementById('formDeleteTujuan');
        if (form) {
            form.action = `/staff/tujuan/${id}`;
        }

        const deleteNameEl = document.getElementById('delete_target_name');
        if (deleteNameEl) {
            deleteNameEl.textContent = nama;
        }

        modalDelete.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    window.closeModalDelete = function () {
        if (!modalDelete) return;
        modalDelete.classList.remove('show');
        document.body.style.overflow = '';
    };

    // ==========================================
    // 5. EVENT LISTENERS: BACKDROP CLICK & ESC KEY
    // ==========================================
    [drawerDetail, modalTambah, modalEdit, modalDelete].forEach(function (el) {
        if (!el) return;
        el.addEventListener('click', function (e) {
            if (e.target === el) {
                if (el === drawerDetail) closeDetailPanel();
                if (el === modalTambah) closeModalTambah();
                if (el === modalEdit) closeModalEdit();
                if (el === modalDelete) closeModalDelete();
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (modalDelete && modalDelete.classList.contains('show')) {
                closeModalDelete();
            } else if (modalEdit && modalEdit.classList.contains('show')) {
                closeModalEdit();
            } else if (modalTambah && modalTambah.classList.contains('show')) {
                closeModalTambah();
            } else if (drawerDetail && drawerDetail.classList.contains('show')) {
                closeDetailPanel();
            }
        }
    });
});

