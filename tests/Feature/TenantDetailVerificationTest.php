<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\Room;
use App\Models\Location;
use App\Models\User;
use App\Livewire\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Carbon\Carbon;

class TenantDetailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);
    }

    public function test_detail_modal_shows_newly_added_fields()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Test Location']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'room_type' => 'Reguler',
            'floor' => '1',
            'price_monthly' => 1500000,
            'status' => 'occupied'
        ]);

        $tenant = User::factory()->create(['name' => 'Indra Nur Utomo']);
        $tenant->assignRole('tenant');

        $registration = Registration::create([
            'user_id' => $tenant->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-123',
            'registration_date' => Carbon::parse('2026-05-01'),
            'stay_start_date' => Carbon::parse('2026-05-28'),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'is_open_ended' => false,
            'room_price' => 1500000,
            'total_price' => 1500000,
            'status' => 'active',
            'identity_type' => 'KTP',
            'identity_number' => '33365498712',
            'gender' => 'Laki-laki',
            'birth_date' => '2001-07-20'
        ]);

        Livewire::actingAs($admin)
            ->test(TenantManager::class)
            ->call('viewDetails', $tenant->id)
            ->assertSee('No. Registrasi')
            ->assertSee('REG-123')
            ->assertSee('Status')
            ->assertSee('AKTIF')
            ->assertSee('Tanggal Daftar')
            ->assertSee('01 May 2026')
            ->assertSee('Mulai Menginap')
            ->assertSee('28 May 2026')
            ->assertSee('Jenis Sewa')
            ->assertSee('Bulanan')
            ->assertSee('Durasi Sewa')
            ->assertSee('1')
            ->assertSee('Total Harga Pendaftaran')
            ->assertSee('Rp 1.500.000')
            ->assertSee('Cetak Data Diri');
    }
}
