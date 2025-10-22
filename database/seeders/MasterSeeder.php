<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Location;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\Booking;
use App\Models\Stay;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Reminder;
use App\Models\Ticket;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 1) User admin dasar (opsional)
        $admin = User::firstOrCreate(
            ['email' => 'admin@kost.test'],
            ['name' => 'Admin Kost', 'password' => bcrypt('password')]
        );

        // 2) Room Types: Kost monthly + Homestay daily
        $kostType = RoomType::firstOrCreate(
            ['name'=>'Standard','billing_cycle'=>'monthly'],
            ['base_price'=>1000000]
        );
        $homestayType = RoomType::firstOrCreate(
            ['name'=>'Homestay','billing_cycle'=>'daily'],
            ['base_price'=>300000]
        );

        // 3) Locations
        $kostLoc = Location::firstOrCreate([
            'code'=>'KOST-A'
        ],[
            'name'=>'Kost A',
            'address'=>'Jl. Mawar No.1',
            'type'=>'kost',
            'default_room_quota'=>40,
            'wa_group_link'=>null,
        ]);

        $homeLoc = Location::firstOrCreate([
            'code'=>'HMS-1'
        ],[
            'name'=>'Homestay 1',
            'address'=>'Jl. Melati No.2',
            'type'=>'homestay',
            'default_room_quota'=>28,
            'wa_group_link'=>null,
        ]);

        // 4) Generate Rooms
        // Kost 40 kamar
        for($i=1;$i<=40;$i++){
            Room::firstOrCreate([
                'location_id'=>$kostLoc->id,
                'number' => 'A-'.str_pad($i,3,'0',STR_PAD_LEFT)
            ],[
                'room_type_id'=>$kostType->id,
                'status'=>'available',
                'floor'=>ceil($i/10),
                'amenities'=>['WiFi','Kamar Mandi Dalam']
            ]);
        }
        // Homestay 28 kamar
        for($i=1;$i<=28;$i++){
            Room::firstOrCreate([
                'location_id'=>$homeLoc->id,
                'number' => 'H-'.str_pad($i,3,'0',STR_PAD_LEFT)
            ],[
                'room_type_id'=>$homestayType->id,
                'status'=>'available',
                'floor'=>ceil($i/10),
                'amenities'=>['WiFi','AC','Air Panas']
            ]);
        }

        // 5) Tenants
        $tenants = Tenant::factory()->count(30)->create();

        // 6) Bookings
        // - 15 booking kost (monthly) di Kost A (acak kamar kosong)
        $kostRoomIds = Room::where('location_id',$kostLoc->id)->pluck('id')->toArray();
        $homeRoomIds = Room::where('location_id',$homeLoc->id)->pluck('id')->toArray();

        foreach(range(1,15) as $i){
            Booking::factory()->create([
                'tenant_id' => $tenants->random()->id,
                'room_id' => collect($kostRoomIds)->random(),
                'start_date' => now()->subDays(rand(1,25))->toDateString(),
                'end_date' => null,
                'status' => rand(0,1)?'confirmed':'pending',
                'rate' => 1200000, // kost bulanan flat
            ]);
        }
        // - 10 booking homestay (daily)
        foreach(range(1,10) as $i){
            $start = now()->addDays(rand(-5,5));
            Booking::factory()->create([
                'tenant_id' => $tenants->random()->id,
                'room_id' => collect($homeRoomIds)->random(),
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addDays(rand(1,4))->toDateString(),
                'status' => 'confirmed',
                'rate' => 350000, // homestay harian
            ]);
        }

        // 7) Konversi sebagian booking -> Stay (Check-in)
        $confirmedBookings = Booking::where('status','confirmed')->get();
        $activeStays = collect();
        foreach($confirmedBookings as $b){
            // tandai kamar occupied + buat stay
            $room = Room::find($b->room_id);
            if($room){
                $room->update(['status'=>'occupied']);
            }
            $stay = Stay::create([
                'tenant_id' => $b->tenant_id,
                'room_id'   => $b->room_id,
                'checkin_date' => $b->start_date,
                'checkout_date'=> null,
                'billing_cycle'=> optional($room->roomType)->billing_cycle ?? 'monthly',
                'rate'         => $b->rate,
                'status'       => 'active',
            ]);
            $activeStays->push($stay);
        }

        // 8) Akuntansi: siapkan akun penting
        $accCash   = Account::where('code','1110')->first(); // Bank
        $accAR     = Account::where('code','1120')->first(); // Piutang
        $accRev    = Account::where('code','4100')->first(); // Pendapatan Sewa

        // 9) Terbitkan Invoices (akrual) utk setiap stay aktif bulan ini
        $invoices = collect();
        foreach($activeStays as $stay){
            // issue today, due H+7 (daily/homestay bisa due di checkout, tapi disederhanakan)
            $issue = now()->toDateString();
            $due   = now()->addDays(7)->toDateString();

            $inv = Invoice::create([
                'number' => 'INV-'.now()->format('Ym').'-'.Str::padLeft($stay->id, 4, '0'),
                'tenant_id' => $stay->tenant_id,
                'stay_id'   => $stay->id,
                'issue_date'=> $issue,
                'due_date'  => $due,
                'subtotal'  => $stay->rate,
                'discount'  => 0,
                'tax'       => 0,
                'total'     => $stay->rate,
                'status'    => 'unpaid',
            ]);
            $invoices->push($inv);

            // Jurnal akrual: Dr Piutang, Cr Pendapatan
            $je = JournalEntry::create([
                'date' => now()->toDateString(),
                'ref'  => $inv->number,
                'memo' => 'Akrual pendapatan sewa',
            ]);
            JournalLine::create([
                'journal_entry_id'=>$je->id,
                'account_id'=>$accAR->id,
                'debit'=>$inv->total,'credit'=>0,
            ]);
            JournalLine::create([
                'journal_entry_id'=>$je->id,
                'account_id'=>$accRev->id,
                'debit'=>0,'credit'=>$inv->total,
            ]);
        }

        // 10) Payments (sebagian lunas, sebagian parsial)
        foreach($invoices as $inv){
            if(rand(0,1)){
                // bayar penuh
                $pay = Payment::create([
                    'invoice_id'=>$inv->id,
                    'paid_at'=>now()->toDateString(),
                    'amount'=>$inv->total,
                    'method'=>'transfer',
                    'reference'=>'TRX-'.Str::upper(Str::random(6)),
                ]);
                $inv->update(['status'=>'paid']);

                // jurnal kas: Dr Bank, Cr Piutang
                $je = JournalEntry::create([
                    'date'=>now()->toDateString(),
                    'ref'=>$pay->reference,
                    'memo'=>'Pembayaran invoice '.$inv->number,
                ]);
                JournalLine::create([
                    'journal_entry_id'=>$je->id,
                    'account_id'=>$accCash->id,
                    'debit'=>$pay->amount,'credit'=>0,
                ]);
                JournalLine::create([
                    'journal_entry_id'=>$je->id,
                    'account_id'=>$accAR->id,
                    'debit'=>0,'credit'=>$pay->amount,
                ]);
            } else {
                // parsial atau belum bayar: buat reminder H-3, H-1, H+1
                foreach([-3,-1,1] as $offset){
                    Reminder::firstOrCreate([
                        'invoice_id'=>$inv->id,
                        'remind_on'=>now()->addDays($offset)->toDateString(),
                        'channel'=>'wa',
                    ],[
                        'status'=>'pending',
                        'payload'=>"Pengingat: Invoice {$inv->number} jatuh tempo {$inv->due_date}",
                    ]);
                }
                // sesekali bayar parsial
                if(rand(0,1)){
                    $partial = (int) round($inv->total * 0.5);
                    $pay = Payment::create([
                        'invoice_id'=>$inv->id,
                        'paid_at'=>now()->toDateString(),
                        'amount'=>$partial,
                        'method'=>'cash',
                        'reference'=>'CASH-'.Str::upper(Str::random(5)),
                    ]);
                    $inv->update(['status'=>'partial']);

                    $je = JournalEntry::create([
                        'date'=>now()->toDateString(),
                        'ref'=>$pay->reference,
                        'memo'=>'Pembayaran parsial '.$inv->number,
                    ]);
                    JournalLine::create([
                        'journal_entry_id'=>$je->id,
                        'account_id'=>$accCash->id,
                        'debit'=>$pay->amount,'credit'=>0,
                    ]);
                    JournalLine::create([
                        'journal_entry_id'=>$je->id,
                        'account_id'=>$accAR->id,
                        'debit'=>0,'credit'=>$pay->amount,
                    ]);
                }
            }
        }

        // 11) Tickets (CS) contoh
        $someRooms = Room::inRandomOrder()->take(8)->get();
        foreach($someRooms as $room){
            Ticket::factory()->create([
                'tenant_id' => $tenants->random()->id,
                'room_id'   => $room->id,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
