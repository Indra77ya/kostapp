<?php

namespace Tests\Feature;

use App\Livewire\FundTransferManager;
use App\Models\ChartOfAccount;
use App\Models\FundTransfer;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FundTransferTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $tenant;
    protected $cashAccount;
    protected $bankAccount;
    protected $adminFeeAccount;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'tenant']);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('owner');

        $this->tenant = User::factory()->create();
        $this->tenant->assignRole('tenant');

        $this->cashAccount = ChartOfAccount::create([
            'code' => '1-1000',
            'name' => 'Kas Tunai',
            'type' => 'asset',
            'sub_type' => 'Kas & Bank',
            'normal_balance' => 'debit',
            'is_active' => true,
        ]);

        $this->bankAccount = ChartOfAccount::create([
            'code' => '1-1001',
            'name' => 'Bank BCA',
            'type' => 'asset',
            'sub_type' => 'Kas & Bank',
            'normal_balance' => 'debit',
            'is_active' => true,
        ]);

        $this->adminFeeAccount = ChartOfAccount::create([
            'code' => '5-7000',
            'name' => 'Beban Administrasi Bank',
            'type' => 'expense',
            'sub_type' => 'Beban Administrasi & Umum',
            'normal_balance' => 'debit',
            'is_active' => true,
        ]);
    }

    public function test_transfers_page_accessible_by_owner()
    {
        $response = $this->actingAs($this->owner)->get(route('accounting.transfers'));
        $response->assertStatus(200);
        $response->assertSee('Transfer Dana');
    }

    public function test_transfers_page_forbidden_for_tenant()
    {
        $response = $this->actingAs($this->tenant)->get(route('accounting.transfers'));
        $response->assertStatus(403);
    }

    public function test_create_transfer_without_admin_fee()
    {
        Livewire::actingAs($this->owner)
            ->test(FundTransferManager::class)
            ->call('openModal')
            ->set('transfer_date', '2026-08-27')
            ->set('from_account_id', $this->cashAccount->id)
            ->set('to_account_id', $this->bankAccount->id)
            ->set('amount', 500000)
            ->set('admin_fee', 0)
            ->set('notes', 'Setor tunai ke bank')
            ->call('save');

        $this->assertDatabaseHas('fund_transfers', [
            'from_account_id' => $this->cashAccount->id,
            'to_account_id' => $this->bankAccount->id,
            'amount' => 500000,
            'admin_fee' => 0,
            'notes' => 'Setor tunai ke bank',
        ]);

        $transfer = FundTransfer::first();
        $this->assertNotNull($transfer);

        // Verify Journal Entry created automatically
        $journal = JournalEntry::where('reference_type', FundTransfer::class)
            ->where('reference_id', $transfer->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertCount(2, $journal->items);

        // Check Debit Bank BCA 500,000
        $this->assertDatabaseHas('journal_entry_items', [
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $this->bankAccount->id,
            'debit' => 500000,
            'credit' => 0,
        ]);

        // Check Credit Kas Tunai 500,000
        $this->assertDatabaseHas('journal_entry_items', [
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $this->cashAccount->id,
            'debit' => 0,
            'credit' => 500000,
        ]);
    }

    public function test_create_transfer_with_admin_fee()
    {
        Livewire::actingAs($this->owner)
            ->test(FundTransferManager::class)
            ->call('openModal')
            ->set('transfer_date', '2026-08-27')
            ->set('from_account_id', $this->bankAccount->id)
            ->set('to_account_id', $this->cashAccount->id)
            ->set('amount', 1000000)
            ->set('admin_fee', 6500)
            ->set('admin_fee_account_id', $this->adminFeeAccount->id)
            ->set('notes', 'Tarik tunai dari bank')
            ->call('save');

        $transfer = FundTransfer::first();
        $this->assertNotNull($transfer);
        $this->assertEquals(6500, $transfer->admin_fee);

        // Verify Journal Entry with admin fee
        $journal = JournalEntry::where('reference_type', FundTransfer::class)
            ->where('reference_id', $transfer->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertCount(3, $journal->items);

        // Debit Kas Tunai 1,000,000
        $this->assertDatabaseHas('journal_entry_items', [
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $this->cashAccount->id,
            'debit' => 1000000,
            'credit' => 0,
        ]);

        // Debit Beban Admin 6,500
        $this->assertDatabaseHas('journal_entry_items', [
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $this->adminFeeAccount->id,
            'debit' => 6500,
            'credit' => 0,
        ]);

        // Credit Bank BCA 1,006,500
        $this->assertDatabaseHas('journal_entry_items', [
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $this->bankAccount->id,
            'debit' => 0,
            'credit' => 1006500,
        ]);
    }

    public function test_cannot_transfer_to_same_account()
    {
        Livewire::actingAs($this->owner)
            ->test(FundTransferManager::class)
            ->call('openModal')
            ->set('from_account_id', $this->cashAccount->id)
            ->set('to_account_id', $this->cashAccount->id)
            ->set('amount', 100000)
            ->call('save')
            ->assertHasErrors(['to_account_id' => 'different']);
    }
}
