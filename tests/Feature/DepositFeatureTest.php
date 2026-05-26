<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Location;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\Deposit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DepositFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        $this->location = Location::create(['name' => 'Test Location']);
        $this->room = Room::create([
            'location_id' => $this->location->id,
            'room_number' => '101',
            'price_monthly' => 1000000,
            'status' => 'available'
        ]);

        PaymentMethod::firstOrCreate(['name' => 'Cash'], ['category' => 'Tunai', 'is_active' => true]);
        PaymentMethod::firstOrCreate(['name' => 'Saldo Deposit'], ['category' => 'Lainnya', 'is_active' => true]);
    }

    public function test_overpayment_creates_deposit_credit()
    {
        $this->actingAs($this->user);

        $tenantUser = User::factory()->create();
        $registration = Registration::create([
            'user_id' => $tenantUser->id,
            'location_id' => $this->location->id,
            'room_id' => $this->room->id,
            'registration_number' => 'REG-001',
            'registration_date' => Carbon::now(),
            'stay_start_date' => Carbon::now(),
            'total_price' => 1000000,
            'room_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);

        $bill = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-001',
            'description' => 'Test Bill',
            'amount' => 1000000,
            'due_date' => Carbon::now(),
            'status' => 'Belum Lunas'
        ]);

        $pm = PaymentMethod::where('name', 'Cash')->first();

        Livewire::test(\App\Livewire\PaymentManager::class)
            ->set('registration_id', $registration->id)
            ->set('bill_id', $bill->id)
            ->set('payment_method_id', $pm->id)
            ->set('amount', 1200000) // Overpayment 200k
            ->call('savePayment');

        $this->assertEquals(200000, $registration->deposit_balance);

        $deposit = Deposit::where('registration_id', $registration->id)->first();
        $this->assertEquals(200000, $deposit->amount);
        $this->assertEquals('credit', $deposit->type);
    }

    public function test_pay_with_deposit_decreases_balance()
    {
        $this->actingAs($this->user);

        $tenantUser = User::factory()->create();
        $registration = Registration::create([
            'user_id' => $tenantUser->id,
            'location_id' => $this->location->id,
            'room_id' => $this->room->id,
            'registration_number' => 'REG-001',
            'registration_date' => Carbon::now(),
            'stay_start_date' => Carbon::now(),
            'total_price' => 1000000,
            'room_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);

        // Add initial deposit
        Deposit::create([
            'registration_id' => $registration->id,
            'amount' => 500000,
            'type' => 'credit',
            'description' => 'Initial',
            'transaction_date' => Carbon::now()
        ]);

        $this->assertEquals(500000, $registration->fresh()->deposit_balance);

        $bill = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-001',
            'description' => 'Test Bill',
            'amount' => 1000000,
            'due_date' => Carbon::now(),
            'status' => 'Belum Lunas'
        ]);

        $pm = PaymentMethod::where('name', 'Saldo Deposit')->first();

        Livewire::test(\App\Livewire\PaymentManager::class)
            ->call('selectRegistration', $registration->id)
            ->call('openModal')
            ->set('bill_id', $bill->id)
            ->set('payment_method_id', $pm->id)
            ->set('amount', 300000)
            ->call('savePayment');

        $this->assertEquals(200000, $registration->fresh()->deposit_balance);

        $debit = Deposit::where('registration_id', $registration->id)->where('type', 'debit')->first();
        $this->assertNotNull($debit);
        $this->assertEquals(300000, $debit->amount);
    }

    public function test_checkout_deposit_handling()
    {
        $this->actingAs($this->user);

        $tenantUser = User::factory()->create();
        $registration = Registration::create([
            'user_id' => $tenantUser->id,
            'location_id' => $this->location->id,
            'room_id' => $this->room->id,
            'registration_number' => 'REG-001',
            'registration_date' => Carbon::now(),
            'stay_start_date' => Carbon::now(),
            'total_price' => 1000000,
            'room_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);

        Deposit::create([
            'registration_id' => $registration->id,
            'amount' => 500000,
            'type' => 'credit',
            'description' => 'Initial',
            'transaction_date' => Carbon::now()
        ]);

        Livewire::test(\App\Livewire\CheckOutManager::class)
            ->call('openModal', $registration->id)
            ->set('deposit_deduction', 100000)
            ->set('deposit_refund', 400000)
            ->call('processCheckOut');

        $this->assertEquals(0, $registration->fresh()->deposit_balance);
        $this->assertEquals('checked_out', $registration->fresh()->status);

        $deduction = Deposit::where('registration_id', $registration->id)
            ->where('description', 'like', 'Potongan%')
            ->first();
        $this->assertEquals(100000, $deduction->amount);

        $refund = Deposit::where('registration_id', $registration->id)
            ->where('description', 'like', 'Pengembalian%')
            ->first();
        $this->assertEquals(400000, $refund->amount);
    }
}
