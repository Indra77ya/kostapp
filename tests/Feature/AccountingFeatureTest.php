<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Bill;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_chart_of_accounts_seeder_populates_default_accounts()
    {
        $this->assertDatabaseHas('chart_of_accounts', ['code' => '1-1000', 'name' => 'Kas Utama & Bank']);
        $this->assertDatabaseHas('chart_of_accounts', ['code' => '2-1000', 'name' => 'Utang Deposit Tenant (Uang Jaminan)']);
        $this->assertDatabaseHas('chart_of_accounts', ['code' => '4-1000', 'name' => 'Pendapatan Sewa Kamar']);
        $this->assertDatabaseHas('chart_of_accounts', ['code' => '5-1000', 'name' => 'Beban Listrik, Air & Utility']);
    }

    public function test_recording_expense_creates_expense_record_and_journal_entry()
    {
        $owner = User::role('owner')->first();
        $account = ChartOfAccount::where('code', '5-1000')->first();
        $paymentMethod = PaymentMethod::first();

        $this->actingAs($owner);

        \Livewire::test(\App\Livewire\ExpenseManager::class)
            ->set('expense_number', 'EXP-20260601-0001')
            ->set('expense_date', '2026-06-01')
            ->set('chart_of_account_id', $account->id)
            ->set('payment_method_id', $paymentMethod->id)
            ->set('amount', 350000)
            ->set('title', 'Bayar Listrik PLN')
            ->set('notes', 'Token PLN Mei')
            ->call('save');

        $this->assertDatabaseHas('expenses', [
            'expense_number' => 'EXP-20260601-0001',
            'amount' => 350000,
            'title' => 'Bayar Listrik PLN',
        ]);

        $expense = Expense::where('expense_number', 'EXP-20260601-0001')->first();

        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => Expense::class,
            'reference_id' => $expense->id,
        ]);

        $journal = JournalEntry::where('reference_id', $expense->id)->first();
        $this->assertEquals(350000, $journal->items->where('chart_of_account_id', $account->id)->first()->debit);
    }

    public function test_payment_approval_generates_journal_entry()
    {
        $owner = User::role('owner')->first();
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $room = Room::first();
        $paymentMethod = PaymentMethod::first();

        $registration = Registration::create([
            'registration_number' => 'REG-TEST-001',
            'registration_date' => now()->toDateString(),
            'user_id' => $tenant->id,
            'identity_type' => 'KTP',
            'identity_number' => '1234567890123456',
            'gender' => 'Laki-laki',
            'birth_place' => 'Jakarta',
            'birth_date' => '2000-01-01',
            'job' => 'Karyawan',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_relation' => 'Orang Tua',
            'emergency_contact_phone' => '08123456789',
            'room_id' => $room->id,
            'location_id' => $room->location_id,
            'stay_start_date' => now()->toDateString(),
            'stay_end_date' => now()->addMonth()->toDateString(),
            'duration_type' => 'monthly',
            'room_price' => 1500000,
            'total_price' => 1500000,
            'status' => 'active',
        ]);

        $bill = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-TEST-001',
            'description' => 'Tagihan Sewa Kamar',
            'amount' => 1500000,
            'due_date' => now()->toDateString(),
            'status' => 'Belum Lunas',
        ]);

        $payment = Payment::create([
            'registration_id' => $registration->id,
            'bill_id' => $bill->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_number' => 'PAY-TEST-001',
            'payment_date' => now()->toDateString(),
            'amount' => 1500000,
            'status' => 'Menunggu Konfirmasi',
        ]);

        $this->actingAs($owner);

        \Livewire::test(\App\Livewire\PaymentConfirmationManager::class)
            ->call('approve', $payment->id);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'Lunas',
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => Payment::class,
            'reference_id' => $payment->id,
        ]);
    }

    public function test_trial_balance_and_profit_loss_rendering()
    {
        $owner = User::role('owner')->first();
        $this->actingAs($owner);

        \Livewire::test(\App\Livewire\TrialBalanceManager::class)
            ->assertStatus(200)
            ->assertSee('Neraca Saldo');

        \Livewire::test(\App\Livewire\ProfitLossManager::class)
            ->assertStatus(200)
            ->assertSee('Laporan Laba Rugi');
    }
}
