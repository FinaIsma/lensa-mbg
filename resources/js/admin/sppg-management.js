window.renderSppgModalContent = function (item) {
    const hasProposed = Boolean(
        item.proposed &&
        typeof item.proposed === 'object' &&
        Object.keys(item.proposed).length > 0
    );
    const hasChanges = Boolean(item.has_changes || hasProposed || item.status_verifikasi === 'perubahan');

    // Update modal dialog width class dynamically
    document.querySelectorAll('.modal-dialog').forEach(dialog => {
        if (hasChanges) {
            dialog.classList.add('modal-dialog-diff');
        } else {
            dialog.classList.remove('modal-dialog-diff');
        }
    });

    // Update modal title dynamically
    const detailTitle = document.querySelector('#modalDetail .modal-title');
    if (detailTitle) {
        detailTitle.textContent = hasChanges ? 'Detail Perubahan SPPG' : 'Detail SPPG';
    }
    const verifTitle = document.querySelector('#modalVerifikasi .modal-title');
    if (verifTitle) {
        verifTitle.textContent = hasChanges ? 'Verifikasi Perubahan SPPG' : 'Verifikasi SPPG';
    }

    // SVG icon paths matching reference design
    const icons = {
        user: '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        email: '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        phone: '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        store: '<rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="22" x2="9" y2="18"/><line x1="15" y1="22" x2="15" y2="18"/><line x1="9" y1="18" x2="15" y2="18"/><line x1="8" y1="6" x2="8.01" y2="6"/><line x1="12" y1="6" x2="12.01" y2="6"/><line x1="16" y1="6" x2="16.01" y2="6"/><line x1="8" y1="10" x2="8.01" y2="10"/><line x1="12" y1="10" x2="12.01" y2="10"/><line x1="16" y1="10" x2="16.01" y2="10"/><line x1="8" y1="14" x2="8.01" y2="14"/><line x1="12" y1="14" x2="12.01" y2="14"/><line x1="16" y1="14" x2="16.01" y2="14"/>',
        id: '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><line x1="15" y1="8" x2="17" y2="8"/><line x1="15" y1="12" x2="17" y2="12"/><line x1="7" y1="16" x2="17" y2="16"/>',
        pin: '<path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        nav: '<polygon points="12 2 19 21 12 17 5 21 12 2"/>',
        cal: '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
    };

    function renderRow(iconSvg, label, value, isModified = false) {
        return `
            <div class="cr-kv-row ${isModified ? 'is-modified' : ''}">
                <span class="cr-kv-label">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${iconSvg}</svg>
                    <span>${label}</span>
                </span>
                <span class="cr-kv-value">${value || '-'}</span>
                ${isModified ? '<span class="badge-diubah">DIUBAH</span>' : ''}
            </div>
        `;
    }

    function renderDocCard(label, fileUrl, isModified = false) {
        const isPdf = fileUrl && /\.pdf($|\?)/i.test(fileUrl);
        let previewHtml = '';

        if (fileUrl) {
            if (isPdf) {
                previewHtml = `<canvas class="mv2-pdf-canvas" data-pdf="${fileUrl}"></canvas>`;
            } else {
                previewHtml = `<img src="${fileUrl}" alt="${label}">`;
            }
        } else {
            previewHtml = `
                <div class="mv2-doc-empty">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                </div>
            `;
        }

        return `
            <div class="diff-doc-card ${isModified ? 'is-modified' : ''}">
                <div class="diff-doc-preview">
                    ${previewHtml}
                </div>
                <div class="diff-doc-label">${label}</div>
                <a href="${fileUrl || '#'}" ${fileUrl ? 'target="_blank"' : ''}
                   class="btn-diff-view ${!fileUrl ? 'mv2-btn-disabled' : ''}">
                    Lihat
                </a>
            </div>
        `;
    }

    // ── KONDISI 1: TIDAK ADA PERUBAHAN (1 KOLOM BERSIH) ────────────────
    if (!hasChanges) {
        const staffNama = item.staff_nama || '-';
        const staffEmail = item.staff_email || '-';
        const staffTelp = item.staff_no_telepon || '-';

        return `
            <!-- Entity Profile Header -->
            <div class="cr-entity-header">
                <div class="cr-entity-left">
                    <div class="cr-entity-avatar" aria-hidden="true">${(item.nama_sppg || 'S').trim().charAt(0).toUpperCase()}</div>
                    <div>
                        <div class="cr-entity-title">${item.nama_sppg || '-'}</div>
                        <div class="cr-entity-subtitle">${item.id_sppg || '-'}</div>
                    </div>
                </div>
            </div>

            <div class="single-col-cards">
                <!-- Section 1: Informasi Kepala SPPG -->
                <div class="cr-info-card">
                    <h3 class="cr-card-title">Informasi Kepala SPPG</h3>
                    <div class="cr-card-body">
                        ${renderRow(icons.user, 'Nama Lengkap', item.nama_pegawai)}
                        ${renderRow(icons.email, 'Email', item.email)}
                        ${renderRow(icons.phone, 'No Telepon', item.no_telepon)}
                    </div>
                </div>

                <!-- Section 2: Informasi Staff SPPG -->
                <div class="cr-info-card">
                    <h3 class="cr-card-title">Informasi Staff SPPG</h3>
                    <div class="cr-card-body">
                        ${renderRow(icons.user, 'Nama Lengkap', staffNama)}
                        ${renderRow(icons.email, 'Email', staffEmail)}
                        ${renderRow(icons.phone, 'No Telepon', staffTelp)}
                    </div>
                </div>

                <!-- Section 3: Informasi SPPG -->
                <div class="cr-info-card">
                    <h3 class="cr-card-title">Informasi SPPG</h3>
                    <div class="cr-card-body">
                        ${renderRow(icons.store, 'Nama SPPG', item.nama_sppg)}
                        ${renderRow(icons.id, 'ID SPPG', item.id_sppg)}
                        ${renderRow(icons.pin, 'Alamat Lengkap', item.alamat_sppg)}
                        ${renderRow(icons.pin, 'Provinsi', item.provinsi)}
                        ${renderRow(icons.pin, 'Kota/Kabupaten', item.kabupaten_kota)}
                        ${renderRow(icons.nav, 'Kecamatan', item.kecamatan)}
                        ${renderRow(icons.cal, 'Tanggal dibuat', item.created_at)}
                    </div>
                </div>

                <!-- Section 4: Dokumen -->
                <div class="cr-info-card diff-docs-card">
                    <h3 class="cr-card-title">Dokumen</h3>
                    <div class="diff-doc-grid">
                        ${renderDocCard('Foto KTP', item.foto_ktp)}
                        ${renderDocCard('Foto Kantor SPPG', item.foto_kantor_sppg)}
                        ${renderDocCard('Foto Surat Resmi', item.foto_surat_resmi)}
                    </div>
                </div>
            </div>
        `;
    }

    // ── KONDISI 2: ADA PERUBAHAN DIAJUKAN (2 KOLOM DIFF SEBELUM VS SESUDAH) ──
    const before = Object.assign({}, item, item.before_values || {});
    const proposed = item.proposed || {};
    const after = Object.assign({}, item, proposed);

    // Helper for case-insensitive and whitespace-trimmed comparison
    const isSame = (a, b) => String(a || '').trim().toLowerCase() === String(b || '').trim().toLowerCase();

    // Comparison flags
    const isModNamaPegawai = !isSame(after.nama_pegawai, before.nama_pegawai);
    const isModEmail = !isSame(after.email, before.email);
    const isModTelepon = !isSame(after.no_telepon, before.no_telepon);

    const staffNamaBefore = before.staff_nama || '-';
    const staffNamaAfter = after.staff_nama || '-';
    const isModStaffNama = !isSame(staffNamaAfter, staffNamaBefore) && staffNamaAfter !== '-';

    const staffEmailBefore = before.staff_email || '-';
    const staffEmailAfter = after.staff_email || '-';
    const isModStaffEmail = !isSame(staffEmailAfter, staffEmailBefore) && staffEmailAfter !== '-';

    const staffTelpBefore = before.staff_no_telepon || '-';
    const staffTelpAfter = after.staff_no_telepon || '-';
    const isModStaffTelp = !isSame(staffTelpAfter, staffTelpBefore) && staffTelpAfter !== '-';

    const isModNamaSppg = !isSame(after.nama_sppg, before.nama_sppg);
    const isModAlamat = !isSame(after.alamat_sppg, before.alamat_sppg);
    const isModProvinsi = !isSame(after.provinsi, before.provinsi);
    const isModKota = !isSame(after.kabupaten_kota, before.kabupaten_kota);
    const isModKecamatan = !isSame(after.kecamatan, before.kecamatan);

    const isModKtp = !!(after.foto_ktp && after.foto_ktp !== before.foto_ktp);
    const isModKantor = !!(after.foto_kantor_sppg && after.foto_kantor_sppg !== before.foto_kantor_sppg);
    const isModSurat = !!(after.foto_surat_resmi && after.foto_surat_resmi !== before.foto_surat_resmi);

    return `
        <!-- Entity Profile Header -->
        <div class="cr-entity-header">
            <div class="cr-entity-left">
                <div class="cr-entity-avatar" aria-hidden="true">${(before.nama_sppg || 'S').trim().charAt(0).toUpperCase()}</div>
                <div>
                    <div class="cr-entity-title">${before.nama_sppg || '-'}</div>
                    <div class="cr-entity-subtitle">${before.id_sppg || '-'}</div>
                </div>
            </div>
        </div>

        <!-- 2 Column Diff Header Indicator -->
        <div class="diff-header-bar">
            <div class="diff-col-heading diff-col-before">
                <span class="diff-dot diff-dot-before"></span>
                <span>SEBELUM (DATA TERSIMPAN)</span>
            </div>
            <div class="diff-col-heading diff-col-after">
                <span class="diff-dot diff-dot-after"></span>
                <span>SESUDAH (PERUBAHAN DIAJUKAN)</span>
            </div>
        </div>

        <!-- Two Column Diff Grid -->
        <div class="diff-grid">
            <!-- KOLOM KIRI (SEBELUM) -->
            <div class="diff-col">
                <!-- Section 1: Informasi Kepala SPPG -->
                <div class="cr-info-card">
                    <h3 class="cr-card-title">Informasi Kepala SPPG</h3>
                    <div class="cr-card-body">
                        ${renderRow(icons.user, 'Nama Lengkap', before.nama_pegawai)}
                        ${renderRow(icons.email, 'Email', before.email)}
                        ${renderRow(icons.phone, 'No Telepon', before.no_telepon)}
                    </div>
                </div>

                <!-- Section 2: Informasi Staff SPPG -->
                <div class="cr-info-card">
                    <h3 class="cr-card-title">Informasi Staff SPPG</h3>
                    <div class="cr-card-body">
                        ${renderRow(icons.user, 'Nama Lengkap', staffNamaBefore)}
                        ${renderRow(icons.email, 'Email', staffEmailBefore)}
                        ${renderRow(icons.phone, 'No Telepon', staffTelpBefore)}
                    </div>
                </div>

                <!-- Section 3: Informasi SPPG -->
                <div class="cr-info-card">
                    <h3 class="cr-card-title">Informasi SPPG</h3>
                    <div class="cr-card-body">
                        ${renderRow(icons.store, 'Nama SPPG', before.nama_sppg)}
                        ${renderRow(icons.id, 'ID SPPG', before.id_sppg)}
                        ${renderRow(icons.pin, 'Alamat Lengkap', before.alamat_sppg)}
                        ${renderRow(icons.pin, 'Provinsi', before.provinsi)}
                        ${renderRow(icons.pin, 'Kota/Kabupaten', before.kabupaten_kota)}
                        ${renderRow(icons.nav, 'Kecamatan', before.kecamatan)}
                        ${renderRow(icons.cal, 'Tanggal dibuat', before.created_at)}
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN (SESUDAH) -->
            <div class="diff-col">
                <!-- Section 1: Informasi Kepala SPPG -->
                <div class="cr-info-card">
                    <h3 class="cr-card-title">Informasi Kepala SPPG</h3>
                    <div class="cr-card-body">
                        ${renderRow(icons.user, 'Nama Lengkap', after.nama_pegawai, isModNamaPegawai)}
                        ${renderRow(icons.email, 'Email', after.email, isModEmail)}
                        ${renderRow(icons.phone, 'No Telepon', after.no_telepon, isModTelepon)}
                    </div>
                </div>

                <!-- Section 2: Informasi Staff SPPG -->
                <div class="cr-info-card">
                    <h3 class="cr-card-title">Informasi Staff SPPG</h3>
                    <div class="cr-card-body">
                        ${renderRow(icons.user, 'Nama Lengkap', staffNamaAfter, isModStaffNama)}
                        ${renderRow(icons.email, 'Email', staffEmailAfter, isModStaffEmail)}
                        ${renderRow(icons.phone, 'No Telepon', staffTelpAfter, isModStaffTelp)}
                    </div>
                </div>

                <!-- Section 3: Informasi SPPG -->
                <div class="cr-info-card">
                    <h3 class="cr-card-title">Informasi SPPG</h3>
                    <div class="cr-card-body">
                        ${renderRow(icons.store, 'Nama SPPG', isModNamaSppg ? after.nama_sppg : before.nama_sppg, isModNamaSppg)}
                        ${renderRow(icons.id, 'ID SPPG', after.id_sppg, false)}
                        ${renderRow(icons.pin, 'Alamat Lengkap', isModAlamat ? after.alamat_sppg : before.alamat_sppg, isModAlamat)}
                        ${renderRow(icons.pin, 'Provinsi', isModProvinsi ? after.provinsi : before.provinsi, isModProvinsi)}
                        ${renderRow(icons.pin, 'Kota/Kabupaten', isModKota ? after.kabupaten_kota : before.kabupaten_kota, isModKota)}
                        ${renderRow(icons.nav, 'Kecamatan', isModKecamatan ? after.kecamatan : before.kecamatan, isModKecamatan)}
                        ${renderRow(icons.cal, 'Tanggal dibuat', after.created_at, false)}
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Dokumen (Grid 2 Kolom: Sebelum vs Sesudah) -->
        <div class="cr-info-card diff-docs-card">
            <h3 class="cr-card-title">Dokumen</h3>
            <div class="diff-grid">
                <!-- Dokumen Sebelum -->
                <div class="diff-col">
                    <div class="diff-doc-grid">
                        ${renderDocCard('Foto KTP', before.foto_ktp)}
                        ${renderDocCard('Foto Kantor SPPG', before.foto_kantor_sppg)}
                        ${renderDocCard('Foto Surat Resmi', before.foto_surat_resmi)}
                    </div>
                </div>

                <!-- Dokumen Sesudah -->
                <div class="diff-col">
                    <div class="diff-doc-grid">
                        ${renderDocCard('Foto KTP', after.foto_ktp, isModKtp)}
                        ${renderDocCard('Foto Kantor SPPG', after.foto_kantor_sppg, isModKantor)}
                        ${renderDocCard('Foto Surat Resmi', after.foto_surat_resmi, isModSurat)}
                    </div>
                </div>
            </div>
        </div>
    `;
};

