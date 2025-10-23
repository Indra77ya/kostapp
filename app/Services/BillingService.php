<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Stay;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BillingService
{
    public function __construct(private JournalService $journal) {}

    /**
     * Generate invoice bulanan untuk semua stay aktif (mode monthly).
     * Due date = setiap bulan di tanggal check-in (disesuaikan untuk bulan pendek).
     */
    public function generateMonthlyInvoices(): int
    {
        $count = 0;

        $stays = Stay::where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->get();

        foreach ($stays as $stay) {
            // Lewati bila checkin_date kosong (data korup/baru dibuat)
            if (empty($stay->checkin_date)) {
                continue;
            }

            // Ambil hari check-in sebagai INTEGER
            $checkinDay = $stay->checkin_date instanceof Carbon
                ? (int) $stay->checkin_date->day
                : (int) Carbon::parse($stay->checkin_date)->day;

            // Amankan ke jumlah hari dalam bulan berjalan (mis. 31 -> 30/28)
            $today   = now();
            $safeDay = min($checkinDay, $today->daysInMonth);

            // Set due date bulan ini
            $due = $today->copy()->day($safeDay);

            // Cegah duplikasi invoice pada bulan yang sama
            $exists = Invoice::where('stay_id', $stay->id)
                ->whereYear('due_date', $today->year)
                ->whereMonth('due_date', $today->month)
                ->exists();

            if ($exists) {
                continue;
            }

            $inv = Invoice::create([
                'number'     => 'INV-' . $today->format('Ym') . '-' . Str::padLeft($stay->id, 4, '0'),
                'tenant_id'  => $stay->tenant_id,
                'stay_id'    => $stay->id,
                'issue_date' => $today->toDateString(),
                'due_date'   => $due->toDateString(),
                'subtotal'   => $stay->rate,
                'discount'   => 0,
                'tax'        => 0,
                'total'      => $stay->rate,
                'status'     => 'unpaid',
            ]);

            $this->journal->postInvoiceAccrual($inv);
            $count++;
        }

        return $count;
    }
}
