/**
 * Limbah & Surplus JavaScript - Staff SPPG
 * Mengelola form modal dinamis (berubah menurut tipe limbah),
 * live AJAX info batch & lot, validasi batas porsi/stok, dan modal ajukan koreksi.
 */

let currentMaxQty = null;
let currentBatchData = null;
let currentLotData = null;

/**
 * Buka modal Catat Limbah Baru (mendukung pre-filled dari pintasan)
 */
export function openAddLimbahModal(defaultType = null, defaultBatchId = null, defaultLotId = null, defaultQty = null) {
    const modal = document.getElementById('modalAddLimbah');
    if (!modal) return;

    modal.classList.add('open', 'show');
    modal.style.setProperty('display', 'flex', 'important');
    document.body.style.overflow = 'hidden';

    const typeSelect = document.getElementById('limbah_type_select');
    if (defaultType && typeSelect) {
        typeSelect.value = defaultType;
        handleLimbahTypeChange(defaultType);
    } else if (typeSelect) {
        handleLimbahTypeChange(typeSelect.value || 'PRODUCTION_LOSS');
    }

    if (defaultBatchId) {
        const batchSelect = document.getElementById('limbah_batch_select');
        if (batchSelect) {
            batchSelect.value = defaultBatchId;
            handleLimbahBatchChange(batchSelect);
        }
    }

    if (defaultLotId) {
        const lotSelect = document.getElementById('limbah_lot_select');
        if (lotSelect) {
            lotSelect.value = defaultLotId;
            handleLimbahLotChange(lotSelect);
        }
    }

    if (defaultQty) {
        const qtyInput = document.getElementById('limbah_qty_input');
        if (qtyInput) qtyInput.value = defaultQty;
    }
}

/**
 * Tutup modal Catat Limbah
 */
export function closeAddLimbahModal() {
    const modal = document.getElementById('modalAddLimbah');
    if (modal) {
        modal.classList.remove('open', 'show');
        modal.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = '';
    }
}

/**
 * Handler ketika Tipe Limbah berubah
 * - Mengubah field yang tampil sesuai tipe
 * - Mengubah label satuan otomatis
 */
export function handleLimbahTypeChange(selectedType) {
    const secBatch = document.getElementById('sec_batch');
    const secDist = document.getElementById('sec_distribution');
    const secLot = document.getElementById('sec_lot');
    const batchSelect = document.getElementById('limbah_batch_select');
    const lotSelect = document.getElementById('limbah_lot_select');
    const unitLabel = document.getElementById('limbah_unit_label');
    const helperBox = document.getElementById('limbah_hint_box');
    const helperText = document.getElementById('limbah_hint_text');

    if (secDist) secDist.style.display = 'none';
    if (helperBox) helperBox.style.display = 'none';

    if (selectedType === 'MATERIAL_DISPOSAL') {
        // Tipe Bahan Dibuang: hanya Lot bahan, tanpa batch
        if (secBatch) secBatch.style.display = 'none';
        if (secLot) secLot.style.display = 'block';
        if (batchSelect) batchSelect.removeAttribute('required');
        if (lotSelect) {
            lotSelect.setAttribute('required', 'required');
            handleLimbahLotChange(lotSelect);
        }
    } else {
        // Tipe Porsi: PRODUCTION_LOSS, FOOD_WASTE, SURPLUS
        if (secLot) secLot.style.display = 'none';
        if (secBatch) secBatch.style.display = 'block';
        if (lotSelect) lotSelect.removeAttribute('required');
        if (batchSelect) {
            batchSelect.setAttribute('required', 'required');
            handleLimbahBatchChange(batchSelect);
        }

        if (unitLabel) unitLabel.innerText = 'Porsi';

        if (selectedType === 'FOOD_WASTE') {
            if (secDist) secDist.style.display = 'block';
        }
    }
}

/**
 * Handler saat Batch dipilih -> Fetch data batch live
 */
