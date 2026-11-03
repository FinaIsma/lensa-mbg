@extends('layouts.staff')

@section('title', 'Detail Penerimaan Bahan - Staff SPPG')

@section('content')
<div class="space-y-6 pb-12" style="max-width: 1040px; margin: 0 auto;">
    {{-- Page Header Sesuai Mockup --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('staff.receivings.index') }}" class="btn-action-icon" style="width: 38px; height: 38px;" title="Kembali ke Daftar Penerimaan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 700; color: #071E49;">
                Detail Penerimaan Bahan
            </h1>
        </div>
    </div>

    {{-- Card 1: Informasi Kedatangan (Sesuai Mockup) --}}
    <div class="detail-card">
        <h2 class="detail-card-title" style="margin-bottom: 20px;">Informasi Kedatangan</h2>

        <div class="detail-grid-2col-exact">
            {{-- Kolom Kiri: 3 baris --}}
            <div>
                <div class="detail-exact-row">
                    <span class="detail-exact-label">No. Penerimaan</span>
                    <span class="detail-exact-colon">:</span>
                    <span class="detail-exact-value" style="font-weight: 700; color: #071E49;">
                        {{ $receiving->receiving_no }}
                    </span>
                </div>
                <div class="detail-exact-row">
                    <span class="detail-exact-label">Nota Pembelian</span>
                    <span class="detail-exact-colon">:</span>
                    <span class="detail-exact-value" style="font-weight: 600; color: #071E49;">
                        {{ $receiving->purchase?->purchase_no ?? '-' }} ({{ $receiving->purchase?->supplier?->nama ?? '-' }})
                    </span>
                </div>
                <div class="detail-exact-row">
                    <span class="detail-exact-label">Petugas</span>
                    <span class="detail-exact-colon">:</span>
                    <span class="detail-exact-value">
                        {{ $receiving->receiver?->nama ?? 'Staff' }} (Staff SPPG)
                    </span>
                </div>
            </div>

            {{-- Kolom Kanan: 3 baris sejajar --}}
            <div>
                <div class="detail-exact-row">
                    <span class="detail-exact-label">Status</span>
                    <span class="detail-exact-colon">:</span>
                    <div class="detail-exact-value">
                        <span class="badge-status {{ $receiving->status_badge_class }}" style="margin: 0;">
                            <span class="status-dot"></span>
                            <span>{{ $receiving->status_label }}</span>
                        </span>
                        @if ($receiving->is_locked)
                            <span title="Dokumen Terkunci" style="color: #64748B; display: inline-flex; margin-left: 6px;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                        @endif
                    </div>
                </div>
                <div class="detail-exact-row">
                    <span class="detail-exact-label">Waktu Terima</span>
                    <span class="detail-exact-colon">:</span>
                    <span class="detail-exact-value">
                        {{ $receiving->received_at ? $receiving->received_at->format('d-m-Y H:i') : '-' }}
                    </span>
                </div>
                <div class="detail-exact-row">
                    <span class="detail-exact-label">Konfirmasi</span>
                    <span class="detail-exact-colon">:</span>
                    <span class="detail-exact-value">
                        {{ $receiving->confirmed_at ? $receiving->confirmed_at->format('d-m-Y H:i') : '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 2: Pemeriksaan Fisik Barang Datang (Sesuai Mockup) --}}
    <div class="detail-card">
        <h2 class="detail-card-title">Pemeriksaan Fisik Barang Datang</h2>

        <div class="table-responsive">
            <table class="detail-items-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th style="min-width: 200px;">Nama Bahan</th>
                        <th style="min-width: 100px;">Dipesan</th>
                        <th style="min-width: 100px;">Diterima</th>
                        <th style="min-width: 130px;">Tgl Expired</th>
                        <th style="min-width: 160px;">Kondisi Fisik</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receiving->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td style="font-weight: 600; color: #071E49;">
                                {{ $item->material?->nama ?? 'Bahan' }}
                            </td>
                            <td>
                                {{ (float) ($item->purchaseItem?->qty_ordered ?? 0) }} {{ $item->material?->satuan ?? 'kg' }}
                            </td>
                            <td style="font-weight: 600; color: #16A34A;">
                                {{ (float) $item->qty_received }} {{ $item->material?->satuan ?? 'kg' }}
                            </td>
                            <td>
                                {{ $item->expiry_date_input ? \Carbon\Carbon::parse($item->expiry_date_input)->format('d - m - Y') : '-' }}
                            </td>
                            <td style="color: #475569;">
                                {{ $item->kondisi_dilaporkan ?: 'Segar, Dingin' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94A3B8; padding: 24px;">
                                Tidak ada item barang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Card 3: Label Lot (QR & Barcode) yang terbit (Sesuai Mockup) --}}
    <div class="detail-card">
        <h2 class="detail-card-title">Label Lot (QR & Barcode) yang terbit</h2>

        @if ($receiving->isConfirmed())
            <div style="display: flex; flex-direction: column; gap: 14px;">
                @foreach ($receiving->items as $item)
                    @php $lot = $item->lot; @endphp
                    <div class="barcode-lot-row">
                        <div class="barcode-lot-left">
                            <span style="color: #071E49; font-size: 16px;">•</span>
                            <span class="barcode-lot-name">{{ $item->material?->nama ?? 'Bahan' }}</span>
                            <span style="color: #071E49; font-weight: 600;">:</span>
                            <span class="lot-badge-pill">{{ $lot?->barcode ?? $lot?->lot_number ?? '-' }}</span>
                        </div>
                        @if ($lot)
                            <a href="{{ route('staff.receivings.barcode', ['id' => $receiving->id, 'lotId' => $lot->id]) }}" target="_blank" class="btn-mockup-cetak-lot">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                <span>Cetak Label</span>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p style="font-size: 13.5px; color: #64748B; margin: 0;">
                Label QR & barcode lot akan diterbitkan otomatis setelah penerimaan dikonfirmasi.
            </p>
        @endif
    </div>

    {{-- Card 4: Foto Bukti Kedatangan (Sesuai Mockup) --}}
    <div class="detail-card">
        <h2 class="detail-card-title">Foto Bukti Kedatangan</h2>

        <div class="evidence-gallery-grid">
            @php
                $evList = $receiving->evidences;
                $totalBoxes = max(3, $evList->count());
            @endphp
            @for ($i = 0; $i < $totalBoxes; $i++)
                <div class="evidence-photo-box">
                    @if ($i < $evList->count())
                        @php $ev = $evList[$i]; @endphp
                        <img src="{{ asset('storage/' . $ev->file_path) }}" alt="{{ $ev->description ?? 'Foto Bukti' }}" class="evidence-photo-img" title="Klik untuk melihat foto penuh" onclick="window.open('{{ asset('storage/' . $ev->file_path) }}', '_blank')">
                    @endif
                </div>
            @endfor
        </div>
    </div>

    {{-- Action Buttons Bar di Bawah Sesuai Mockup --}}
    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 10px;">
        @if ($receiving->isConfirmed())
            <button type="button" class="btn-mockup-batal" onclick="openVoidModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <span>Batalkan Dokumen</span>
            </button>

            <a href="{{ route('staff.receivings.barcode-all', $receiving->id) }}" target="_blank" class="btn-mockup-cetak-all">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <span>Cetak Semua Label (QR / Barcode)</span>
            </a>
        @elseif ($receiving->isDraft())
            <button type="button" class="btn-mockup-batal" onclick="openVoidModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                <span>Batalkan Draft</span>
            </button>

            <form method="POST" action="{{ route('staff.receivings.confirm', $receiving->id) }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-mockup-confirm">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>Konfirmasi Penerimaan</span>
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

@section('modals')
{{-- Modal Konfirmasi Pembatalan --}}
<div class="modal-backdrop" id="voidConfirmModal">
    <div class="modal-dialog" style="max-width: 450px; text-align: left; position: relative; padding: 26px 26px 22px;">
        <button type="button" class="modal-close-corner" onclick="closeVoidModal()" title="Tutup" aria-label="Tutup">
            &times;
        </button>

        <form method="POST" action="{{ route('staff.receivings.void', $receiving->id) }}">
            @csrf
            <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px;">
                <div style="width: 42px; height: 42px; border-radius: 50%; background-color: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 700; color: #071E49; margin: 0 0 4px 0; text-align: left;">Batalkan Dokumen Penerimaan?</h3>
                    <p style="font-size: 13.5px; color: #64748B; margin: 0; line-height: 1.5; text-align: left;">
                        Dokumen penerimaan ini akan berstatus <strong style="color: #DC2626;">Dibatalkan (Voided)</strong>. Penerimaan draft yang dibatalkan tidak akan memengaruhi stok.
                    </p>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-cancel-action" style="padding: 8px 18px;" onclick="closeVoidModal()">
                    Batal
                </button>
                <button type="submit" class="btn-batal-doc" style="background-color: #DC2626; color: #FFFFFF; padding: 8px 20px;">
                    Ya, Batalkan Dokumen
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openVoidModal() {
        document.getElementById('voidConfirmModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeVoidModal() {
        document.getElementById('voidConfirmModal').classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeVoidModal();
        }
    });
</script>
@endpush
