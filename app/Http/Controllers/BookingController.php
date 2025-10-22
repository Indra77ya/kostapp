<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Room;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with('tenant','room.location','room.roomType')->latest()->paginate(20);
        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        // tampilkan form (pilih tenant, room, tanggal, rate, mode)
        return view('bookings.create');
    }

    public function store(StoreBookingRequest $request, RoomAvailabilityService $avail)
    {
        $data = $request->validated();

        // cek ketersediaan
        if (! $avail->isAvailable($data['room_id'], $data['start_date'], $data['end_date'] ?? null)) {
            return back()->withErrors(['room_id' => 'Kamar tidak tersedia pada tanggal tersebut'])->withInput();
        }

        // homestay wajib end_date, kost tidak
        if ($data['mode'] === 'daily' && empty($data['end_date'])) {
            return back()->withErrors(['end_date' => 'Homestay harian wajib isi tanggal selesai'])->withInput();
        }

        $booking = Booking::create([
            'tenant_id'  => $data['tenant_id'],
            'room_id'    => $data['room_id'],
            'start_date' => $data['start_date'],
            'end_date'   => $data['mode']==='daily' ? $data['end_date'] : null,
            'status'     => 'pending',
            'rate'       => $data['rate'],
        ]);

        return redirect()->route('bookings.show', $booking)->with('ok','Booking dibuat (pending).');
    }

    public function show(Booking $booking)
    {
        $booking->load('tenant','room.location','room.roomType');
        return view('bookings.show', compact('booking'));
    }

    public function confirm(Booking $booking)
    {
        $booking->update(['status'=>'confirmed']);
        return back()->with('ok','Booking dikonfirmasi.');
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return redirect()->route('bookings.index')->with('ok','Booking dihapus.');
    }
}
