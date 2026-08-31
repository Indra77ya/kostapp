<?php

namespace Tests\Feature;

use App\Livewire\AssetManager;
use App\Models\AccountMapping;
use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->owner = User::whereHas('roles', function ($q) {
            $q->where('name', 'owner');
        })->first();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function owner_can_access_assets_page()
    {
        $response = $this->actingAs($this->owner)->get(route('assets.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Aset');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function owner_can_create_and_delete_asset()
    {
        $location = Location::first();
        $room = Room::where('location_id', $location->id)->first();

        Livewire::actingAs($this->owner)
            ->test(AssetManager::class)
            ->call('openModal')
            ->set('code', 'AST-TEST-001')
            ->set('name', 'AC Sharp 1 PK')
            ->set('category', 'Elektronik')
            ->set('location_id', $location->id)
            ->set('room_id', $room ? $room->id : null)
            ->set('purchase_date', '2026-01-15')
            ->set('purchase_cost', 3500000)
            ->set('condition', 'Baik')
            ->set('status', 'Aktif')
            ->set('useful_life_months', 36)
            ->set('salvage_value', 500000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('assets', [
            'code' => 'AST-TEST-001',
            'name' => 'AC Sharp 1 PK',
            'purchase_cost' => 3500000,
        ]);

        $asset = Asset::where('code', 'AST-TEST-001')->first();

        Livewire::actingAs($this->owner)
            ->test(AssetManager::class)
            ->call('delete', $asset->id);

        $this->assertDatabaseMissing('assets', [
            'id' => $asset->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function processing_asset_depreciation_creates_journal_entry()
    {
        $asset = Asset::create([
            'code' => 'AST-DEP-001',
            'name' => 'Springbed Single',
            'category' => 'Meubel & Furnitur',
            'purchase_date' => '2026-01-01',
            'purchase_cost' => 3600000,
            'condition' => 'Baik',
            'status' => 'Aktif',
            'useful_life_months' => 36,
            'salvage_value' => 0,
        ]);

        $this->assertEquals(100000, $asset->monthly_depreciation);

        Livewire::actingAs($this->owner)
            ->test(AssetManager::class)
            ->call('openDepreciationModal', $asset->id)
            ->set('depreciation_period_date', '2026-02-01')
            ->set('depreciation_amount', 100000)
            ->set('depreciation_notes', 'Penyusutan Feb 2026')
            ->call('processDepreciation')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('asset_depreciations', [
            'asset_id' => $asset->id,
            'depreciation_amount' => 100000,
        ]);

        $depreciation = AssetDepreciation::where('asset_id', $asset->id)->first();
        $this->assertNotNull($depreciation->journal_entry_id);

        $journal = JournalEntry::find($depreciation->journal_entry_id);
        $this->assertNotNull($journal);
        $this->assertEquals(AssetDepreciation::class, $journal->reference_type);
        $this->assertEquals($depreciation->id, $journal->reference_id);

        // Check book value after 1 month depreciation
        $asset->refresh();
        $this->assertEquals(3500000, $asset->book_value);
    }
}
