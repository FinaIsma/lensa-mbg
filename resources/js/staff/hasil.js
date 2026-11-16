/**
 * Hasil Produksi JavaScript - Staff SPPG
 * Mengelola form catat hasil produksi, perhitungan susut otomatis,
 * peringatan porsi melebihi target, preview bukti foto, dan modal catat susut limbah.
 */

let currentBatchTarget = 0;

function formatDateTimeLocal(date) {
    const pad = num => String(num).padStart(2, '0');
    const y = date.getFullYear();
    const m = pad(date.getMonth() + 1);
    const d = pad(date.getDate());
    const h = pad(date.getHours());
    const mi = pad(date.getMinutes());
    return `${y}-${m}-${d}T${h}:${mi}`;
}

/**
 * Sinkronisasi otomatis jam uji fisik, estimasi jam distribusi, dan waktu kritis konsumsi
 */
export function autoUpdateCriticalTimes(forceNow = false) {
    const checkedInput = document.getElementById('input_checked_at');
    const distInput = document.getElementById('input_estimated_distribution_at');
    const consumeInput = document.getElementById('input_consume_by');

    if (!checkedInput || !consumeInput) return;

    if (forceNow || !checkedInput.value) {
        const now = new Date();
        checkedInput.value = formatDateTimeLocal(now);
    }

    const checkDate = new Date(checkedInput.value);
    if (!isNaN(checkDate.getTime())) {
        if (distInput && (forceNow || !distInput.value)) {
            // Estimasi distribusi: +30 menit
            const distDate = new Date(checkDate.getTime() + 30 * 60 * 1000);
            distInput.value = formatDateTimeLocal(distDate);
        }

        // Batas maksimal dimakan (waktu kritis): +4 jam
        if (forceNow || !consumeInput.value) {
            const consumeDate = new Date(checkDate.getTime() + 4 * 60 * 60 * 1000);
            consumeInput.value = formatDateTimeLocal(consumeDate);
        }
    }
}

/**
 * Handler saat jam uji fisik (checked_at) diubah manual
 */
export function onCheckedAtChange(val) {
    if (!val) return;
    const checkDate = new Date(val);
    if (isNaN(checkDate.getTime())) return;

    const distInput = document.getElementById('input_estimated_distribution_at');
    const consumeInput = document.getElementById('input_consume_by');

    if (distInput) {
        const distDate = new Date(checkDate.getTime() + 30 * 60 * 1000);
        distInput.value = formatDateTimeLocal(distDate);
    }
    if (consumeInput) {
        const consumeDate = new Date(checkDate.getTime() + 4 * 60 * 60 * 1000);
        consumeInput.value = formatDateTimeLocal(consumeDate);
    }
}

/**
 * Handler saat opsi Tenaga Ahli Gizi dipilih
 */
export function handleNutritionistSelect(select) {
    if (!select) return;
    const nameInput = document.getElementById('hasil_nutritionist_name');
    if (!nameInput) return;

    const opt = select.options[select.selectedIndex];
    const isCustom = select.value === 'custom' || !select.value || (opt && opt.getAttribute('data-custom') === 'true');

    if (opt && opt.getAttribute('data-custom') === 'true') {
        select.value = '';
        nameInput.value = '';
        nameInput.placeholder = 'Ketik nama lengkap Tenaga Ahli Gizi...';
        nameInput.focus();
    } else if (select.value === 'custom') {
        select.value = '';
        nameInput.value = '';
        nameInput.placeholder = 'Ketik nama lengkap Tenaga Ahli Gizi...';
        nameInput.focus();
    } else if (opt && opt.getAttribute('data-nama')) {
        nameInput.value = opt.getAttribute('data-nama');
    }
}

/**
 * Atur waktu kritis konsumsi secara cepat (+N jam dari jam uji fisik)
 */
export function setConsumeByPlusHours(hours = 4) {
    const checkedInput = document.getElementById('input_checked_at');
    const consumeInput = document.getElementById('input_consume_by');
    if (!consumeInput) return;

    const baseDate = (checkedInput && checkedInput.value) ? new Date(checkedInput.value) : new Date();
    if (!isNaN(baseDate.getTime())) {
        const targetDate = new Date(baseDate.getTime() + hours * 60 * 60 * 1000);
        consumeInput.value = formatDateTimeLocal(targetDate);
    }
}

/**
 * Sinkronisasi otomatis ringkasan kondisi dan kesimpulan kelayakan dari 6 parameter matriks fisik
 */
