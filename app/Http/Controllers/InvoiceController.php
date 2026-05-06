<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Registration $registration)
    {
        $registration->load(['user', 'location', 'room']);
        return view('invoices.print', compact('registration'));
    }
}
