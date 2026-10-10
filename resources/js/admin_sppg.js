window.renderSppgModalContent = function (item) {
    // Determine status badge styling
    let statusDotColor = '#D97706';
    let statusLabel  = 'Belum Aktif';
    let statusColor  = '#D97706';
    let statusBg     = '#FEF3C7';

    if (item.status === 'aktif') {
        statusDotColor = '#16A34A';
        statusLabel    = 'Aktif';
        statusColor    = '#16A34A';
        statusBg       = '#DCFCE7';
    } else if (item.status === 'nonaktif') {
        statusDotColor = '#DC2626';
        statusLabel    = 'Nonaktif';
        statusColor    = '#DC2626';
        statusBg       = '#FEE2E2';
    }

    const createdDate = item.created_at || '-';

    // Helper: single icon+label+value row
    const row = (iconPath, label, value) => `
        <div class="mv2-row">
            <div class="mv2-label-group">
                <svg class="mv2-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${iconPath}</svg>
                <span class="mv2-label-text">${label}</span>
            </div>
            <span class="mv2-value-text">${value || '-'}</span>
        </div>`;

    // SVG icon paths matching reference design
    const icons = {
        user  : '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        email : '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        phone : '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        store : '<rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="22" x2="9" y2="18"/><line x1="15" y1="22" x2="15" y2="18"/><line x1="9" y1="18" x2="15" y2="18"/><line x1="8" y1="6" x2="8.01" y2="6"/><line x1="12" y1="6" x2="12.01" y2="6"/><line x1="16" y1="6" x2="16.01" y2="6"/><line x1="8" y1="10" x2="8.01" y2="10"/><line x1="12" y1="10" x2="12.01" y2="10"/><line x1="16" y1="10" x2="16.01" y2="10"/><line x1="8" y1="14" x2="8.01" y2="14"/><line x1="12" y1="14" x2="12.01" y2="14"/><line x1="16" y1="14" x2="16.01" y2="14"/>',
        id    : '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><line x1="15" y1="8" x2="17" y2="8"/><line x1="15" y1="12" x2="17" y2="12"/><line x1="7" y1="16" x2="17" y2="16"/>',
        pin   : '<path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        map   : '<path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        nav   : '<polygon points="12 2 19 21 12 17 5 21 12 2"/>',
        cal   : '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
    };

    return `
        <!-- ── PROFILE HEADER ─────────────────────── -->
        <div class="mv2-profile-header">
            <div class="mv2-avatar"></div>
            <div class="mv2-header-meta">
                <span class="mv2-sppg-name">${item.nama_sppg || '-'}</span>
                <span class="mv2-sppg-code">${item.id_sppg || '-'}</span>
            </div>
            <span class="mv2-status-pill" style="background:${statusBg}; color:${statusColor};">
                <span class="mv2-status-dot" style="background:${statusDotColor};"></span>
                ${statusLabel}
            </span>
        </div>

        <!-- ── CARD: Informasi Admin SPPG ─────────── -->
        <div class="mv2-card">
            <h4 class="mv2-card-title">Informasi Admin SPPG</h4>
            <div class="mv2-rows">
                ${row(icons.user,  'Nama Lengkap', item.nama_pegawai)}
                ${row(icons.email, 'Email',        item.email)}
                ${row(icons.phone, 'No Telepon',   item.no_telepon)}
            </div>
        </div>

        <!-- ── CARD: Informasi SPPG ───────────────── -->
        <div class="mv2-card">
            <h4 class="mv2-card-title">Informasi SPPG</h4>
            <div class="mv2-rows">
                ${row(icons.store, 'Nama SPPG',     item.nama_sppg)}
                ${row(icons.id,   'ID SPPG',        item.id_sppg)}
                ${row(icons.pin,  'Alamat Lengkap', item.alamat_sppg)}
                ${row(icons.map,  'Provinsi',       item.provinsi)}
                ${row(icons.map,  'Kota/Kabupaten', item.kabupaten_kota)}
                ${row(icons.nav,  'Kecamatan',      item.kecamatan)}
                ${row(icons.cal,  'Tanggal dibuat', createdDate)}
            </div>
        </div>

        <!-- ── CARD: Dokumen ──────────────────────── -->
        <div class="mv2-card">
            <h4 class="mv2-card-title">Dokumen</h4>
            <div class="mv2-doc-grid">

                <div class="mv2-doc-item">
                    <div class="mv2-doc-img-box">
                        ${item.foto_ktp ? `<img src="${item.foto_ktp}" alt="Foto KTP">` : ''}
                    </div>
                    <span class="mv2-doc-label">Foto KTP</span>
                    <a href="${item.foto_ktp || '#'}" ${item.foto_ktp ? 'target="_blank"' : ''}
                       class="mv2-btn-lihat ${!item.foto_ktp ? 'mv2-btn-disabled' : ''}">Lihat</a>
                </div>

                <div class="mv2-doc-item">
                    <div class="mv2-doc-img-box">
                        ${item.foto_kantor_sppg ? `<img src="${item.foto_kantor_sppg}" alt="Foto Kantor SPPG">` : ''}
                    </div>
                    <span class="mv2-doc-label">Foto Kantor SPPG</span>
                    <a href="${item.foto_kantor_sppg || '#'}" ${item.foto_kantor_sppg ? 'target="_blank"' : ''}
                       class="mv2-btn-lihat ${!item.foto_kantor_sppg ? 'mv2-btn-disabled' : ''}">Lihat</a>
                </div>

                <div class="mv2-doc-item">
                    <div class="mv2-doc-img-box">
                        ${item.foto_surat_resmi ? `<img src="${item.foto_surat_resmi}" alt="Foto Surat Resmi">` : ''}
                    </div>
                    <span class="mv2-doc-label">Foto Surat Resmi</span>
                    <a href="${item.foto_surat_resmi || '#'}" ${item.foto_surat_resmi ? 'target="_blank"' : ''}
                       class="mv2-btn-lihat ${!item.foto_surat_resmi ? 'mv2-btn-disabled' : ''}">Lihat</a>
                </div>

            </div>
        </div>
    `;
};

/* ── Modal openers ──────────────────────────────── */
window.openDetailModal = function (item) {
    const content = document.getElementById('detailModalContent');
    if (!content) return;
    content.innerHTML = window.renderSppgModalContent(item);
    document.getElementById('modalDetail').classList.remove('hidden');
};

window.openVerifikasiModal = function (item) {
    const form    = document.getElementById('formVerifikasi');
    const content = document.getElementById('verifikasiModalContent');
    if (!form || !content) return;

    const baseUpdateUrl = window.adminSppgUpdateBase || '/admin/sppg';
    form.action = `${baseUpdateUrl}/${item.raw_id}/status`;
    content.innerHTML = window.renderSppgModalContent(item);

    document.getElementById('modalVerifikasi').classList.remove('hidden');
};

window.submitVerifikasi = function (statusVal) {
    const input = document.getElementById('inputStatusVal');
    const form  = document.getElementById('formVerifikasi');
    if (input && form) {
        input.value = statusVal;
        form.submit();
    }
};

window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('hidden');
};

// Dismiss modal when backdrop is clicked
document.addEventListener('click', function(e) {
    if (e.target.classList && e.target.classList.contains('modal-backdrop')) {
        e.target.classList.add('hidden');
    }
});
