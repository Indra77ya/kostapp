<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\ExpenseManager;
use App\Livewire\LocationManager;

class ValidationErrorNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_incomplete_expense_submission_has_validation_errors()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        Livewire::actingAs($owner)
            ->test(ExpenseManager::class)
            ->set('title', '')
            ->set('amount', null)
            ->call('save')
            ->assertHasErrors(['chart_of_account_id', 'title', 'amount']);
    }

    public function test_incomplete_location_submission_has_validation_errors()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        Livewire::actingAs($owner)
            ->test(LocationManager::class)
            ->call('saveLocation')
            ->assertHasErrors(['name']);
    }
}
