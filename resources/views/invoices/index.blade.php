<h1>Daftar Invoice</h1>

<ul>
@foreach ($invoices as $invoice)
    <li>
        <a href="{{ route('invoices.show', $invoice) }}">
            {{ $invoice->number }} - {{ $invoice->tenant->full_name ?? 'Tanpa Tenant' }} - {{ $invoice->status }}
        </a>
    </li>
@endforeach
</ul>

{{ $invoices->links() }}
