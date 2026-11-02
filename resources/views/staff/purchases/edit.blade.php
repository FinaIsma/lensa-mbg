@extends('layouts.staff')

@section('title', 'Ubah Draft Pembelian - Staff SPPG')

@section('content')
<div class="space-y-6 pb-12" style="max-width: 1040px; margin: 0 auto;">
    {{-- Page Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('staff.purchases.show', $purchase->id) }}" class="btn-action-icon" style="width: 38px; height: 38px;" title="Kembali ke Detail Nota">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <div>
                <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 700; color: #071E49;">
                    Ubah Draft Pembelian Bahan
                </h1>
                <p class="page-subtitle" style="margin: 2px 0 0; font-size: 13px; color: #64748B;">
                    Perbarui data nota pembelian bahan baku sebelum dipesan ke pemasok.
                </p>
            </div>
        </div>
    </div>

    <form id="purchaseForm" method="POST" action="{{ route('staff.purchases.update', $purchase->id) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="action" id="formAction" value="draft">

        {{-- Card 1: Informasi Nota & Pemasok --}}
        <div class="detail-card">
            <h2 class="detail-card-title">Informasi Nota Pembelian</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                {{-- No. Nota --}}
                <div>
                    <label class="form-label" style="display: block; font-size: 13.5px; font-weight: 600; color: #071E49; margin-bottom: 8px;">
                        No. Nota
                    </label>
                    <input type="text" value="{{ $purchase->purchase_no }}" readonly class="form-input-field form-input-readonly" style="font-weight: 600; color: #071E49;">
                </div>

                {{-- Tanggal Pembelian --}}
                <div>
                    <label class="form-label" style="display: block; font-size: 13.5px; font-weight: 600; color: #071E49; margin-bottom: 8px;">
                        Tanggal Pembelian <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', $purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : date('Y-m-d')) }}" required class="form-input-field">
                </div>

                {{-- Pemasok (Toko) --}}
                <div>
                    <label class="form-label" style="display: block; font-size: 13.5px; font-weight: 600; color: #071E49; margin-bottom: 8px;">
                        Pemasok / Toko <span style="color: #DC2626;">*</span>
                    </label>
                    <select name="supplier_id" id="supplierSelect" required class="form-input-field" onchange="updateSupplierAddress()">
                        <option value="">-- Pilih Pemasok Aktif --</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}" data-alamat="{{ $s->alamat }}" data-kontak="{{ $s->kontak }}" {{ old('supplier_id', $purchase->supplier_id) == $s->id ? 'selected' : '' }}>
                                {{ $s->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Detail Toko & Alamat Preview --}}
            <div id="supplierInfoPreview" style="margin-top: 16px; padding: 12px 16px; background-color: #F8FAFC; border-radius: 10px; border: 1px solid #E2E8F0; font-size: 13px; color: #475569;">
                <div><strong>Alamat:</strong> <span id="supplierAddressText">{{ $purchase->supplier->alamat ?? '-' }}</span></div>
                <div style="margin-top: 4px;"><strong>Kontak:</strong> <span id="supplierContactText">{{ $purchase->supplier->kontak ?? '-' }}</span></div>
            </div>
        </div>

        {{-- Card 2: Rincian Bahan yang Dibeli --}}
        <div class="detail-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                <h2 class="detail-card-title" style="margin-bottom: 0;">Rincian Bahan yang Dibeli</h2>
                <span style="font-size: 13px; color: #64748B;">Satu bahan hanya boleh satu baris</span>
            </div>

            <div class="table-responsive">
                <table class="items-form-table">
                    <thead>
                        <tr>
                            <th style="min-width: 250px;">Bahan <span style="color: #DC2626;">*</span></th>
                            <th style="width: 100px;">Satuan</th>
                            <th style="width: 140px;">Jumlah (Qty) <span style="color: #DC2626;">*</span></th>
                            <th style="width: 180px;">Harga Satuan (Rp) <span style="color: #DC2626;">*</span></th>
                            <th style="width: 180px; text-align: right;">Subtotal</th>
                            <th style="width: 50px; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        {{-- Row template diisi oleh JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Tombol Tambah Baris --}}
            <button type="button" class="btn-add-item-row" onclick="addNewItemRow()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>+ Tambah Baris Bahan</span>
            </button>

            {{-- Total Belanja Display --}}
            <div class="detail-total-bar" style="margin-top: 24px;">
                <div class="detail-total-text" style="font-size: 18px;">
                    Total Belanja : <span id="grandTotalDisplay" style="color: #042A70;">{{ $purchase->formatted_total_cost }}</span>
                </div>
            </div>
        </div>

        {{-- Form Actions Bar --}}
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px;">
            <a href="{{ route('staff.purchases.show', $purchase->id) }}" class="btn-edit-action" style="text-decoration: none;">
                Batal
            </a>
            
            <button type="button" class="btn-edit-action" onclick="submitAsDraft()" style="background-color: #FFFFFF; border-color: #CBD5E1;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>Simpan Perubahan Draft</span>
            </button>

            <button type="button" class="btn-pesan-action" onclick="promptOrderConfirmation()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                <span>Pesan ke Pemasok</span>
            </button>
        </div>
    </form>
