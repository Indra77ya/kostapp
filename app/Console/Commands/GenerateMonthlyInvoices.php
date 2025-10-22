<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BillingService;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'billing:generate-monthly';
    protected $description = 'Generate invoice bulanan untuk stay aktif monthly';

    public function handle(BillingService $billing)
    {
        $n = $billing->generateMonthlyInvoices();
        $this->info("Generated $n invoices.");
        return self::SUCCESS;
    }
}
