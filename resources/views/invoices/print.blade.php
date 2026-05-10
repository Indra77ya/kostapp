<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Diri Penghuni - {{ $registration->registration_number }}</title>
    <style>
        body { font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 40px; color: #333; line-height: 1.5; }
        .container { max-width: 850px; margin: auto; border: 1px solid #eee; padding: 40px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, .05); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #3b82f6; padding-bottom: 15px; margin-bottom: 25px; align-items: center; }
        .logo { font-size: 24px; font-weight: bold; color: #3b82f6; }
        .document-title { font-size: 20px; text-transform: uppercase; font-weight: bold; color: #444; }

        .section-title { background: #f3f4f6; padding: 8px 15px; font-weight: bold; font-size: 14px; text-transform: uppercase; margin: 25px 0 15px 0; border-left: 4px solid #3b82f6; }

        .row { display: flex; flex-wrap: wrap; margin-bottom: 10px; }
        .col { flex: 1; min-width: 200px; padding-right: 20px; }
        .label { font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 600; display: block; }
        .value { font-size: 15px; color: #111827; display: block; font-weight: 500; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { text-align: left; padding: 10px; border-bottom: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; text-transform: uppercase; }
        td { padding: 10px; border-bottom: 1px solid #f3f4f6; font-size: 14px; }

        .photo-container { width: 120px; height: 160px; border: 2px dashed #e5e7eb; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 20px; border-radius: 4px; }
        .photo-container img { width: 100%; height: 100%; object-fit: cover; }

        .footer { margin-top: 40px; display: flex; justify-content: space-between; align-items: flex-end; }
        .signature-box { text-align: center; width: 200px; }
        .signature-line { border-top: 1px solid #333; margin-top: 80px; padding-top: 5px; font-weight: bold; }

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
        <a href="javascript:window.print()" class="print-btn">Cetak Dokumen</a>
    </div>

    <div class="container">
        <div class="header">
            <div class="logo">KOST MANAGEMENT</div>
            <div class="document-title">Data Diri Penghuni</div>
        </div>

        <div style="display: flex; gap: 30px;">
            <div style="flex: 1;">
                <div class="section-title" style="margin-top: 0;">Informasi Registrasi</div>
                <div class="row">
                    <div class="col">
                        <span class="label">Nomor Registrasi</span>
                        <span class="value">{{ $registration->registration_number }}</span>
                    </div>
                    <div class="col">
                        <span class="label">Status</span>
                        <span class="value" style="color: {{ $registration->status === 'active' ? '#10b981' : '#6b7280' }}">{{ $registration->status === 'active' ? 'AKTIF' : 'SUDAH KELUAR' }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <span class="label">Tanggal Daftar</span>
                        <span class="value">{{ $registration->registration_date->format('d F Y') }}</span>
                    </div>
                    <div class="col">
                        <span class="label">Mulai Menginap</span>
                        <span class="value">{{ $registration->stay_start_date->format('d F Y') }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <span class="label">Lokasi</span>
                        <span class="value">{{ $registration->location->name }}</span>
                    </div>
                    <div class="col">
                        <span class="label">Kamar / Lantai</span>
                        <span class="value">Nomor {{ $registration->room->room_number }} / Lantai {{ $registration->room->floor }}</span>
                    </div>
                </div>
            </div>
            <div style="width: 120px;">
                <span class="label">Foto Diri</span>
                <div class="photo-container">
                    @if($registration->photo_self)
                        <img src="{{ asset('storage/' . $registration->photo_self) }}" alt="Foto Diri">
                    @else
                        <span style="color: #ccc; font-size: 10px;">FOTO 3X4</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="section-title">Data Pribadi</div>
        <div class="row">
            <div class="col">
                <span class="label">Nama Lengkap</span>
                <span class="value">{{ $registration->user->name }}</span>
            </div>
            <div class="col">
                <span class="label">Jenis Kelamin</span>
                <span class="value">{{ $registration->gender }}</span>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <span class="label">Tempat, Tanggal Lahir</span>
                <span class="value">{{ $registration->birth_place ?? '-' }}, {{ $registration->birth_date ? $registration->birth_date->format('d F Y') : '-' }}</span>
            </div>
            <div class="col">
                <span class="label">Email</span>
                <span class="value">{{ $registration->user->email }}</span>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <span class="label">Nomor Telepon</span>
                <span class="value">{{ $registration->user->phone_number ?? '-' }}</span>
            </div>
            <div class="col">
                <span class="label">Identitas ({{ $registration->identity_type }})</span>
                <span class="value">{{ $registration->identity_number }}</span>
            </div>
        </div>
        <div class="row">
            <div class="col" style="flex: 100%;">
                <span class="label">Alamat Lengkap</span>
                <span class="value">{{ $registration->user->address ?? '-' }}</span>
            </div>
        </div>

        @if($registration->institution_name)
        <div class="section-title">Informasi Instansi (Sekolah / Kampus / Kantor)</div>
        <div class="row">
            <div class="col">
                <span class="label">Nama Instansi</span>
                <span class="value">{{ $registration->institution_name }}</span>
            </div>
            <div class="col">
                <span class="label">Telepon Instansi</span>
                <span class="value">{{ $registration->institution_phone ?? '-' }}</span>
            </div>
        </div>
        <div class="row">
            <div class="col" style="flex: 100%;">
                <span class="label">Alamat Instansi</span>
                <span class="value">{{ $registration->institution_address ?? '-' }}</span>
            </div>
        </div>
        @endif

        @if($registration->emergencyContacts->count() > 0)
        <div class="section-title">Kontak Darurat (Emergency Contact)</div>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Hubungan</th>
                    <th>Nomor Telepon</th>
                    <th>Alamat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registration->emergencyContacts as $contact)
                <tr>
                    <td><strong>{{ $contact->name }}</strong></td>
                    <td>{{ $contact->relationship }}</td>
                    <td>{{ $contact->phone_number }}</td>
                    <td style="font-size: 12px;">{{ $contact->address ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($rules->count() > 0)
        <div class="section-title">Tata Tertib & Peraturan</div>
        <div style="font-size: 13px; color: #444;">
            @foreach($rules as $category => $categoryRules)
                <div style="margin-bottom: 12px;">
                    <strong style="color: #1f2937; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">— {{ $category }}</strong>
                    <ul style="margin: 5px 0 0 0; padding-left: 20px; list-style-type: square;">
                        @foreach($categoryRules as $rule)
                            <li style="margin-bottom: 5px;">
                                <strong style="color: #374151;">{{ $rule->title }}:</strong> {!! $rule->description !!}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
            <p style="font-style: italic; font-size: 12px; margin-top: 20px; border-left: 3px solid #3b82f6; padding: 5px 0 5px 15px; background: #f9fafb; color: #4b5563;">
                Saya yang bertanda tangan di bawah ini menyatakan telah membaca, memahami, dan menyetujui seluruh tata tertib dan peraturan yang berlaku di lingkungan Kost.
            </p>
        </div>
        @endif

        <div class="footer">
            <div class="signature-box">
                <p style="font-size: 13px;">Petugas / Pengelola,</p>
                <div class="signature-line">( .................................... )</div>
            </div>
            <div class="signature-box">
                <p style="font-size: 13px;">Penghuni,</p>
                <div class="signature-line">{{ $registration->user->name }}</div>
            </div>
        </div>

        <div style="margin-top: 30px; border-top: 1px dashed #eee; padding-top: 10px; font-size: 11px; color: #999; text-align: center;">
            Dokumen ini dicetak secara otomatis melalui Sistem Manajemen Kost pada {{ date('d/m/Y H:i') }}.
        </div>
    </div>
</body>
</html>
