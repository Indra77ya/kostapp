<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Location;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RegistrationDepositTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Storage::fake('public');
    }

    public function test_can_save_registration_with_initial_deposit()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        $location = Location::create(['name' => 'Loc 1']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => 'A1',
            'price_monthly' => 1000000,
            'status' => 'available'
        ]);

        Livewire::test(\App\Livewire\RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room->id)
            ->set('registration_date', '2024-01-01')
            ->set('stay_start_date', '2024-01-01')
            ->set('name', 'Tenant A')
            ->set('email', 'tenant@a.com')
            ->set('phone_number', '08123456789')
            ->set('identity_number', '123')
            ->set('gender', 'Laki-laki')
            ->set('birth_date', '1990-01-01')
            ->set('initial_deposit', 500000)
            ->set('photo_self', UploadedFile::fake()->image('self.jpg'))
            ->set('photo_identity', UploadedFile::fake()->image('id.jpg'))
            ->call('saveRegistration');

        $this->assertDatabaseHas('registrations', [
            'initial_deposit' => 500000
        ]);

        $reg = Registration::whereHas('user', function($q) { $q->where('email', 'tenant@a.com'); })->first();
        $this->assertNotNull($reg);

        $this->assertDatabaseHas('bills', [
            'registration_id' => $reg->id,
            'amount' => 500000,
            'description' => 'Deposit Awal (Uang Jaminan)'
        ]);
    }
}
