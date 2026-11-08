@extends('layouts.staff')

@section('title', 'Ubah Batch Produksi - Staff SPPG')

@section('content')
<div class="space-y-6 pb-12" style="max-width: 1040px; margin: 0 auto;">
    {{-- Page Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('staff.batches.show', $batch->id) }}" class="btn-action-icon" style="width: 38px; height: 38px;" title="Kembali ke Detail Batch">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <div>
                <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 700; color: #071E49;">
                    Ubah Batch Produksi: {{ $batch->batch_code }}
                </h1>
                <p class="page-subtitle" style="margin: 2px 0 0; font-size: 13px; color: #64748B;">
                    Perbarui tanggal produksi, target porsi, atau alokasi pemakaian lot bahan baku.
                </p>
            </div>
        </div>
        <div>
            <span class="badge-status badge-draft">
                <span class="status-dot"></span>
                <span>Draft</span>
            </span>
        </div>
    </div>

    <form id="batchEditForm" method="POST" action="{{ route('staff.batches.update', $batch->id) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="action" id="formAction" value="draft">

        {{-- Detail Menu & Target --}}
        <div class="detail-card" style="margin-bottom: 24px;">
            <h2 class="detail-card-title" style="margin-bottom: 18px;">Informasi Batch</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 8px;">Kode Batch</label>
                    <input type="text" value="{{ $batch->batch_code }}" readonly class="form-input" style="height: 42px; font-weight: 700; color: #042A70; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; width: 100%; padding: 0 12px;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 8px;">Menu & Ukuran Porsi</label>
                    <input type="text" value="{{ $batch->menu?->nama }} (Ukuran: {{ ucfirst($batch->recipe?->ukuran_porsi) }})" readonly class="form-input" style="height: 42px; font-weight: 600; color: #071E49; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; width: 100%; padding: 0 12px;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 8px;">
                        Tanggal Produksi <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="date" name="production_date" id="productionDate" value="{{ old('production_date', $batch->production_date ? $batch->production_date->format('Y-m-d') : '') }}" required class="form-input" style="height: 42px; font-size: 13.5px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; width: 100%;" onchange="fetchMaterials()">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 8px;">
                        Target Porsi <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="number" name="target_porsi" id="targetPorsi" value="{{ old('target_porsi', $batch->target_porsi) }}" min="1" step="1" required class="form-input" style="height: 42px; font-size: 15px; font-weight: 700; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; width: 100%; color: #071E49;" oninput="fetchMaterials()">
                </div>
            </div>
        </div>

        {{-- Langkah 2: Kebutuhan Bahan & Pemilihan Lot (FEFO) --}}
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
                <div>
                    <h2 class="detail-card-title" style="margin-bottom: 0;">Kebutuhan Bahan & Alokasi Lot</h2>
                    <p style="margin: 2px 0 0; font-size: 12.5px; color: #64748B;">Sesuaikan pemakaian lot bahan baku dengan tombol Isi Otomatis FEFO atau input manual.</p>
                </div>
                <button type="button" onclick="openExtraMaterialModal()" class="btn-secondary" style="font-size: 12.5px; padding: 7px 14px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Bahan di Luar Resep</span>
                </button>
            </div>

            <div id="loadingBlocks" style="display: none; text-align: center; padding: 40px; background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0;">
                <div style="display: inline-block; width: 32px; height: 32px; border: 3px solid #E2E8F0; border-top-color: #042A70; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <div style="font-size: 13.5px; color: #64748B; margin-top: 12px;">Memuat stok lot bahan...</div>
            </div>

            <div id="materialBlocksContainer"></div>
            <div id="extraMaterialsContainer"></div>

            {{-- Floating / Sticky Action Bar --}}
            <div class="detail-card" style="margin-top: 24px; position: sticky; bottom: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); border-color: #CBD5E1; z-index: 100;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <div style="font-size: 12.5px; color: #64748B;">Ringkasan Alokasi:</div>
                        <div id="overallShortageText" style="font-size: 14px; font-weight: 700; color: #16A34A;">Semua bahan resep terpenuhi</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <a href="{{ route('staff.batches.show', $batch->id) }}" class="btn-secondary">Batal</a>
                        <button type="button" onclick="submitBatch('draft')" class="btn-secondary" style="font-weight: 600; color: #071E49; border-color: #CBD5E1;">
                            Simpan Perubahan Draft
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
    const existingUsages = @json($batch->materialUsages->mapWithKeys(fn ($u) => [$u->material_lot_id => (float) $u->qty_used]));
    const menuId = {{ $batch->menu_id }};
    const ukuranPorsi = '{{ $batch->recipe?->ukuran_porsi ?? "besar" }}';

    let currentBlocks = [];

    async function fetchMaterials() {
        const target = parseInt(document.getElementById('targetPorsi').value || '0', 10);
        const date = document.getElementById('productionDate').value;
        const loader = document.getElementById('loadingBlocks');
        const container = document.getElementById('materialBlocksContainer');

        if (target <= 0 || !date) return;

        loader.style.display = 'block';
        container.innerHTML = '';

        try {
            const res = await fetch(`/staff/batch/recipe-materials?menu_id=${menuId}&ukuran_porsi=${ukuranPorsi}&target_porsi=${target}&production_date=${date}`);
            const data = await res.json();
            loader.style.display = 'none';

            if (!data.success) {
                container.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
                return;
            }

            currentBlocks = data.blocks || [];
            renderBlocks();
        } catch (e) {
            loader.style.display = 'none';
            container.innerHTML = `<div class="alert alert-error">Gagal memuat resep: ${e.message}</div>`;
        }
    }

    function renderBlocks() {
        const container = document.getElementById('materialBlocksContainer');
        container.innerHTML = '';

        currentBlocks.forEach(block => {
            const card = document.createElement('div');
            card.className = 'material-block-card';

            let lotsHtml = '';
            if (!block.lots || block.lots.length === 0) {
                lotsHtml = `<div style="text-align: center; padding: 20px; color: #DC2626; font-size: 13px;">Tidak ada stok lot yang tersedia.</div>`;
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
                                    const prefilledVal = existingUsages[lot.id] || '';
                                    return `
                                        <tr>
                                            <td style="font-family: monospace; font-weight: 600;">${lot.barcode}</td>
                                            <td>${lot.lot_number || '-'}</td>
                                            <td>${lot.expiry_date_formatted}</td>
                                            <td style="font-weight: 600;">${lot.current_qty} ${block.satuan}</td>
                                            <td style="color: #64748B;">${lot.supplier_name}</td>
                                            <td>
                                                <input type="number" 
                                                       name="usages[${lot.id}][qty_used]"
                                                       data-material-id="${block.material_id}"
                                                       data-lot-id="${lot.id}"
                                                       data-max-qty="${lot.current_qty}"
                                                       class="form-input qty-usage-input"
                                                       min="0"
                                                       max="${lot.current_qty}"
                                                       step="0.001"
                                                       value="${prefilledVal}"
                                                       placeholder="0.000"
                                                       oninput="onQtyInput(this, ${block.material_id}, ${lot.current_qty})"
                                                       style="width: 100%; height: 36px; font-size: 13px; font-weight: 600; padding: 0 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                                                <input type="hidden" name="usages[${lot.id}][material_lot_id]" value="${lot.id}">
                                                <div id="warn_${lot.id}" style="display:none; font-size: 11px; color: #DC2626; margin-top: 3px;"></div>
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
                            <div class="material-meta">Resep: ${block.qty_per_porsi} ${block.satuan} / porsi</div>
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
                ${lotsHtml}
            `;

            container.appendChild(card);
            updateMaterialBlockStatus(block.material_id);
        });

        updateOverallSummary();
    }

    function onQtyInput(input, materialId, maxQty) {
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

        updateMaterialBlockStatus(materialId);
        updateOverallSummary();
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

        if (pillSelected) pillSelected.innerHTML = `Terpilih: <strong>${totalUsed.toFixed(3)} ${block.satuan}</strong>`;

        const diff = Math.round((block.kebutuhan - totalUsed) * 1000) / 1000;
        if (pillStatus) {
            if (diff <= 0) {
                pillStatus.className = 'pill-terpenuhi';
                pillStatus.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Terpenuhi</span>`;
            } else {
                pillStatus.className = 'pill-kurang';
                pillStatus.innerHTML = `Kurang ${diff.toFixed(3)} ${block.satuan}`;
            }
        }
    }

    function autoFillFefo(materialId) {
        const block = currentBlocks.find(b => b.material_id === materialId);
        if (!block) return;

        let needed = block.kebutuhan;
        const inputs = Array.from(document.querySelectorAll(`.qty-usage-input[data-material-id="${materialId}"]`));

        inputs.forEach(input => {
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
        if (summaryText) {
            if (allSatisfied) {
                summaryText.style.color = '#16A34A';
                summaryText.textContent = 'Semua bahan resep telah terpenuhi!';
            } else {
                summaryText.style.color = '#DC2626';
                summaryText.textContent = shortages.join(' • ');
            }
        }
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

        document.getElementById('batchEditForm').submit();
    }

    function confirmAndSubmitBatch() {
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
            alert("Konfirmasi Ditolak:\n" + unfulfilled.join("\n") + "\n\nSemua bahan resep harus terpenuhi sebelum konfirmasi.");
            return;
        }

        if (confirm("Konfirmasi batch ini sekarang?\nStok bahan akan langsung dipotong dan batch akan dikunci.")) {
            submitBatch('confirm');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchMaterials();
    });
</script>
@endpush
@endsection
