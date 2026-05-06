<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $registration->registration_number }}</title>
    <style>
        body { font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 40px; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; border: 1px solid #eee; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, .15); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 28px; font-weight: bold; color: #3b82f6; }
        .invoice-title { font-size: 24px; text-transform: uppercase; font-weight: bold; color: #666; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .info-col { width: 45%; }
        .info-col h3 { font-size: 14px; text-transform: uppercase; color: #888; margin-bottom: 5px; border-bottom: 1px solid #eee; }
        .info-col p { margin: 2px 0; font-size: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f9fafb; text-align: left; padding: 12px; border-bottom: 2px solid #eee; font-size: 13px; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .total-section { display: flex; justify-content: flex-end; }
        .total-table { width: 250px; }
        .total-table td { border: none; padding: 5px 12px; }
        .grand-total { font-weight: bold; font-size: 18px; color: #3b82f6; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
        @media print {
            body { padding: 0; }
            .invoice-box { border: none; box-shadow: none; }
            .no-print { display: none; }
        }
        .print-btn { display: inline-block; background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="javascript:window.print()" class="print-btn">Cetak Invoice</a>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="logo">KOST MANAGEMENT</div>
            <div class="invoice-title">Invoice</div>
        </div>

        <div class="info-row">
            <div class="info-col">
                <h3>Penghuni:</h3>
                <p><strong>{{ $registration->user->name }}</strong></p>
                <p>{{ $registration->user->email }}</p>
                <p>{{ $registration->user->phone_number }}</p>
                <p>{{ $registration->user->address }}</p>
            </div>
            <div class="info-col" style="text-align: right;">
                <h3>Detail:</h3>
                <p>No. Registrasi: <strong>{{ $registration->registration_number }}</strong></p>
                <p>Tgl Daftar: {{ $registration->registration_date->format('d M Y') }}</p>
                <p>Mulai Inap: {{ $registration->stay_start_date->format('d M Y') }}</p>
                <p>Status: <span style="color: {{ $registration->status === 'active' ? '#10b981' : '#6b7280' }}; font-weight: bold;">{{ $registration->status === 'active' ? 'Aktif' : 'Check Out' }}</span></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th>Lokasi</th>
                    <th>Kamar / Lantai</th>
                    <th style="text-align: right;">Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Biaya Sewa Kamar (Deposit Awal / Pendaftaran)</td>
                    <td>{{ $registration->location->name }}</td>
                    <td>Kamar {{ $registration->room->room_number }} / Lantai {{ $registration->room->floor }} ({{ $registration->room->room_type }})</td>
                    <td style="text-align: right;">Rp {{ number_format($registration->room_price, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total-section">
            <table class="total-table">
                <tr>
                    <td>Subtotal:</td>
                    <td style="text-align: right;">Rp {{ number_format($registration->room_price, 0, ',', '.') }}</td>
                </tr>
                @if($registration->discount_value > 0)
                <tr>
                    <td>Diskon ({{ $registration->discount_type === 'percent' ? $registration->discount_value . '%' : 'Fixed' }}):</td>
                    <td style="text-align: right; color: #ef4444;">
                        @php
                            $discountAmount = $registration->discount_type === 'percent'
                                ? $registration->room_price * ($registration->discount_value / 100)
                                : $registration->discount_value;
                        @endphp
                        - Rp {{ number_format($discountAmount, 0, ',', '.') }}
                    </td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td>TOTAL:</td>
                    <td style="text-align: right;">Rp {{ number_format($registration->total_price, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Terima kasih telah memilih hunian kami. Harap simpan invoice ini sebagai bukti pembayaran yang sah.</p>
            <p>&copy; {{ date('Y') }} KOST MANAGEMENT SYSTEM</p>
        </div>
    </div>
</body>
</html>
