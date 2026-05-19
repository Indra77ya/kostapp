<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Tagihan - {{ $bill->bill_number }}</title>
    <style>
        body { font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 40px; color: #333; line-height: 1.5; }
        .container { max-width: 800px; margin: auto; border: 1px solid #eee; padding: 40px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, .05); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #3b82f6; padding-bottom: 15px; margin-bottom: 25px; align-items: center; }
        .logo { font-size: 24px; font-weight: bold; color: #3b82f6; }
        .document-title { font-size: 20px; text-transform: uppercase; font-weight: bold; color: #444; }

        .info-section { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .info-box { flex: 1; }
        .info-box:last-child { text-align: right; }
        .info-title { font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 600; margin-bottom: 5px; }
        .info-value { font-size: 15px; color: #111827; font-weight: 500; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { text-align: left; padding: 12px; border-bottom: 2px solid #e5e7eb; font-size: 12px; color: #6b7280; text-transform: uppercase; background: #f9fafb; }
        td { padding: 12px; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .text-right { text-align: right; }

        .total-section { margin-left: auto; width: 300px; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; }
        .total-label { font-weight: 500; color: #6b7280; }
        .total-value { font-weight: bold; color: #111827; }
        .grand-total { border-top: 2px solid #e5e7eb; margin-top: 10px; padding-top: 10px; font-size: 18px; color: #3b82f6; }

        .footer { margin-top: 50px; text-align: center; color: #999; font-size: 11px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .bg-success { background: #dcfce7; color: #166534; }
        .bg-danger { background: #fee2e2; color: #991b1b; }
        .bg-warning { background: #fef9c3; color: #854d0e; }

        @media print {
            body { padding: 0; }
            .container { border: none; box-shadow: none; max-width: 100%; }
            .no-print { display: none; }
        }
        .print-btn { display: inline-block; background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center;">
        <a href="javascript:window.print()" class="print-btn">Cetak Invoice</a>
    </div>

    <div class="container">
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
                <div class="info-title" style="margin-top: 10px;">Status</div>
                <div class="info-value">
                    @if($bill->status === 'Lunas')
                        <span class="badge bg-success">Lunas</span>
                    @elseif($bill->status === 'Cicilan')
                        <span class="badge bg-warning">Cicilan</span>
                    @else
                        <span class="badge bg-danger">Belum Lunas</span>
                    @endif
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Keterangan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $bill->description }}</td>
                    <td class="text-right">Rp {{ number_format($bill->amount + $bill->discount, 0, ',', '.') }}</td>
                </tr>
                @if($bill->discount > 0)
                <tr>
                    <td class="text-secondary">Diskon</td>
                    <td class="text-right text-danger">- Rp {{ number_format($bill->discount, 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row grand-total">
                <div class="total-label" style="color: #3b82f6;">Total Tagihan</div>
                <div class="total-value">Rp {{ number_format($bill->amount, 0, ',', '.') }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">Terbayar</div>
                <div class="total-value">Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">Sisa Tagihan</div>
                <div class="total-value text-danger">Rp {{ number_format($bill->remaining_amount, 0, ',', '.') }}</div>
            </div>
        </div>

        <div style="margin-top: 40px; font-size: 13px; color: #666;">
            <strong>Catatan:</strong><br>
            Mohon lakukan pembayaran sebelum tanggal jatuh tempo. Abaikan jika Anda sudah melakukan pembayaran.
        </div>

        <div class="footer">
            Dokumen ini dicetak secara otomatis melalui Sistem Manajemen Kost pada {{ date('d/m/Y H:i') }}.
        </div>
    </div>
</body>
</html>