/* ── PDF Page 1 Thumbnail Renderer ──────────────── */
window.renderPdfPreview = function (container) {
    const root = container || document;
    const canvases = root.querySelectorAll('canvas[data-pdf]');

    canvases.forEach(canvas => {
        const url = canvas.getAttribute('data-pdf');
        if (!url) return;

        function doRender() {
            if (typeof pdfjsLib === 'undefined') return;
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';

            pdfjsLib.getDocument(url).promise.then(pdf => {
                return pdf.getPage(1);
            }).then(page => {
                const parent = canvas.parentElement;
                const parentWidth = parent ? (parent.clientWidth || 100) : 100;
                const unscaledViewport = page.getViewport({ scale: 1 });
                const scale = (parentWidth * 1.5) / unscaledViewport.width;
                const viewport = page.getViewport({ scale: scale });

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                const renderContext = {
                    canvasContext: canvas.getContext('2d'),
                    viewport: viewport
                };
                return page.render(renderContext).promise;
            }).catch(err => {
                console.warn('Gagal me-render preview halaman 1 PDF:', err);
                if (canvas.parentElement) {
                    canvas.parentElement.innerHTML = `
                        <div class="mv2-doc-pdf-fallback">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                            <span class="mv2-doc-pdf-tag">PDF</span>
                        </div>
                    `;
                }
            });
        }

        if (typeof pdfjsLib === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js';
            script.onload = doRender;
            document.head.appendChild(script);
        } else {
            doRender();
        }
    });
};

