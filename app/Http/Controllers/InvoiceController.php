<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Rule;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Registration $registration)
    {
        $registration->load(['user', 'location', 'room', 'emergencyContacts']);

        $rules = Rule::where('is_active', true)
            ->where(function ($query) use ($registration) {
                $query->whereNull('location_id')
                    ->orWhere('location_id', $registration->location_id);
            })
            ->orderBy('category')
            ->get()
            ->groupBy('category');

        return view('invoices.print', compact('registration', 'rules'));
    }
}
