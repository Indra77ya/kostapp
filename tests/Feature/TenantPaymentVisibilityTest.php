<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\PaymentMethod;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class TenantPaymentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('tenant');
    }

    public function test_payment_destination_details_have_correct_visibility_classes()
    {
        $user = User::factory()->create([
            'name' => 'Test Tenant',
            'email' => 'tenant@example.com'
        ]);
        $user->assignRole('tenant');

        $location = Location::create(['name' => 'Test Location', 'address' => 'Test Address']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'room_type' => 'Standard',
            'price_daily' => 100000,
            'price_weekly' => 600000,
            'price_monthly' => 2000000,
            'price_yearly' => 20000000,
            'status' => 'available'
        ]);

        $registration = Registration::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'location_id' => $location->id,
            'registration_number' => 'REG-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 2000000,
            'total_price' => 2000000,
            'status' => 'active',
            'identity_type' => 'KTP',
            'identity_number' => '1234567890',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01'
        ]);

        $pm = PaymentMethod::create([
            'name' => 'BCA',
            'category' => 'Transfer Bank',
            'account_number' => '12345678',
            'account_name' => 'Owner Kost',
            'instructions' => '<p>Please transfer exactly</p>',
            'is_active' => true
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\TenantPaymentManager::class)
            ->set('isModalOpen', true)
            ->set('payment_method_id', $pm->id)
            ->assertSee('text-dark')
            ->assertSee('fw-bold')
            ->assertSeeHtml('class="col-4 text-dark">Nama Bank/App:</div>')
            ->assertSeeHtml('class="col-8 fw-bold text-dark">BCA</div>')
            ->assertSeeHtml('class="text-dark fw-bold mb-1">Instruksi:</div>')
            ->assertSeeHtml('class="instructions-content text-dark">');
    }
}
