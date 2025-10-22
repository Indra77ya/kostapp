<?php

namespace App\Http\Controllers;

use App\Models\Stay;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;

class StayController extends Controller
{
    public function index()
    {
        $stays = Stay::with('tenant','room.location','room.roomType')->where('status','active')->paginate(20);
        return view('stays.index', compact('stays'));
    }

    public function store(Request $request)
    {
        // Dari booking terkonfirmasi (id di-request)
        $request->validate(['booking_id'=>'required|exists:bookings,id']);

        $booking = Booking::with('room.roomType')->findOrFail($request->booking_id);

        // set room occupied
        $booking->room->update(['status'=>'occupied']);

        $stay = Stay::create([
            'tenant_id'    => $booking->tenant_id,
            'room_id'      => $booking->room_id,
            'checkin_date' => $booking->start_date,
            'checkout_date'=> null,
            'billing_cycle'=> $booking->room->roomType->billing_cycle ?? 'monthly',
            'rate'         => $booking->rate,
            'status'       => 'active',
        ]);

        return redirect()->route('stays.show', $stay)->with('ok','Check-in berhasil, stay aktif.');
    }

    public function show(Stay $stay)
    {
        $stay->load('tenant','room.location','room.roomType','invoices');
        return view('stays.show', compact('stay'));
    }

    public function checkout(Stay $stay)
    {
        $stay->update([
            'status' => 'ended',
            'checkout_date' => now()->toDateString(),
        ]);
        $stay->room()->update(['status'=>'available']);

        return redirect()->route('stays.index')->with('ok','Checkout selesai, kamar kembali tersedia.');
    }
}
