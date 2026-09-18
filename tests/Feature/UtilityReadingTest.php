<?php

namespace Tests\Feature;

use App\Livewire\UtilityManager;
use App\Models\Bill;
use App\Models\Location;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\UtilityReading;
use App\Models\UtilityType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UtilityReadingTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $tenant;
    protected $location;
    protected $room;
    protected $registration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('owner');

        $this->location = Location::create([
            'name' => 'Gedung Utama',
            'address' => 'Jl. Merdeka No. 123',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'room_number' => '101',
            'price_monthly' => 1500000,
            'status' => 'occupied',
        ]);

        $this->tenant = User::factory()->create();
        $this->tenant->assignRole('tenant');

        $this->registration = Registration::create([
            'registration_number' => 'REG-202609-0001',
            'user_id' => $this->tenant->id,
            'location_id' => $this->location->id,
            'room_id' => $this->room->id,
            'registration_date' => now()->subMonths(1)->format('Y-m-d'),
            'stay_start_date' => now()->subMonths(1)->format('Y-m-d'),
            'identity_type' => 'KTP',
            'identity_number' => '1234567890123456',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'phone' => '08123456789',
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 1500000,
            'total_price' => 1500000,
            'status' => 'active',
        ]);
    }

    public function test_owner_can_create_utility_type_tariff()
    {
        Livewire::actingAs($this->owner)
            ->test(UtilityManager::class)
            ->set('activeTab', 'tariffs')
            ->call('openTariffModal')
            ->set('tariff_location_id', $this->location->id)
            ->set('tariff_name', 'Listrik PLN')
            ->set('tariff_unit', 'kWh')
            ->set('tariff_rate_per_unit', 2000)
            ->set('tariff_is_active', true)
            ->call('saveTariff')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('utility_types', [
            'location_id' => $this->location->id,
            'name' => 'Listrik PLN',
            'unit' => 'kWh',
            'rate_per_unit' => 2000,
            'is_active' => true,
        ]);
    }

    public function test_meter_reading_calculates_usage_and_generates_bill()
    {
        Storage::fake('public');

        $utilityType = UtilityType::create([
            'location_id' => $this->location->id,
            'name' => 'Air PAM',
            'unit' => 'm3',
            'rate_per_unit' => 5000,
            'is_active' => true,
        ]);

        $image = UploadedFile::fake()->image('meter.jpg');

        Livewire::actingAs($this->owner)
            ->test(UtilityManager::class)
            ->call('openReadingModal')
            ->set('reading_location_id', $this->location->id)
            ->set('reading_room_id', $this->room->id)
            ->set('reading_utility_type_id', $utilityType->id)
            ->set('reading_date', now()->format('Y-m-d'))
            ->set('period_month', (int) now()->format('m'))
            ->set('period_year', (int) now()->format('Y'))
            ->set('previous_reading', 100)
            ->set('current_reading', 120) // Usage = 20 m3, Total = 20 * 5000 = 100,000
            ->set('rate_per_unit', 5000)
            ->set('reading_image', $image)
            ->set('auto_generate_bill', true)
            ->call('saveReading')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('utility_readings', [
            'location_id' => $this->location->id,
            'room_id' => $this->room->id,
            'registration_id' => $this->registration->id,
            'utility_type_id' => $utilityType->id,
            'previous_reading' => 100,
            'current_reading' => 120,
            'usage_amount' => 20,
            'total_amount' => 100000,
            'status' => 'billed',
        ]);

        $this->assertDatabaseHas('bills', [
            'registration_id' => $this->registration->id,
            'amount' => 100000,
            'status' => 'Belum Lunas',
        ]);
    }

    public function test_tenant_can_view_utility_details_in_payment_page()
    {
        $utilityType = UtilityType::create([
            'location_id' => $this->location->id,
            'name' => 'Listrik PLN',
            'unit' => 'kWh',
            'rate_per_unit' => 2000,
            'is_active' => true,
        ]);

        $bill = Bill::create([
            'registration_id' => $this->registration->id,
            'bill_number' => 'UTIL-TEST-0001',
            'description' => 'Tagihan Utilitas Listrik PLN Kamar 101',
            'discount' => 0,
            'amount' => 100000,
            'paid_amount' => 0,
            'due_date' => now()->addDays(7),
            'status' => 'Belum Lunas',
        ]);

        UtilityReading::create([
            'location_id' => $this->location->id,
            'room_id' => $this->room->id,
            'registration_id' => $this->registration->id,
            'utility_type_id' => $utilityType->id,
            'reading_date' => now()->format('Y-m-d'),
            'period_month' => (int) now()->format('m'),
            'period_year' => (int) now()->format('Y'),
            'previous_reading' => 50,
            'current_reading' => 100,
            'usage_amount' => 50,
            'rate_per_unit' => 2000,
            'total_amount' => 100000,
            'status' => 'billed',
            'bill_id' => $bill->id,
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.payments'));
        $response->assertStatus(200);
        $response->assertSee('UTIL-TEST-0001');
        $response->assertSee('50,00 kWh');
    }
}
