<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
use App\Models\Room;
use App\Models\Registration;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class VerificationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Location
        $location = Location::create([
            'name' => 'Kost Kebon Jeruk',
            'address' => 'Jl. Kebon Jeruk No. 123',
        ]);

        // 2. Create Rooms
        $room1 = Room::create([
            'location_id' => $location->id,
            'room_number' => 'VERIF-01',
            'price_monthly' => 2000000,
            'status' => 'occupied',
        ]);

        // 3. Create Tenant User
        $tenantUser = User::create([
            'name' => 'Andi Tenant',
            'email' => 'andi@tenant.com',
            'password' => Hash::make('password'),
            'password_plain' => 'password',
        ]);
        $tenantUser->assignRole('tenant');

        // 4. Create Registration (Open-ended)
        $registration = Registration::create([
            'user_id' => $tenantUser->id,
            'location_id' => $location->id,
            'room_id' => $room1->id,
            'registration_number' => 'REG-VERIF-001',
            'registration_date' => Carbon::now()->subMonths(2),
            'stay_start_date' => Carbon::now()->subMonths(2),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'is_open_ended' => true,
            'room_price' => 2000000,
            'status' => 'active',
            'total_price' => 24000000, // Placeholder
            'identity_type' => 'KTP',
            'identity_number' => '1234567890',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
        ]);

        // Sync initial bills
        $registration->syncBills();

        // 5. Create Payment Method
        $pm = PaymentMethod::create([
            'name' => 'Transfer BCA',
            'category' => 'Bank',
            'account_number' => '123456789',
            'account_name' => 'Kost Admin',
            'is_active' => true,
        ]);

        // 6. Record an Overpayment on the first bill
        $firstBill = $registration->bills()->orderBy('due_date', 'asc')->first();
        if ($firstBill) {
            Payment::create([
                'registration_id' => $registration->id,
                'bill_id' => $firstBill->id,
                'payment_method_id' => $pm->id,
                'payment_number' => 'PAY-VERIF-001',
                'payment_date' => Carbon::now()->subMonths(2),
                'amount' => 2500000, // Overpayment (Bill is 2,000,000)
                'status' => 'Lunas',
            ]);

            $firstBill->paid_amount = 2500000;
            $firstBill->status = 'Lunas';
            $firstBill->save();
        }
    }
}
