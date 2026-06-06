<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use App\Livewire\TenantPaymentManager;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantPaymentDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'tenant']);
    }

    public function test_tenant_can_delete_pending_payment()
    {
        Storage::fake('public');

        $location = Location::create(['name' => 'Test Location', 'address' => 'Test Address']);
        $room = Room::create(['room_number' => '101', 'location_id' => $location->id, 'type' => 'Standard', 'price_monthly' => 1000000, 'status' => 'occupied']);
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $reg = Registration::create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'location_id' => $location->id,
            'registration_number' => 'REG-123',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);

        $pm = PaymentMethod::create(['name' => 'Cash', 'category' => 'Manual', 'is_active' => true]);

        $file = UploadedFile::fake()->image('receipt.jpg');
        $path = $file->store('payments', 'public');

        $payment = Payment::create([
            'registration_id' => $reg->id,
            'payment_method_id' => $pm->id,
            'payment_number' => 'PAY-001',
            'payment_date' => now(),
            'amount' => 1000000,
            'status' => 'Menunggu Konfirmasi',
            'proof_of_payment' => $path
        ]);

        Storage::disk('public')->assertExists($path);

        Livewire::actingAs($tenant)
            ->test(TenantPaymentManager::class)
            ->assertSee('PAY-001')
            ->call('deletePayment', $payment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_tenant_cannot_delete_approved_payment()
    {
        $location = Location::create(['name' => 'Test Location', 'address' => 'Test Address']);
        $room = Room::create(['room_number' => '101', 'location_id' => $location->id, 'type' => 'Standard', 'price_monthly' => 1000000, 'status' => 'occupied']);
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $reg = Registration::create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'location_id' => $location->id,
            'registration_number' => 'REG-123',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);

        $pm = PaymentMethod::create(['name' => 'Cash', 'category' => 'Manual', 'is_active' => true]);

        $payment = Payment::create([
            'registration_id' => $reg->id,
            'payment_method_id' => $pm->id,
            'payment_number' => 'PAY-001',
            'payment_date' => now(),
            'amount' => 1000000,
            'status' => 'Lunas'
        ]);

        Livewire::actingAs($tenant)
            ->test(TenantPaymentManager::class)
            ->call('deletePayment', $payment->id)
            ->assertDispatched('notify', message: 'Hanya pembayaran dengan status "Menunggu Konfirmasi" yang dapat dihapus.');

        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }
}