</div>
@endsection

@section('modals')
{{-- Modal Konfirmasi Langsung Pesan --}}
<div id="confirmOrderModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Konfirmasi Pesan Bahan ke Pemasok</h3>
            <button type="button" class="btn-close-modal" onclick="closeConfirmOrderModal()">&times;</button>
        </div>
        <div style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 12px;">
            <p>Apakah Anda yakin ingin menyimpan dan langsung memesan nota pembelian <strong>{{ $purchase->purchase_no }}</strong> ke pemasok?</p>
            <div style="background-color: #FEF3C7; border: 1px solid #FDE68A; color: #92400E; padding: 12px; border-radius: 8px; margin-top: 10px; font-size: 13px;">
                <strong>Perhatian:</strong> Setelah dipesan, nota pembelian tidak dapat diubah lagi dan status langsung menjadi <em>Menunggu Cek</em>.
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-edit-action" onclick="closeConfirmOrderModal()">Batal</button>
            <button type="button" class="btn-pesan-action" onclick="submitAsOrder()">Ya, Pesan Sekarang</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const materialsData = @json($materials);
    const materialsMap = {};
    materialsData.forEach(m => {
        materialsMap[m.id] = m;
    });

    const existingItems = @json($purchase->items);
    let rowIndexCounter = 0;

    function formatRupiah(number) {
        return 'Rp ' + Number(number).toLocaleString('id-ID');
    }

    function updateSupplierAddress() {
        const select = document.getElementById('supplierSelect');
        const selectedOpt = select.options[select.selectedIndex];
        const previewBox = document.getElementById('supplierInfoPreview');
        
        if (selectedOpt && selectedOpt.value) {
            const alamat = selectedOpt.getAttribute('data-alamat') || '-';
            const kontak = selectedOpt.getAttribute('data-kontak') || '-';
            document.getElementById('supplierAddressText').textContent = alamat;
            document.getElementById('supplierContactText').textContent = kontak;
            previewBox.style.display = 'block';
        } else {
            previewBox.style.display = 'none';
        }
    }

    function addNewItemRow(materialId = '', qty = '', unitPrice = '') {
        const tbody = document.getElementById('itemsTableBody');
        const rowIndex = rowIndexCounter++;

        const tr = document.createElement('tr');
        tr.id = `itemRow_${rowIndex}`;
        tr.className = 'item-row';

        let optionsHtml = '<option value="">-- Pilih Bahan --</option>';
        materialsData.forEach(m => {
            const selected = (m.id == materialId) ? 'selected' : '';
            optionsHtml += `<option value="${m.id}" ${selected}>${m.nama} (${m.kode})</option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][material_id]" class="form-input-field material-select" required onchange="handleMaterialChange(${rowIndex})">
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="text" id="unit_${rowIndex}" readonly class="form-input-field form-input-readonly" style="text-align: center;" value="">
            </td>
            <td>
                <input type="number" step="0.001" min="0.001" name="items[${rowIndex}][qty_ordered]" value="${qty}" required class="form-input-field qty-input" placeholder="0" oninput="calculateRow(${rowIndex})">
            </td>
            <td>
                <input type="number" step="1" min="0" name="items[${rowIndex}][unit_price]" value="${unitPrice}" required class="form-input-field price-input" placeholder="0" oninput="calculateRow(${rowIndex})">
            </td>
            <td style="text-align: right; font-weight: 600; color: #071E49;">
                <span id="subtotal_${rowIndex}">Rp 0</span>
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-delete-row" title="Hapus baris" onclick="removeRow(${rowIndex})">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        if (materialId) {
            handleMaterialChange(rowIndex, false);
            calculateRow(rowIndex);
        }
    }

    function handleMaterialChange(rowIndex, checkDuplicate = true) {
        const row = document.getElementById(`itemRow_${rowIndex}`);
        const select = row.querySelector('.material-select');
        const selectedId = select.value;
        const unitInput = document.getElementById(`unit_${rowIndex}`);

        if (!selectedId) {
            unitInput.value = '';
            calculateRow(rowIndex);
            return;
        }

        if (checkDuplicate) {
            const allSelects = document.querySelectorAll('.material-select');
            let duplicateFound = false;

            allSelects.forEach(s => {
                if (s !== select && s.value === selectedId) {
                    duplicateFound = true;
                }
            });

            if (duplicateFound) {
                alert('Bahan sudah ada di daftar. Satu bahan hanya boleh satu baris.');
                select.value = '';
                unitInput.value = '';
                calculateRow(rowIndex);
                return;
            }
        }

        if (materialsMap[selectedId]) {
            unitInput.value = materialsMap[selectedId].satuan || '-';
        }

        calculateRow(rowIndex);
    }

    function calculateRow(rowIndex) {
        const row = document.getElementById(`itemRow_${rowIndex}`);
        if (!row) return;

        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const subtotalSpan = document.getElementById(`subtotal_${rowIndex}`);

        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const subtotal = qty * price;

        subtotalSpan.textContent = formatRupiah(subtotal);
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        const rows = document.querySelectorAll('.item-row');
        let grandTotal = 0;

        rows.forEach(r => {
            const qty = parseFloat(r.querySelector('.qty-input').value) || 0;
            const price = parseFloat(r.querySelector('.price-input').value) || 0;
            grandTotal += (qty * price);
        });

        document.getElementById('grandTotalDisplay').textContent = formatRupiah(grandTotal);
    }

    function removeRow(rowIndex) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('Minimal harus ada 1 baris bahan belanja.');
            return;
        }

        const row = document.getElementById(`itemRow_${rowIndex}`);
        if (row) {
            row.remove();
            calculateGrandTotal();
        }
    }

    function validateFormInputs() {
        const supplierSelect = document.getElementById('supplierSelect');
        if (!supplierSelect.value) {
            alert('Silakan pilih pemasok terlebih dahulu.');
            supplierSelect.focus();
            return false;
        }

        const rows = document.querySelectorAll('.item-row');
        if (rows.length === 0) {
            alert('Silakan tambahkan minimal satu baris bahan belanja.');
            return false;
        }

        let isValid = true;
        rows.forEach(r => {
            const mat = r.querySelector('.material-select').value;
            const qty = parseFloat(r.querySelector('.qty-input').value) || 0;
            const price = parseFloat(r.querySelector('.price-input').value) || 0;

            if (!mat) {
                alert('Pastikan semua baris sudah memilih bahan.');
                isValid = false;
            } else if (qty <= 0) {
                alert('Jumlah pesanan (Qty) harus lebih besar dari 0.');
                isValid = false;
            } else if (price < 0) {
                alert('Harga satuan tidak boleh negatif.');
                isValid = false;
            }
        });

        return isValid;
    }

    function submitAsDraft() {
        if (!validateFormInputs()) return;
        document.getElementById('formAction').value = 'draft';
        document.getElementById('purchaseForm').submit();
    }

    function promptOrderConfirmation() {
        if (!validateFormInputs()) return;
        document.getElementById('confirmOrderModal').classList.add('active');
    }

    function closeConfirmOrderModal() {
        document.getElementById('confirmOrderModal').classList.remove('active');
    }

    function submitAsOrder() {
        document.getElementById('formAction').value = 'order';
        document.getElementById('purchaseForm').submit();
    }

    // Inisialisasi awal dengan data existing
    document.addEventListener('DOMContentLoaded', function() {
        if (existingItems && existingItems.length > 0) {
            existingItems.forEach(item => {
                addNewItemRow(item.material_id, item.qty_ordered, item.unit_price);
            });
        } else {
            addNewItemRow();
        }
    });
</script>
@endpush
