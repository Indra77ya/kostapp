<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Stay;
use Illuminate\Support\Str;

class BillingService
{
    public function __construct(private JournalService $journal) {}

    /**
     * Generate invoice bulanan untuk semua stay aktif (mode monthly).
     * Due date = setiap bulan di tanggal check-in (bisa disesuaikan).
     */
    public function generateMonthlyInvoices(): int
    {
        $count = 0;
        $stays = Stay::where('status','active')->where('billing_cycle','monthly')->get();

        foreach ($stays as $stay) {
            $due = now()->day($stay->checkin_date->format('d'));
            $exists = Invoice::where('stay_id',$stay->id)
                ->whereYear('due_date', now()->year)
                ->whereMonth('due_date', now()->month)
                ->exists();

            if ($exists) continue;

            $inv = Invoice::create([
                'number'    => 'INV-'.now()->format('Ym').'-'.Str::padLeft($stay->id,4,'0'),
                'tenant_id' => $stay->tenant_id,
                'stay_id'   => $stay->id,
                'issue_date'=> now()->toDateString(),
                'due_date'  => $due->toDateString(),
                'subtotal'  => $stay->rate,
                'discount'  => 0,
                'tax'       => 0,
                'total'     => $stay->rate,
                'status'    => 'unpaid',
            ]);

            $this->journal->postInvoiceAccrual($inv);
            $count++;
        }

        return $count;
    }
}
