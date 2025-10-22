<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;

class RoomAvailabilityService
{
    /**
     * Cek apakah kamar tersedia di rentang tanggal (untuk booking homestay harian).
     * Untuk kost bulanan, cukup cek status room = available dan tidak ada stay aktif.
     */
    public function isAvailable(int $roomId, string $startDate, ?string $endDate = null): bool
    {
        $start = Carbon::parse($startDate);
        $end   = $endDate ? Carbon::parse($endDate) : $start->copy()->addMonth(); // default 1 bulan

        $hasBookingConflict = Booking::where('room_id', $roomId)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start)->where('end_date', '>=', $end);
                  });
            })->exists();

        if ($hasBookingConflict) return false;

        // Selain booking, cek room status (occupied/maintenance)
        $room = Room::find($roomId);
        return $room && $room->status === 'available';
    }

    /**
     * Ambil daftar kamar kosong (filter opsional lokasi/tipe).
     */
    public function listAvailable(?int $locationId = null, ?int $roomTypeId = null)
    {
        return Room::query()
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->when($roomTypeId, fn($q) => $q->where('room_type_id', $roomTypeId))
            ->where('status', 'available')
            ->with('location', 'roomType')
            ->orderBy('location_id')
            ->orderBy('number')
            ->get();
    }
}
