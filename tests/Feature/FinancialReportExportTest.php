<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinancialReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected User $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'developer']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('owner');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->tenant = User::factory()->create();
        $this->tenant->assignRole('tenant');

        // Seed basic accounts
        ChartOfAccount::create([
            'code' => '1-1000',
            'name' => 'Kas Utama',
            'type' => 'asset',
            'sub_type' => 'Aset Lancar',
            'normal_balance' => 'debit',
            'category' => 'Kas & Setara Kas',
            'is_active' => true,
        ]);

        ChartOfAccount::create([
            'code' => '4-1000',
            'name' => 'Pendapatan Sewa Kamar',
            'type' => 'revenue',
            'sub_type' => 'Pendapatan Operasional',
            'normal_balance' => 'credit',
            'category' => 'Pendapatan Utama',
            'is_active' => true,
        ]);

        ChartOfAccount::create([
            'code' => '5-1000',
            'name' => 'Beban Listrik & Air',
            'type' => 'expense',
            'sub_type' => 'Beban Operasional',
            'normal_balance' => 'debit',
            'category' => 'Beban Utilitas',
            'is_active' => true,
        ]);
    }

    public function test_owner_can_export_all_financial_reports(): void
    {
        $types = ['profit-loss', 'trial-balance', 'ledger', 'journal', 'cash-flow', 'expenses'];

        foreach ($types as $type) {
            $response = $this->actingAs($this->owner)
                ->get(route('accounting.export', [
                    'type' => $type,
                    'date_start' => now()->startOfMonth()->format('Y-m-d'),
                    'date_end' => now()->endOfMonth()->format('Y-m-d'),
                ]));

            $response->assertStatus(200);
            $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
    }

    public function test_admin_and_tenant_cannot_export_financial_reports(): void
    {
        // Admin forbidden
        $responseAdmin = $this->actingAs($this->admin)
            ->get(route('accounting.export', ['type' => 'profit-loss']));
        $responseAdmin->assertStatus(403);

        // Tenant forbidden
        $responseTenant = $this->actingAs($this->tenant)
            ->get(route('accounting.export', ['type' => 'profit-loss']));
        $responseTenant->assertStatus(403);
    }

    public function test_export_invalid_type_returns_404(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('accounting.export', ['type' => 'invalid-report']));

        $response->assertStatus(404);
    }
}
