/**
 * Modul Lot Bahan Baku & Pemindai QR/Barcode - Staff SPPG
 * Mengatur interaksi pemindai kamera live, tab navigasi, dan kartu mutasi stok lot.
 */
    let liveScannerInstance = null;
    let isCameraRunning = false;
    let cameraDevices = [];
    let selectedCameraId = null;

    // State Inspeksi
    let currentInspectedLotId = null;

    // Helper update status pill badge di header kamera
    function setCameraBadge(type, text) {
        const badge = document.getElementById('camera-live-badge');
        if (!badge) return;
        badge.className = `camera-status-pill ${type}`;
        badge.innerText = text;
    }

    // 1. NAVIGASI SISTEM TAB UTAMA
    function switchMainTab(tab) {
        const btnScan = document.getElementById('tab-btn-scan');
        const btnTable = document.getElementById('tab-btn-table');
        const contentScan = document.getElementById('main-tab-content-scan');
        const contentTable = document.getElementById('main-tab-content-table');

        if (tab === 'scan') {
            btnScan.classList.add('active');
            btnTable.classList.remove('active');
            contentScan.style.display = 'block';
            contentTable.style.display = 'none';
        } else {
            btnTable.classList.add('active');
            btnScan.classList.remove('active');
            contentTable.style.display = 'block';
            contentScan.style.display = 'none';
        }

        // Sinkronkan preferensi tab ke URL browser tanpa reload halaman
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url.toString());
        } catch(e) {}
    }

    // 2. DUKUNGAN SCANNER USB & PENCARIAN MANUAL
    const searchInput = document.getElementById('barcode-search-input');
    if (searchInput) {
        searchInput.addEventListener('keydown', async function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                await performManualLookup();
            }
        });
    }

    async function performManualLookup() {
        const val = searchInput ? searchInput.value.trim() : '';
        if (!val) {
            showLotNotFoundModal('-', 'Silakan masukkan barcode atau nomor lot pada kotak Input Manual terlebih dahulu.');
            if (searchInput) searchInput.focus();
            return;
        }
        await lookupAndShowLot(val);
    }

    // 3. DETEKSI KAMERA & INISIALISASI
    document.addEventListener('DOMContentLoaded', async function() {
        if (typeof Html5Qrcode !== 'undefined') {
            try {
                cameraDevices = await Html5Qrcode.getCameras();
                const dropdown = document.getElementById('camera-select-dropdown');
                dropdown.innerHTML = '';

                if (cameraDevices && cameraDevices.length > 0) {
                    cameraDevices.forEach((cam, idx) => {
                        const opt = document.createElement('option');
                        opt.value = cam.id;
                        opt.text = cam.label || `Kamera ${idx + 1}`;
                        dropdown.appendChild(opt);
                    });

                    // Prioritaskan kamera belakang jika ada, atau kamera pertama (webcam)
                    const back = cameraDevices.find(c => /back|rear|environment|belakang/i.test(c.label));
                    selectedCameraId = back ? back.id : cameraDevices[0].id;
                    dropdown.value = selectedCameraId;
                    setCameraBadge('standby', 'Kamera Siaga');
                } else {
                    dropdown.innerHTML = '<option value="">Tidak ada kamera terdeteksi</option>';
                    setCameraBadge('standby', 'Tidak Ada Kamera');
                }
            } catch(e) {
                console.warn("Gagal deteksi kamera awal:", e);
                setCameraBadge('standby', 'Izin Belum Aktif');
            }
        }
    });

    // Toggle Nyalakan / Matikan Kamera Live
    async function toggleLiveCamera() {
        if (isCameraRunning) {
            await stopLiveCamera();
        } else {
            await startLiveCamera();
        }
    }

    async function startLiveCamera() {
        const btnToggle = document.getElementById('btn-toggle-camera-text');
        const dot = document.getElementById('live-indicator-dot');
        const placeholder = document.getElementById('camera-placeholder-msg');

        setCameraBadge('searching', 'Menghubungkan...');

        try {
            if (typeof Html5Qrcode === 'undefined') {
                setCameraBadge('standby', 'Library Error');
                return;
            }

            if (!selectedCameraId && cameraDevices.length > 0) {
                selectedCameraId = cameraDevices[0].id;
            }

            if (!liveScannerInstance) {
                liveScannerInstance = new Html5Qrcode("embedded-camera-feed");
            }

            // Area penuh tanpa batasan kotakan!
            const config = {
                fps: 20,
                aspectRatio: 1.333
            };

            await liveScannerInstance.start(
                selectedCameraId || { facingMode: "user" },
                config,
                (decodedText, decodedResult) => {
                    handleSuccessfulScan(decodedText);
                },
                (errorMessage) => {
                    // Frame scan aktif...
                }
            );

            isCameraRunning = true;
            btnToggle.innerText = 'Matikan Kamera';
            dot.className = 'live-pulse-dot';
            placeholder.style.display = 'none';
            setCameraBadge('active', 'Kamera Aktif');
        } catch (err) {
            console.error("Gagal menyalakan kamera live:", err);
            isCameraRunning = false;
            btnToggle.innerText = 'Nyalakan Kamera';
            dot.className = 'live-pulse-dot inactive';
            placeholder.style.display = 'flex';
            setCameraBadge('standby', 'Gagal Buka Kamera');
            alert('Tidak dapat mengakses kamera: ' + (err.message || err));
        }
    }

    async function stopLiveCamera() {
        if (liveScannerInstance && isCameraRunning) {
            try {
                await liveScannerInstance.stop();
            } catch(e) {}
        }
        isCameraRunning = false;
        document.getElementById('btn-toggle-camera-text').innerText = 'Nyalakan Kamera';
        document.getElementById('live-indicator-dot').className = 'live-pulse-dot inactive';
        document.getElementById('camera-placeholder-msg').style.display = 'flex';
        setCameraBadge('standby', 'Kamera Siaga');
    }

    async function changeCameraDevice(newCameraId) {
        if (!newCameraId) return;
        selectedCameraId = newCameraId;
        if (isCameraRunning) {
            await stopLiveCamera();
            await startLiveCamera();
        }
    }

    // 4. DETEKSI HASIL SCAN BERHASIL
    let isProcessingScan = false;
    async function handleSuccessfulScan(codeText) {
        if (isProcessingScan) return;
        isProcessingScan = true;

        // Beep audio feedback
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch(e) {}

        await lookupAndShowLot(codeText);

        setTimeout(() => {
            isProcessingScan = false;
        }, 2000);
    }

    // 5. SCAN DARI UPLOAD FILE FOTO
    async function handleFileScan(inputEl) {
        if (!inputEl.files || inputEl.files.length === 0) return;
        const file = inputEl.files[0];
        setCameraBadge('searching', 'Membaca Foto...');

        try {
            const fileReaderScanner = new Html5Qrcode("file-decode-sandbox");
            const decodedText = await fileReaderScanner.scanFile(file, true);
            fileReaderScanner.clear();

            await handleSuccessfulScan(decodedText);
            setCameraBadge('success', 'Foto Terbaca!');
        } catch (err) {
            console.error("Gagal baca QR dari file:", err);
            showLotNotFoundModal('File Gambar Tidak Terbaca', 'Gambar QR Code tidak dapat dikenali. Pastikan foto memiliki pencahayaan cukup terang, fokus jelas, dan gambar kode tidak terpotong.');
            setCameraBadge('standby', isCameraRunning ? 'Kamera Aktif' : 'Kamera Siaga');
        }
    }

    // Modal Alert Tematik "Data Lot Tidak Ditemukan"
    function showLotNotFoundModal(scannedCode, customMessage) {
        const modal = document.getElementById('lotNotFoundModal');
        const codeEl = document.getElementById('lotNotFoundScannedCode');
        const msgEl = document.getElementById('lotNotFoundMessage');

        if (codeEl) {
            codeEl.innerText = scannedCode || '-';
        }
        if (msgEl && customMessage) {
            msgEl.innerText = customMessage;
        }

        if (modal) {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeLotNotFoundModal() {
        const modal = document.getElementById('lotNotFoundModal');
        if (modal) {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }
        setCameraBadge('standby', isCameraRunning ? 'Kamera Aktif' : 'Kamera Siaga');
        isProcessingScan = false;
        const searchInput = document.getElementById('barcode-search-input');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    // Helper Tampilkan & Sembunyikan Animasi Loading di Area Hasil Inspeksi
    function showInspectionLoading(code) {
        const emptyEl = document.getElementById('empty-inspection-state');
        if (emptyEl) emptyEl.style.display = 'none';

        const detectedPanel = document.getElementById('detected-inspection-state');
        if (detectedPanel) detectedPanel.style.display = 'none';

        const loadingEl = document.getElementById('loading-inspection-state');
        if (loadingEl) {
            const previewEl = document.getElementById('loading-lot-code-preview');
            if (previewEl) previewEl.innerText = code || '...';
            loadingEl.style.display = 'block';
            loadingEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        const btn = document.getElementById('btn-manual-search');
        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.85';
            btn.innerHTML = `
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin-icon">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                </svg>
                <span>Memuat...</span>
            `;
        }
    }

    function hideInspectionLoading() {
        const loadingEl = document.getElementById('loading-inspection-state');
        if (loadingEl) loadingEl.style.display = 'none';

        const btn = document.getElementById('btn-manual-search');
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.innerHTML = `<span>Periksa</span>`;
        }
    }

    // 6. LOOKUP API & TAMPILKAN PANEL HASIL INSPEKSI
    async function lookupAndShowLot(code) {
        if (!code || !code.trim()) {
            showLotNotFoundModal('-', 'Silakan ketik atau pindai kode lot bahan terlebih dahulu.');
            return;
        }

        const rawCode = code.trim();
        const cleanCode = encodeURIComponent(rawCode);
        setCameraBadge('searching', 'Mencari data...');
        showInspectionLoading(rawCode);

        try {
            const res = await fetch(`/staff/lot/barcode/${cleanCode}?code=${cleanCode}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json().catch(() => null);

            if (data && data.success && data.lot_id) {
                setCameraBadge('success', '✓ Ditemukan');
                await loadLotInspectionData(data.lot_id);
            } else {
                hideInspectionLoading();
                const emptyEl = document.getElementById('empty-inspection-state');
                if (emptyEl) emptyEl.style.display = 'block';
                setCameraBadge('standby', isCameraRunning ? 'Kamera Aktif' : 'Kamera Siaga');
                const errMsg = (data && data.message) 
                    ? data.message 
                    : `Data lot tidak ditemukan pada unit SPPG ini. Pastikan label QR Code atau Barcode yang dipindai sudah benar.`;
                showLotNotFoundModal(rawCode, errMsg);
            }
        } catch (err) {
            console.error(err);
            hideInspectionLoading();
            const emptyEl = document.getElementById('empty-inspection-state');
            if (emptyEl) emptyEl.style.display = 'block';
            setCameraBadge('standby', isCameraRunning ? 'Kamera Aktif' : 'Kamera Siaga');
            showLotNotFoundModal(rawCode, 'Terjadi kendala saat memeriksa kode lot ke server.');
        }
    }

    // Load Data Lengkap ke Panel Inspeksi Tab 1
    async function loadLotInspectionData(lotId) {
        try {
            const loadingEl = document.getElementById('loading-inspection-state');
            if (!loadingEl || loadingEl.style.display === 'none') {
                showInspectionLoading('ID Lot #' + lotId);
            }

            const res = await fetch(`/staff/lot/${lotId}?format=json`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (!data || !data.success) {
                hideInspectionLoading();
                const emptyEl = document.getElementById('empty-inspection-state');
                if (emptyEl) emptyEl.style.display = 'block';
                return;
            }

            const l = data.lot;
            currentInspectedLotId = l.id;

            // Pastikan aktif di Tab 1
            switchMainTab('scan');

            // Sembunyikan loading & placeholder, tampilkan panel data hasil inspeksi
            hideInspectionLoading();
            const emptyEl = document.getElementById('empty-inspection-state');
            if (emptyEl) emptyEl.style.display = 'none';

            const detectedPanel = document.getElementById('detected-inspection-state');
            if (detectedPanel) detectedPanel.style.display = 'block';

            // Isi data header
            const barcodeEl = document.getElementById('insp-lot-barcode');
            if (barcodeEl) barcodeEl.innerText = l.barcode || '-';

            const matTitleEl = document.getElementById('insp-material-title');
            if (matTitleEl) matTitleEl.innerText = `${l.material_nama} (${l.material_kategori})`;

            const statusBadge = document.getElementById('insp-status-badge');
            if (statusBadge) {
                statusBadge.innerText = l.status_label;
                statusBadge.className = `badge-status ${l.status_badge_class}`;
            }

            const expBadge = document.getElementById('insp-expiry-badge');
            if (expBadge) {
                expBadge.innerText = l.expiry_status_label;
                expBadge.className = `badge-status ${l.expiry_badge_class}`;
            }

            // Isi grid info
            const lotNumEl = document.getElementById('insp-lot-number');
            if (lotNumEl) lotNumEl.innerText = l.lot_number || '-';

            const suppEl = document.getElementById('insp-supplier');
            if (suppEl) suppEl.innerText = l.supplier_nama || '-';

            const recDateEl = document.getElementById('insp-received-date');
            if (recDateEl) recDateEl.innerText = l.received_date || '-';

            const expDateEl = document.getElementById('insp-expiry-date');
            if (expDateEl) expDateEl.innerText = `${l.expiry_date} (${l.expiry_status_label})`;

            const qtyInfoEl = document.getElementById('insp-qty-info');
            if (qtyInfoEl) {
                qtyInfoEl.innerText = `${Number(l.current_qty).toLocaleString('id-ID')} / ${Number(l.initial_qty).toLocaleString('id-ID')} ${l.material_satuan}`;
            }

            const unitCostEl = document.getElementById('insp-unit-cost');
            if (unitCostEl) unitCostEl.innerText = l.formatted_unit_cost || '-';

            const recNoEl = document.getElementById('insp-receiving-no');
            if (recNoEl) recNoEl.innerText = l.receiving_no || '-';

            const purNoEl = document.getElementById('insp-purchase-no');
            if (purNoEl) purNoEl.innerText = l.purchase_no || '-';

            // Render Kartu Stok Movements
            const tbody = document.getElementById('insp-movements-tbody');
            if (tbody) {
                tbody.innerHTML = '';

                if (data.movements && data.movements.length > 0) {
                    data.movements.forEach(m => {
                        const isIN = m.direction === 'IN';
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td style="padding: 10px 14px; color: #475569;">${m.created_at}</td>
                            <td style="padding: 10px 14px; font-weight: 600;">
                                <span class="badge-status ${isIN ? 'badge-safe' : 'badge-cancelled'}" style="padding: 2px 8px; font-size: 11px;">
                                    ${m.movement_type}
                                </span>
                            </td>
                            <td style="padding: 10px 14px; text-align: right; font-weight: 700; color: ${isIN ? '#16A34A' : '#DC2626'};">
                                ${isIN ? '+' : '-'}${Number(m.qty).toLocaleString('id-ID')} ${l.material_satuan}
                            </td>
                            <td style="padding: 10px 14px; text-align: right; font-weight: 800; color: #071E49;">
                                ${Number(m.balance_after).toLocaleString('id-ID')} ${l.material_satuan}
                            </td>
                            <td style="padding: 10px 14px; color: #64748B;">${m.creator_name}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 24px; color: #94A3B8;">Belum ada mutasi stok tercatat untuk lot ini.</td></tr>`;
                }
            }

            // Smooth scroll ke panel hasil
            if (detectedPanel) {
                detectedPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

        } catch (err) {
            console.error("Gagal load data inspeksi:", err);
            hideInspectionLoading();
            const emptyEl = document.getElementById('empty-inspection-state');
            if (emptyEl) emptyEl.style.display = 'block';
        }
    }

    // Reset Panel Hasil Inspeksi untuk Scan Ulang
    function resetInspectionState() {
        currentInspectedLotId = null;
        hideInspectionLoading();
        const detectedPanel = document.getElementById('detected-inspection-state');
        if (detectedPanel) detectedPanel.style.display = 'none';
        const emptyEl = document.getElementById('empty-inspection-state');
        if (emptyEl) emptyEl.style.display = 'block';
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        setCameraBadge('standby', isCameraRunning ? 'Kamera Aktif' : 'Kamera Siaga');
    }

    // Pindah dari Tabel Tab 2 ke Pemindai Tab 1
    function inspectLotFromTable(lotId) {
        switchMainTab('scan');
        showInspectionLoading('ID Lot #' + lotId);
        loadLotInspectionData(lotId);
    }

// Expose functions globally on window for inline HTML onclick handlers
window.setCameraBadge = setCameraBadge;
window.switchMainTab = switchMainTab;
window.performManualLookup = performManualLookup;
window.toggleLiveCamera = toggleLiveCamera;
window.startLiveCamera = startLiveCamera;
window.stopLiveCamera = stopLiveCamera;
window.changeCameraDevice = changeCameraDevice;
window.handleSuccessfulScan = handleSuccessfulScan;
window.handleFileScan = handleFileScan;
window.showLotNotFoundModal = showLotNotFoundModal;
window.closeLotNotFoundModal = closeLotNotFoundModal;
window.showInspectionLoading = showInspectionLoading;
window.hideInspectionLoading = hideInspectionLoading;
window.lookupAndShowLot = lookupAndShowLot;
window.loadLotInspectionData = loadLotInspectionData;
window.resetInspectionState = resetInspectionState;
window.inspectLotFromTable = inspectLotFromTable;

