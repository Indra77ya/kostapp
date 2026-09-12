<?php

namespace App\Http\Controllers;

use App\Services\FinancialReportExportService;
use Illuminate\Http\Request;

class FinancialReportExportController extends Controller
{
    protected FinancialReportExportService $exportService;

    public function __construct(FinancialReportExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function export(Request $request, string $type)
    {
        $dateStart = $request->query('date_start');
        $dateEnd = $request->query('date_end');
        $accountId = $request->query('account_id') ? (int) $request->query('account_id') : null;
        $search = $request->query('search');

        return match ($type) {
            'profit-loss' => $this->exportService->exportProfitLoss($dateStart, $dateEnd),
            'trial-balance' => $this->exportService->exportTrialBalance($dateStart, $dateEnd),
            'ledger' => $this->exportService->exportGeneralLedger($accountId, $dateStart, $dateEnd),
            'journal' => $this->exportService->exportGeneralJournal($dateStart, $dateEnd, $search),
            'cash-flow' => $this->exportService->exportCashFlow($dateStart, $dateEnd),
            'expenses' => $this->exportService->exportExpenses($dateStart, $dateEnd, $accountId, $search),
            default => abort(404, 'Tipe laporan tidak ditemukan.'),
        };
    }
}
