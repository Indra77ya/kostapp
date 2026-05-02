<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'tenant']);
    }

    public function test_profile_page_is_accessible()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'New Name',
            'phone_number' => '+628123456789',
            'bio' => 'New bio content',
            'address' => 'New Address',
            'bank_info' => 'BCA 123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $user->refresh();

        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('+628123456789', $user->phone_number);
        $this->assertEquals('New bio content', $user->bio);
        $this->assertEquals('New Address', $user->address);
        $this->assertEquals('BCA 123', $user->bank_info);
    }

    public function test_tenant_cannot_update_owner_fields()
    {
        $user = User::factory()->create();
        $user->assignRole('tenant');

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'New Name',
            'phone_number' => '+628123456789',
            'address' => 'Should be ignored',
            'bank_info' => 'Should be ignored',
        ]);

        $user->refresh();

        $this->assertEquals('New Name', $user->name);
        $this->assertNull($user->address);
        $this->assertNull($user->bank_info);
    }

    public function test_avatar_can_be_uploaded()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'avatar' => $file,
        ]);

        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_avatar_is_not_deleted_when_updating_other_fields()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar' => 'avatars/old-avatar.jpg'
        ]);
        Storage::disk('public')->put('avatars/old-avatar.jpg', 'content');

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Updated Name',
        ]);

        $user->refresh();

        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('avatars/old-avatar.jpg', $user->avatar);
        Storage::disk('public')->assertExists('avatars/old-avatar.jpg');
    }

    public function test_password_can_be_updated()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_password_update_requires_correct_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password');
        $this->assertFalse(Hash::check('new-password', $user->refresh()->password));
    }
}
