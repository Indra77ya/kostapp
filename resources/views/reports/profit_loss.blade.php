<h2>Laporan Laba Rugi</h2>
<p>Periode: {{ $from }} s/d {{ $to }}</p>
<p>Pendapatan: Rp {{ number_format($totalRevenue,0,',','.') }}</p>
<p>Biaya: Rp {{ number_format($totalExpense,0,',','.') }}</p>
<hr>
<h3>Laba Bersih: Rp {{ number_format($netProfit,0,',','.') }}</h3>
