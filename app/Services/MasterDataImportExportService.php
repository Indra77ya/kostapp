<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Facility;
use App\Models\Location;
use App\Models\PaymentMethod;
use App\Models\Room;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterDataImportExportService
{
    /**
     * Entity definitions with columns and headers
     */
    public static function getEntityConfig(string $entity): array
    {
        return match ($entity) {
            'locations' => [
                'title' => 'Lokasi',
                'headers' => ['ID Ekspor', 'Nama Lokasi', 'Alamat', 'Link Google Maps', 'Nomor Telepon', 'Keterangan'],
                'sample' => ['', 'Kost Utama', 'Jl. Merdeka No. 123, Jakarta', 'https://maps.google.com/?q=-6.1,106.8', '081234567890', 'Gedung utama 3 lantai'],
            ],
            'facilities' => [
                'title' => 'Fasilitas',
                'headers' => ['ID Ekspor', 'Nama Fasilitas', 'Kategori'],
                'sample' => ['', 'AC 1 PK', 'Kamar'],
            ],
            'rooms' => [
                'title' => 'Kamar',
                'headers' => ['ID Ekspor', 'Nama Lokasi', 'Nomor Kamar', 'Tipe Kamar', 'Lantai', 'Harga Bulanan', 'Harga Harian', 'Harga Mingguan', 'Harga Tahunan', 'Status', 'Fasilitas (Pisahkan Koma)', 'Keterangan'],
                'sample' => ['', 'Kost Utama', '101', 'Deluxe', '1', 1500000, 100000, 500000, 15000000, 'Tersedia', 'AC 1 PK, Wi-Fi, Kasur Springbed', 'Kamar mandi dalam'],
            ],
            'users' => [
                'title' => 'Pengguna',
                'headers' => ['ID Ekspor', 'Nama Lengkap', 'Email', 'Nomor HP', 'Role', 'Password (Opsional)'],
                'sample' => ['', 'Ahmad Fauzi', 'ahmad@example.com', '081298765432', 'admin', 'password123'],
            ],
            'rules' => [
                'title' => 'Peraturan',
                'headers' => ['ID Ekspor', 'Nama Lokasi (Kosongkan jika semua)', 'Judul Peraturan', 'Kategori', 'Status Aktif (1/0)', 'Keterangan'],
                'sample' => ['', 'Kost Utama', 'Dilarang Merokok di Dalam Kamar', 'Umum', 1, 'Demi kenyamanan dan keselamatan bersama'],
            ],
            'payment-methods' => [
                'title' => 'Metode Pembayaran',
                'headers' => ['ID Ekspor', 'Nama Metode', 'Kategori', 'Kode Akun COA', 'Nomor Rekening', 'Atas Nama', 'Status Aktif (1/0)', 'Instruksi'],
                'sample' => ['', 'Transfer BCA', 'Bank Transfer', '1-1000', '1234567890', 'PT Kost Indonesia', 1, 'Transfer sesuai nominal tagihan'],
            ],
            default => throw new \InvalidArgumentException("Entitas master data '$entity' tidak dikenali."),
        };
    }

    /**
     * Download template for import
     */
    public function downloadTemplate(string $entity, string $format = 'xlsx'): StreamedResponse
    {
        $config = self::getEntityConfig($entity);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Write Headers
        foreach ($config['headers'] as $colIndex => $header) {
            $cell = Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
            $sheet->setCellValue($cell, $header);
        }

        // Write Sample Data Row
        foreach ($config['sample'] as $colIndex => $sampleVal) {
            $cell = Coordinate::stringFromColumnIndex($colIndex + 1) . '2';
            $sheet->setCellValue($cell, $sampleVal);
        }

        $filename = 'Template_Impor_' . ucfirst($entity) . '_' . date('Ymd_His') . '.' . strtolower($format);

        return $this->streamSpreadsheetResponse($spreadsheet, $filename, $format);
    }

    /**
     * Export master data to file stream
     */
    public function export(string $entity, string $format = 'xlsx'): StreamedResponse
    {
        $config = self::getEntityConfig($entity);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Write Headers
        foreach ($config['headers'] as $colIndex => $header) {
            $cell = Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
            $sheet->setCellValue($cell, $header);
        }

        $rowNum = 2;

        switch ($entity) {
            case 'locations':
                $locations = Location::orderBy('id')->get();
                foreach ($locations as $item) {
                    $sheet->setCellValue('A' . $rowNum, $item->id);
                    $sheet->setCellValue('B' . $rowNum, $item->name);
                    $sheet->setCellValue('C' . $rowNum, $item->address);
                    $sheet->setCellValue('D' . $rowNum, $item->google_maps_link);
                    $sheet->setCellValue('E' . $rowNum, $item->phone);
                    $sheet->setCellValue('F' . $rowNum, $item->description);
                    $rowNum++;
                }
                break;

            case 'facilities':
                $facilities = Facility::orderBy('id')->get();
                foreach ($facilities as $item) {
                    $sheet->setCellValue('A' . $rowNum, $item->id);
                    $sheet->setCellValue('B' . $rowNum, $item->name);
                    $sheet->setCellValue('C' . $rowNum, $item->category);
                    $rowNum++;
                }
                break;

            case 'rooms':
                $rooms = Room::with('location')->orderBy('id')->get();
                foreach ($rooms as $item) {
                    $facilitiesStr = is_array($item->facilities) ? implode(', ', $item->facilities) : ($item->facilities ?? '');
                    $sheet->setCellValue('A' . $rowNum, $item->id);
                    $sheet->setCellValue('B' . $rowNum, $item->location?->name ?? '');
                    $sheet->setCellValue('C' . $rowNum, $item->room_number);
                    $sheet->setCellValue('D' . $rowNum, $item->room_type);
                    $sheet->setCellValue('E' . $rowNum, $item->floor);
                    $sheet->setCellValue('F' . $rowNum, $item->price_monthly);
                    $sheet->setCellValue('G' . $rowNum, $item->price_daily);
                    $sheet->setCellValue('H' . $rowNum, $item->price_weekly);
                    $sheet->setCellValue('I' . $rowNum, $item->price_yearly);
                    $sheet->setCellValue('J' . $rowNum, $item->status);
                    $sheet->setCellValue('K' . $rowNum, $facilitiesStr);
                    $sheet->setCellValue('L' . $rowNum, $item->description);
                    $rowNum++;
                }
                break;

            case 'users':
                $users = User::orderBy('id')->get();
                foreach ($users as $item) {
                    $roleName = $item->getRoleNames()->first() ?? '';
                    $sheet->setCellValue('A' . $rowNum, $item->id);
                    $sheet->setCellValue('B' . $rowNum, $item->name);
                    $sheet->setCellValue('C' . $rowNum, $item->email);
                    $sheet->setCellValue('D' . $rowNum, $item->phone);
                    $sheet->setCellValue('E' . $rowNum, $roleName);
                    $sheet->setCellValue('F' . $rowNum, ''); // Password empty on export
                    $rowNum++;
                }
                break;

            case 'rules':
                $rules = Rule::with('location')->orderBy('id')->get();
                foreach ($rules as $item) {
                    $sheet->setCellValue('A' . $rowNum, $item->id);
                    $sheet->setCellValue('B' . $rowNum, $item->location?->name ?? '');
                    $sheet->setCellValue('C' . $rowNum, $item->title);
                    $sheet->setCellValue('D' . $rowNum, $item->category);
                    $sheet->setCellValue('E' . $rowNum, $item->is_active ? 1 : 0);
                    $sheet->setCellValue('F' . $rowNum, $item->description);
                    $rowNum++;
                }
                break;

            case 'payment-methods':
                $methods = PaymentMethod::with('account')->orderBy('id')->get();
                foreach ($methods as $item) {
                    $sheet->setCellValue('A' . $rowNum, $item->id);
                    $sheet->setCellValue('B' . $rowNum, $item->name);
                    $sheet->setCellValue('C' . $rowNum, $item->category);
                    $sheet->setCellValue('D' . $rowNum, $item->account?->code ?? '');
                    $sheet->setCellValue('E' . $rowNum, $item->account_number);
                    $sheet->setCellValue('F' . $rowNum, $item->account_name);
                    $sheet->setCellValue('G' . $rowNum, $item->is_active ? 1 : 0);
                    $sheet->setCellValue('H' . $rowNum, $item->instructions);
                    $rowNum++;
                }
                break;
        }

        $filename = 'Ekspor_' . ucfirst($entity) . '_' . date('Ymd_His') . '.' . strtolower($format);

        return $this->streamSpreadsheetResponse($spreadsheet, $filename, $format);
    }

    /**
     * Import master data from uploaded file
     * Reads all rows first, validates dependencies and duplicates, then executes inside DB transaction.
     * If any error occurs, throws Exception to trigger full rollback.
     */
    public function import(string $entity, string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) <= 1) {
            throw new \Exception('File impor kosong atau hanya berisi header.');
        }

        // Header is row 1
        $dataRows = array_slice($rows, 1, null, true);

        return DB::transaction(function () use ($entity, $dataRows) {
            return match ($entity) {
                'locations' => $this->importLocations($dataRows),
                'facilities' => $this->importFacilities($dataRows),
                'rooms' => $this->importRooms($dataRows),
                'users' => $this->importUsers($dataRows),
                'rules' => $this->importRules($dataRows),
                'payment-methods' => $this->importPaymentMethods($dataRows),
                default => throw new \InvalidArgumentException("Entitas master data '$entity' tidak dikenali."),
            };
        });
    }

    private function importLocations(array $dataRows): array
    {
        $createdCount = 0;
        $updatedCount = 0;
        $seenNames = [];

        foreach ($dataRows as $lineIndex => $row) {
            $excelRow = $lineIndex;
            $id = trim($row['A'] ?? '');
            $name = trim($row['B'] ?? '');
            $address = trim($row['C'] ?? '');
            $mapsLink = trim($row['D'] ?? '');
            $phone = trim($row['E'] ?? '');
            $description = trim($row['F'] ?? '');

            if (empty($name)) {
                // Skip completely empty rows
                if (empty($id) && empty($address) && empty($phone)) {
                    continue;
                }
                throw new \Exception("Baris {$excelRow}: Nama Lokasi wajib diisi.");
            }

            // Check duplicate in file itself
            $nameLower = strtolower($name);
            if (isset($seenNames[$nameLower])) {
                throw new \Exception("Baris {$excelRow}: Duplikasi nama lokasi '{$name}' dalam file impor (sama dengan baris {$seenNames[$nameLower]}).");
            }
            $seenNames[$nameLower] = $excelRow;

            if (!empty($id)) {
                $location = Location::find($id);
                if (!$location) {
                    throw new \Exception("Baris {$excelRow}: ID Ekspor '{$id}' tidak ditemukan di sistem.");
                }
                // Check if name is taken by another location
                $existing = Location::where('name', $name)->where('id', '!=', $id)->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Nama lokasi '{$name}' sudah digunakan oleh lokasi lain di sistem.");
                }

                $location->update([
                    'name' => $name,
                    'address' => $address ?: null,
                    'google_maps_link' => $mapsLink ?: null,
                    'phone' => $phone ?: null,
                    'description' => $description ?: null,
                ]);
                $updatedCount++;
            } else {
                // New record check duplicate in DB
                $existing = Location::where('name', $name)->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Lokasi dengan nama '{$name}' sudah ada di sistem. Untuk memperbarui data yang ada, gunakan ID Ekspor dari file ekspor.");
                }

                Location::create([
                    'name' => $name,
                    'address' => $address ?: null,
                    'google_maps_link' => $mapsLink ?: null,
                    'phone' => $phone ?: null,
                    'description' => $description ?: null,
                ]);
                $createdCount++;
            }
        }

        return ['created' => $createdCount, 'updated' => $updatedCount];
    }

    private function importFacilities(array $dataRows): array
    {
        $createdCount = 0;
        $updatedCount = 0;
        $seenNames = [];

        foreach ($dataRows as $lineIndex => $row) {
            $excelRow = $lineIndex + 1;
            $id = trim($row['A'] ?? '');
            $name = trim($row['B'] ?? '');
            $category = trim($row['C'] ?? '');

            if (empty($name)) {
                if (empty($id) && empty($category)) {
                    continue;
                }
                throw new \Exception("Baris {$excelRow}: Nama Fasilitas wajib diisi.");
            }

            $nameLower = strtolower($name);
            if (isset($seenNames[$nameLower])) {
                throw new \Exception("Baris {$excelRow}: Duplikasi nama fasilitas '{$name}' dalam file impor (sama dengan baris {$seenNames[$nameLower]}).");
            }
            $seenNames[$nameLower] = $excelRow;

            if (!empty($id)) {
                $facility = Facility::find($id);
                if (!$facility) {
                    throw new \Exception("Baris {$excelRow}: ID Ekspor '{$id}' tidak ditemukan di sistem.");
                }
                $existing = Facility::where('name', $name)->where('id', '!=', $id)->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Nama fasilitas '{$name}' sudah digunakan oleh data fasilitas lain.");
                }

                $facility->update([
                    'name' => $name,
                    'category' => $category ?: 'Kamar',
                ]);
                $updatedCount++;
            } else {
                $existing = Facility::where('name', $name)->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Fasilitas dengan nama '{$name}' sudah ada di sistem. Gunakan ID Ekspor jika ingin memperbarui data.");
                }

                Facility::create([
                    'name' => $name,
                    'category' => $category ?: 'Kamar',
                ]);
                $createdCount++;
            }
        }

        return ['created' => $createdCount, 'updated' => $updatedCount];
    }

    private function importRooms(array $dataRows): array
    {
        $createdCount = 0;
        $updatedCount = 0;
        $seenRooms = [];

        foreach ($dataRows as $lineIndex => $row) {
            $excelRow = $lineIndex + 1;
            $id = trim($row['A'] ?? '');
            $locationName = trim($row['B'] ?? '');
            $roomNumber = trim($row['C'] ?? '');
            $roomType = trim($row['D'] ?? '');
            $floor = trim($row['E'] ?? '');
            $priceMonthly = str_replace(['.', ','], ['', ''], trim($row['F'] ?? '0'));
            $priceDaily = str_replace(['.', ','], ['', ''], trim($row['G'] ?? '0'));
            $priceWeekly = str_replace(['.', ','], ['', ''], trim($row['H'] ?? '0'));
            $priceYearly = str_replace(['.', ','], ['', ''], trim($row['I'] ?? '0'));
            $status = trim($row['J'] ?? '');
            $facilitiesRaw = trim($row['K'] ?? '');
            $description = trim($row['L'] ?? '');

            if (empty($locationName) && empty($roomNumber)) {
                if (empty($id)) continue;
            }

            if (empty($locationName)) {
                throw new \Exception("Baris {$excelRow}: Nama Lokasi wajib diisi.");
            }

            if (empty($roomNumber)) {
                throw new \Exception("Baris {$excelRow}: Nomor Kamar wajib diisi.");
            }

            // Verify Relational Dependency (Location)
            $location = Location::where('name', $locationName)->orWhere('id', $locationName)->first();
            if (!$location) {
                throw new \Exception("Baris {$excelRow}: Lokasi '{$locationName}' tidak ditemukan di sistem. Harap buat/impor data Lokasi terlebih dahulu.");
            }

            // Format facilities array
            $facilitiesArr = [];
            if (!empty($facilitiesRaw)) {
                $facilitiesArr = array_values(array_filter(array_map('trim', explode(',', $facilitiesRaw))));
            }

            // Key for internal duplicate check
            $roomKey = $location->id . '_' . strtolower($roomNumber);
            if (isset($seenRooms[$roomKey])) {
                throw new \Exception("Baris {$excelRow}: Duplikasi kamar nomor '{$roomNumber}' untuk lokasi '{$location->name}' dalam file impor (sama dengan baris {$seenRooms[$roomKey]}).");
            }
            $seenRooms[$roomKey] = $excelRow;

            $validStatuses = ['Tersedia', 'Terisi', 'Perbaikan'];
            $roomStatus = in_array($status, $validStatuses) ? $status : 'Tersedia';

            if (!empty($id)) {
                $room = Room::find($id);
                if (!$room) {
                    throw new \Exception("Baris {$excelRow}: ID Ekspor '{$id}' tidak ditemukan di sistem.");
                }
                $existing = Room::where('location_id', $location->id)
                    ->where('room_number', $roomNumber)
                    ->where('id', '!=', $id)
                    ->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Kamar nomor '{$roomNumber}' sudah ada pada lokasi '{$location->name}'.");
                }

                $room->update([
                    'location_id' => $location->id,
                    'room_number' => $roomNumber,
                    'room_type' => $roomType ?: 'Standard',
                    'floor' => $floor ?: '1',
                    'price_monthly' => is_numeric($priceMonthly) ? (float) $priceMonthly : 0,
                    'price_daily' => is_numeric($priceDaily) ? (float) $priceDaily : 0,
                    'price_weekly' => is_numeric($priceWeekly) ? (float) $priceWeekly : 0,
                    'price_yearly' => is_numeric($priceYearly) ? (float) $priceYearly : 0,
                    'status' => $roomStatus,
                    'facilities' => !empty($facilitiesArr) ? implode(', ', $facilitiesArr) : null,
                    'description' => $description ?: null,
                ]);
                $updatedCount++;
            } else {
                $existing = Room::where('location_id', $location->id)
                    ->where('room_number', $roomNumber)
                    ->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Kamar nomor '{$roomNumber}' sudah ada pada lokasi '{$location->name}' di sistem. Gunakan ID Ekspor untuk memperbarui.");
                }

                Room::create([
                    'location_id' => $location->id,
                    'room_number' => $roomNumber,
                    'room_type' => $roomType ?: 'Standard',
                    'floor' => $floor ?: '1',
                    'price_monthly' => is_numeric($priceMonthly) ? (float) $priceMonthly : 0,
                    'price_daily' => is_numeric($priceDaily) ? (float) $priceDaily : 0,
                    'price_weekly' => is_numeric($priceWeekly) ? (float) $priceWeekly : 0,
                    'price_yearly' => is_numeric($priceYearly) ? (float) $priceYearly : 0,
                    'status' => $roomStatus,
                    'facilities' => !empty($facilitiesArr) ? implode(', ', $facilitiesArr) : null,
                    'description' => $description ?: null,
                ]);
                $createdCount++;
            }
        }

        return ['created' => $createdCount, 'updated' => $updatedCount];
    }

    private function importUsers(array $dataRows): array
    {
        $createdCount = 0;
        $updatedCount = 0;
        $seenEmails = [];

        foreach ($dataRows as $lineIndex => $row) {
            $excelRow = $lineIndex + 1;
            $id = trim($row['A'] ?? '');
            $name = trim($row['B'] ?? '');
            $email = trim($row['C'] ?? '');
            $phone = trim($row['D'] ?? '');
            $roleInput = strtolower(trim($row['E'] ?? 'tenant'));
            $passwordInput = trim($row['F'] ?? '');

            if (empty($name) && empty($email)) {
                if (empty($id)) continue;
            }

            if (empty($name)) {
                throw new \Exception("Baris {$excelRow}: Nama Lengkap pengguna wajib diisi.");
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("Baris {$excelRow}: Format email '{$email}' tidak valid.");
            }

            $emailLower = strtolower($email);
            if (isset($seenEmails[$emailLower])) {
                throw new \Exception("Baris {$excelRow}: Duplikasi email '{$email}' dalam file impor (sama dengan baris {$seenEmails[$emailLower]}).");
            }
            $seenEmails[$emailLower] = $excelRow;

            $validRoles = ['owner', 'developer', 'admin', 'tenant'];
            $roleName = in_array($roleInput, $validRoles) ? $roleInput : 'tenant';

            if (!empty($id)) {
                $user = User::find($id);
                if (!$user) {
                    throw new \Exception("Baris {$excelRow}: ID Ekspor '{$id}' tidak ditemukan di sistem.");
                }

                $existing = User::where('email', $email)->where('id', '!=', $id)->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Email '{$email}' sudah digunakan oleh pengguna lain di sistem.");
                }

                $updateData = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone ?: null,
                ];

                if (!empty($passwordInput)) {
                    $updateData['password'] = Hash::make($passwordInput);
                }

                $user->update($updateData);
                $user->syncRoles([$roleName]);
                $updatedCount++;
            } else {
                $existing = User::where('email', $email)->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Email '{$email}' sudah terdaftar di sistem. Gunakan ID Ekspor jika ingin memperbarui data pengguna.");
                }

                $pwd = !empty($passwordInput) ? $passwordInput : 'password123';

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone ?: null,
                    'password' => Hash::make($pwd),
                ]);
                $user->assignRole($roleName);
                $createdCount++;
            }
        }

        return ['created' => $createdCount, 'updated' => $updatedCount];
    }

    private function importRules(array $dataRows): array
    {
        $createdCount = 0;
        $updatedCount = 0;
        $seenRules = [];

        foreach ($dataRows as $lineIndex => $row) {
            $excelRow = $lineIndex + 1;
            $id = trim($row['A'] ?? '');
            $locationName = trim($row['B'] ?? '');
            $title = trim($row['C'] ?? '');
            $category = trim($row['D'] ?? '');
            $isActiveInput = trim($row['E'] ?? '1');
            $description = trim($row['F'] ?? '');

            if (empty($title)) {
                if (empty($id) && empty($locationName)) continue;
                throw new \Exception("Baris {$excelRow}: Judul Peraturan wajib diisi.");
            }

            $locationId = null;
            if (!empty($locationName)) {
                $location = Location::where('name', $locationName)->orWhere('id', $locationName)->first();
                if (!$location) {
                    throw new \Exception("Baris {$excelRow}: Lokasi '{$locationName}' tidak ditemukan di sistem. Harap buat/impor data Lokasi terlebih dahulu.");
                }
                $locationId = $location->id;
            }

            $ruleKey = ($locationId ?? 'global') . '_' . strtolower($title);
            if (isset($seenRules[$ruleKey])) {
                throw new \Exception("Baris {$excelRow}: Duplikasi judul peraturan '{$title}' dalam file impor (sama dengan baris {$seenRules[$ruleKey]}).");
            }
            $seenRules[$ruleKey] = $excelRow;

            $isActive = in_array(strtolower($isActiveInput), ['1', 'true', 'ya', 'yes', 'aktif']);

            if (!empty($id)) {
                $rule = Rule::find($id);
                if (!$rule) {
                    throw new \Exception("Baris {$excelRow}: ID Ekspor '{$id}' tidak ditemukan di sistem.");
                }

                $existing = Rule::where('title', $title)
                    ->where('location_id', $locationId)
                    ->where('id', '!=', $id)
                    ->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Peraturan '{$title}' sudah ada untuk lokasi tersebut.");
                }

                $rule->update([
                    'location_id' => $locationId,
                    'title' => $title,
                    'category' => $category ?: 'Umum',
                    'is_active' => $isActive,
                    'description' => $description ?: null,
                ]);
                $updatedCount++;
            } else {
                $existing = Rule::where('title', $title)
                    ->where('location_id', $locationId)
                    ->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Peraturan '{$title}' sudah ada di sistem. Gunakan ID Ekspor untuk memperbarui.");
                }

                Rule::create([
                    'location_id' => $locationId,
                    'title' => $title,
                    'category' => $category ?: 'Umum',
                    'is_active' => $isActive,
                    'description' => $description ?: null,
                ]);
                $createdCount++;
            }
        }

        return ['created' => $createdCount, 'updated' => $updatedCount];
    }

    private function importPaymentMethods(array $dataRows): array
    {
        $createdCount = 0;
        $updatedCount = 0;
        $seenMethods = [];

        foreach ($dataRows as $lineIndex => $row) {
            $excelRow = $lineIndex + 1;
            $id = trim($row['A'] ?? '');
            $name = trim($row['B'] ?? '');
            $category = trim($row['C'] ?? '');
            $coaCode = trim($row['D'] ?? '');
            $accountNumber = trim($row['E'] ?? '');
            $accountName = trim($row['F'] ?? '');
            $isActiveInput = trim($row['G'] ?? '1');
            $instructions = trim($row['H'] ?? '');

            if (empty($name)) {
                if (empty($id) && empty($coaCode)) continue;
                throw new \Exception("Baris {$excelRow}: Nama Metode Pembayaran wajib diisi.");
            }

            if (empty($coaCode)) {
                throw new \Exception("Baris {$excelRow}: Kode Akun COA wajib diisi.");
            }

            // Verify COA Relational Dependency
            $coa = ChartOfAccount::where('code', $coaCode)
                ->orWhere('id', $coaCode)
                ->orWhere('name', $coaCode)
                ->first();

            if (!$coa) {
                throw new \Exception("Baris {$excelRow}: Akun COA dengan kode/nama '{$coaCode}' tidak ditemukan di Bagan Akun. Harap pastikan akun COA sudah ada di sistem.");
            }

            $nameLower = strtolower($name);
            if (isset($seenMethods[$nameLower])) {
                throw new \Exception("Baris {$excelRow}: Duplikasi nama metode pembayaran '{$name}' dalam file impor (sama dengan baris {$seenMethods[$nameLower]}).");
            }
            $seenMethods[$nameLower] = $excelRow;

            $isActive = in_array(strtolower($isActiveInput), ['1', 'true', 'ya', 'yes', 'aktif']);

            if (!empty($id)) {
                $method = PaymentMethod::find($id);
                if (!$method) {
                    throw new \Exception("Baris {$excelRow}: ID Ekspor '{$id}' tidak ditemukan di sistem.");
                }

                $existing = PaymentMethod::where('name', $name)->where('id', '!=', $id)->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Nama metode pembayaran '{$name}' sudah digunakan oleh data lain.");
                }

                $method->update([
                    'name' => $name,
                    'category' => $category ?: 'Bank Transfer',
                    'chart_of_account_id' => $coa->id,
                    'account_number' => $accountNumber ?: null,
                    'account_name' => $accountName ?: null,
                    'is_active' => $isActive,
                    'instructions' => $instructions ?: null,
                ]);
                $updatedCount++;
            } else {
                $existing = PaymentMethod::where('name', $name)->first();
                if ($existing) {
                    throw new \Exception("Baris {$excelRow}: Metode pembayaran '{$name}' sudah ada di sistem. Gunakan ID Ekspor untuk memperbarui data.");
                }

                PaymentMethod::create([
                    'name' => $name,
                    'category' => $category ?: 'Bank Transfer',
                    'chart_of_account_id' => $coa->id,
                    'account_number' => $accountNumber ?: null,
                    'account_name' => $accountName ?: null,
                    'is_active' => $isActive,
                    'instructions' => $instructions ?: null,
                ]);
                $createdCount++;
            }
        }

        return ['created' => $createdCount, 'updated' => $updatedCount];
    }

    /**
     * Helper stream response for XLSX/CSV
     */
    private function streamSpreadsheetResponse(Spreadsheet $spreadsheet, string $filename, string $format): StreamedResponse
    {
        $isCsv = strtolower($format) === 'csv';
        $contentType = $isCsv ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return response()->stream(
            function () use ($spreadsheet, $isCsv) {
                if ($isCsv) {
                    $writer = new Csv($spreadsheet);
                    $writer->setDelimiter(',');
                    $writer->setEnclosure('"');
                    $writer->setLineEnding("\r\n");
                    $writer->setSheetIndex(0);
                } else {
                    $writer = new Xlsx($spreadsheet);
                }
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
