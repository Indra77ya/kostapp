<?php

namespace Tests\Feature;

use App\Livewire\AccountMappingManager;
use App\Models\AccountMapping;
use App\Models\ChartOfAccount;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\AccountingService;
use Database\Seeders\ChartOfAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountMappingTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'developer']);
        Role::firstOrCreate(['name' => 'tenant']);

        $this->seed(ChartOfAccountSeeder::class);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('owner');

        $this->tenant = User::factory()->create();
        $this->tenant->assignRole('tenant');
    }

    public function test_access_restriction_to_account_mapping_page(): void
    {
        $this->actingAs($this->tenant)
            ->get(route('accounting.account-mapping'))
            ->assertStatus(403);

        $this->actingAs($this->owner)
            ->get(route('accounting.account-mapping'))
            ->assertStatus(200);
    }

    public function test_livewire_can_update_account_mapping(): void
    {
        $customCoa = ChartOfAccount::create([
            'code' => '4-9999',
            'name' => 'Pendapatan Custom Sewa',
            'type' => 'revenue',
            'normal_balance' => 'credit',
            'category' => 'Pendapatan Usaha',
        ]);

        Livewire::actingAs($this->owner)
            ->test(AccountMappingManager::class)
            ->call('updateMapping', 'rental_revenue', $customCoa->id);

        $this->assertDatabaseHas('account_mappings', [
            'key' => 'rental_revenue',
            'chart_of_account_id' => $customCoa->id,
        ]);
    }

    public function test_accounting_service_uses_custom_mapped_account_for_payment_journal(): void
    {
        $customCoa = ChartOfAccount::create([
            'code' => '4-8888',
            'name' => 'Pendapatan Sewa Custom Test',
            'type' => 'revenue',
            'normal_balance' => 'credit',
            'category' => 'Pendapatan Usaha',
        ]);

        AccountMapping::seedDefaults();
        AccountMapping::where('key', 'rental_revenue')->update([
            'chart_of_account_id' => $customCoa->id,
        ]);

        $pm = PaymentMethod::create([
            'name' => 'Transfer Bank BCA',
            'category' => 'Bank',
            'account_number' => '12345678',
            'account_name' => 'Owner Kost',
            'is_active' => true,
        ]);

        $location = \App\Models\Location::create(['name' => 'Lokasi Test']);
        $room = \App\Models\Room::create(['room_number' => '101', 'location_id' => $location->id, 'price_monthly' => 1500000, 'status' => 'occupied']);
        $registration = \App\Models\Registration::create([
            'registration_number' => 'REG-TEST-001',
            'registration_date' => now()->toDateString(),
            'user_id' => $this->tenant->id,
            'room_id' => $room->id,
            'location_id' => $location->id,
            'room_price' => 1500000,
            'total_price' => 1500000,
            'stay_start_date' => now()->toDateString(),
            'stay_end_date' => now()->addMonth()->toDateString(),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'identity_type' => 'KTP',
            'identity_number' => '123456789',
            'gender' => 'Laki-laki',
            'birth_date' => '1995-01-01',
            'status' => 'aktif',
        ]);

        $payment = Payment::create([
            'registration_id' => $registration->id,
            'payment_number' => 'PAY-TEST-001',
            'amount' => 1500000,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $pm->id,
            'status' => 'Dikonfirmasi',
        ]);

        $journal = AccountingService::recordPaymentJournal($payment);

        $this->assertNotNull($journal);
        $this->assertDatabaseHas('journal_entry_items', [
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $customCoa->id,
            'credit' => 1500000,
        ]);
    }
}
