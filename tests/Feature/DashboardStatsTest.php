<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Models\Room;
use App\Models\Registration;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Models\ChartOfAccount;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_admin_dashboard_kpis_are_calculated_correctly()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Lokasi Utama']);

        $room1 = Room::create(['location_id' => $location->id, 'room_number' => '101', 'price_monthly' => 1000000, 'status' => 'occupied']);
        $room2 = Room::create(['location_id' => $location->id, 'room_number' => '102', 'price_monthly' => 1000000, 'status' => 'available']);

        $tenantUser = User::factory()->create();
        $tenantUser->assignRole('tenant');

        $registration = Registration::create([
            'registration_number' => 'REG-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'user_id' => $tenantUser->id,
            'location_id' => $location->id,
            'room_id' => $room1->id,
            'status' => 'active',
            'duration_type' => 'monthly',
            'duration_value' => 12,
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
        ]);

        $bill = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-TEST-001',
            'description' => 'Sewa Bulan 1',
            'amount' => 1000000,
            'paid_amount' => 0,
            'due_date' => now()->addDays(5),
            'status' => 'Belum Lunas',
        ]);

        $coa = ChartOfAccount::create([
            'code' => '1-1001',
            'name' => 'Kas Bank',
            'type' => 'Aset',
            'normal_balance' => 'debit',
            'is_active' => true,
        ]);

        $paymentMethod = PaymentMethod::create([
            'name' => 'Transfer Bank',
            'category' => 'bank',
            'chart_of_account_id' => $coa->id,
            'is_active' => true,
        ]);

        Payment::create([
            'payment_number' => 'PAY-001',
            'registration_id' => $registration->id,
            'bill_id' => $bill->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 500000,
            'payment_date' => now(),
            'status' => 'Menunggu Konfirmasi',
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\DashboardStats::class)
            ->assertSet('totalRooms', 2)
            ->assertSet('availableRooms', 1)
            ->assertSet('occupiedRooms', 1)
            ->assertSet('occupancyRate', 50.0)
            ->assertSet('activeTenantsCount', 1)
            ->assertSet('outstandingBillsCount', 1)
            ->assertSet('outstandingBillsAmount', 1000000)
            ->assertSet('pendingConfirmationsCount', 1)
            ->assertSee('2 Total Kamar')
            ->assertSee('Okupansi 50%')
            ->assertSee('Pintasan Cepat')
            ->assertSee('Peta Status Kamar Real-time')
            ->assertSee('Konfirmasi Pembayaran Pending')
            ->assertSee('Tagihan Belum Lunas Terdekat');
    }

    public function test_admin_can_approve_payment_directly_from_dashboard()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Lokasi Utama']);
        $room = Room::create(['location_id' => $location->id, 'room_number' => '101', 'price_monthly' => 1000000, 'status' => 'occupied']);

        $tenantUser = User::factory()->create();
        $tenantUser->assignRole('tenant');

        $registration = Registration::create([
            'registration_number' => 'REG-002',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'user_id' => $tenantUser->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'status' => 'active',
            'duration_type' => 'monthly',
            'duration_value' => 12,
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
        ]);

        $bill = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-TEST-002',
            'description' => 'Sewa Bulan 1',
            'amount' => 1000000,
            'paid_amount' => 0,
            'due_date' => now()->addDays(5),
            'status' => 'Belum Lunas',
        ]);

        $coa = ChartOfAccount::create([
            'code' => '1-1002',
            'name' => 'Kas Bank Utama',
            'type' => 'Aset',
            'normal_balance' => 'debit',
            'is_active' => true,
        ]);

        $paymentMethod = PaymentMethod::create([
            'name' => 'Transfer Bank',
            'category' => 'bank',
            'chart_of_account_id' => $coa->id,
            'is_active' => true,
        ]);

        $payment = Payment::create([
            'payment_number' => 'PAY-002',
            'registration_id' => $registration->id,
            'bill_id' => $bill->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 1000000,
            'payment_date' => now(),
            'status' => 'Menunggu Konfirmasi',
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\DashboardStats::class)
            ->call('approvePayment', $payment->id)
            ->assertSet('pendingConfirmationsCount', 0);

        $this->assertEquals('Lunas', $payment->fresh()->status);
        $this->assertEquals('Lunas', $bill->fresh()->status);
        $this->assertEquals(1000000, $bill->fresh()->paid_amount);
    }

    public function test_tenant_dashboard_displays_tenant_specific_stay_and_bills()
    {
        $location = Location::create(['name' => 'Lokasi Melati']);
        $room = Room::create(['location_id' => $location->id, 'room_number' => '201', 'price_monthly' => 1500000, 'status' => 'occupied']);

        $tenantUser = User::factory()->create();
        $tenantUser->assignRole('tenant');

        $registration = Registration::create([
            'registration_number' => 'REG-003',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'user_id' => $tenantUser->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'status' => 'active',
            'duration_type' => 'monthly',
            'duration_value' => 6,
            'room_price' => 1500000,
            'total_price' => 1500000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
        ]);

        $bill = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-TENANT-001',
            'description' => 'Sewa Kamar 201 Bulan Ini',
            'amount' => 1500000,
            'paid_amount' => 500000,
            'due_date' => now()->addDays(3),
            'status' => 'Cicilan',
        ]);

        Livewire::actingAs($tenantUser)
            ->test(\App\Livewire\DashboardStats::class)
            ->assertSet('tenantTotalOutstanding', 1000000)
            ->assertSet('tenantUnpaidBillsCount', 1)
            ->assertSee('Kamar 201')
            ->assertSee('Lokasi Melati')
            ->assertSee('Ringkasan Tagihan Aktif Anda')
            ->assertSee('BILL-TENANT-001');
    }

    public function test_checked_out_tenant_bills_are_excluded_from_dashboard()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Lokasi Ceria']);
        $room = Room::create(['location_id' => $location->id, 'room_number' => '102', 'price_monthly' => 1800000, 'status' => 'available']);

        $tenantUser = User::factory()->create();
        $tenantUser->assignRole('tenant');

        // Checked out registration
        $registration = Registration::create([
            'registration_number' => 'REG-CHECKOUT-001',
            'registration_date' => now()->subMonths(3),
            'stay_start_date' => now()->subMonths(3),
            'user_id' => $tenantUser->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'status' => 'checked_out',
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 1800000,
            'total_price' => 1800000,
            'identity_type' => 'KTP',
            'identity_number' => '12345678',
            'gender' => 'Laki-laki',
            'birth_date' => '1992-01-01',
        ]);

        Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-CHECKOUT-001',
            'description' => 'Tagihan Sisa Checked Out',
            'amount' => 1800000,
            'paid_amount' => 0,
            'due_date' => now()->addDays(5),
            'status' => 'Belum Lunas',
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\DashboardStats::class)
            ->assertSet('outstandingBillsCount', 0)
            ->assertSet('outstandingBillsAmount', 0)
            ->assertDontSee('BILL-CHECKOUT-001')
            ->assertSee('Semua tagihan tergolong lunas.');
    }

    public function test_dashboard_filters_out_far_future_bills_and_limits_to_one_bill_per_tenant()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Lokasi Indah']);
        $room = Room::create(['location_id' => $location->id, 'room_number' => '103', 'price_monthly' => 1500000, 'status' => 'occupied']);

        $tenantUser = User::factory()->create(['name' => 'Budi Santoso']);
        $tenantUser->assignRole('tenant');

        $registration = Registration::create([
            'registration_number' => 'REG-004',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'user_id' => $tenantUser->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'status' => 'active',
            'duration_type' => 'monthly',
            'duration_value' => 12,
            'room_price' => 1500000,
            'total_price' => 18000000,
            'identity_type' => 'KTP',
            'identity_number' => '99999',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
        ]);

        // Bill due in 5 days (Should be shown in table)
        $nearBill1 = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-NEAR-001',
            'description' => 'Sewa Bulan 1 (Terdekat)',
            'amount' => 1500000,
            'paid_amount' => 0,
            'due_date' => now()->addDays(5),
            'status' => 'Belum Lunas',
        ]);

        // Bill due in 20 days (Within 30 days, but second bill for same tenant -> Table picks earliest only)
        $nearBill2 = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-NEAR-002',
            'description' => 'Sewa Bulan 2 (Segera)',
            'amount' => 1500000,
            'paid_amount' => 0,
            'due_date' => now()->addDays(20),
            'status' => 'Belum Lunas',
        ]);

        // Bill due in 100 days (Far future -> Excluded from 30d window)
        $farBill = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-FAR-001',
            'description' => 'Sewa Bulan 4 (Masa Depan)',
            'amount' => 1500000,
            'paid_amount' => 0,
            'due_date' => now()->addDays(100),
            'status' => 'Belum Lunas',
        ]);

        $test = Livewire::actingAs($owner)
            ->test(\App\Livewire\DashboardStats::class)
            ->assertSet('outstandingBillsCount', 2) // Only 2 bills within 30 days
            ->assertSet('outstandingBillsAmount', 3000000)
            ->assertSee('Budi Santoso');

        $upcomingBills = $test->viewData('upcomingBills');
        $this->assertCount(1, $upcomingBills);
        $this->assertEquals($nearBill1->id, $upcomingBills->first()->id);
    }

    public function test_admin_can_open_and_close_payment_detail_modal_on_dashboard()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Lokasi Mawar']);
        $room = Room::create(['location_id' => $location->id, 'room_number' => '301', 'price_monthly' => 1200000, 'status' => 'occupied']);

        $tenantUser = User::factory()->create(['name' => 'Siti Nurhaliza']);
        $tenantUser->assignRole('tenant');

        $registration = Registration::create([
            'registration_number' => 'REG-DETAIL-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'user_id' => $tenantUser->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'status' => 'active',
            'duration_type' => 'monthly',
            'duration_value' => 6,
            'room_price' => 1200000,
            'total_price' => 7200000,
            'identity_type' => 'KTP',
            'identity_number' => '77777',
            'gender' => 'Perempuan',
            'birth_date' => '1995-05-05',
        ]);

        $coa = ChartOfAccount::create([
            'code' => '1-1003',
            'name' => 'Kas Bank BCA',
            'type' => 'Aset',
            'normal_balance' => 'debit',
            'is_active' => true,
        ]);

        $paymentMethod = PaymentMethod::create([
            'name' => 'Transfer Bank BCA',
            'category' => 'bank',
            'chart_of_account_id' => $coa->id,
            'is_active' => true,
        ]);

        $payment = Payment::create([
            'payment_number' => 'PAY-DETAIL-001',
            'registration_id' => $registration->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 1200000,
            'payment_date' => now(),
            'status' => 'Menunggu Konfirmasi',
            'sender_bank_name' => 'Bank Mandiri',
            'sender_account_number' => '1234567890',
            'sender_account_name' => 'Siti Nurhaliza',
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\DashboardStats::class)
            ->call('showPaymentDetail', $payment->id)
            ->assertSet('isDetailModalOpen', true)
            ->assertSet('selectedPaymentId', $payment->id)
            ->assertSee('Detail Konfirmasi Pembayaran')
            ->assertSee('PAY-DETAIL-001')
            ->assertSee('Bank Mandiri')
            ->assertSee('1234567890')
            ->call('closeDetailModal')
            ->assertSet('isDetailModalOpen', false)
            ->assertSet('selectedPaymentId', null);
    }

    public function test_admin_can_filter_dashboard_stats_by_location()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $locationA = Location::create(['name' => 'Kost Ceria A']);
        $locationB = Location::create(['name' => 'Kost Ceria B']);

        // Rooms
        $roomA = Room::create(['location_id' => $locationA->id, 'room_number' => 'A101', 'price_monthly' => 1000000, 'status' => 'occupied']);
        $roomB = Room::create(['location_id' => $locationB->id, 'room_number' => 'B201', 'price_monthly' => 2000000, 'status' => 'occupied']);

        // Tenants
        $tenantA = User::factory()->create(['name' => 'Penghuni A']);
        $tenantA->assignRole('tenant');

        $tenantB = User::factory()->create(['name' => 'Penghuni B']);
        $tenantB->assignRole('tenant');

        // Registrations
        $regA = Registration::create([
            'registration_number' => 'REG-A',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'user_id' => $tenantA->id,
            'location_id' => $locationA->id,
            'room_id' => $roomA->id,
            'status' => 'active',
            'duration_type' => 'monthly',
            'duration_value' => 12,
            'room_price' => 1000000,
            'total_price' => 12000000,
            'identity_type' => 'KTP',
            'identity_number' => '111',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
        ]);

        $regB = Registration::create([
            'registration_number' => 'REG-B',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'user_id' => $tenantB->id,
            'location_id' => $locationB->id,
            'room_id' => $roomB->id,
            'status' => 'active',
            'duration_type' => 'monthly',
            'duration_value' => 12,
            'room_price' => 2000000,
            'total_price' => 24000000,
            'identity_type' => 'KTP',
            'identity_number' => '222',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
        ]);

        // Unpaid Bills
        Bill::create([
            'registration_id' => $regA->id,
            'bill_number' => 'BILL-A',
            'description' => 'Tagihan A',
            'amount' => 1000000,
            'paid_amount' => 0,
            'due_date' => now()->addDays(5),
            'status' => 'Belum Lunas',
        ]);

        Bill::create([
            'registration_id' => $regB->id,
            'bill_number' => 'BILL-B',
            'description' => 'Tagihan B',
            'amount' => 2000000,
            'paid_amount' => 0,
            'due_date' => now()->addDays(5),
            'status' => 'Belum Lunas',
        ]);

        // Unfiltered check (All locations)
        Livewire::actingAs($owner)
            ->test(\App\Livewire\DashboardStats::class)
            ->assertSet('totalRooms', 2)
            ->assertSet('activeTenantsCount', 2)
            ->assertSet('outstandingBillsCount', 2)
            ->assertSet('outstandingBillsAmount', 3000000)
            // Filter by Location A
            ->set('selectedLocationId', $locationA->id)
            ->assertSet('totalRooms', 1)
            ->assertSet('activeTenantsCount', 1)
            ->assertSet('outstandingBillsCount', 1)
            ->assertSet('outstandingBillsAmount', 1000000)
            ->assertSee('Penghuni A')
            ->assertDontSee('Penghuni B')
            // Filter by Location B
            ->set('selectedLocationId', $locationB->id)
            ->assertSet('totalRooms', 1)
            ->assertSet('activeTenantsCount', 1)
            ->assertSet('outstandingBillsCount', 1)
            ->assertSet('outstandingBillsAmount', 2000000)
            ->assertSee('Penghuni B')
            ->assertDontSee('Penghuni A');
    }

    public function test_admin_can_open_occupied_room_detail_modal_on_dashboard()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Lokasi Anggrek']);
        $room = Room::create(['location_id' => $location->id, 'room_number' => 'K-501', 'price_monthly' => 1500000, 'status' => 'occupied']);

        $tenantUser = User::factory()->create(['name' => 'Dewi Lestari', 'phone_number' => '08123456789']);
        $tenantUser->assignRole('tenant');

        $registration = Registration::create([
            'registration_number' => 'REG-ROOM-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'user_id' => $tenantUser->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'status' => 'active',
            'duration_type' => 'monthly',
            'duration_value' => 12,
            'room_price' => 1500000,
            'total_price' => 18000000,
            'identity_type' => 'KTP',
            'identity_number' => '88888',
            'gender' => 'Perempuan',
            'birth_date' => '1993-03-03',
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\DashboardStats::class)
            ->assertSee('Peta Status Kamar Real-time')
            ->assertSee('K-501')
            ->call('showOccupiedRoomDetail', $room->id)
            ->assertSet('isOccupiedRoomModalOpen', true)
            ->assertSet('selectedOccupiedRoomId', $room->id)
            ->assertSee('Informasi Kamar K-501')
            ->assertSee('Dewi Lestari')
            ->assertSee('08123456789')
            ->call('closeOccupiedRoomModal')
            ->assertSet('isOccupiedRoomModalOpen', false)
            ->assertSet('selectedOccupiedRoomId', null);
    }
}
