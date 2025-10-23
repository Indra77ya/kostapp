<h2>Laporan Ketersediaan Kamar</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Lokasi</th>
        <th>Kosong</th>
        <th>Terisi</th>
        <th>Maintenance</th>
    </tr>

    @forelse($byLocation as $d)
        <tr>
            <td>{{ optional($d->location)->name ?? '—' }}</td>
            <td>{{ $d->available }}</td>
            <td>{{ $d->occupied }}</td>
            <td>{{ $d->maintenance }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="4" align="center">Belum ada data kamar.</td>
        </tr>
    @endforelse
</table>
