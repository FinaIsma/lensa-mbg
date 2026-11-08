@extends('layouts.staff')

@section('title', 'Buat Batch Produksi - Staff SPPG')

@section('content')
<div class="space-y-6 pb-12" style="max-width: 1040px; margin: 0 auto;">
    {{-- Page Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('staff.batches.index') }}" class="btn-action-icon" style="width: 38px; height: 38px;" title="Kembali ke Daftar Batch">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <div>
                <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 700; color: #071E49;">
                    Buat Batch Produksi
                </h1>
                <p class="page-subtitle" style="margin: 2px 0 0; font-size: 13px; color: #64748B;">
                    Satu batch = satu menu = satu ukuran porsi. Alokasikan lot bahan baku dengan aturan FEFO.
                </p>
            </div>
        </div>
    </div>

    <form id="batchForm" method="POST" action="{{ route('staff.batches.store') }}">
        @csrf
        <input type="hidden" name="action" id="formAction" value="draft">

        {{-- Bagian 1: Pengaturan Menu, Porsi & Tanggal --}}
        <div class="detail-card" style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px;">
                <div style="width: 28px; height: 28px; border-radius: 50%; background: #042A70; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;">
                    1
                </div>
                <h2 class="detail-card-title" style="margin-bottom: 0;">Tentukan Menu & Target Porsi</h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
                {{-- No. Batch (Otomatis) --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 8px;">
                        Kode Batch (Otomatis)
                    </label>
                    <input type="text" value="{{ $autoBatchCode }}" readonly class="form-input" style="height: 42px; font-weight: 700; color: #042A70; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; width: 100%; padding: 0 12px;">
                    <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">Format standar sistem: BTH-YYYYMMDD-NNN</small>
                </div>

                {{-- Tanggal Produksi --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 8px;">
                        Tanggal Produksi <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="date" name="production_date" id="productionDate" value="{{ old('production_date', $today) }}" required class="form-input" style="height: 42px; font-size: 13.5px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; width: 100%;" onchange="onStepOneChange()">
                </div>

                {{-- Menu Pilihan --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 8px;">
                        Pilih Menu Aktif <span style="color: #DC2626;">*</span>
                    </label>
                    <select name="menu_id" id="menuSelect" required class="form-select" style="height: 42px; font-size: 13.5px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; width: 100%;" onchange="onMenuSelected()">
                        <option value="">-- Pilih Menu --</option>
                        @foreach ($menus as $m)
                            <option value="{{ $m->id }}" data-kategori="{{ $m->kategori_label }}" data-recipes="{{ json_encode($m->recipes) }}" {{ old('menu_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->nama }} ({{ $m->kategori_label }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Ukuran Porsi (Besar / Kecil) --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 8px;">
                        Ukuran Porsi <span style="color: #DC2626;">*</span>
                    </label>
                    <div style="display: flex; gap: 12px;">
                        <label id="labelPorsiBesar" style="flex: 1; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s ease;">
                            <span style="font-size: 13.5px; font-weight: 600; color: #071E49;">Besar</span>
                            <input type="radio" name="ukuran_porsi" value="besar" id="radioPorsiBesar" onchange="onStepOneChange()" style="accent-color: #042A70;">
                        </label>
                        <label id="labelPorsiKecil" style="flex: 1; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.15s ease;">
                            <span style="font-size: 13.5px; font-weight: 600; color: #071E49;">Kecil</span>
                            <input type="radio" name="ukuran_porsi" value="kecil" id="radioPorsiKecil" onchange="onStepOneChange()" style="accent-color: #042A70;">
                        </label>
                    </div>
                    <small id="recipeNote" style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block;">Resep aktif akan dipilih otomatis sesuai ukuran.</small>
                </div>

                {{-- Target Porsi --}}
                <div style="grid-column: 1 / -1; max-width: 320px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 8px;">
                        Target Porsi (Porsi Jadi) <span style="color: #DC2626;">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="number" name="target_porsi" id="targetPorsi" value="{{ old('target_porsi', 100) }}" min="1" step="1" required class="form-input" style="height: 42px; font-size: 15px; font-weight: 700; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 60px 0 12px; width: 100%; color: #071E49;" oninput="onStepOneChange()">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 13px; font-weight: 600; color: #64748B;">porsi</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Langkah 2: Kebutuhan Bahan & Pemilihan Lot (FEFO) --}}
        <div id="stepTwoSection" style="display: none;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #042A70; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;">
                        2
                    </div>
                    <div>
                        <h2 class="detail-card-title" style="margin-bottom: 0;">Kebutuhan Bahan & Alokasi Lot Bahan</h2>
                        <p style="margin: 2px 0 0; font-size: 12.5px; color: #64748B;">Gunakan tombol Isi Otomatis FEFO atau tentukan jumlah pemakaian tiap lot.</p>
                    </div>
                </div>

                {{-- Action Tambah Bahan Luar Resep --}}
                <button type="button" onclick="openExtraMaterialModal()" class="btn-secondary" style="font-size: 12.5px; padding: 7px 14px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Bahan di Luar Resep</span>
                </button>
            </div>

            {{-- Loading State --}}
            <div id="loadingBlocks" style="display: none; text-align: center; padding: 40px; background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0;">
                <div style="display: inline-block; width: 32px; height: 32px; border: 3px solid #E2E8F0; border-top-color: #042A70; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <div style="font-size: 13.5px; color: #64748B; margin-top: 12px;">Menghitung kebutuhan resep & membaca stok lot FEFO...</div>
            </div>

            {{-- Container Blok Bahan --}}
            <div id="materialBlocksContainer"></div>

            {{-- Container Blok Bahan Tambahan di Luar Resep --}}
            <div id="extraMaterialsContainer"></div>

            {{-- Bottom Sticky / Floating Action Bar --}}
            <div class="detail-card" style="margin-top: 24px; position: sticky; bottom: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); border-color: #CBD5E1; z-index: 100;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <div style="font-size: 12.5px; color: #64748B;">Ringkasan Alokasi:</div>
                        <div id="overallShortageText" style="font-size: 14px; font-weight: 700; color: #16A34A;">Semua bahan resep terpenuhi</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <a href="{{ route('staff.batches.index') }}" class="btn-secondary">Batal</a>
                        <button type="button" onclick="submitBatch('draft')" class="btn-secondary" style="font-weight: 600; color: #071E49; border-color: #CBD5E1;">
                            Simpan Draft
                        </button>
                        <button type="button" onclick="confirmAndSubmitBatch()" class="btn-success">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Konfirmasi Batch (Potong Stok)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Modal Tambah Bahan di Luar Resep --}}
<div id="modalExtraMaterial" class="modal-backdrop-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #071E49;">Tambah Bahan di Luar Resep</h3>
            <button type="button" onclick="closeExtraMaterialModal()" style="border: none; background: transparent; cursor: pointer; color: #64748B;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="modal-body-custom">
            <p style="font-size: 13px; color: #64748B; margin-top: 0;">
                Pilih bahan tambahan yang digunakan di luar komposisi resep standar.
            </p>
            <div>
                <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">Pilih Bahan Baku</label>
                <select id="extraMaterialSelect" class="form-select" style="height: 42px; font-size: 13.5px; border-radius: 8px; border: 1px solid #CBD5E1; width: 100%; padding: 0 12px;">
                    <option value="">-- Pilih Bahan --</option>
                    @foreach ($allMaterials as $mat)
                        <option value="{{ $mat->id }}">{{ $mat->nama }} ({{ $mat->satuan ?: 'kg' }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" onclick="closeExtraMaterialModal()" class="btn-secondary">Batal</button>
            <button type="button" onclick="confirmAddExtraMaterial()" class="btn-primary">Tambahkan Bahan</button>
        </div>
    </div>
</div>

<style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

@push('scripts')
<script>
    let currentBlocks = [];
    let extraBlocks = [];
    let materialInRecipeIds = new Set();

    function onMenuSelected() {
        const menuSelect = document.getElementById('menuSelect');
        const selectedOption = menuSelect.options[menuSelect.selectedIndex];
        
        if (!selectedOption || !selectedOption.value) {
            document.getElementById('radioPorsiBesar').disabled = true;
            document.getElementById('radioPorsiKecil').disabled = true;
            document.getElementById('stepTwoSection').style.display = 'none';
            return;
        }

        const recipesData = JSON.parse(selectedOption.getAttribute('data-recipes') || '[]');
        const availableUkuran = recipesData.map(r => r.ukuran_porsi);

        const radioBesar = document.getElementById('radioPorsiBesar');
        const radioKecil = document.getElementById('radioPorsiKecil');
        const labelBesar = document.getElementById('labelPorsiBesar');
        const labelKecil = document.getElementById('labelPorsiKecil');

        radioBesar.disabled = !availableUkuran.includes('besar');
        radioKecil.disabled = !availableUkuran.includes('kecil');

        labelBesar.style.opacity = radioBesar.disabled ? '0.4' : '1';
        labelKecil.style.opacity = radioKecil.disabled ? '0.4' : '1';

        // Auto pilih ukuran pertama yang tersedia jika belum terpilih
        if (!radioBesar.checked && !radioKecil.checked) {
            if (availableUkuran.includes('besar')) {
                radioBesar.checked = true;
            } else if (availableUkuran.includes('kecil')) {
                radioKecil.checked = true;
            }
        } else if (radioBesar.checked && radioBesar.disabled) {
            radioBesar.checked = false;
            if (!radioKecil.disabled) radioKecil.checked = true;
        } else if (radioKecil.checked && radioKecil.disabled) {
            radioKecil.checked = false;
            if (!radioBesar.disabled) radioBesar.checked = true;
        }

        onStepOneChange();
    }

    function onStepOneChange() {
        const menuId = document.getElementById('menuSelect').value;
        const ukuran = document.querySelector('input[name="ukuran_porsi"]:checked')?.value;
        const target = parseInt(document.getElementById('targetPorsi').value || '0', 10);
        const date = document.getElementById('productionDate').value;

        if (!menuId || !ukuran || target <= 0 || !date) {
            document.getElementById('stepTwoSection').style.display = 'none';
            return;
        }

        fetchRecipeMaterials(menuId, ukuran, target, date);
    }

    async function fetchRecipeMaterials(menuId, ukuran, target, date) {
        const stepTwo = document.getElementById('stepTwoSection');
        const loader = document.getElementById('loadingBlocks');
        const container = document.getElementById('materialBlocksContainer');

        stepTwo.style.display = 'block';
        loader.style.display = 'block';
        container.innerHTML = '';
        materialInRecipeIds.clear();

        try {
            const res = await fetch(`/staff/batch/recipe-materials?menu_id=${menuId}&ukuran_porsi=${ukuran}&target_porsi=${target}&production_date=${date}`);
            const data = await res.json();

            loader.style.display = 'none';

            if (!data.success) {
                container.innerHTML = `<div class="alert alert-error" style="background:#FEE2E2; color:#B91C1C; padding:16px; border-radius:10px;">${data.message}</div>`;
                return;
            }

            currentBlocks = data.blocks || [];
            currentBlocks.forEach(b => materialInRecipeIds.add(b.material_id));
            renderMaterialBlocks();
        } catch (e) {
            loader.style.display = 'none';
            container.innerHTML = `<div class="alert alert-error">Terjadi kesalahan memuat resep: ${e.message}</div>`;
        }
    }

    function renderMaterialBlocks() {
        const container = document.getElementById('materialBlocksContainer');
        container.innerHTML = '';

        currentBlocks.forEach((block, bIdx) => {
            const card = document.createElement('div');
            card.className = 'material-block-card';
            card.id = `block_${block.material_id}`;

            let lotsHtml = '';
            if (!block.lots || block.lots.length === 0) {
                lotsHtml = `
                    <div style="text-align: center; padding: 24px; color: #DC2626; font-size: 13px; background: #FEF2F2; border-radius: 8px;">
                        <strong>Tidak ada lot tersedia untuk bahan ini.</strong><br>
                        Pastikan stok tersedia, berstatus available, dan belum kedaluwarsa sebelum tanggal produksi.
                    </div>
                `;
            } else {
                lotsHtml = `
                    <div class="table-responsive" style="margin-top: 10px;">
                        <table class="data-table" style="font-size: 12.5px;">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Barcode</th>
                                    <th style="width: 130px;">No. Lot</th>
                                    <th style="width: 130px;">Kedaluwarsa</th>
                                    <th style="width: 110px;">Sisa Stok</th>
                                    <th style="min-width: 140px;">Pemasok</th>
                                    <th style="width: 160px;">Pakai (${block.satuan})</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${block.lots.map((lot, lIdx) => {
                                    const isExpired = lot.days_to_expiry !== null && lot.days_to_expiry < 0;
                                    const isNear = lot.is_near_expiry;
                                    const expiryBadge = isExpired
                                        ? `<span class="badge-status badge-expired"><span class="status-dot"></span>Kedaluwarsa</span>`
                                        : (isNear ? `<span class="badge-status badge-near-expired" title="${lot.days_to_expiry} hari lagi"><span class="status-dot"></span>H-${lot.days_to_expiry}</span>` : `<span style="color:#16A34A; font-weight:600;">${lot.expiry_date_formatted}</span>`);

                                    return `
                                        <tr class="${isExpired ? 'opacity-50 bg-slate-50' : ''}">
                                            <td style="font-family: monospace; font-weight: 600;">${lot.barcode}</td>
                                            <td>${lot.lot_number || '-'}</td>
                                            <td>${expiryBadge}</td>
                                            <td style="font-weight: 600;">${lot.current_qty} ${block.satuan}</td>
                                            <td style="color: #64748B;">${lot.supplier_name}</td>
                                            <td>
                                                <input type="number" 
                                                       name="usages[${lot.id}][qty_used]"
                                                       data-material-id="${block.material_id}"
                                                       data-lot-id="${lot.id}"
                                                       data-max-qty="${lot.current_qty}"
                                                       data-lot-idx="${lIdx}"
                                                       data-days-to-expiry="${lot.days_to_expiry ?? 999}"
                                                       class="form-input qty-usage-input"
                                                       min="0"
                                                       max="${lot.current_qty}"
                                                       step="0.001"
                                                       placeholder="0.000"
                                                       ${isExpired ? 'disabled' : ''}
                                                       oninput="onQtyInput(this, ${block.material_id}, ${lot.current_qty}, ${lIdx})"
                                                       style="width: 100%; height: 36px; font-size: 13px; font-weight: 600; padding: 0 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                                                <input type="hidden" name="usages[${lot.id}][material_lot_id]" value="${lot.id}">
                                                <div class="lot-row-warning" id="warn_${lot.id}" style="display:none; font-size: 11px; color: #DC2626; margin-top: 3px;"></div>
                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            card.innerHTML = `
                <div class="material-block-header">
                    <div class="material-title-group">
                        <div class="material-avatar">${block.material_name.charAt(0).toUpperCase()}</div>
                        <div>
                            <h3 class="material-name">${block.material_name}</h3>
                            <div class="material-meta">Kode: ${block.kode || '-'} • Resep: ${block.qty_per_porsi} ${block.satuan} / porsi</div>
                        </div>
                    </div>
                    <div class="material-pills-group">
                        <span class="pill-info">Kebutuhan: <strong>${block.kebutuhan} ${block.satuan}</strong></span>
                        <span class="pill-info" id="selected_${block.material_id}">Terpilih: <strong>0.000 ${block.satuan}</strong></span>
                        <span class="pill-kurang" id="statusPill_${block.material_id}">Kurang ${block.kebutuhan} ${block.satuan}</span>
                        ${block.lots && block.lots.length > 0 ? `
                            <button type="button" class="btn-fefo" onclick="autoFillFefo(${block.material_id})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                <span>Isi otomatis FEFO</span>
                            </button>
                        ` : ''}
                    </div>
                </div>
                ${!block.is_stock_sufficient ? `
                    <div class="alert-warning-pill" style="margin-bottom: 10px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span><strong>Peringatan:</strong> Total stok tersedia (${block.total_stok_tersedia} ${block.satuan}) tidak mencukupi kebutuhan resep (${block.kebutuhan} ${block.satuan}).</span>
                    </div>
                ` : ''}
                <div id="blockWarning_${block.material_id}" style="display:none;" class="alert-warning-pill"></div>
                ${lotsHtml}
            `;

            container.appendChild(card);
        });

        updateOverallSummary();
    }

    function onQtyInput(input, materialId, maxQty, lotIdx) {
        const val = parseFloat(input.value) || 0;
        const warnDiv = document.getElementById(`warn_${input.getAttribute('data-lot-id')}`);

        if (val > maxQty) {
            warnDiv.style.display = 'block';
            warnDiv.textContent = `Stok lot hanya ${maxQty}`;
            input.style.borderColor = '#DC2626';
        } else {
            warnDiv.style.display = 'none';
            input.style.borderColor = '#CBD5E1';
        }

        // Warning FEFO override check: jika mengisi lot bukan teratas padahal lot teratas masih punya sisa
        checkFefoWarning(materialId);
        updateMaterialBlockStatus(materialId);
        updateOverallSummary();
    }

    function checkFefoWarning(materialId) {
        const block = currentBlocks.find(b => b.material_id === materialId);
        if (!block) return;

        const inputs = Array.from(document.querySelectorAll(`.qty-usage-input[data-material-id="${materialId}"]`));
        let skippedHigherFefo = false;
        let foundEmptyEarlier = false;

        for (let i = 0; i < inputs.length; i++) {
            const input = inputs[i];
            const max = parseFloat(input.getAttribute('data-max-qty') || '0');
            const used = parseFloat(input.value || '0');

            if (used < max) {
                foundEmptyEarlier = true;
            } else if (foundEmptyEarlier && used > 0) {
                skippedHigherFefo = true;
                break;
            }
        }

        const warnEl = document.getElementById(`blockWarning_${materialId}`);
        if (skippedHigherFefo) {
            warnEl.style.display = 'flex';
            warnEl.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span>Masih ada lot dengan kedaluwarsa lebih dekat (Override FEFO).</span>`;
        } else {
            warnEl.style.display = 'none';
        }
    }

    function updateMaterialBlockStatus(materialId) {
        const block = currentBlocks.find(b => b.material_id === materialId);
        if (!block) return;

        const inputs = document.querySelectorAll(`.qty-usage-input[data-material-id="${materialId}"]`);
        let totalUsed = 0;
        inputs.forEach(inp => totalUsed += (parseFloat(inp.value) || 0));

        totalUsed = Math.round(totalUsed * 1000) / 1000;
        const pillSelected = document.getElementById(`selected_${materialId}`);
        const pillStatus = document.getElementById(`statusPill_${materialId}`);

        pillSelected.innerHTML = `Terpilih: <strong>${totalUsed.toFixed(3)} ${block.satuan}</strong>`;

        const diff = Math.round((block.kebutuhan - totalUsed) * 1000) / 1000;

        if (diff <= 0) {
            pillStatus.className = 'pill-terpenuhi';
            pillStatus.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Terpenuhi</span>`;
            if (diff < 0) {
                pillStatus.innerHTML += ` <small>(+${Math.abs(diff)})</small>`;
            }
        } else {
            pillStatus.className = 'pill-kurang';
            pillStatus.innerHTML = `Kurang ${diff.toFixed(3)} ${block.satuan}`;
        }
    }

    function autoFillFefo(materialId) {
        const block = currentBlocks.find(b => b.material_id === materialId);
        if (!block) return;

        let needed = block.kebutuhan;
        const inputs = Array.from(document.querySelectorAll(`.qty-usage-input[data-material-id="${materialId}"]`));

        inputs.forEach(input => {
            if (input.disabled) return;
            const max = parseFloat(input.getAttribute('data-max-qty') || '0');

            if (needed <= 0) {
                input.value = '';
            } else if (max <= needed) {
                input.value = max.toFixed(3);
                needed = Math.round((needed - max) * 1000) / 1000;
            } else {
                input.value = needed.toFixed(3);
                needed = 0;
            }

            input.dispatchEvent(new Event('input'));
        });
    }

    function updateOverallSummary() {
        let allSatisfied = true;
        let shortages = [];

        currentBlocks.forEach(b => {
            const inputs = document.querySelectorAll(`.qty-usage-input[data-material-id="${b.material_id}"]`);
            let total = 0;
            inputs.forEach(inp => total += (parseFloat(inp.value) || 0));

            const diff = Math.round((b.kebutuhan - total) * 1000) / 1000;
            if (diff > 0) {
                allSatisfied = false;
                shortages.push(`${b.material_name} kurang ${diff} ${b.satuan}`);
            }
        });

        const summaryText = document.getElementById('overallShortageText');
        if (allSatisfied) {
            summaryText.style.color = '#16A34A';
            summaryText.textContent = 'Semua bahan resep telah terpenuhi!';
        } else {
            summaryText.style.color = '#DC2626';
            summaryText.textContent = shortages.join(' • ');
        }
    }

    function openExtraMaterialModal() {
        document.getElementById('modalExtraMaterial').classList.add('show');
    }

    function closeExtraMaterialModal() {
        document.getElementById('modalExtraMaterial').classList.remove('show');
    }

    async function confirmAddExtraMaterial() {
        const select = document.getElementById('extraMaterialSelect');
        const matId = parseInt(select.value, 10);
        if (!matId) {
            alert('Pilih bahan terlebih dahulu.');
            return;
        }

        const date = document.getElementById('productionDate').value;
        closeExtraMaterialModal();

        try {
            const res = await fetch(`/staff/batch/material-lots?material_id=${matId}&production_date=${date}`);
            const data = await res.json();

            if (!data.success) {
                alert(data.message || 'Gagal memuat lot bahan.');
                return;
            }

            renderExtraMaterialBlock(data.material, data.lots);
        } catch (e) {
            alert('Terjadi kesalahan: ' + e.message);
        }
    }

    function renderExtraMaterialBlock(material, lots) {
        const container = document.getElementById('extraMaterialsContainer');
        const card = document.createElement('div');
        card.className = 'material-block-card';
        card.style.borderLeft = '4px solid #0284C7';

        let lotsHtml = '';
        if (!lots || lots.length === 0) {
            lotsHtml = `<div style="padding:16px; color:#64748B; font-size:13px; text-align:center;">Tidak ada stok lot yang tersedia saat ini.</div>`;
        } else {
            lotsHtml = `
                <div class="table-responsive" style="margin-top: 10px;">
                    <table class="data-table" style="font-size: 12.5px;">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Barcode</th>
                                <th style="width: 130px;">No. Lot</th>
                                <th style="width: 130px;">Kedaluwarsa</th>
                                <th style="width: 110px;">Sisa Stok</th>
                                <th style="min-width: 140px;">Pemasok</th>
                                <th style="width: 160px;">Pakai (${material.satuan})</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${lots.map(lot => `
                                <tr>
                                    <td style="font-family: monospace; font-weight: 600;">${lot.barcode}</td>
                                    <td>${lot.lot_number || '-'}</td>
                                    <td>${lot.expiry_date_formatted}</td>
                                    <td style="font-weight: 600;">${lot.current_qty} ${material.satuan}</td>
                                    <td style="color: #64748B;">${lot.supplier_name}</td>
                                    <td>
                                        <input type="number" 
                                               name="usages[${lot.id}][qty_used]"
                                               class="form-input"
                                               min="0"
                                               max="${lot.current_qty}"
                                               step="0.001"
                                               placeholder="0.000"
                                               style="width: 100%; height: 36px; font-size: 13px; font-weight: 600; padding: 0 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                                        <input type="hidden" name="usages[${lot.id}][material_lot_id]" value="${lot.id}">
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        card.innerHTML = `
            <div class="material-block-header">
                <div class="material-title-group">
                    <div class="material-avatar" style="background:#E0F2FE; color:#0284C7;">+</div>
                    <div>
                        <h3 class="material-name">${material.nama} <span class="chip-kategori" style="background:#E0F2FE; color:#0369A1; border:none;">Bahan di Luar Resep</span></h3>
                        <div class="material-meta">Kode: ${material.kode || '-'}</div>
                    </div>
                </div>
                <button type="button" class="btn-danger-outline" onclick="this.closest('.material-block-card').remove()" style="font-size:12px; padding:4px 10px;">Hapus</button>
            </div>
            ${lotsHtml}
        `;

        container.appendChild(card);
    }

    function submitBatch(actionType) {
        document.getElementById('formAction').value = actionType;

        // Disable lot inputs yang kosong/0 agar tidak memicu validasi error di backend
        document.querySelectorAll('.qty-usage-input').forEach(input => {
            const val = parseFloat(input.value) || 0;
            if (val <= 0) {
                input.disabled = true;
                const hiddenInput = input.parentElement.querySelector('input[type="hidden"]');
                if (hiddenInput) hiddenInput.disabled = true;
            }
        });

        document.getElementById('batchForm').submit();
    }

    function confirmAndSubmitBatch() {
        // Validasi pemenuhan resep saat konfirmasi
        let unfulfilled = [];
        currentBlocks.forEach(b => {
            const inputs = document.querySelectorAll(`.qty-usage-input[data-material-id="${b.material_id}"]`);
            let total = 0;
            inputs.forEach(inp => total += (parseFloat(inp.value) || 0));

            const diff = Math.round((b.kebutuhan - total) * 1000) / 1000;
            if (diff > 0) {
                unfulfilled.push(`Bahan ${b.material_name} masih kurang ${diff} ${b.satuan}`);
            }
        });

        if (unfulfilled.length > 0) {
            alert("Konfirmasi Ditolak:\n" + unfulfilled.join("\n") + "\n\nSemua bahan resep harus memenuhi target sebelum batch dapat dikonfirmasi.");
            return;
        }

        if (confirm("Konfirmasi batch sekarang?\nStok bahan akan langsung dipotong dan batch akan dikunci.")) {
            submitBatch('confirm');
        }
    }

    // Auto-trigger if pre-selected
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('menuSelect').value) {
            onMenuSelected();
        }
    });
</script>
@endpush
@endsection
