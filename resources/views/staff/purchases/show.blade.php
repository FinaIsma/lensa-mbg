@extends('layouts.staff')

@section('title', 'Detail Nota Pembelian - Staff SPPG')

@section('content')
<div class="space-y-6 pb-12" style="max-width: 1040px; margin: 0 auto;">
    {{-- Page Header Sesuai Mockup --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('staff.purchases.index') }}" class="btn-action-icon" style="width: 38px; height: 38px;" title="Kembali ke Daftar Pembelian">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 700; color: #071E49;">
                Detail Nota Pembelian
            </h1>
        </div>
    </div>

    {{-- Card 1: Detail Nota Pembelian (Sesuai Mockup) --}}
    <div class="detail-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
            <h2 class="detail-card-title" style="margin-bottom: 0;">Detail Nota Pembelian</h2>
            <span class="badge-status {{ $purchase->status_badge_class }}">
                <span class="status-dot"></span>
                <span>{{ $purchase->status_label }}</span>
            </span>
        </div>
        
        <div class="detail-grid-2col">
            {{-- Kolom Kiri --}}
            <div>
                <div class="detail-item-row">
                    <span class="detail-item-label">No. Nota :</span>
                    <span class="detail-item-value" style="font-weight: 600; color: #071E49;">
                        {{ $purchase->purchase_no }}
                    </span>
                </div>
                <div class="detail-item-row">
                    <span class="detail-item-label">Tanggal :</span>
                    <span class="detail-item-value">
                        {{ $purchase->purchase_date ? $purchase->purchase_date->format('d-m-Y') : '-' }}
                    </span>
                </div>
                <div class="detail-item-row">
                    <span class="detail-item-label">Toko :</span>
                    <span class="detail-item-value">
                        {{ $purchase->supplier->nama ?? '-' }}
                    </span>
                </div>
                <div class="detail-item-row">
                    <span class="detail-item-label">Alamat :</span>
                    <span class="detail-item-value">
                        {{ $purchase->supplier->alamat ?? '-' }}
                    </span>
                </div>
            </div>

            {{-- Kolom Kanan --}}
            <div>
                <div class="detail-item-row">
                    <span class="detail-item-label">Dicatat Oleh :</span>
                    <span class="detail-item-value">
                        {{ $purchase->creator->nama ?? (auth('staff')->user()->nama ?? 'Staff') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 2: Rincian Bahan yang Dibeli (Sesuai Mockup) --}}
    <div class="detail-card">
        <h2 class="detail-card-title">Rincian Bahan yang Dibeli</h2>

        <div class="table-responsive">
            <table class="detail-items-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="min-width: 220px;">Nama Bahan</th>
                        <th style="min-width: 110px;">Jumlah</th>
                        @if(in_array($purchase->status, ['received_partial', 'received']))
                            <th style="min-width: 100px;">Diterima</th>
                            <th style="min-width: 100px;">Sisa</th>
                        @endif
                        <th style="min-width: 140px;">Harga / Satuan</th>
                        <th style="min-width: 150px; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td style="font-weight: 500; color: #071E49;">
                                {{ $item->material->nama ?? 'Bahan' }}
                            </td>
                            <td>
                                {{ rtrim(rtrim(number_format((float) $item->qty_ordered, 3, ',', '.'), '0'), ',') }} {{ $item->material->satuan ?? '' }}
                            </td>
                            @if(in_array($purchase->status, ['received_partial', 'received']))
                                <td style="color: #16A34A; font-weight: 600;">
                                    {{ rtrim(rtrim(number_format((float) $item->qty_received_total, 3, ',', '.'), '0'), ',') }} {{ $item->material->satuan ?? '' }}
                                </td>
                                <td style="color: {{ $item->qty_remaining > 0 ? '#D97706' : '#64748B' }}; font-weight: 600;">
                                    {{ rtrim(rtrim(number_format((float) $item->qty_remaining, 3, ',', '.'), '0'), ',') }} {{ $item->material->satuan ?? '' }}
                                </td>
                            @endif
                            <td>
                                {{ $item->formatted_unit_price }}
                            </td>
                            <td style="text-align: right; font-weight: 600; color: #071E49;">
                                {{ $item->formatted_subtotal }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer Total Belanja Sesuai Mockup --}}
        <div class="detail-total-bar">
            <div class="detail-total-text">
                Total Belanja : {{ $purchase->formatted_total_cost }}
            </div>
        </div>
    </div>

    {{-- Card 3: Status Penerimaan Gudang Sesuai Mockup --}}
    <div class="detail-card">
        <h2 class="detail-card-title">Status Penerimaan Gudang:</h2>
        
        @if($purchase->receivings->isEmpty())
            <p class="warehouse-status-empty">
                Fisik barang belum dikonfirmasi di menu Penerimaan.
            </p>
        @else
            <div class="space-y-3">
                <div class="table-responsive">
                    <table class="detail-items-table">
                        <thead>
                            <tr>
                                <th>No. Penerimaan</th>
                                <th>Tanggal Diterima</th>
                                <th>Diterima Oleh</th>
                                <th>Status</th>
                                <th>Item Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->receivings as $rec)
                                <tr>
                                    <td style="font-weight: 600; color: #071E49;">{{ $rec->receiving_no }}</td>
                                    <td>{{ $rec->received_at ? $rec->received_at->format('d-m-Y H:i') : '-' }}</td>
                                    <td>{{ $rec->receiver->nama ?? '-' }}</td>
                                    <td>
                                        <span class="badge-status {{ $rec->status === 'confirmed' ? 'badge-received' : 'badge-draft' }}">
                                            <span class="status-dot"></span>
                                            <span>{{ ucfirst($rec->status) }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        {{ $rec->items->count() }} Bahan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Bottom Action Buttons Bar Sesuai Mockup --}}
    <div class="detail-actions-bar">
        {{-- Tombol Cetak Sesuai Mockup (Green Outline Box) --}}
        <a href="{{ route('staff.purchases.print', $purchase->id) }}" target="_blank" class="btn-cetak">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span>Cetak</span>
        </a>

        {{-- Tombol Ubah (Hanya status draft) --}}
        @if($purchase->canBeEdited())
            <a href="{{ route('staff.purchases.edit', $purchase->id) }}" class="btn-edit-action">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span>Ubah</span>
            </a>
        @endif

        {{-- Tombol Pesan (Hanya status draft) --}}
        @if($purchase->canBeOrdered())
            <button type="button" class="btn-pesan-action" onclick="openOrderModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                <span>Pesan</span>
            </button>
        @endif

        {{-- Tombol Catat Penerimaan (Jika ordered atau received_partial) --}}
        @if($purchase->canReceive())
            <a href="{{ route('staff.receivings.index', ['purchase_id' => $purchase->id]) }}" class="btn-pesan-action" style="background-color: #16A34A; text-decoration: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Catat Penerimaan</span>
            </a>
        @endif

        {{-- Tombol Batalkan (Hanya jika belum ada penerimaan terkonfirmasi) --}}
        @if($purchase->canBeCancelled())
            <button type="button" class="btn-cancel-action" onclick="openCancelModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <span>Batalkan</span>
            </button>
        @endif
    </div>
