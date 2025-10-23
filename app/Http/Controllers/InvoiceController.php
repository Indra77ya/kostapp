<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    // Menampilkan semua invoice
    public function index()
    {
        $invoices = Invoice::with('tenant', 'stay.room.location')
            ->orderBy('issue_date', 'desc')
            ->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    // Menampilkan detail satu invoice
    public function show(Invoice $invoice)
    {
        $invoice->load('tenant', 'stay.room.location', 'payments');

        return view('invoices.show', compact('invoice'));
    }

    // (Opsional) Membuat invoice manual
    public function create()
    {
        return view('invoices.create');
    }

    // (Opsional) Simpan invoice manual ke database
    public function store(Request $request)
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'stay_id'   => ['nullable', 'exists:stays,id'],
            'issue_date'=> ['required', 'date'],
            'due_date'  => ['required', 'date'],
            'total'     => ['required', 'numeric'],
        ]);

        $invoice = Invoice::create($data + [
            'subtotal' => $data['total'],
            'discount' => 0,
            'tax' => 0,
            'status' => 'unpaid',
            'number' => 'INV-' . now()->format('YmdHis'),
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('ok', 'Invoice berhasil dibuat.');
    }
}
