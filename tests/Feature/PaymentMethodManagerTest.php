<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\PaymentMethodManager;
use Spatie\Permission\Models\Role;

class PaymentMethodManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (Role::count() === 0) {
            Role::create(['name' => 'owner']);
            Role::create(['name' => 'developer']);
            Role::create(['name' => 'admin']);
        }
    }

    public function test_payment_method_manager_is_accessible_by_owner()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $this->actingAs($owner)
            ->get(route('payment-methods.index'))
            ->assertStatus(200);
    }

    public function test_payment_method_manager_is_not_accessible_by_admin()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('payment-methods.index'))
            ->assertStatus(403);
    }

    public function test_can_create_payment_method_with_logo()
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $file = UploadedFile::fake()->image('bca.png');

        Livewire::actingAs($owner)
            ->test(PaymentMethodManager::class)
            ->set('name', 'Transfer BCA')
            ->set('category', 'Bank')
            ->set('account_number', '1234567890')
            ->set('account_name', 'John Doe')
            ->set('logo', $file)
            ->call('savePaymentMethod')
            ->assertHasNoErrors();

        $pm = PaymentMethod::first();
        $this->assertEquals('Transfer BCA', $pm->name);
        $this->assertNotNull($pm->logo);
        Storage::disk('public')->assertExists($pm->logo);
    }

    public function test_can_update_payment_method_and_replace_logo()
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $oldFile = UploadedFile::fake()->image('old.png');
        $oldPath = $oldFile->store('payment_methods', 'public');

        $pm = PaymentMethod::create([
            'name' => 'Lama',
            'category' => 'Bank',
            'logo' => $oldPath
        ]);

        $newFile = UploadedFile::fake()->image('new.png');

        Livewire::actingAs($owner)
            ->test(PaymentMethodManager::class)
            ->call('openModal', $pm->id)
            ->set('name', 'Baru')
            ->set('logo', $newFile)
            ->call('savePaymentMethod')
            ->assertHasNoErrors();

        $pm->refresh();
        $this->assertEquals('Baru', $pm->name);
        $this->assertNotEquals($oldPath, $pm->logo);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($pm->logo);
    }

    public function test_can_toggle_payment_method_status()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $pm = PaymentMethod::create([
            'name' => 'Aktif',
            'category' => 'Bank',
            'is_active' => true
        ]);

        Livewire::actingAs($owner)
            ->test(PaymentMethodManager::class)
            ->call('toggleStatus', $pm->id);

        $this->assertDatabaseHas('payment_methods', [
            'id' => $pm->id,
            'is_active' => false
        ]);
    }

    public function test_can_delete_payment_method_and_its_logo()
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $file = UploadedFile::fake()->image('logo.png');
        $path = $file->store('payment_methods', 'public');

        $pm = PaymentMethod::create([
            'name' => 'Hapus',
            'category' => 'Bank',
            'logo' => $path
        ]);

        Livewire::actingAs($owner)
            ->test(PaymentMethodManager::class)
            ->call('deletePaymentMethod', $pm->id);

        $this->assertDatabaseMissing('payment_methods', ['id' => $pm->id]);
        Storage::disk('public')->assertMissing($path);
    }
}
