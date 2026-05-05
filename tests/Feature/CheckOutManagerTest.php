<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\CheckOutManager;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckOutManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);
    }

    public function test_can_process_check_out()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Test', 'address' => 'Test']);
        $room = Room::create([
            'room_number' => '101',
            'location_id' => $location->id,
            'type' => 'Standard',
            'price' => 1000000,
            'status' => 'occupied'
        ]);

        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $registration = Registration::create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'location_id' => $location->id,
            'registration_number' => 'REG-123',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'room_price' => 1000000,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);

        Livewire::actingAs($admin)
            ->test(CheckOutManager::class)
            ->call('openModal', $registration->id)
            ->set('check_out_date', '2026-05-10')
            ->set('check_out_notes', 'Checking out.')
            ->call('processCheckOut')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'status' => 'checked_out',
            'check_out_date' => '2026-05-10',
            'check_out_notes' => 'Checking out.',
        ]);

        $this->assertEquals('available', $room->refresh()->status);
    }
}
