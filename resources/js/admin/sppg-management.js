function getSppgInitials(name) {
    if (!name || typeof name !== 'string') return 'SP';
    const words = name.trim().split(/\s+/).filter(w => w.length > 0);
    if (words.length >= 2) {
        return (words[0][0] + words[1][0]).toUpperCase();
    }
    if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase();
    }
    return 'SP';
}

window.renderSppgModalContent = function (item) {
    const createdDate = item.created_at || '-';
    const initials = getSppgInitials(item.nama_sppg);

    const icons = {
        user: '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        email: '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        phone: '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        store: '<rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="22" x2="9" y2="18"/><line x1="15" y1="22" x2="15" y2="18"/><line x1="9" y1="18" x2="15" y2="18"/><line x1="8" y1="6" x2="8.01" y2="6"/><line x1="12" y1="6" x2="12.01" y2="6"/><line x1="16" y1="6" x2="16.01" y2="6"/><line x1="8" y1="10" x2="8.01" y2="10"/><line x1="12" y1="10" x2="12.01" y2="10"/><line x1="16" y1="10" x2="16.01" y2="10"/><line x1="8" y1="14" x2="8.01" y2="14"/><line x1="12" y1="14" x2="12.01" y2="14"/><line x1="16" y1="14" x2="16.01" y2="14"/>',
        id: '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><line x1="15" y1="8" x2="17" y2="8"/><line x1="15" y1="12" x2="17" y2="12"/><line x1="7" y1="16" x2="17" y2="16"/>',
        pin: '<path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        nav: '<polygon points="12 2 19 21 12 17 5 21 12 2"/>',
        cal: '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>',
    };

    const isSuratPdf = item.foto_surat_resmi && /\.pdf($|\?)/i.test(item.foto_surat_resmi);
    let suratMediaHtml = '';
    if (item.foto_surat_resmi) {
        if (isSuratPdf) {
            suratMediaHtml = `<canvas class="mv2-pdf-canvas" data-pdf="${item.foto_surat_resmi}"></canvas>`;
        } else {
            suratMediaHtml = `<img src="${item.foto_surat_resmi}" alt="Foto Surat Resmi">`;
        }
    } else {
        suratMediaHtml = '<div class="mv2-doc-empty"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></div>';
    }

    return `
        <div class="cr-entity-header">
            <div class="cr-entity-left">
                <div class="cr-entity-avatar" aria-hidden="true">${initials}</div>
                <div>
                    <div class="cr-entity-title">${item.nama_sppg || '-'}</div>
                    <div class="cr-entity-subtitle">${item.id_sppg || '-'}</div>
                </div>
            </div>
        </div>

        <div class="cr-info-card">
            <h3 class="cr-card-title">Informasi Kepala SPPG</h3>
            <div class="cr-card-body">
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.user}</svg>
                        Nama Lengkap
                    </span>
                    <span class="cr-kv-value">${item.nama_pegawai || '-'}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.email}</svg>
                        Email
                    </span>
                    <span class="cr-kv-value">${item.email || '-'}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.phone}</svg>
                        No Telepon
                    </span>
                    <span class="cr-kv-value">${item.no_telepon || '-'}</span>
                </div>
            </div>
        </div>

        <div class="cr-info-card">
            <h3 class="cr-card-title">Informasi Staff SPPG</h3>
            <div class="cr-card-body">
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.user}</svg>
                        Nama Lengkap
                    </span>
                    <span class="cr-kv-value">${item.staff_nama || '-'}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.email}</svg>
                        Email
                    </span>
                    <span class="cr-kv-value">${item.staff_email || '-'}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.phone}</svg>
                        No Telepon
                    </span>
                    <span class="cr-kv-value">${item.staff_no_telepon || '-'}</span>
                </div>
            </div>
        </div>

        <div class="cr-info-card">
            <h3 class="cr-card-title">Informasi SPPG</h3>
            <div class="cr-card-body">
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.store}</svg>
                        Nama SPPG
                    </span>
                    <span class="cr-kv-value">${item.nama_sppg || '-'}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.id}</svg>
                        ID SPPG
                    </span>
                    <span class="cr-kv-value">${item.id_sppg || '-'}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.pin}</svg>
                        Alamat Lengkap
                    </span>
                    <span class="cr-kv-value">${item.alamat_sppg || '-'}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.pin}</svg>
                        Provinsi
                    </span>
                    <span class="cr-kv-value">${item.provinsi || '-'}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.pin}</svg>
                        Kota/Kabupaten
                    </span>
                    <span class="cr-kv-value">${item.kabupaten_kota || '-'}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.nav}</svg>
                        Kecamatan
                    </span>
                    <span class="cr-kv-value">${item.kecamatan || '-'}</span>
                </div>
                <div class="cr-kv-row">
                    <span class="cr-kv-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icons.cal}</svg>
                        Tanggal dibuat
                    </span>
                    <span class="cr-kv-value">${createdDate}</span>
                </div>
            </div>
        </div>

        <div class="cr-info-card">
            <h3 class="cr-card-title">Dokumen</h3>
            <div class="mv2-doc-grid">
                <div class="mv2-doc-item">
                    <div class="mv2-doc-img-box">
                        ${item.foto_ktp ? `<img src="${item.foto_ktp}" alt="Foto KTP">` : '<div class="mv2-doc-empty"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>'}
                    </div>
                    <span class="mv2-doc-label">Foto KTP</span>
                    <a href="${item.foto_ktp || '#'}" ${item.foto_ktp ? 'target="_blank"' : ''}
                       class="btn-file-view ${!item.foto_ktp ? 'mv2-btn-disabled' : ''}">Lihat</a>
                </div>

                <div class="mv2-doc-item">
                    <div class="mv2-doc-img-box">
                        ${item.foto_kantor_sppg ? `<img src="${item.foto_kantor_sppg}" alt="Foto Kantor SPPG">` : '<div class="mv2-doc-empty"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>'}
                    </div>
                    <span class="mv2-doc-label">Foto Kantor SPPG</span>
                    <a href="${item.foto_kantor_sppg || '#'}" ${item.foto_kantor_sppg ? 'target="_blank"' : ''}
                       class="btn-file-view ${!item.foto_kantor_sppg ? 'mv2-btn-disabled' : ''}">Lihat</a>
                </div>

                <div class="mv2-doc-item">
                    <div class="mv2-doc-img-box">
                        ${suratMediaHtml}
                    </div>
                    <span class="mv2-doc-label">Foto Surat Resmi</span>
                    <a href="${item.foto_surat_resmi || '#'}" ${item.foto_surat_resmi ? 'target="_blank"' : ''}
                       class="btn-file-view ${!item.foto_surat_resmi ? 'mv2-btn-disabled' : ''}">Lihat</a>
                </div>
            </div>
        </div>
    `;
};

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
    if (input && form) {
        input.value = statusVal;
        form.submit();
    }
};

window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('hidden');
};

document.addEventListener('click', function (e) {
    if (e.target.classList && e.target.classList.contains('modal-backdrop')) {
        e.target.classList.add('hidden');
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const detail = document.getElementById('modalDetail');
        const verif = document.getElementById('modalVerifikasi');
        if (detail) detail.classList.add('hidden');
        if (verif) verif.classList.add('hidden');
    }
});
