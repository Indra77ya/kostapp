<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TrustProxiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_https_header_forces_https_urls()
    {
        Role::create(['name' => 'owner']);
        $user = User::factory()->create();
        $user->assignRole('owner');

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Host' => 'tinderlike-unquickly-shayne.ngrok-free.dev',
            ])
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('https://tinderlike-unquickly-shayne.ngrok-free.dev/assets/tabler/css/tabler.min.css');
    }
}
