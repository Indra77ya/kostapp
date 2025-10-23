<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('tenant','room')->latest()->paginate(20);
        return view('tickets.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tenant_id'=>['nullable','exists:tenants,id'],
            'room_id'  =>['nullable','exists:rooms,id'],
            'subject'  =>['required','string','max:200'],
            'description'=>['nullable','string'],
            'priority' =>['required','in:low,medium,high'],
        ]);
        $data['status'] = 'open';
        $ticket = Ticket::create($data);
        return back()->with('ok','Ticket dibuat.');
    }

    public function update(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status'=>['required','in:open,in_progress,resolved,closed']
        ]);
        $ticket->update($data);
        return back()->with('ok','Ticket diupdate.');
    }
}
