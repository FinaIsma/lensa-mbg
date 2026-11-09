/**
 * Script Interaktivitas Modul Distribusi: Pengiriman Staff
 * Lensa MBG
 */

document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi default datetime input jika belum terisi
    const catatDateInput = document.getElementById('catat_distributed_at');
    if (catatDateInput && !catatDateInput.value) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        catatDateInput.value = now.toISOString().slice(0, 16);
    }

    // Keyboard ESC listener untuk menutup modal / drawer
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAllModals();
            closeDrawerDetail();
        }
    });

    // Auto dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-auto-dismiss');
    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });
});

// Helper: Formatter Rupiah & Angka
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num || 0);
}

// Helper: Close all modals
function closeAllModals() {
    document.querySelectorAll('.pengiriman-modal-backdrop.show').forEach((m) => {
        m.classList.remove('show');
    });
}

/* ==========================================================================
   1. MODAL CATAT PENGIRIMAN
   ========================================================================== */
window.openModalCatat = function () {
    const modal = document.getElementById('modalCatatPengiriman');
    const form = document.getElementById('formCatatPengiriman');
    if (form) {
        form.reset();
        const dateInput = document.getElementById('catat_distributed_at');
        if (dateInput) {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            dateInput.value = now.toISOString().slice(0, 16);
        }
    }
    const summaryBox = document.getElementById('catat_batch_summary');
    if (summaryBox) {
        summaryBox.style.display = 'none';
    }
    const portionInput = document.getElementById('catat_portions_sent');
    if (portionInput) {
        portionInput.removeAttribute('max');
    }
    const warningEl = document.getElementById('catat_quota_warning');
    if (warningEl) {
        warningEl.style.display = 'none';
    }

    if (modal) {
        modal.classList.add('show');
    }
};

window.closeModalCatat = function () {
    const modal = document.getElementById('modalCatatPengiriman');
    if (modal) {
        modal.classList.remove('show');
    }
};

