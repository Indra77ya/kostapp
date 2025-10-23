<h1>Detail Invoice {{ $invoice->number }}</h1>

<p><strong>Tenant:</strong> {{ $invoice->tenant->full_name ?? '-' }}</p>
<p><strong>Tanggal Terbit:</strong> {{ $invoice->issue_date }}</p>
<p><strong>Jatuh Tempo:</strong> {{ $invoice->due_date }}</p>
<p><strong>Total:</strong> Rp{{ number_format($invoice->total) }}</p>
<p><strong>Status:</strong> {{ strtoupper($invoice->status) }}</p>

@if($invoice->payments->count())
    <h3>Pembayaran</h3>
    <ul>
        @foreach($invoice->payments as $p)
            <li>{{ $p->method }} - {{ number_format($p->amount) }} ({{ $p->paid_at }})</li>
        @endforeach
    </ul>
@endif
