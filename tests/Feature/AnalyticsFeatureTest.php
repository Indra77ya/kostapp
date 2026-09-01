<?php

namespace Tests\Feature;

use App\Livewire\AnalyticsManager;
use App\Livewire\DashboardStats;
use App\Models\Bill;
use App\Models\Expense;
use App\Models\Location;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnalyticsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $tenant;
    protected $location;
    protected $room;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'tenant']);

        $this->owner = User::factory()->create();
        $this->owner->assignRole('owner');

        $this->tenant = User::factory()->create();
        $this->tenant->assignRole('tenant');

        $this->location = Location::create([
            'name' => 'Kost Utama Analytics',
            'address' => 'Jl. Analytics No 1',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'room_number' => '101',
            'price_monthly' => 1500000,
            'status' => 'occupied',
        ]);

        // Unoccupied room
        Room::create([
            'location_id' => $this->location->id,
            'room_number' => '102',
            'price_monthly' => 1500000,
            'status' => 'available',
        ]);
    }

    public function test_owner_can_access_analytics_page()
    {
        $response = $this->actingAs($this->owner)->get('/analytics');
        $response->assertStatus(200);
        $response->assertSee('Analisis & Laporan Eksekutif');
    }

    public function test_tenant_cannot_access_analytics_page()
    {
        $response = $this->actingAs($this->tenant)->get('/analytics');
        $response->assertStatus(403);
    }

    public function test_analytics_manager_calculates_occupancy_revenue_and_outstanding_correctly()
    {
        $registration = Registration::create([
            'registration_number' => 'REG-TEST-001',
            'registration_date' => now()->startOfMonth()->format('Y-m-d'),
            'stay_start_date' => now()->startOfMonth()->format('Y-m-d'),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 1500000,
            'total_price' => 1500000,
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'location_id' => $this->location->id,
            'identity_type' => 'KTP',
            'identity_number' => '1234567890123456',
            'gender' => 'Laki-laki',
            'birth_date' => '2000-01-01',
            'status' => 'active',
        ]);

        $pm = \App\Models\PaymentMethod::create([
            'name' => 'Transfer Bank',
            'category' => 'Bank',
            'account_number' => '1234567890',
            'account_name' => 'Owner',
            'is_active' => true,
        ]);

        // Realized Payment
        Payment::create([
            'registration_id' => $registration->id,
            'payment_method_id' => $pm->id,
            'payment_number' => 'PAY-TEST-001',
            'amount' => 1500000,
            'payment_date' => now()->format('Y-m-d'),
            'status' => 'diterima',
            'type' => 'bill',
        ]);

        // Outstanding Bill
        Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-M-001',
            'description' => 'Sewa Bulan Ini',
            'amount' => 1500000,
            'paid_amount' => 500000,
            'due_date' => now()->format('Y-m-d'),
            'status' => 'Belum Lunas',
        ]);

        Livewire::actingAs($this->owner)
            ->test(AnalyticsManager::class)
            ->assertSet('location_id', 'all')
            ->assertViewHas('totalRooms', 2)
            ->assertViewHas('occupiedRooms', 1)
            ->assertViewHas('occupancyRate', 50.0)
            ->assertViewHas('revenueRealized', 1500000)
            ->assertViewHas('totalOutstanding', 1000000);
    }

    public function test_dashboard_stats_component_renders_correctly()
    {
        Livewire::actingAs($this->owner)
            ->test(DashboardStats::class)
            ->assertViewHas('totalRooms', 2)
            ->assertViewHas('occupiedRooms', 1)
            ->assertViewHas('occupancyRate', 50.0);
    }
}
