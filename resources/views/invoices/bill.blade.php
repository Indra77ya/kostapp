<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Tagihan - {{ $bill->bill_number }}</title>
    <style>
        body { font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 40px; color: #333; line-height: 1.5; }
        .container { max-width: 800px; margin: auto; border: 1px solid #eee; padding: 40px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, .05); position: relative; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #10b981; padding-bottom: 15px; margin-bottom: 25px; align-items: center; }
        .logo { font-size: 24px; font-weight: bold; color: #10b981; }
        .document-title { font-size: 20px; text-transform: uppercase; font-weight: bold; color: #444; }

        .info-section { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .info-box { flex: 1; }
        .info-box:last-child { text-align: right; }
        .info-title { font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 600; margin-bottom: 5px; }
        .info-value { font-size: 15px; color: #111827; font-weight: 500; }

        .receipt-body { background: #f9fafb; padding: 25px; border-radius: 8px; margin-bottom: 20px; }
        .receipt-row { display: flex; margin-bottom: 15px; border-bottom: 1px dashed #e5e7eb; padding-bottom: 8px; }
        .receipt-label { width: 200px; color: #6b7280; font-weight: 500; }
        .receipt-value { flex: 1; color: #111827; font-weight: 600; }

        .amount-box { background: #10b981; color: white; display: inline-block; padding: 10px 25px; border-radius: 4px; font-size: 20px; font-weight: bold; margin-top: 10px; }

        .footer { margin-top: 50px; text-align: center; color: #999; font-size: 11px; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 100px; color: rgba(16, 185, 129, 0.1); font-weight: bold; pointer-events: none; text-transform: uppercase; z-index: 0; white-space: nowrap; }
        .watermark.warning { color: rgba(245, 158, 11, 0.1); }
        .watermark.danger { color: rgba(239, 68, 68, 0.1); }

        @media print {
            body { padding: 0; }
            .container { border: none; box-shadow: none; max-width: 100%; }
            .no-print { display: none; }
        }
        .print-btn { display: inline-block; background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center;">
        <a href="javascript:window.print()" class="print-btn">Cetak Invoice</a>
    </div>

    <div class="container">
        @php
            $status = $bill->status;
            $watermarkClass = '';

            if ($status === 'Cicilan') {
                $watermarkClass = 'warning';
            } elseif ($status === 'Belum Lunas') {
                $watermarkClass = 'danger';
            }
        @endphp
        <div class="watermark {{ $watermarkClass }}">{{ $status }}</div>
        <div class="header">
            <div class="logo">KOST MANAGEMENT</div>
            <div class="document-title">Invoice Tagihan</div>
        </div>

        <div class="info-section">
            <div class="info-box">
                <div class="info-title">Tagihan Untuk</div>
                <div class="info-value">{{ $bill->registration->user->name }}</div>
                <div class="info-value">{{ $bill->registration->location->name }}</div>
                <div class="info-value">Kamar {{ $bill->registration->room->room_number }}</div>
            </div>
            <div class="info-box">
                <div class="info-title">Nomor Tagihan</div>
                <div class="info-value">{{ $bill->bill_number }}</div>
                <div class="info-title" style="margin-top: 10px;">Tanggal Jatuh Tempo</div>
                <div class="info-value">{{ $bill->due_date->format('d F Y') }}</div>
            </div>
        </div>

        <div class="receipt-body">
            <div class="receipt-row">
                <div class="receipt-label">Keterangan Tagihan</div>
                <div class="receipt-value">{{ $bill->description }}</div>
            </div>
            <div class="receipt-row">
                <div class="receipt-label">Harga Dasar</div>
                <div class="receipt-value">Rp {{ number_format($bill->amount + $bill->discount, 0, ',', '.') }}</div>
            </div>
            @if($bill->discount > 0)
            <div class="receipt-row">
                <div class="receipt-label">Diskon</div>
                <div class="receipt-value text-danger">- Rp {{ number_format($bill->discount, 0, ',', '.') }}</div>
            </div>
            @endif

            <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <div class="info-title">Total Tagihan (Net)</div>
                    <div class="amount-box">Rp {{ number_format($bill->amount, 0, ',', '.') }}</div>
                </div>
                <div style="text-align: right; min-width: 250px;">
                    <div style="margin-bottom: 5px; display: flex; justify-content: space-between;">
                        <span class="info-title" style="margin: 0;">Total Terbayar:</span>
                        <span class="info-value">Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div style="border-top: 1px solid #e5e7eb; padding-top: 5px; display: flex; justify-content: space-between;">
                        <span class="info-title" style="margin: 0; font-weight: bold; color: {{ $bill->remaining_amount > 0 ? '#ef4444' : '#10b981' }};">Sisa Tagihan:</span>
                        <span class="info-value" style="font-weight: bold; color: {{ $bill->remaining_amount > 0 ? '#ef4444' : '#10b981' }};">Rp {{ number_format(max(0, $bill->remaining_amount), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 40px; font-size: 13px; color: #666; background: #fffbeb; padding: 15px; border-left: 4px solid #f59e0b; border-radius: 4px;">
            <strong>Catatan Penting:</strong><br>
            Mohon lakukan pembayaran sebelum tanggal jatuh tempo. Abaikan pesan ini jika Anda sudah melunasi tagihan tersebut. Pembayaran dapat dilakukan melalui menu "Pembayaran Saya" di Dashboard Penghuni.
        </div>

        <div class="footer">
            Dokumen ini dicetak secara otomatis melalui Sistem Manajemen Kost pada {{ date('d/m/Y H:i') }}.
        </div>
    </div>
</body>
</html>