export function handleLimbahBatchChange(select) {
    if (!select || !select.value) {
        currentBatchData = null;
        updateLimbahHint();
        return;
    }

    const batchId = select.value;
    const typeSelect = document.getElementById('limbah_type_select');
    const currentType = typeSelect ? typeSelect.value : 'PRODUCTION_LOSS';

    fetch(`/staff/limbah/batch-info/${batchId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentBatchData = data.batch;

                // Isi dropdown pengiriman jika tipe FOOD_WASTE
                const distSelect = document.getElementById('limbah_distribution_select');
                if (distSelect) {
                    distSelect.innerHTML = '<option value="">-- Semua / Tanpa Pengiriman Spesifik --</option>';
                    if (data.distributions && data.distributions.length > 0) {
                        data.distributions.forEach(d => {
                            const opt = document.createElement('option');
                            opt.value = d.id;
                            opt.text = `${d.destination_nama} (${d.portions_sent} porsi) - ${d.status}`;
                            opt.setAttribute('data-sent', d.portions_sent);
                            distSelect.appendChild(opt);
                        });
                    }
                }

                updateLimbahHint(currentType);
            }
        })
        .catch(err => console.error('Gagal memuat info batch:', err));
}

/**
 * Handler saat Lot Bahan dipilih -> Fetch data lot live
 */
export function handleLimbahLotChange(select) {
    if (!select || !select.value) {
        currentLotData = null;
        updateLimbahHint('MATERIAL_DISPOSAL');
        return;
    }

    const lotId = select.value;

    fetch(`/staff/limbah/lot-info/${lotId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentLotData = data.lot;
                const unitLabel = document.getElementById('limbah_unit_label');
                if (unitLabel) {
                    unitLabel.innerText = data.lot.satuan || 'unit';
                }
                updateLimbahHint('MATERIAL_DISPOSAL');
            }
        })
        .catch(err => console.error('Gagal memuat info lot:', err));
}

/**
 * Update petunjuk batas & info otomatis
 */
function updateLimbahHint(type = null) {
    const helperBox = document.getElementById('limbah_hint_box');
    const helperText = document.getElementById('limbah_hint_text');
    const qtyInput = document.getElementById('limbah_qty_input');

    if (!type) {
        const typeSelect = document.getElementById('limbah_type_select');
        type = typeSelect ? typeSelect.value : 'PRODUCTION_LOSS';
    }

    if (!helperBox || !helperText) return;

    if (type === 'MATERIAL_DISPOSAL' && currentLotData) {
        helperBox.style.display = 'block';
        helperText.innerHTML = `<strong>Petunjuk Stok:</strong> Sisa stok lot saat ini <strong>${currentLotData.current_qty} ${currentLotData.satuan}</strong> (Kedaluwarsa: ${currentLotData.expiry_date}). Jumlah dibuang maksimal $\\le$ sisa stok lot.`;
        if (qtyInput) {
            qtyInput.max = currentLotData.current_qty;
            qtyInput.step = '0.001';
        }
    } else if (type === 'SURPLUS' && currentBatchData) {
        helperBox.style.display = 'block';
        helperText.innerHTML = `<strong>Petunjuk SURPLUS:</strong> Porsi belum terkirim: <strong style="color: #2563EB;">${currentBatchData.belum_kirim_porsi} porsi</strong> (Porsi siap: ${currentBatchData.actual_porsi}, Terkirim: ${currentBatchData.terkirim_porsi}). Jumlah surplus maksimal $\\le$ porsi belum terkirim.`;
        if (qtyInput) {
            qtyInput.max = currentBatchData.belum_kirim_porsi;
            qtyInput.step = '1';
        }
    } else if (type === 'PRODUCTION_LOSS' && currentBatchData) {
        helperBox.style.display = 'block';
        helperText.innerHTML = `<strong>Petunjuk Susut Olah:</strong> Target batch: ${currentBatchData.target_porsi} ps, Realisasi siap: ${currentBatchData.actual_porsi} ps. Estimasi selisih susut: <strong>${currentBatchData.max_loss} porsi</strong>.`;
        if (qtyInput) {
            qtyInput.max = currentBatchData.max_loss > 0 ? currentBatchData.max_loss : 999999;
            qtyInput.step = '0.1';
        }
    } else if (type === 'FOOD_WASTE' && currentBatchData) {
        helperBox.style.display = 'block';
        helperText.innerHTML = `<strong>Petunjuk Food Waste:</strong> Total porsi terkirim batch ini: <strong>${currentBatchData.terkirim_porsi} porsi</strong>. Jumlah sisa makanan maksimal $\\le$ porsi terkirim.`;
        if (qtyInput) {
            qtyInput.max = currentBatchData.terkirim_porsi;
            qtyInput.step = '0.1';
        }
    } else {
        helperBox.style.display = 'none';
        if (qtyInput) {
            qtyInput.removeAttribute('max');
        }
    }
}

