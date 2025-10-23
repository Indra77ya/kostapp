<h2>Neraca per {{ $asOf }}</h2>

@foreach($balances as $type => $accounts)
    <h3>{{ strtoupper($type) }}</h3>
    <table border="1" cellpadding="5">
        <tr><th>Kode</th><th>Nama Akun</th><th>Saldo</th></tr>
        @foreach($accounts as $a)
            <tr>
                <td>{{ $a->code }}</td>
                <td>{{ $a->name }}</td>
                <td align="right">{{ number_format($a->balance,0,',','.') }}</td>
            </tr>
        @endforeach
    </table>
@endforeach
