<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Reminder;

class SendDueReminders extends Command
{
    protected $signature = 'reminder:due-invoices';
    protected $description = 'Buat reminder untuk invoice mendekati jatuh tempo';

    public function handle()
    {
        $targets = Invoice::whereIn('status',['unpaid','partial'])
            ->whereDate('due_date','>=', now()->subDays(1)->toDateString())
            ->whereDate('due_date','<=', now()->addDays(3)->toDateString())
            ->get();

        foreach ($targets as $inv) {
            Reminder::firstOrCreate([
                'invoice_id' => $inv->id,
                'remind_on'  => now()->toDateString(),
                'channel'    => 'wa',
            ],[
                'status'     => 'pending',
                'payload'    => "Pengingat: Invoice {$inv->number} jatuh tempo {$inv->due_date}",
            ]);
        }

        $this->info("Queued reminders for {$targets->count()} invoices.");
        return self::SUCCESS;
    }
}
