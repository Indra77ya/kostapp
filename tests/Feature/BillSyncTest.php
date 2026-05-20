<?php

namespace Tests\Feature;

use App\Livewire\RegistrationManager;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use App\Models\Registration;
use App\Models\Bill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BillSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);
        Storage::fake('public');
    }

    public function test_bills_are_synced_when_registration_is_updated()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost A', 'address' => 'Jl. A']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'type' => 'Standard',
            'price_monthly' => 1000000
        ]);

        $tenant = User::factory()->create(['name' => 'John Doe']);

        // 1. Initial registration with fixed discount
        $registration = Registration::create([
            'user_id' => $tenant->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 2,
            'room_price' => 1000000,
            'total_price' => 1900000,
            'discount_type' => 'fixed',
            'discount_value' => 100000,
            'discount_duration' => 1,
            'identity_type' => 'KTP',
            'identity_number' => '123',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);

        // Manually trigger initial bill generation (usually done via component but for test setup we can call it or simulate)
        // Actually, let's use the Livewire component to save it from the start to be sure.

        $photoSelf = UploadedFile::fake()->image('self.jpg');
        $photoIdentity = UploadedFile::fake()->image('ktp.jpg');

        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room->id)
            ->set('stay_start_date', now()->format('Y-m-d'))
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('identity_number', '123456789')
            ->set('birth_date', '1995-01-01')
            ->set('photo_self', $photoSelf)
            ->set('photo_identity', $photoIdentity)
            ->set('duration_value', 2)
            ->set('discount_type', 'fixed')
            ->set('discount_value', 100000)
            ->set('discount_duration', 1)
            ->call('saveRegistration');

        $reg = Registration::whereHas('user', function($q) { $q->where('email', 'john@example.com'); })->first();
        $this->assertEquals(2, $reg->bills()->count());
        $this->assertEquals(100000, $reg->bills()->orderBy('due_date')->first()->discount);
        $this->assertEquals(900000, $reg->bills()->orderBy('due_date')->first()->amount);

        // 2. Update registration to percent discount
        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->call('openModal', $reg->id)
            ->set('discount_type', 'percent')
            ->set('discount_value', 10) // 10% of 1,000,000 = 100,000. Wait, let's change value too.
            ->set('discount_value', 20) // 20% of 1,000,000 = 200,000
            ->set('discount_duration', 2)
            ->call('saveRegistration');

        $reg->refresh();
        $bills = $reg->bills()->orderBy('due_date')->get();
        $this->assertEquals(2, $bills->count());

        // Bill 1: 1,000,000 - 20% (200,000) = 800,000
        $this->assertEquals(200000, $bills[0]->discount);
        $this->assertEquals(800000, $bills[0]->amount);

        // Bill 2: 1,000,000 - 20% (200,000) = 800,000
        $this->assertEquals(200000, $bills[1]->discount);
        $this->assertEquals(800000, $bills[1]->amount);
    }

    public function test_paid_bills_are_not_updated()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost A', 'address' => 'Jl. A']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'type' => 'Standard',
            'price_monthly' => 1000000
        ]);

        $photoSelf = UploadedFile::fake()->image('self.jpg');
        $photoIdentity = UploadedFile::fake()->image('ktp.jpg');

        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room->id)
            ->set('stay_start_date', now()->format('Y-m-d'))
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('identity_number', '123456789')
            ->set('birth_date', '1995-01-01')
            ->set('photo_self', $photoSelf)
            ->set('photo_identity', $photoIdentity)
            ->set('duration_value', 2)
            ->call('saveRegistration');

        $reg = Registration::whereHas('user', function($q) { $q->where('email', 'jane@example.com'); })->first();
        $bill1 = $reg->bills()->orderBy('due_date')->first();
        $bill1->update(['status' => 'Lunas', 'paid_amount' => 1000000]);

        // Update price to 1,200,000
        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->call('openModal', $reg->id)
            ->set('room_price', 1200000)
            ->call('saveRegistration');

        $bill1->refresh();
        $this->assertEquals(1000000, $bill1->amount, 'Paid bill should keep original amount');

        $bill2 = $reg->bills()->orderBy('due_date', 'desc')->first();
        $this->assertEquals(1200000, $bill2->amount, 'Unpaid bill should update to new price');
    }
}