export function syncReportedConditionFromMatrix() {
    const statusSelects = document.querySelectorAll('[name^="physical_test_matrix"][name$="[status]"]');
    const conditionInput = document.getElementById('input_condition');
    const overallStatus = document.getElementById('physical_test_status');

    let hasIssue = false;
    let hasWarning = false;

    statusSelects.forEach(sel => {
        const val = sel.value;
        if (val.includes('tidak') || val.includes('mentah') || val.includes('bocor') || val.includes('kusam') || val.includes('apek')) {
            hasIssue = true;
        } else if (val.includes('kurang') || val.includes('agak') || val.includes('cukup') || val.includes('dingin')) {
            hasWarning = true;
        }
    });

    if (overallStatus) {
        if (hasIssue) {
            overallStatus.value = 'failed';
            overallStatus.style.borderColor = '#EF4444';
            overallStatus.style.color = '#B91C1C';
            overallStatus.style.background = '#FEF2F2';
        } else if (hasWarning) {
            overallStatus.value = 'warning';
            overallStatus.style.borderColor = '#F59E0B';
            overallStatus.style.color = '#92400E';
            overallStatus.style.background = '#FFFBEB';
        } else {
            overallStatus.value = 'passed';
            overallStatus.style.borderColor = '#10B981';
            overallStatus.style.color = '#065F46';
            overallStatus.style.background = '#ECFDF5';
        }
    }

    if (conditionInput && (!conditionInput.value || conditionInput.value.startsWith('Lolos Uji Fisik:'))) {
        if (hasIssue) {
            conditionInput.value = 'Tidak Lolos Uji Fisik: Ditemukan parameter fisik yang tidak memenuhi standar kelayakan saji.';
        } else if (hasWarning) {
            conditionInput.value = 'Lolos Bersyarat: Terdapat catatan minor pada parameter uji fisik sebelum distribusi.';
        } else {
            conditionInput.value = 'Lolos Uji Fisik: Matang sempurna, aroma segar, rasa sesuai resep, higienis siap distribusi';
        }
    }
}

/**
 * Buka modal Catat Hasil Baru
 */
export function openAddHasilModal() {
    autoUpdateCriticalTimes(false);
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

    // Otomatis sesuaikan kategori form sesuai jenis menu batch
    const batchKat = selectedOpt.getAttribute('data-kategori');
    const formTypeSelect = document.getElementById('hasil_form_type');
    if (formTypeSelect && batchKat) {
        if ([...formTypeSelect.options].some(o => o.value === batchKat)) {
            formTypeSelect.value = batchKat;
        }
    }

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
 * Modal Preview Bukti Foto & Detail Pemeriksaan QC
 */
export function showEvidencePreview(
    imgUrl,
    batchCode,
    description,
    isPublic,
    checkedAt,
    estimatedDist,
    consumeBy,
    nutritionist,
    formType,
    note
) {
    const modal = document.getElementById('modalPreviewFoto');
    const img = document.getElementById('preview_modal_img');
    const imgWrapper = document.getElementById('preview_img_wrapper');
    const title = document.getElementById('preview_modal_title');
    const desc = document.getElementById('preview_modal_desc');
    const badge = document.getElementById('preview_modal_public_badge');
    const consumeByEl = document.getElementById('preview_consume_by_text');
    const checkedAtEl = document.getElementById('preview_checked_at_text');
    const estimatedDistEl = document.getElementById('preview_estimated_dist_text');
    const nutritionistEl = document.getElementById('preview_nutritionist_text');
    const formTypeBadge = document.getElementById('preview_form_type_badge');
    const noteBox = document.getElementById('preview_modal_note_box');
    const noteEl = document.getElementById('preview_modal_note');

    if (img) {
        if (imgUrl) {
            img.src = imgUrl;
            if (imgWrapper) imgWrapper.style.display = 'block';
        } else {
            if (imgWrapper) imgWrapper.style.display = 'none';
        }
    }

    if (title) title.innerText = 'Detail Hasil QC & Kelayakan - ' + batchCode;
    if (desc) desc.innerText = description || 'Lolos Uji Fisik & Siap Distribusi';
    if (consumeByEl) consumeByEl.innerText = consumeBy || 'Maks. 4 Jam Pasca Masak';
    if (checkedAtEl) checkedAtEl.innerText = checkedAt || '-';
    if (estimatedDistEl) estimatedDistEl.innerText = estimatedDist || '-';
    if (nutritionistEl) nutritionistEl.innerText = nutritionist || 'Tenaga Ahli Gizi';
    if (formTypeBadge) formTypeBadge.innerText = formType || 'Makanan Siap Santap';

    if (noteBox && noteEl) {
        if (note && note.trim() !== '') {
            noteEl.innerText = note;
            noteBox.style.display = 'block';
        } else {
            noteBox.style.display = 'none';
        }
    }

    if (badge) {
        badge.innerHTML = isPublic
            ? '<span style="background: #DCFCE7; color: #15803D; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 11px;">Publik / QR</span>'
            : '<span style="background: #F1F5F9; color: #64748B; padding: 2px 8px; border-radius: 4px; font-size: 11px;">Internal SPPG</span>';
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
window.autoUpdateCriticalTimes = autoUpdateCriticalTimes;
window.setConsumeByPlusHours = setConsumeByPlusHours;
window.onCheckedAtChange = onCheckedAtChange;
window.handleNutritionistSelect = handleNutritionistSelect;
window.syncReportedConditionFromMatrix = syncReportedConditionFromMatrix;
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

    // Sanitasi form sebelum submit: jika nutritionist_id bernilai non-numeric atau 'custom', kosongkan agar dikirim null
    const formAddHasil = document.getElementById('formAddHasil');
    if (formAddHasil) {
        formAddHasil.addEventListener('submit', function () {
            const select = document.getElementById('hasil_nutritionist_select');
            if (select) {
                const val = (select.value || '').trim();
                if (val === 'custom' || (val !== '' && isNaN(parseInt(val, 10)))) {
                    select.value = '';
                }
            }
        });
    }

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

