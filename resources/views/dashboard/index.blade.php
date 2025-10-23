<x-app-layout>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">
        Dashboard
        @if($locationId)
          — {{ optional($locations->firstWhere('id',$locationId))->name }}
        @else
          — Semua Lokasi
        @endif
      </h1>

      <form method="GET" action="{{ route('dashboard') }}" class="flex gap-2">
        <select name="location_id" class="border rounded-lg px-3 py-2">
          <option value="">Semua Lokasi</option>
          @foreach($locations as $loc)
            <option value="{{ $loc->id }}" @selected($locationId==$loc->id)>{{ $loc->name }}</option>
          @endforeach
        </select>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Terapkan</button>
      </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <x-kpi title="Kamar Total" :value="$data['totalRooms']" subtitle="Semua status" />
      <x-kpi title="Terisi" :value="$data['occupied']" :extra="$data['occupancyRate'].'% occ'" />
      <x-kpi title="Kosong" :value="$data['available']" />
      <x-kpi title="Maintenance" :value="$data['maintenance']" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
      <x-kpi title="Stays Aktif" :value="$data['activeStays']" />
      <x-kpi title="Cash In (MTD)" :value="number_format($data['revenueMTD'],0,',','.')" prefix="Rp" />
      <x-kpi title="Piutang (Unpaid)" :value="number_format($data['unpaidTotal'],0,',','.')" prefix="Rp" />
      <x-kpi title="Invoice Overdue" :value="$data['overdueCount']" />
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
      <div class="bg-white rounded-2xl p-4 shadow">
        <h3 class="font-semibold mb-2">Occupancy per Lokasi</h3>
        <canvas id="occChart" height="130"></canvas>
      </div>
      <div class="bg-white rounded-2xl p-4 shadow">
        <h3 class="font-semibold mb-2">Cash In (MTD) per Lokasi</h3>
        <canvas id="revChart" height="130"></canvas>
      </div>
      <div class="bg-white rounded-2xl p-4 shadow">
        <h3 class="font-semibold mb-2">Trend Cash In (6 Bulan)</h3>
        <canvas id="trendChart" height="130"></canvas>
      </div>
    </div>

    {{-- Per-Lokasi Cards Grid --}}
    <div class="mb-10">
      <h3 class="font-semibold mb-3">Ringkasan per Lokasi</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($data['perLocation'] as $row)
        <div class="bg-white rounded-2xl p-4 shadow">
          <div class="flex items-center justify-between mb-2">
            <div>
              <div class="text-lg font-semibold">{{ $row->location->name }}</div>
              <div class="text-sm text-gray-500">{{ $row->total }} kamar • {{ $row->occ }}% occ</div>
            </div>
            @if($row->location->wa_group_link)
              <a href="{{ $row->location->wa_group_link }}" target="_blank" class="text-green-600 text-sm underline">WA Group</a>
            @endif
          </div>
          <div class="grid grid-cols-3 gap-2 text-center">
            <div class="rounded-lg bg-gray-50 p-3">
              <div class="text-xs text-gray-500">Kosong</div>
              <div class="text-xl font-semibold">{{ $row->available }}</div>
            </div>
            <div class="rounded-lg bg-gray-50 p-3">
              <div class="text-xs text-gray-500">Terisi</div>
              <div class="text-xl font-semibold">{{ $row->occupied }}</div>
            </div>
            <div class="rounded-lg bg-gray-50 p-3">
              <div class="text-xs text-gray-500">Maint.</div>
              <div class="text-xl font-semibold">{{ $row->maintenance }}</div>
            </div>
          </div>
          <div class="mt-3">
            <a href="{{ route('dashboard',['location_id'=>$row->location_id]) }}" class="text-blue-600 text-sm underline">Lihat dashboard lokasi</a>
          </div>
        </div>
        @empty
          <div class="text-gray-500">Tidak ada data lokasi.</div>
        @endforelse
      </div>
    </div>

    {{-- Due soon table --}}
    <div class="bg-white rounded-2xl p-4 shadow">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold">Invoice Jatuh Tempo (7 hari ke depan)</h3>
        <div class="text-sm text-gray-500">Periode: {{ now()->toDateString() }} s/d {{ now()->addDays(7)->toDateString() }}</div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500 border-b">
              <th class="py-2">Invoice</th>
              <th class="py-2">Tenant</th>
              <th class="py-2">Lokasi/Kamar</th>
              <th class="py-2">Due</th>
              <th class="py-2 text-right">Total</th>
              <th class="py-2">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($data['dueSoon'] as $inv)
              <tr class="border-b last:border-0">
                <td class="py-2">{{ $inv->number }}</td>
                <td class="py-2">{{ $inv->tenant->full_name ?? '-' }}</td>
                <td class="py-2">
                  {{ optional($inv->stay->room->location)->name }} /
                  {{ optional($inv->stay->room)->number }}
                </td>
                <td class="py-2">{{ $inv->due_date }}</td>
                <td class="py-2 text-right">Rp {{ number_format($inv->total,0,',','.') }}</td>
                <td class="py-2">
                  <span class="px-2 py-1 rounded text-xs
                      @if($inv->status==='unpaid') bg-red-100 text-red-700
                      @elseif($inv->status==='partial') bg-yellow-100 text-yellow-700
                      @else bg-green-100 text-green-700 @endif">
                    {{ strtoupper($inv->status) }}
                  </span>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="py-4 text-gray-500">Tidak ada invoice yang akan jatuh tempo.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="text-xs text-gray-400 mt-4">* Data di-cache 5 menit untuk performa.</div>
  </div>

  {{-- Chart.js CDN --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    new Chart(document.getElementById('occChart'), {
      type: 'bar',
      data: {
        labels: {!! json_encode($data['occLabels']) !!},
        datasets: [{ label: 'Occupancy (%)', data: {!! json_encode($data['occData']) !!} }]
      },
      options: { responsive: true, scales: { y: { beginAtZero:true, suggestedMax: 100 } } }
    });

    new Chart(document.getElementById('revChart'), {
      type: 'bar',
      data: {
        labels: {!! json_encode($data['revLabels']) !!},
        datasets: [{ label: 'Cash In (Rp)', data: {!! json_encode($data['revData']) !!} }]
      },
      options: { responsive: true, scales: { y: { beginAtZero:true } }, plugins:{ legend:{ display:false } } }
    });

    new Chart(document.getElementById('trendChart'), {
      type: 'line',
      data: {
        labels: {!! json_encode($data['trendLabels']) !!},
        datasets: [{ label: 'Cash In', data: {!! json_encode($data['trendData']) !!}, tension: 0.3 }]
      },
      options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
  </script>
</x-app-layout>
