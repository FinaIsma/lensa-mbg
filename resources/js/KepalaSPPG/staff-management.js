document.addEventListener('DOMContentLoaded', function () {
    // Modal Elements
    const tambahModal = document.getElementById('tambahStaffModal');
    const detailModal = document.getElementById('detailStaffModal');
    const nonaktifModal = document.getElementById('nonaktifStaffModal');

    let currentDetailStaff = null;

    // Open Tambah Staff Modal
    window.openTambahStaffModal = function () {
        if (!tambahModal) return;
        const form = tambahModal.querySelector('form');
        if (form) {
            form.reset();
            const emailInput = document.getElementById('inputEmailStaff');
            const passInput = document.getElementById('inputPasswordStaff');
            const passConfInput = document.getElementById('inputPasswordConfirmationStaff');
            if (emailInput) emailInput.value = '';
            if (passInput) passInput.value = '';
            if (passConfInput) passConfInput.value = '';
        }
        tambahModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    window.closeTambahStaffModal = function () {
        if (!tambahModal) return;
        tambahModal.classList.remove('show');
        document.body.style.overflow = '';
    };

    // Open Detail Modal
    window.openDetailModal = function (staffData) {
        if (!detailModal) return;
        currentDetailStaff = staffData;

        // Populate detail fields
        const avatarEl = document.getElementById('detailAvatar');
        const nameEl = document.getElementById('detailStaffName');
        const sppgIdEl = document.getElementById('detailSppgId');

        const staffIdEl = document.getElementById('detailStaffId');
        const infoNamaEl = document.getElementById('detailInfoNama');
        const infoEmailEl = document.getElementById('detailInfoEmail');
        const infoTeleponEl = document.getElementById('detailInfoTelepon');
        const infoTglEl = document.getElementById('detailInfoTgl');

        const sppgNamaEl = document.getElementById('detailSppgNama');
        const sppgIdDetailEl = document.getElementById('detailSppgIdDetail');
        const sppgAlamatEl = document.getElementById('detailSppgAlamat');
        const sppgProvinsiEl = document.getElementById('detailSppgProvinsi');
        const sppgKabupatenEl = document.getElementById('detailSppgKabupaten');
        const sppgKecamatanEl = document.getElementById('detailSppgKecamatan');
        const sppgTglEl = document.getElementById('detailSppgTgl');
        const sppgKepalaEl = document.getElementById('detailSppgKepala');

        const footerEl = document.getElementById('detailModalFooter');

        if (nameEl) nameEl.textContent = staffData.nama || '-';
        if (avatarEl) {
            const initial = (staffData.nama || 'S').trim().charAt(0).toUpperCase();
            avatarEl.textContent = initial;
        }
        if (sppgIdEl) {
            sppgIdEl.textContent = staffData.staff_id_formatted ? `${staffData.staff_id_formatted} • ${staffData.sppg_id_formatted || '-'}` : (staffData.sppg_id_formatted || '-');
        }

        if (staffIdEl) staffIdEl.textContent = staffData.staff_id_formatted || '-';
        if (infoNamaEl) infoNamaEl.textContent = staffData.nama || '-';
        if (infoEmailEl) infoEmailEl.textContent = staffData.email || '-';
        if (infoTeleponEl) infoTeleponEl.textContent = staffData.no_telepon || '-';
        if (infoTglEl) infoTglEl.textContent = staffData.tanggal_dibuat || '-';

        if (sppgNamaEl) sppgNamaEl.textContent = staffData.sppg_nama || '-';
        if (sppgIdDetailEl) sppgIdDetailEl.textContent = staffData.sppg_id_formatted || '-';
        if (sppgAlamatEl) sppgAlamatEl.textContent = staffData.sppg_alamat || '-';
        if (sppgProvinsiEl) sppgProvinsiEl.textContent = staffData.sppg_provinsi || '-';
        if (sppgKabupatenEl) sppgKabupatenEl.textContent = staffData.sppg_kabupaten || '-';
        if (sppgKecamatanEl) sppgKecamatanEl.textContent = staffData.sppg_kecamatan || '-';
        if (sppgTglEl) sppgTglEl.textContent = staffData.sppg_tanggal_dibuat || staffData.tanggal_dibuat || '-';
        if (sppgKepalaEl) sppgKepalaEl.textContent = staffData.nama_kepala || '-';

        // Rejection Alert & Note logic
        const rejectedAlertEl = document.getElementById('detailRejectedAlert');
        const catatanAdminEl = document.getElementById('detailCatatanAdmin');
        const badgeContainerEl = document.getElementById('detailStaffBadgeContainer');

        if (badgeContainerEl) {
            if (staffData.status === 'ditolak') {
                badgeContainerEl.innerHTML = `
                    <span class="badge-status badge-status-ditolak">
                        <span class="status-dot"></span> Ditolak
                    </span>
                `;
            } else if (staffData.status === 'aktif') {
                badgeContainerEl.innerHTML = `
                    <span class="badge-status badge-status-aktif">
                        <span class="status-dot"></span> Aktif
                    </span>
                `;
            } else if (staffData.status === 'menunggu_persetujuan' || staffData.status === 'menunggu') {
                badgeContainerEl.innerHTML = `
                    <span class="badge-status badge-status-belum">
                        <span class="status-dot"></span> Belum Verifikasi
                    </span>
                `;
            } else {
                badgeContainerEl.innerHTML = `
                    <span class="badge-status badge-status-nonaktif">
                        <span class="status-dot"></span> Nonaktif
                    </span>
                `;
            }
        }

        if (staffData.status === 'ditolak') {
            const alasan = staffData.catatan_admin && staffData.catatan_admin.trim() !== ''
                ? staffData.catatan_admin
                : 'Tidak ada alasan penolakan spesifik yang dicantumkan oleh admin.';

            if (rejectedAlertEl) rejectedAlertEl.style.display = 'flex';
            if (catatanAdminEl) catatanAdminEl.textContent = alasan;
        } else {
            if (rejectedAlertEl) rejectedAlertEl.style.display = 'none';
        }

        // Show Nonaktifkan button if staff is active
        if (footerEl) {
            if (staffData.status === 'aktif') {
                footerEl.style.display = 'block';
            } else {
                footerEl.style.display = 'none';
            }
        }

        detailModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    window.closeDetailModal = function () {
        if (!detailModal) return;
        detailModal.classList.remove('show');
        document.body.style.overflow = '';
    };

    // Trigger Nonaktifkan confirmation popup from Detail modal
    window.deactivateFromDetail = function () {
        if (!currentDetailStaff) return;
        closeDetailModal();
        openNonaktifModal(currentDetailStaff.id, currentDetailStaff.nama);
    };

    // Open Nonaktifkan Confirmation Modal
    window.openNonaktifModal = function (staffId, staffName) {
        if (!nonaktifModal) return;

        const form = document.getElementById('nonaktifStaffForm');
        const nameEl = document.getElementById('nonaktifStaffName');

        if (form) {
            form.action = `/sppg/staff/${staffId}/deactivate`;
        }
        if (nameEl) {
            nameEl.textContent = staffName;
        }

        nonaktifModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    window.closeNonaktifModal = function () {
        if (!nonaktifModal) return;
        nonaktifModal.classList.remove('show');
        document.body.style.overflow = '';
    };

    // Close on backdrop click
    [tambahModal, detailModal, nonaktifModal].forEach(modal => {
        if (!modal) return;
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });

    // Close on escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            [tambahModal, detailModal, nonaktifModal].forEach(modal => {
                if (modal && modal.classList.contains('show')) {
                    modal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        }
    });
});
