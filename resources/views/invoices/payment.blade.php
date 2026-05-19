<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran - {{ $payment->payment_number }}</title>
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

        .receipt-body { background: #f9fafb; padding: 25px; border-radius: 8px; margin-bottom: 30px; }
        .receipt-row { display: flex; margin-bottom: 15px; border-bottom: 1px dashed #e5e7eb; padding-bottom: 8px; }
        .receipt-label { width: 200px; color: #6b7280; font-weight: 500; }
        .receipt-value { flex: 1; color: #111827; font-weight: 600; }

        .amount-box { background: #10b981; color: white; display: inline-block; padding: 10px 25px; border-radius: 4px; font-size: 20px; font-weight: bold; margin-top: 10px; }

        .footer { margin-top: 50px; text-align: center; color: #999; font-size: 11px; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 100px; color: rgba(16, 185, 129, 0.1); font-weight: bold; pointer-events: none; text-transform: uppercase; }

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
        <a href="javascript:window.print()" class="print-btn">Cetak Kuitansi</a>
    </div>

    <div class="container">
        <div class="watermark">LUNAS</div>
        <div class="header">
            <div class="logo">KOST MANAGEMENT</div>
            <div class="document-title">Kuitansi Pembayaran</div>
        </div>

        <div class="info-section">
            <div class="info-box">
                <div class="info-title">Diterima Dari</div>
                <div class="info-value">{{ $payment->registration->user->name }}</div>
                <div class="info-value">{{ $payment->registration->location->name }}</div>
                <div class="info-value">Kamar {{ $payment->registration->room->room_number }}</div>
            </div>
            <div class="info-box">
                <div class="info-title">Nomor Kuitansi</div>
                <div class="info-value">{{ $payment->payment_number }}</div>
                <div class="info-title" style="margin-top: 10px;">Tanggal Bayar</div>
                <div class="info-value">{{ $payment->payment_date->format('d F Y') }}</div>
                <div class="info-title" style="margin-top: 10px;">Metode Pembayaran</div>
                <div class="info-value">{{ $payment->paymentMethod->name }}</div>
            </div>
        </div>

        <div class="receipt-body">
            <div class="receipt-row">
                <div class="receipt-label">Untuk Pembayaran</div>
                <div class="receipt-value">
                    @if($payment->bill)
                        {{ $payment->bill->description }} ({{ $payment->bill->bill_number }})
                    @else
                        Pembayaran Umum / Deposit
                    @endif
                </div>
            </div>
            @if($payment->notes)
            <div class="receipt-row">
                <div class="receipt-label">Catatan</div>
                <div class="receipt-value">{{ $payment->notes }}</div>
            </div>
            @endif
            <div style="margin-top: 20px;">
                <div class="info-title">Sejumlah</div>
                <div class="amount-box">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 40px;">
            <div style="text-align: center; width: 200px;">
                <div class="info-title">Penerima,</div>
                <div style="margin-top: 60px; font-weight: bold; border-top: 1px solid #333; padding-top: 5px;">
                    Kasir / Pengelola
                </div>
            </div>
        </div>

        <div class="footer">
            Dokumen ini dicetak secara otomatis melalui Sistem Manajemen Kost pada {{ date('d/m/Y H:i') }}.
        </div>
    </div>
</body>
</html>
