/**
 * Hasil Produksi JavaScript - Staff SPPG
 * Mengelola form catat hasil produksi, perhitungan susut otomatis,
 * peringatan porsi melebihi target, preview bukti foto, dan modal catat susut limbah.
 */

let currentBatchTarget = 0;

/**
 * Buka modal Catat Hasil Baru
 */
export function openAddHasilModal() {
    const modal = document.getElementById('modalAddHasil');
    if (modal) {
        modal.classList.add('open', 'show');
        modal.style.setProperty('display', 'flex', 'important');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Tutup modal Catat Hasil Baru
 */
export function closeAddHasilModal() {
    const modal = document.getElementById('modalAddHasil');
    if (modal) {
        modal.classList.remove('open', 'show');
        modal.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = '';
    }
}

/**
 * Handler saat batch dipilih pada dropdown
 */
export function handleHasilBatchChange(select) {
    if (!select) return;
    const selectedOpt = select.options[select.selectedIndex];
    const infoBox = document.getElementById('batch_info_box');

    if (!selectedOpt || !selectedOpt.value) {
        currentBatchTarget = 0;
        if (infoBox) infoBox.style.display = 'none';
        calculateHasilSusut();
        return;
    }

    currentBatchTarget = parseInt(selectedOpt.getAttribute('data-target') || '0', 10);

    const menuEl = document.getElementById('info_menu');
    const targetEl = document.getElementById('info_target');
    const tglEl = document.getElementById('info_tgl');
    const porsiEl = document.getElementById('info_porsi');

    if (menuEl) menuEl.innerText = selectedOpt.getAttribute('data-menu') || '-';
    if (targetEl) targetEl.innerText = currentBatchTarget.toLocaleString('id-ID') + ' Porsi';
    if (tglEl) tglEl.innerText = selectedOpt.getAttribute('data-tgl') || '-';
    if (porsiEl) porsiEl.innerText = selectedOpt.getAttribute('data-porsi') || '-';

    if (infoBox) infoBox.style.display = 'block';

    calculateHasilSusut();
}

/**
 * Hitung susut secara otomatis & tampilkan peringatan bila melebihi target
 * Aturan:
 * - portions_ready melebihi target -> Tampilkan peringatan (bukan blokir). Susut dihitung tidak boleh negatif, tampilkan 0 dan catatan.
 * - portions_ready < target -> Tampil info Susut: X porsi (dihitung otomatis, nilai minimal 0).
 */
export function calculateHasilSusut() {
    const portionsInput = document.getElementById('input_portions_ready');
    const warnExcess = document.getElementById('warning_excess');
    const infoSusut = document.getElementById('info_susut_box');
    const warnTargetVal = document.getElementById('warn_target_val');
    const warnReadyVal = document.getElementById('warn_ready_val');
    const warnExcessDiff = document.getElementById('warn_excess_diff');
    const susutMsgText = document.getElementById('susut_message_text');

    if (!portionsInput || currentBatchTarget <= 0) {
        if (warnExcess) warnExcess.style.display = 'none';
        if (infoSusut) infoSusut.style.display = 'none';
        return;
    }

    const valStr = portionsInput.value.trim();
    if (valStr === '') {
        if (warnExcess) warnExcess.style.display = 'none';
        if (infoSusut) infoSusut.style.display = 'none';
        portionsInput.style.borderColor = '#CBD5E1';
        return;
    }

    const portionsReady = parseInt(valStr, 10);
    if (Number.isNaN(portionsReady) || portionsReady < 0) {
        if (warnExcess) warnExcess.style.display = 'none';
        if (infoSusut) infoSusut.style.display = 'none';
        portionsInput.style.borderColor = '#CBD5E1';
        return;
    }

    if (warnTargetVal) warnTargetVal.innerText = currentBatchTarget.toLocaleString('id-ID');
    if (warnReadyVal) warnReadyVal.innerText = portionsReady.toLocaleString('id-ID');

    // Kasus 1: Melebihi target [BARU: Peringatan, bukan blokir. Susut tampil 0]
    if (portionsReady > currentBatchTarget) {
        const excess = portionsReady - currentBatchTarget;
        if (warnExcess) warnExcess.style.display = 'block';
        if (warnExcessDiff) warnExcessDiff.innerText = excess.toLocaleString('id-ID');
        if (infoSusut) infoSusut.style.display = 'none';
        portionsInput.style.borderColor = '#F59E0B';
    }
    // Kasus 2: Kurang dari target (Ada susut dihitung otomatis)
    else if (portionsReady < currentBatchTarget) {
        const susut = currentBatchTarget - portionsReady;
        if (warnExcess) warnExcess.style.display = 'none';
        if (infoSusut) infoSusut.style.display = 'block';
        if (susutMsgText) {
            susutMsgText.innerHTML = `Susut: <strong style="color: #DC2626;">${susut.toLocaleString('id-ID')} porsi</strong> (bisa dicatat ke Limbah).`;
        }
        portionsInput.style.borderColor = '#CBD5E1';
    }
    // Kasus 3: Tepat sama dengan target
    else {
        if (warnExcess) warnExcess.style.display = 'none';
        if (infoSusut) infoSusut.style.display = 'block';
        if (susutMsgText) {
            susutMsgText.innerHTML = `Susut: <strong>0 porsi</strong> (sesuai target).`;
        }
        portionsInput.style.borderColor = '#10B981';
    }
}

/**
 * Isi cepat template kondisi makanan siap saji
 */
export function setConditionText(text) {
    const input = document.getElementById('input_condition');
    if (input) {
        input.value = text;
        input.focus();
    }
}

/**
 * Preview bukti foto yang diunggah (Hasil & Susut)
 */
export function previewEvidenceImage(input) {
    if (!input || !input.files || !input.files[0]) return;

    const isSusut = input.id === 'susut_evidence_file_input';
    const imgId = isSusut ? 'susut_preview_img' : 'preview_img';
    const boxId = isSusut ? 'susut_image_preview_box' : 'image_preview_box';

    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById(imgId);
        const box = document.getElementById(boxId);
        if (img && box) {
            img.src = e.target.result;
            box.style.display = 'block';
        }
    };
    reader.readAsDataURL(file);
}

/**
 * Hapus pilihan bukti foto hasil produksi
 */
export function clearEvidencePreview() {
    const input = document.getElementById('evidence_file_input');
    const box = document.getElementById('image_preview_box');
    const img = document.getElementById('preview_img');
    if (input) input.value = '';
    if (img) img.src = '';
    if (box) box.style.display = 'none';
}

/**
 * Hapus pilihan bukti foto susut limbah
 */
export function clearSusutEvidencePreview() {
    const input = document.getElementById('susut_evidence_file_input');
    const box = document.getElementById('susut_image_preview_box');
    const img = document.getElementById('susut_preview_img');
    if (input) input.value = '';
    if (img) img.src = '';
    if (box) box.style.display = 'none';
}

/**
 * Buka modal Catat Susut (Limbah tipe PRODUCTION_LOSS)
 */
export function openCatatSusutModal(batchId, batchCode, defaultQty) {
    const modal = document.getElementById('modalCatatSusut');
    const idInput = document.getElementById('susut_batch_id_input');
    const codeDisplay = document.getElementById('susut_batch_code_display');
    const qtyInput = document.getElementById('susut_qty_input');

    if (idInput) idInput.value = batchId;
    if (codeDisplay) codeDisplay.innerText = batchCode;
    if (qtyInput) qtyInput.value = defaultQty || '';

    clearSusutEvidencePreview();

    if (modal) {
        modal.classList.add('open', 'show');
        modal.style.setProperty('display', 'flex', 'important');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Tutup modal Catat Susut
 */
export function closeCatatSusutModal() {
    const modal = document.getElementById('modalCatatSusut');
    clearSusutEvidencePreview();

    if (modal) {
        modal.classList.remove('open', 'show');
        modal.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = '';
    }
}

/**
 * Modal Preview Bukti Foto
 */
export function showEvidencePreview(imgUrl, batchCode, description, isPublic) {
    const modal = document.getElementById('modalPreviewFoto');
    const img = document.getElementById('preview_modal_img');
    const title = document.getElementById('preview_modal_title');
    const desc = document.getElementById('preview_modal_desc');
    const badge = document.getElementById('preview_modal_public_badge');

    if (img) img.src = imgUrl;
    if (title) title.innerText = 'Foto Bukti Porsi - ' + batchCode;
    if (desc) desc.innerText = description || 'Foto porsi siap kirim';
    if (badge) {
        badge.innerHTML = isPublic
            ? '<span style="background: #DCFCE7; color: #15803D; padding: 2px 8px; border-radius: 4px; font-weight: 600;">Publik</span>'
            : '<span style="background: #F1F5F9; color: #64748B; padding: 2px 8px; border-radius: 4px;">Internal</span>';
    }

    if (modal) {
        modal.classList.add('open', 'show');
        modal.style.setProperty('display', 'flex', 'important');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Tutup modal Preview Foto
 */
export function closePreviewModal(event) {
    if (event && event.target && event.target.id !== 'modalPreviewFoto') {
        return;
    }
    const modal = document.getElementById('modalPreviewFoto');
    if (modal) {
        modal.classList.remove('open', 'show');
        modal.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = '';
    }
}

// Attach to window object for inline HTML event handlers (onclick, onchange, oninput)
window.openAddHasilModal = openAddHasilModal;
window.closeAddHasilModal = closeAddHasilModal;
window.handleHasilBatchChange = handleHasilBatchChange;
window.calculateHasilSusut = calculateHasilSusut;
window.setConditionText = setConditionText;
window.previewEvidenceImage = previewEvidenceImage;
window.clearEvidencePreview = clearEvidencePreview;
window.clearSusutEvidencePreview = clearSusutEvidencePreview;
window.openCatatSusutModal = openCatatSusutModal;
window.closeCatatSusutModal = closeCatatSusutModal;
window.showEvidencePreview = showEvidencePreview;
window.closePreviewModal = closePreviewModal;

function initDropzoneEvents(dropzoneId, inputId) {
    const dropzone = document.getElementById(dropzoneId);
    const input = document.getElementById(inputId);
    if (!dropzone || !input) return;

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = '#042A70';
            dropzone.style.backgroundColor = '#EFF6FF';
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = '#CBD5E1';
            dropzone.style.backgroundColor = '#F8FAFC';
        });
    });

    dropzone.addEventListener('drop', function (e) {
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
            input.files = dt.files;
            previewEvidenceImage(input);
        }
    });
}

// Keyboard & Backdrop Listener
document.addEventListener('DOMContentLoaded', function () {
    initDropzoneEvents('dropzone_hasil', 'evidence_file_input');
    initDropzoneEvents('dropzone_susut', 'susut_evidence_file_input');

    // Escape key listener to close modals
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAddHasilModal();
            closeCatatSusutModal();
            closePreviewModal();
        }
    });

    // Close on backdrop click
    const backdropAdd = document.getElementById('modalAddHasil');
    if (backdropAdd) {
        backdropAdd.addEventListener('click', function (e) {
            if (e.target === backdropAdd) {
                closeAddHasilModal();
            }
        });
    }

    const backdropSusut = document.getElementById('modalCatatSusut');
    if (backdropSusut) {
        backdropSusut.addEventListener('click', function (e) {
            if (e.target === backdropSusut) {
                closeCatatSusutModal();
            }
        });
    }
});