/* ── Modal openers ──────────────────────────────── */
window.openDetailModal = function (item) {
    const content = document.getElementById('detailModalContent');
    if (!content) return;

    const form = document.getElementById('formDetail');
    if (form) {
        const baseUpdateUrl = window.adminSppgUpdateBase || '/admin/sppg';
        form.action = `${baseUpdateUrl}/${item.raw_id}/status`;
    }

    const inputStatus = document.getElementById('inputDetailStatusVal');
    const btn = document.getElementById('btnDetailDeactivate');

    if (btn && inputStatus) {
        if (item.status === 'nonaktif') {
            inputStatus.value = 'aktif';
            btn.className = 'btn-modal-action-activate';
            btn.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <polyline points="17 11 19 13 23 9"></polyline>
                </svg>
                <span id="btnDetailText">Aktifkan</span>
            `;
        } else {
            inputStatus.value = 'nonaktif';
            btn.className = 'btn-modal-action-deactivate';
            btn.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="18" y1="8" x2="23" y2="13"></line>
                    <line x1="23" y1="8" x2="18" y2="13"></line>
                </svg>
                <span id="btnDetailText">Nonaktifkan</span>
            `;
        }
    }

    content.innerHTML = window.renderSppgModalContent(item);
    document.getElementById('modalDetail').classList.remove('hidden');
    window.renderPdfPreview(content);
};