/**
 * Modal Ajukan Koreksi Pembatalan (Void)
 */
export function openKoreksiLimbahModal(recordId, typeLabel, qtyWithSatuan) {
    const modal = document.getElementById('modalKoreksiLimbah');
    const idInput = document.getElementById('koreksi_record_id');
    const infoText = document.getElementById('koreksi_record_info');

    if (idInput) idInput.value = recordId;
    if (infoText) {
        infoText.innerHTML = `Anda mengajukan pembatalan (void) untuk catatan <strong>${typeLabel}</strong> sebesar <strong>${qtyWithSatuan}</strong>.`;
    }

    if (modal) {
        modal.classList.add('open', 'show');
        modal.style.setProperty('display', 'flex', 'important');
        document.body.style.overflow = 'hidden';
    }
}

export function closeKoreksiLimbahModal() {
    const modal = document.getElementById('modalKoreksiLimbah');
    if (modal) {
        modal.classList.remove('open', 'show');
        modal.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = '';
    }
}

/**
 * Preview Foto Bukti
 */
export function showEvidencePreview(imgUrl, title, desc) {
    const modal = document.getElementById('modalPreviewFoto');
    const img = document.getElementById('preview_modal_img');
    const titleEl = document.getElementById('preview_modal_title');
    const descEl = document.getElementById('preview_modal_desc');

    if (img) img.src = imgUrl;
    if (titleEl) titleEl.innerText = title || 'Foto Bukti';
    if (descEl) descEl.innerText = desc || '-';

    if (modal) {
        modal.classList.add('open', 'show');
        modal.style.setProperty('display', 'flex', 'important');
        document.body.style.overflow = 'hidden';
    }
}

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

/**
 * Preview file foto bukti fisik yang dipilih
 */
export function previewLimbahEvidence(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.getElementById('limbah_preview_img');
            const box = document.getElementById('limbah_image_preview_box');
            if (img && box) {
                img.src = e.target.result;
                box.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }
}

/**
 * Hapus pilihan file foto bukti fisik
 */
export function clearLimbahEvidence() {
    const input = document.getElementById('limbah_evidence_file_input');
    const box = document.getElementById('limbah_image_preview_box');
    const img = document.getElementById('limbah_preview_img');
    if (input) input.value = '';
    if (img) img.src = '';
    if (box) box.style.display = 'none';
}

// Global window binding
window.openAddLimbahModal = openAddLimbahModal;
window.closeAddLimbahModal = closeAddLimbahModal;
window.handleLimbahTypeChange = handleLimbahTypeChange;
window.handleLimbahBatchChange = handleLimbahBatchChange;
window.handleLimbahLotChange = handleLimbahLotChange;
window.openKoreksiLimbahModal = openKoreksiLimbahModal;
window.closeKoreksiLimbahModal = closeKoreksiLimbahModal;
window.showEvidencePreview = showEvidencePreview;
window.closePreviewModal = closePreviewModal;
window.previewLimbahEvidence = previewLimbahEvidence;
window.clearLimbahEvidence = clearLimbahEvidence;

// Event Listeners
document.addEventListener('DOMContentLoaded', function () {
    // Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAddLimbahModal();
            closeKoreksiLimbahModal();
            closePreviewModal();
        }
    });

    // Close on backdrop click
    const backdropAdd = document.getElementById('modalAddLimbah');
    if (backdropAdd) {
        backdropAdd.addEventListener('click', function (e) {
            if (e.target === backdropAdd) closeAddLimbahModal();
        });
    }

    const backdropKoreksi = document.getElementById('modalKoreksiLimbah');
    if (backdropKoreksi) {
        backdropKoreksi.addEventListener('click', function (e) {
            if (e.target === backdropKoreksi) closeKoreksiLimbahModal();
        });
    }

    // Auto open modal jika ada parameter URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('create') === '1' || urlParams.get('type')) {
        openAddLimbahModal(
            urlParams.get('type'),
            urlParams.get('batch_id'),
            urlParams.get('lot_id'),
            urlParams.get('qty')
        );
    }
});