// Handler ketika Batch di form Catat dipilih
window.onBatchChange = function (selectEl) {
    const batchId = selectEl.value;
    const summaryBox = document.getElementById('catat_batch_summary');
    const portionInput = document.getElementById('catat_portions_sent');
    const warningEl = document.getElementById('catat_quota_warning');

    if (!batchId) {
        if (summaryBox) summaryBox.style.display = 'none';
        if (portionInput) portionInput.removeAttribute('max');
        if (warningEl) warningEl.style.display = 'none';
        return;
    }

    // Tampilkan placeholder loading
    if (summaryBox) {
        summaryBox.style.display = 'flex';
        document.getElementById('sum_batch_menu').textContent = 'Memuat...';
        document.getElementById('sum_target_porsi').textContent = '-';
        document.getElementById('sum_actual_porsi').textContent = '-';
        document.getElementById('sum_allocated_porsi').textContent = '-';
        document.getElementById('sum_sisa_porsi').textContent = '-';
    }

    fetch(`/staff/pengiriman/batch-info/${batchId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success || !res.data) {
            alert(res.message || 'Gagal memuat informasi batch.');
            return;
        }

        const data = res.data;
        document.getElementById('sum_batch_menu').textContent = `${data.batch_code} - ${data.menu_nama}`;
        document.getElementById('sum_target_porsi').textContent = `${formatNumber(data.target_porsi)} Porsi`;
        document.getElementById('sum_actual_porsi').textContent = `${formatNumber(data.actual_porsi)} Porsi`;
        document.getElementById('sum_allocated_porsi').textContent = `${formatNumber(data.allocated_porsi)} Porsi`;
        
        const sisaEl = document.getElementById('sum_sisa_porsi');
        sisaEl.textContent = `${formatNumber(data.sisa_porsi)} Porsi`;
        sisaEl.style.color = data.sisa_porsi > 0 ? '#10B981' : '#EF4444';

        if (portionInput) {
            portionInput.max = data.sisa_porsi;
            if (data.sisa_porsi <= 0) {
                portionInput.value = 0;
                portionInput.disabled = true;
            } else {
                portionInput.disabled = false;
                if (!portionInput.value || parseInt(portionInput.value) > data.sisa_porsi) {
                    portionInput.value = data.sisa_porsi;
                }
            }
        }

        // Listener check kuota real-time
        if (portionInput) {
            portionInput.oninput = () => {
                const val = parseInt(portionInput.value) || 0;
                if (val > data.sisa_porsi) {
                    warningEl.textContent = `Peringatan: Jumlah input (${val}) melebihi sisa porsi siap kirim (${data.sisa_porsi} porsi).`;
                    warningEl.style.display = 'block';
                } else {
                    warningEl.style.display = 'none';
                }
            };
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
    });
};

/* ==========================================================================
   2. MODAL UBAH PENGIRIMAN (PLANNED)
   ========================================================================== */
window.openModalEdit = function (id, destinationId, portionsSent, distributedAtRaw, batchCode, maxAvailable) {
    const modal = document.getElementById('modalEditPengiriman');
    const form = document.getElementById('formEditPengiriman');
    if (!modal || !form) return;

    form.action = `/staff/pengiriman/${id}`;
    document.getElementById('edit_destination_id').value = destinationId;
    document.getElementById('edit_portions_sent').value = portionsSent;
    document.getElementById('edit_portions_sent').max = maxAvailable;
    document.getElementById('edit_distributed_at').value = distributedAtRaw;
    document.getElementById('edit_batch_code_label').textContent = batchCode;
    document.getElementById('edit_max_label').textContent = `Maksimal kuota batch tersedia: ${formatNumber(maxAvailable)} porsi`;

    modal.classList.add('show');
};

window.closeModalEdit = function () {
    const modal = document.getElementById('modalEditPengiriman');
    if (modal) modal.classList.remove('show');
};

/* ==========================================================================
   3. MODAL TANDAI TERKIRIM (PLANNED -> SENT)
   ========================================================================== */
window.openModalSent = function (id, destinationName, batchCode, portions) {
    const modal = document.getElementById('modalSentPengiriman');
    const form = document.getElementById('formSentPengiriman');
    if (!modal || !form) return;

    form.action = `/staff/pengiriman/${id}/kirim`;
    document.getElementById('sent_dest_label').textContent = destinationName;
    document.getElementById('sent_batch_label').textContent = batchCode;
    document.getElementById('sent_portions_label').textContent = `${formatNumber(portions)} Porsi`;

    const dateInput = document.getElementById('sent_distributed_at');
    if (dateInput) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        dateInput.value = now.toISOString().slice(0, 16);
    }

    modal.classList.add('show');
};

window.closeModalSent = function () {
    const modal = document.getElementById('modalSentPengiriman');
    if (modal) modal.classList.remove('show');
};

/* ==========================================================================
   4. MODAL TANDAI DITERIMA (SENT -> RECEIVED)
   ========================================================================== */
window.openModalReceived = function (id, destinationName, batchCode, portions) {
    const modal = document.getElementById('modalReceivedPengiriman');
    const form = document.getElementById('formReceivedPengiriman');
    if (!modal || !form) return;

    form.action = `/staff/pengiriman/${id}/terima`;
    document.getElementById('recv_dest_label').textContent = destinationName;
    document.getElementById('recv_batch_label').textContent = batchCode;
    document.getElementById('recv_portions_label').textContent = `${formatNumber(portions)} Porsi`;

    modal.classList.add('show');
};

window.closeModalReceived = function () {
    const modal = document.getElementById('modalReceivedPengiriman');
    if (modal) modal.classList.remove('show');
};

/* ==========================================================================
   5. MODAL BATALKAN PENGIRIMAN (PLANNED -> CANCELLED)
   ========================================================================== */
window.openModalCancel = function (id, destinationName, batchCode) {
    const modal = document.getElementById('modalCancelPengiriman');
    const form = document.getElementById('formCancelPengiriman');
    if (!modal || !form) return;

    form.action = `/staff/pengiriman/${id}/batal`;
    document.getElementById('cancel_dest_label').textContent = destinationName;
    document.getElementById('cancel_batch_label').textContent = batchCode;
    document.getElementById('cancellation_reason').value = '';

    modal.classList.add('show');
};

window.closeModalCancel = function () {
    const modal = document.getElementById('modalCancelPengiriman');
    if (modal) modal.classList.remove('show');
};

/* ==========================================================================
   6. SLIDE-OVER DRAWER (DETAIL PENGIRIMAN LENGKAP)
   ========================================================================== */
window.openDrawerDetail = function (id) {
    const backdrop = document.getElementById('drawerDetailPengiriman');
    if (!backdrop) return;

    // Buka drawer dengan loading indicator
    backdrop.classList.add('show');
    document.getElementById('drawer_loading').style.display = 'flex';
    document.getElementById('drawer_content').style.display = 'none';

    fetch(`/staff/pengiriman/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success || !res.data) {
            alert(res.message || 'Gagal memuat detail pengiriman.');
            closeDrawerDetail();
            return;
        }

        const d = res.data;

        const setText = (id, text) => {
            const el = document.getElementById(id);
            if (el) el.textContent = text !== null && text !== undefined ? text : '-';
        };

        // Header info
        setText('drawer_shipment_id', `#KRM-${String(d.id).padStart(4, '0')}`);
        
        const badgeEl = document.getElementById('drawer_status_badge');
        if (badgeEl) {
            badgeEl.textContent = d.status_label || '-';
            badgeEl.className = `status-chip ${d.status_badge_class || 'badge-planned'}`;
        }

        // Timeline Step: 1=planned, 2=sent, 3=received
        const step1 = document.getElementById('timeline_step_1');
        const step2 = document.getElementById('timeline_step_2');
        const step3 = document.getElementById('timeline_step_3');

        if (step1 && step2 && step3) {
            step1.className = 'timeline-step';
            step2.className = 'timeline-step';
            step3.className = 'timeline-step';

            if (d.status === 'cancelled') {
                step1.classList.add('completed');
                const lbl = step2.querySelector('.timeline-label');
                if (lbl) lbl.textContent = 'Dibatalkan';
                step2.classList.add('active');
                const node = step2.querySelector('.timeline-node');
                if (node) {
                    node.style.backgroundColor = '#EF4444';
                    node.style.borderColor = '#EF4444';
                }
            } else {
                const lbl = step2.querySelector('.timeline-label');
                if (lbl) lbl.textContent = 'Dalam Kirim';
                const node = step2.querySelector('.timeline-node');
                if (node) {
                    node.style.backgroundColor = '';
                    node.style.borderColor = '';
                }

                if (d.timeline_step >= 1) step1.classList.add(d.timeline_step > 1 ? 'completed' : 'active');
                if (d.timeline_step >= 2) step2.classList.add(d.timeline_step > 2 ? 'completed' : 'active');
                if (d.timeline_step >= 3) step3.classList.add('completed');
            }
        }

        // Card Tujuan
        if (d.destination) {
            setText('drawer_dest_name', d.destination.nama);
            setText('drawer_dest_type', d.destination.tipe);
            setText('drawer_dest_address', d.destination.alamat);
            setText('drawer_dest_target', `${formatNumber(d.destination.target_penerima)} Porsi Target`);
        }

        // Card Batch & Porsi
        if (d.batch) {
            setText('drawer_batch_code', d.batch.batch_code);
            setText('drawer_menu_name', d.batch.menu_nama);
            setText('drawer_batch_target', formatNumber(d.batch.target_porsi));
            setText('drawer_batch_actual', formatNumber(d.batch.actual_porsi));
            setText('drawer_batch_allocated', formatNumber(d.batch.allocated_porsi));
            setText('drawer_batch_sisa', formatNumber(d.batch.sisa_porsi));

            // Progress persentase alokasi batch
            const pctAllocated = d.batch.actual_porsi > 0
                ? Math.min(100, Math.round((d.batch.allocated_porsi / d.batch.actual_porsi) * 100))
                : 0;
            const bar = document.getElementById('drawer_batch_progress_bar');
            if (bar) bar.style.width = `${pctAllocated}%`;
            setText('drawer_batch_pct', `${pctAllocated}% Dialokasikan`);
        }
        setText('drawer_portions_sent', `${formatNumber(d.portions_sent)} Porsi`);

        // Meta timestamps & Petugas
        setText('drawer_distributed_at', d.distributed_at);
        setText('drawer_recorder_name', d.recorder_name);
        setText('drawer_created_at', d.created_at);

        // Evidence Gallery
        const evidenceContainer = document.getElementById('drawer_evidences_list');
        const emptyEvidence = document.getElementById('drawer_no_evidence');
        if (evidenceContainer) {
            evidenceContainer.innerHTML = '';

            if (d.evidences && d.evidences.length > 0) {
                if (emptyEvidence) emptyEvidence.style.display = 'none';
                d.evidences.forEach(ev => {
                    const card = document.createElement('a');
                    card.href = ev.file_url;
                    card.target = '_blank';
                    card.className = 'evidence-thumb-card';
                    card.innerHTML = `
                        <img src="${ev.file_url}" alt="Bukti Foto" class="evidence-thumb-img" loading="lazy">
                        <div class="evidence-thumb-info">
                            <div class="font-semibold text-slate-800 truncate">${ev.description || 'Foto Pengiriman'}</div>
                            <div class="text-[10px] text-slate-500">${ev.uploaded_at}</div>
                        </div>
                    `;
                    evidenceContainer.appendChild(card);
                });
            } else {
                if (emptyEvidence) emptyEvidence.style.display = 'block';
            }
        }

        // Tombol Aksi di Footer Drawer
        const footerActions = document.getElementById('drawer_footer_actions');
        if (footerActions) {
            footerActions.innerHTML = '';

            if (d.status === 'planned' && d.destination && d.batch) {
                footerActions.innerHTML = `
                    <button type="button" class="btn-act btn-act-cancel" onclick="closeDrawerDetail(); openModalCancel(${d.id}, '${d.destination.nama}', '${d.batch.batch_code}')">
                        Batalkan
                    </button>
                    <button type="button" class="btn-act btn-act-send" onclick="closeDrawerDetail(); openModalSent(${d.id}, '${d.destination.nama}', '${d.batch.batch_code}', ${d.portions_sent})">
                        Memberangkatkan
                    </button>
                `;
            } else if (d.status === 'sent' && d.destination && d.batch) {
                footerActions.innerHTML = `
                    <button type="button" class="btn-act btn-act-receive" onclick="closeDrawerDetail(); openModalReceived(${d.id}, '${d.destination.nama}', '${d.batch.batch_code}', ${d.portions_sent})">
                        Tandai Telah Diterima
                    </button>
                `;
            }
        }

        const loader = document.getElementById('drawer_loading');
        if (loader) loader.style.display = 'none';
        const content = document.getElementById('drawer_content');
        if (content) content.style.display = 'flex';
    })
    .catch(err => {
        console.error('Drawer fetch error:', err);
        alert('Terjadi kesalahan saat memuat detail pengiriman.');
        closeDrawerDetail();
    });
};

window.closeDrawerDetail = function () {
    const backdrop = document.getElementById('drawerDetailPengiriman');
    if (backdrop) {
        backdrop.classList.remove('show');
    }
};