window.submitDeactivateDetail = function () {
    const form = document.getElementById('formDetail');
    if (form) {
        form.submit();
    }
};

window.openVerifikasiModal = function (item) {
    const form = document.getElementById('formVerifikasi');
    const content = document.getElementById('verifikasiModalContent');
    if (!form || !content) return;

    const baseUpdateUrl = window.adminSppgUpdateBase || '/admin/sppg';
    form.action = `${baseUpdateUrl}/${item.raw_id}/status`;
    content.innerHTML = window.renderSppgModalContent(item);

    document.getElementById('modalVerifikasi').classList.remove('hidden');
    window.renderPdfPreview(content);
};

window.submitVerifikasi = function (statusVal) {
    const input = document.getElementById('inputStatusVal');
    const form = document.getElementById('formVerifikasi');
    if (!input || !form) return;

    if (statusVal === 'nonaktif') {
        window.openRejectModal();
        return;
    }

    input.value = statusVal;
    form.submit();
};

window.openRejectModal = function () {
    const modal = document.getElementById('modalRejectSppg');
    const textarea = document.getElementById('rejectReasonTextarea');
    if (textarea) textarea.value = '';
    if (modal) {
        modal.classList.remove('hidden');
        if (textarea) setTimeout(() => textarea.focus(), 150);
    }
};

