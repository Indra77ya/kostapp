<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Facility;
use App\Models\Location;
use App\Models\PaymentMethod;
use App\Models\Room;
use App\Models\Rule;
use App\Models\User;
use App\Services\MasterDataImportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MasterDataImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ChartOfAccountSeeder::class);

        $this->owner = User::factory()->create(['email' => 'owner@test.com']);
        $this->owner->assignRole('owner');
    }

    private function createExcelFile(array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $colIndex => $val) {
                $cell = Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1);
                $sheet->setCellValue($cell, $val);
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'test_import_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    public function test_can_download_templates()
    {
        $service = new MasterDataImportExportService();
        $entities = ['locations', 'facilities', 'rooms', 'users', 'rules', 'payment-methods'];

        foreach ($entities as $entity) {
            $response = $service->downloadTemplate($entity, 'xlsx');
            $this->assertEquals(200, $response->getStatusCode());

            $responseCsv = $service->downloadTemplate($entity, 'csv');
            $this->assertEquals(200, $responseCsv->getStatusCode());
        }
    }

    public function test_can_export_master_data()
    {
        Location::create(['name' => 'Lokasi Test', 'address' => 'Jl. Test No. 1']);
        Facility::create(['name' => 'AC Test', 'category' => 'Kamar']);

        $service = new MasterDataImportExportService();
        $entities = ['locations', 'facilities', 'rooms', 'users', 'rules', 'payment-methods'];

        foreach ($entities as $entity) {
            $response = $service->export($entity, 'xlsx');
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    public function test_can_import_new_locations()
    {
        $rows = [
            ['ID Ekspor', 'Nama Lokasi', 'Alamat', 'Link Google Maps', 'Nomor Telepon', 'Keterangan'],
            ['', 'Lokasi Baru 1', 'Jl. Merdeka 1', 'https://maps.google.com', '0812345678', 'Keterangan 1'],
            ['', 'Lokasi Baru 2', 'Jl. Merdeka 2', '', '', ''],
        ];

        $filePath = $this->createExcelFile($rows);

        $service = new MasterDataImportExportService();
        $result = $service->import('locations', $filePath);

        $this->assertEquals(2, $result['created']);
        $this->assertDatabaseHas('locations', ['name' => 'Lokasi Baru 1']);
        $this->assertDatabaseHas('locations', ['name' => 'Lokasi Baru 2']);

        @unlink($filePath);
    }

    public function test_can_update_existing_location_with_export_id()
    {
        $loc = Location::create(['name' => 'Lokasi Lama', 'address' => 'Alamat Lama']);

        $rows = [
            ['ID Ekspor', 'Nama Lokasi', 'Alamat', 'Link Google Maps', 'Nomor Telepon', 'Keterangan'],
            [$loc->id, 'Lokasi Diperbarui', 'Alamat Baru', '', '', ''],
        ];

        $filePath = $this->createExcelFile($rows);

        $service = new MasterDataImportExportService();
        $result = $service->import('locations', $filePath);

        $this->assertEquals(1, $result['updated']);
        $this->assertDatabaseHas('locations', ['id' => $loc->id, 'name' => 'Lokasi Diperbarui', 'address' => 'Alamat Baru']);

        @unlink($filePath);
    }

    public function test_duplicate_location_triggers_rollback()
    {
        $this->expectException(\Exception::class);

        $rows = [
            ['ID Ekspor', 'Nama Lokasi', 'Alamat', 'Link Google Maps', 'Nomor Telepon', 'Keterangan'],
            ['', 'Lokasi Sama', 'Alamat 1', '', '', ''],
            ['', 'Lokasi Sama', 'Alamat 2', '', '', ''],
        ];

        $filePath = $this->createExcelFile($rows);

        try {
            $service = new MasterDataImportExportService();
            $service->import('locations', $filePath);
        } finally {
            $this->assertDatabaseMissing('locations', ['name' => 'Lokasi Sama']);
            @unlink($filePath);
        }
    }

    public function test_room_import_fails_if_location_missing()
    {
        $this->expectException(\Exception::class);

        $rows = [
            ['ID Ekspor', 'Nama Lokasi', 'Nomor Kamar', 'Tipe Kamar', 'Lantai', 'Harga Bulanan', 'Harga Harian', 'Harga Mingguan', 'Harga Tahunan', 'Status', 'Fasilitas', 'Keterangan'],
            ['', 'Lokasi Tidak Ada', '101', 'Standard', '1', 1000000, 0, 0, 0, 'Tersedia', 'AC', ''],
        ];

        $filePath = $this->createExcelFile($rows);

        try {
            $service = new MasterDataImportExportService();
            $service->import('rooms', $filePath);
        } finally {
            $this->assertDatabaseMissing('rooms', ['room_number' => '101']);
            @unlink($filePath);
        }
    }

    public function test_can_import_rooms_with_valid_location()
    {
        $location = Location::create(['name' => 'Kost Utama']);

        $rows = [
            ['ID Ekspor', 'Nama Lokasi', 'Nomor Kamar', 'Tipe Kamar', 'Lantai', 'Harga Bulanan', 'Harga Harian', 'Harga Mingguan', 'Harga Tahunan', 'Status', 'Fasilitas', 'Keterangan'],
            ['', 'Kost Utama', '101', 'Deluxe', '1', 1500000, 100000, 500000, 15000000, 'Tersedia', 'AC, WiFi', 'Bagus'],
        ];

        $filePath = $this->createExcelFile($rows);

        $service = new MasterDataImportExportService();
        $result = $service->import('rooms', $filePath);

        $this->assertEquals(1, $result['created']);
        $this->assertDatabaseHas('rooms', ['location_id' => $location->id, 'room_number' => '101', 'price_monthly' => 1500000]);

        @unlink($filePath);
    }

    public function test_payment_method_import_fails_if_coa_missing()
    {
        $this->expectException(\Exception::class);

        $rows = [
            ['ID Ekspor', 'Nama Metode', 'Kategori', 'Kode Akun COA', 'Nomor Rekening', 'Atas Nama', 'Status Aktif', 'Instruksi'],
            ['', 'Transfer XYZ', 'Bank Transfer', '9-9999', '12345678', 'PT Test', 1, ''],
        ];

        $filePath = $this->createExcelFile($rows);

        try {
            $service = new MasterDataImportExportService();
            $service->import('payment-methods', $filePath);
        } finally {
            $this->assertDatabaseMissing('payment_methods', ['name' => 'Transfer XYZ']);
            @unlink($filePath);
        }
    }

    public function test_can_import_payment_methods_with_valid_coa()
    {
        $coa = ChartOfAccount::first();

        $rows = [
            ['ID Ekspor', 'Nama Metode', 'Kategori', 'Kode Akun COA', 'Nomor Rekening', 'Atas Nama', 'Status Aktif', 'Instruksi'],
            ['', 'Transfer Mandiri', 'Bank Transfer', $coa->code, '987654321', 'PT Kost', 1, 'Transfer ke Mandiri'],
        ];

        $filePath = $this->createExcelFile($rows);

        $service = new MasterDataImportExportService();
        $result = $service->import('payment-methods', $filePath);

        $this->assertEquals(1, $result['created']);
        $this->assertDatabaseHas('payment_methods', ['name' => 'Transfer Mandiri', 'chart_of_account_id' => $coa->id]);

        @unlink($filePath);
    }

    public function test_livewire_location_import_integration()
    {
        $rows = [
            ['ID Ekspor', 'Nama Lokasi', 'Alamat', 'Link Google Maps', 'Nomor Telepon', 'Keterangan'],
            ['', 'Lokasi Livewire', 'Jl. Test 100', '', '', ''],
        ];

        $filePath = $this->createExcelFile($rows);
        $file = UploadedFile::fake()->createWithContent('import.xlsx', file_get_contents($filePath));

        Livewire::actingAs($this->owner)
            ->test(\App\Livewire\LocationManager::class)
            ->set('importFile', $file)
            ->call('importData')
            ->assertDispatched('notify');

        $this->assertDatabaseHas('locations', ['name' => 'Lokasi Livewire']);
        @unlink($filePath);
    }
}
