<?php

namespace Tests\Feature;

use App\Livewire\RegistrationManager;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use App\Models\Bill;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationDiscountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);
        Storage::fake('public');
    }

    public function test_discount_is_applied_correctly_to_total_and_bills()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost C', 'address' => 'Jl. C']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '303',
            'type' => 'Standard',
            'price_monthly' => 1500000,
            'facilities' => 'Bed, AC'
        ]);

        $photoSelf = UploadedFile::fake()->image('self.jpg');
        $photoIdentity = UploadedFile::fake()->image('ktp.jpg');

        // Case: 3 months stay, 2 months discount of 100,000
        // Expected: (1,400,000 * 2) + 1,500,000 = 2,800,000 + 1,500,000 = 4,300,000
        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room->id)
            ->set('duration_type', 'monthly')
            ->set('duration_value', 3)
            ->set('discount_type', 'fixed')
            ->set('discount_value', 100000)
            ->set('discount_duration', 2)
            ->set('stay_start_date', '2026-06-01')
            ->set('name', 'Discount Tester')
            ->set('email', 'tester@example.com')
            ->set('identity_number', '12345')
            ->set('birth_date', '1995-01-01')
            ->set('photo_self', $photoSelf)
            ->set('photo_identity', $photoIdentity)
            ->call('saveRegistration')
            ->assertHasNoErrors();

        $registration = Registration::whereHas('user', function($q) {
            $q->where('email', 'tester@example.com');
        })->first();
        $this->assertEquals(4300000, $registration->total_price);

        // Check bills
        $bills = Bill::where('registration_id', $registration->id)->orderBy('due_date')->get();
        $this->assertCount(3, $bills);
        $this->assertEquals(1400000, $bills[0]->amount);
        $this->assertEquals(1400000, $bills[1]->amount);
        $this->assertEquals(1500000, $bills[2]->amount);
    }

    public function test_open_ended_registration_generates_12_bills_with_initial_discount()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost D', 'address' => 'Jl. D']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '404',
            'type' => 'Standard',
            'price_monthly' => 1000000,
        ]);

        $photoSelf = UploadedFile::fake()->image('self.jpg');
        $photoIdentity = UploadedFile::fake()->image('ktp.jpg');

        // Case: Open-ended, 1 month discount of 50%
        // Expected total price (for display 12 months): (500,000 * 1) + (1,000,000 * 11) = 11,500,000
        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room->id)
            ->set('is_open_ended', true)
            ->set('discount_type', 'percent')
            ->set('discount_value', 50)
            ->set('discount_duration', 1)
            ->set('stay_start_date', '2026-06-01')
            ->set('name', 'Open Ended Tester')
            ->set('email', 'open@example.com')
            ->set('identity_number', '67890')
            ->set('birth_date', '1995-01-01')
            ->set('photo_self', $photoSelf)
            ->set('photo_identity', $photoIdentity)
            ->call('saveRegistration')
            ->assertHasNoErrors();

        $registration = Registration::whereHas('user', function($q) {
            $q->where('email', 'open@example.com');
        })->first();
        $this->assertEquals(11500000, $registration->total_price);

        // Check bills
        $bills = Bill::where('registration_id', $registration->id)->orderBy('due_date')->get();
        $this->assertCount(12, $bills);
        $this->assertEquals(500000, $bills[0]->amount);
        $this->assertEquals(1000000, $bills[1]->amount);
        $this->assertEquals(1000000, $bills[11]->amount);
    }

    public function test_indefinite_discount_applies_to_all_bills()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost E', 'address' => 'Jl. E']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '505',
            'type' => 'Standard',
            'price_monthly' => 1000000,
        ]);

        $photoSelf = UploadedFile::fake()->image('self.jpg');
        $photoIdentity = UploadedFile::fake()->image('ktp.jpg');

        // Case: 6 months stay, Indefinite discount of Rp 100k
        // Expected total price: 900,000 * 6 = 5,400,000
        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room->id)
            ->set('duration_value', 6)
            ->set('discount_type', 'fixed')
            ->set('discount_value', 100000)
            ->set('is_discount_open_ended', true)
            ->set('stay_start_date', '2026-06-01')
            ->set('name', 'Indefinite Tester')
            ->set('email', 'indefinite@example.com')
            ->set('identity_number', '13579')
            ->set('birth_date', '1995-01-01')
            ->set('photo_self', $photoSelf)
            ->set('photo_identity', $photoIdentity)
            ->call('saveRegistration')
            ->assertHasNoErrors();

        $registration = Registration::whereHas('user', function($q) {
            $q->where('email', 'indefinite@example.com');
        })->first();
        $this->assertEquals(5400000, $registration->total_price);

        // Check all 6 bills
        $bills = Bill::where('registration_id', $registration->id)->orderBy('due_date')->get();
        $this->assertCount(6, $bills);
        foreach ($bills as $bill) {
            $this->assertEquals(900000, $bill->amount);
        }
    }
}