window.closeRejectModal = function () {
    const modal = document.getElementById('modalRejectSppg');
    if (modal) modal.classList.add('hidden');
};

window.confirmRejectVerifikasi = function () {
    const input = document.getElementById('inputStatusVal');
    const noteInput = document.getElementById('inputReviewNote');
    const form = document.getElementById('formVerifikasi');
    const textarea = document.getElementById('rejectReasonTextarea');
    if (!input || !form) return;

    if (noteInput && textarea) {
        noteInput.value = textarea.value.trim();
    }

    input.value = 'nonaktif';
    form.submit();
};

window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('hidden');
};

// Dismiss modal when backdrop is clicked
document.addEventListener('click', function (e) {
    if (e.target.classList) {
        if (e.target.classList.contains('modal-backdrop')) {
            e.target.classList.add('hidden');
        } else if (e.target.classList.contains('sppg-confirm-backdrop')) {
            e.target.classList.add('hidden');
        }
    }
});

// Dismiss modal on Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const detail = document.getElementById('modalDetail');
        const verif = document.getElementById('modalVerifikasi');
        const rejectModal = document.getElementById('modalRejectSppg');
        if (rejectModal && !rejectModal.classList.contains('hidden')) {
            rejectModal.classList.add('hidden');
            return;
        }
        if (detail) detail.classList.add('hidden');
        if (verif) verif.classList.add('hidden');
    }
});

