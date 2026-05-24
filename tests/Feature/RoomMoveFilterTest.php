<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Registration;
use App\Models\Room;
use App\Models\RoomMove;
use App\Models\User;
use App\Livewire\RoomMoveManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoomMoveFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function createRegistration(array $data)
    {
        return Registration::create(array_merge([
            'registration_number' => 'REG-' . uniqid(),
            'registration_date' => now(),
            'stay_start_date' => now(),
            'status' => 'active',
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 1000,
            'total_price' => 1000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
        ], $data));
    }

    /** @test */
    public function can_filter_by_location_old_or_new()
    {
        $loc1 = Location::create(['name' => 'Location A']);
        $loc2 = Location::create(['name' => 'Location B']);

        $room1 = Room::create(['room_number' => '101', 'location_id' => $loc1->id, 'status' => 'occupied', 'price_monthly' => 1000]);
        $room2 = Room::create(['room_number' => '201', 'location_id' => $loc2->id, 'status' => 'available', 'price_monthly' => 1000]);
        $room3 = Room::create(['room_number' => '102', 'location_id' => $loc1->id, 'status' => 'available', 'price_monthly' => 1000]);
        $room4 = Room::create(['room_number' => '202', 'location_id' => $loc2->id, 'status' => 'occupied', 'price_monthly' => 1000]);

        $user1 = User::factory()->create(['name' => 'User 1']);
        $reg1 = $this->createRegistration(['user_id' => $user1->id, 'room_id' => $room3->id, 'location_id' => $loc1->id]);
        RoomMove::create(['registration_id' => $reg1->id, 'user_id' => $user1->id, 'old_room_id' => $room1->id, 'new_room_id' => $room3->id, 'move_date' => now()]);

        $user2 = User::factory()->create(['name' => 'User 2']);
        $reg2 = $this->createRegistration(['user_id' => $user2->id, 'room_id' => $room2->id, 'location_id' => $loc2->id]);
        RoomMove::create(['registration_id' => $reg2->id, 'user_id' => $user2->id, 'old_room_id' => $room1->id, 'new_room_id' => $room2->id, 'move_date' => now()]);

        $user3 = User::factory()->create(['name' => 'User 3']);
        $reg3 = $this->createRegistration(['user_id' => $user3->id, 'room_id' => $room2->id, 'location_id' => $loc2->id]);
        RoomMove::create(['registration_id' => $reg3->id, 'user_id' => $user3->id, 'old_room_id' => $room4->id, 'new_room_id' => $room2->id, 'move_date' => now()]);

        // Filter Loc 1: should see User 1 (both in loc1) and User 2 (old room was in loc 1)
        Livewire::test(RoomMoveManager::class)
            ->set('filterLocationId', $loc1->id)
            ->assertSee('User 1')
            ->assertSee('User 2')
            ->assertDontSee('User 3');

        // Filter Loc 2: should see User 2 (new room is in loc 2) and User 3 (both in loc2)
        Livewire::test(RoomMoveManager::class)
            ->set('filterLocationId', $loc2->id)
            ->assertDontSee('User 1')
            ->assertSee('User 2')
            ->assertSee('User 3');
    }

    /** @test */
    public function can_filter_by_duration_type()
    {
        $loc = Location::create(['name' => 'Loc']);
        $roomOld = Room::create(['room_number' => 'OLD', 'location_id' => $loc->id, 'status' => 'available', 'price_monthly' => 1000]);
        $roomNew = Room::create(['room_number' => 'NEW', 'location_id' => $loc->id, 'status' => 'occupied', 'price_monthly' => 1000]);

        $user1 = User::factory()->create(['name' => 'Daily User']);
        $reg1 = $this->createRegistration([
            'user_id' => $user1->id,
            'room_id' => $roomNew->id,
            'location_id' => $loc->id,
            'duration_type' => 'daily',
        ]);
        RoomMove::create(['registration_id' => $reg1->id, 'user_id' => $user1->id, 'old_room_id' => $roomOld->id, 'new_room_id' => $roomNew->id, 'move_date' => now()]);

        $user2 = User::factory()->create(['name' => 'Monthly User']);
        $reg2 = $this->createRegistration([
            'user_id' => $user2->id,
            'room_id' => $roomNew->id,
            'location_id' => $loc->id,
            'duration_type' => 'monthly',
        ]);
        RoomMove::create(['registration_id' => $reg2->id, 'user_id' => $user2->id, 'old_room_id' => $roomOld->id, 'new_room_id' => $roomNew->id, 'move_date' => now()]);

        Livewire::test(RoomMoveManager::class)
            ->set('filterDurationType', 'daily')
            ->assertSee('Daily User')
            ->assertDontSee('Monthly User');

        Livewire::test(RoomMoveManager::class)
            ->set('filterDurationType', 'monthly')
            ->assertSee('Monthly User')
            ->assertDontSee('Daily User');
    }

    /** @test */
    public function can_sort_by_name()
    {
        $loc = Location::create(['name' => 'Loc']);
        $roomOld = Room::create(['room_number' => 'OLD', 'location_id' => $loc->id, 'status' => 'available', 'price_monthly' => 1000]);
        $roomNew = Room::create(['room_number' => 'NEW', 'location_id' => $loc->id, 'status' => 'occupied', 'price_monthly' => 1000]);

        $userB = User::factory()->create(['name' => 'Budi']);
        $regB = $this->createRegistration(['user_id' => $userB->id, 'room_id' => $roomNew->id, 'location_id' => $loc->id]);
        RoomMove::create(['registration_id' => $regB->id, 'user_id' => $userB->id, 'old_room_id' => $roomOld->id, 'new_room_id' => $roomNew->id, 'move_date' => now()]);

        $userA = User::factory()->create(['name' => 'Andi']);
        $regA = $this->createRegistration(['user_id' => $userA->id, 'room_id' => $roomNew->id, 'location_id' => $loc->id]);
        RoomMove::create(['registration_id' => $regA->id, 'user_id' => $userA->id, 'old_room_id' => $roomOld->id, 'new_room_id' => $roomNew->id, 'move_date' => now()]);

        Livewire::test(RoomMoveManager::class)
            ->set('sort', 'name_asc')
            ->assertSeeInOrder(['Andi', 'Budi']);

        Livewire::test(RoomMoveManager::class)
            ->set('sort', 'name_desc')
            ->assertSeeInOrder(['Budi', 'Andi']);
    }
}