</div>
@endsection

@section('modals')
{{-- Modal Konfirmasi Pesan --}}
<div id="orderModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Konfirmasi Pesan Bahan</h3>
            <button type="button" class="btn-close-modal" onclick="closeOrderModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('staff.purchases.order', $purchase->id) }}">
            @csrf
            <div style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 12px;">
                <p>Apakah Anda yakin ingin memesan nota pembelian <strong>{{ $purchase->purchase_no }}</strong> ke pemasok?</p>
                <div style="background-color: #FEF3C7; border: 1px solid #FDE68A; color: #92400E; padding: 12px; border-radius: 8px; margin-top: 10px; font-size: 13px;">
                    <strong>Perhatian:</strong> Setelah dipesan, nota pembelian tidak dapat diubah lagi dan status akan menjadi <em>Menunggu Cek</em>.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-edit-action" onclick="closeOrderModal()">Batal</button>
                <button type="submit" class="btn-pesan-action">Ya, Pesan Sekarang</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Batalkan Nota --}}
<div id="cancelModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title" style="color: #DC2626;">Batalkan Nota Pembelian</h3>
            <button type="button" class="btn-close-modal" onclick="closeCancelModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('staff.purchases.cancel', $purchase->id) }}">
            @csrf
            <div style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 16px;">
                <p>Nota pembelian <strong>{{ $purchase->purchase_no }}</strong> akan dibatalkan. Masukkan alasan pembatalan:</p>
                <div style="margin-top: 12px;">
                    <label for="cancelReason" style="display: block; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Alasan Pembatalan <span style="color: #DC2626;">*</span>
                    </label>
                    <textarea id="cancelReason" name="reason" rows="3" required placeholder="Contoh: Stok pemasok kosong / revisi anggaran operasional" class="form-input-field" style="height: auto; padding: 10px 14px;"></textarea>
                    <small style="color: #64748B; font-size: 12px;">Alasan pembatalan akan tercatat secara permanen di audit trail sistem.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-edit-action" onclick="closeCancelModal()">Tutup</button>
                <button type="submit" class="btn-cancel-action" style="background-color: #DC2626; color: #FFFFFF; border-color: #DC2626;">
                    Konfirmasi Pembatalan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openOrderModal() {
        document.getElementById('orderModal').classList.add('active');
    }
    function closeOrderModal() {
        document.getElementById('orderModal').classList.remove('active');
    }
    function openCancelModal() {
        document.getElementById('cancelModal').classList.add('active');
    }
    function closeCancelModal() {
        document.getElementById('cancelModal').classList.remove('active');
    }
    // Close modal when clicking overlay outside box
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
        }
    });
</script>
@endpush
