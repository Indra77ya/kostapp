<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportExportService
{
    /**
     * Export Profit & Loss Report to Excel
     */
    public function exportProfitLoss(?string $dateStart, ?string $dateEnd): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laba Rugi');

        $dateStartStr = $dateStart ? Carbon::parse($dateStart)->format('d/m/Y') : '-';
        $dateEndStr = $dateEnd ? Carbon::parse($dateEnd)->format('d/m/Y') : '-';

        // Title Header
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'LAPORAN LABA RUGI');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', "Periode: {$dateStartStr} s/d {$dateEndStr}");
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Calculate Data
        $revenueAccounts = ChartOfAccount::where('type', 'revenue')->orderBy('code')->get();
        $revenues = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $account) {
            $items = JournalEntryItem::where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($dateStart, $dateEnd) {
                    if ($dateStart) $q->whereDate('entry_date', '>=', $dateStart);
                    if ($dateEnd) $q->whereDate('entry_date', '<=', $dateEnd);
                })->get();

            $amount = $items->sum('credit') - $items->sum('debit');
            if ($amount != 0) {
                $revenues[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => $amount,
                ];
                $totalRevenue += $amount;
            }
        }

        $expenseAccounts = ChartOfAccount::where('type', 'expense')->orderBy('code')->get();
        $expenses = [];
        $totalExpense = 0;

        foreach ($expenseAccounts as $account) {
            $items = JournalEntryItem::where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($dateStart, $dateEnd) {
                    if ($dateStart) $q->whereDate('entry_date', '>=', $dateStart);
                    if ($dateEnd) $q->whereDate('entry_date', '<=', $dateEnd);
                })->get();

            $amount = $items->sum('debit') - $items->sum('credit');
            if ($amount != 0) {
                $expenses[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => $amount,
                ];
                $totalExpense += $amount;
            }
        }

        $netProfit = $totalRevenue - $totalExpense;

        // Table Header 1: Pendapatan
        $row = 4;
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'PENDAPATAN (REVENUE)');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1E7DD');

        $row++;
        $sheet->setCellValue("A{$row}", 'Kode Akun');
        $sheet->setCellValue("B{$row}", 'Nama Akun');
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", 'Jumlah (Rp)');
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $row++;
        if (empty($revenues)) {
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada pendapatan pada periode ini.');
            $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
            $row++;
        } else {
            foreach ($revenues as $rev) {
                $sheet->setCellValue("A{$row}", $rev['code']);
                $sheet->setCellValue("B{$row}", $rev['name']);
                $sheet->mergeCells("C{$row}:D{$row}");
                $sheet->setCellValue("C{$row}", $rev['amount']);
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $row++;
            }
        }

        // Total Pendapatan
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL PENDAPATAN');
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", $totalRevenue);
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E9');

        // Table Header 2: Beban Operasional
        $row += 2;
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'BEBAN & OPERASIONAL (EXPENSES)');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');

        $row++;
        $sheet->setCellValue("A{$row}", 'Kode Akun');
        $sheet->setCellValue("B{$row}", 'Nama Akun');
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", 'Jumlah (Rp)');
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $row++;
        if (empty($expenses)) {
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada beban operasional pada periode ini.');
            $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
            $row++;
        } else {
            foreach ($expenses as $exp) {
                $sheet->setCellValue("A{$row}", $exp['code']);
                $sheet->setCellValue("B{$row}", $exp['name']);
                $sheet->mergeCells("C{$row}:D{$row}");
                $sheet->setCellValue("C{$row}", $exp['amount']);
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $row++;
            }
        }

        // Total Beban
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL BEBAN & OPERASIONAL');
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", $totalExpense);
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFEBEE');

        // Net Profit / Loss Summary
        $row += 2;
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", $netProfit >= 0 ? 'HASIL OPERASIONAL (LABA BERSIH)' : 'HASIL OPERASIONAL (RUGI BERSIH)');
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", $netProfit);
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $color = $netProfit >= 0 ? 'D1E7DD' : 'F8D7DA';
        $sheet->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);

        $this->autoSizeColumns($sheet, ['A', 'B', 'C', 'D']);
        $filename = 'Laporan_Laba_Rugi_' . date('Ymd_His') . '.xlsx';

        return $this->streamSpreadsheetResponse($spreadsheet, $filename);
    }

    /**
     * Export Trial Balance Report to Excel
     */
    public function exportTrialBalance(?string $dateStart, ?string $dateEnd): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Neraca Saldo');

        $dateStartStr = $dateStart ? Carbon::parse($dateStart)->format('d/m/Y') : '-';
        $dateEndStr = $dateEnd ? Carbon::parse($dateEnd)->format('d/m/Y') : '-';

        // Title Header
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'NERACA SALDO (TRIAL BALANCE)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', "Periode: {$dateStartStr} s/d {$dateEndStr}");
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Headers
        $sheet->mergeCells('A4:A5');
        $sheet->setCellValue('A4', 'Kode Akun');
        $sheet->mergeCells('B4:B5');
        $sheet->setCellValue('B4', 'Nama Akun');

        $sheet->mergeCells('C4:D4');
        $sheet->setCellValue('C4', 'Mutasi Periode');
        $sheet->setCellValue('C5', 'Debit');
        $sheet->setCellValue('D5', 'Kredit');

        $sheet->mergeCells('E4:F4');
        $sheet->setCellValue('E4', 'Saldo Akhir');
        $sheet->setCellValue('E5', 'Debit');
        $sheet->setCellValue('F5', 'Kredit');

        $sheet->getStyle('A4:F5')->getFont()->setBold(true);
        $sheet->getStyle('A4:F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A4:F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');

        // Data Rows
        $accounts = ChartOfAccount::orderBy('code')->get();
        $row = 6;
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $items = JournalEntryItem::where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($dateStart, $dateEnd) {
                    if ($dateStart) $q->whereDate('entry_date', '>=', $dateStart);
                    if ($dateEnd) $q->whereDate('entry_date', '<=', $dateEnd);
                })->get();

            $sumDebit = $items->sum('debit');
            $sumCredit = $items->sum('credit');

            $netDebit = 0;
            $netCredit = 0;

            if ($account->normal_balance === 'debit') {
                $net = $sumDebit - $sumCredit;
                if ($net >= 0) {
                    $netDebit = $net;
                } else {
                    $netCredit = abs($net);
                }
            } else {
                $net = $sumCredit - $sumDebit;
                if ($net >= 0) {
                    $netCredit = $net;
                } else {
                    $netDebit = abs($net);
                }
            }

            if ($sumDebit > 0 || $sumCredit > 0 || $netDebit > 0 || $netCredit > 0) {
                $sheet->setCellValue("A{$row}", $account->code);
                $sheet->setCellValue("B{$row}", $account->name);
                $sheet->setCellValue("C{$row}", $sumDebit);
                $sheet->setCellValue("D{$row}", $sumCredit);
                $sheet->setCellValue("E{$row}", $netDebit);
                $sheet->setCellValue("F{$row}", $netCredit);

                $sheet->getStyle("C{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $totalDebit += $netDebit;
                $totalCredit += $netCredit;
                $row++;
            }
        }

        // Totals Footer
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL SALDO AKHIR');
        $sheet->setCellValue("E{$row}", $totalDebit);
        $sheet->setCellValue("F{$row}", $totalCredit);
        $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');

        $this->autoSizeColumns($sheet, ['A', 'B', 'C', 'D', 'E', 'F']);
        $filename = 'Neraca_Saldo_' . date('Ymd_His') . '.xlsx';

        return $this->streamSpreadsheetResponse($spreadsheet, $filename);
    }

    /**
     * Export General Ledger Report to Excel
     */
    public function exportGeneralLedger(?int $accountId, ?string $dateStart, ?string $dateEnd): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Buku Besar');

        $account = $accountId ? ChartOfAccount::find($accountId) : null;
        $accountName = $account ? "{$account->code} - {$account->name}" : 'Semua Akun';

        $dateStartStr = $dateStart ? Carbon::parse($dateStart)->format('d/m/Y') : '-';
        $dateEndStr = $dateEnd ? Carbon::parse($dateEnd)->format('d/m/Y') : '-';

        // Title Header
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'LAPORAN BUKU BESAR (GENERAL LEDGER)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', "Akun: {$accountName} | Periode: {$dateStartStr} s/d {$dateEndStr}");
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Headers
        $sheet->setCellValue('A4', 'Tanggal');
        $sheet->setCellValue('B4', 'No. Jurnal');
        $sheet->setCellValue('C4', 'Keterangan');
        $sheet->setCellValue('D4', 'Debit (Rp)');
        $sheet->setCellValue('E4', 'Kredit (Rp)');
        $sheet->setCellValue('F4', 'Saldo Akhir (Rp)');

        $sheet->getStyle('A4:F4')->getFont()->setBold(true);
        $sheet->getStyle('A4:F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');

        $row = 5;
        $openingBalance = 0;

        if ($account) {
            // Opening Balance
            if ($dateStart) {
                $prevItems = JournalEntryItem::where('chart_of_account_id', $account->id)
                    ->whereHas('journalEntry', function ($q) use ($dateStart) {
                        $q->whereDate('entry_date', '<', $dateStart);
                    })->get();

                $sumDebit = $prevItems->sum('debit');
                $sumCredit = $prevItems->sum('credit');

                if ($account->normal_balance === 'debit') {
                    $openingBalance = $sumDebit - $sumCredit;
                } else {
                    $openingBalance = $sumCredit - $sumDebit;
                }
            }

            $sheet->setCellValue("A{$row}", $dateStart ? Carbon::parse($dateStart)->format('d/m/Y') : '-');
            $sheet->setCellValue("B{$row}", '-');
            $sheet->setCellValue("C{$row}", 'SALDO AWAL');
            $sheet->setCellValue("D{$row}", 0);
            $sheet->setCellValue("E{$row}", 0);
            $sheet->setCellValue("F{$row}", $openingBalance);
            $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
            $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;

            // Journal Items
            $items = JournalEntryItem::with('journalEntry')
                ->where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($dateStart, $dateEnd) {
                    if ($dateStart) $q->whereDate('entry_date', '>=', $dateStart);
                    if ($dateEnd) $q->whereDate('entry_date', '<=', $dateEnd);
                })
                ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
                ->orderBy('journal_entries.entry_date', 'asc')
                ->orderBy('journal_entries.id', 'asc')
                ->select('journal_entry_items.*')
                ->get();

            $runningBalance = $openingBalance;
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($items as $item) {
                $entryDate = $item->journalEntry ? $item->journalEntry->entry_date->format('d/m/Y') : '';
                $entryNumber = $item->journalEntry ? $item->journalEntry->entry_number : '';
                $desc = $item->journalEntry ? $item->journalEntry->description : '';
                if ($item->memo) {
                    $desc .= " ({$item->memo})";
                }

                if ($account->normal_balance === 'debit') {
                    $runningBalance += ($item->debit - $item->credit);
                } else {
                    $runningBalance += ($item->credit - $item->debit);
                }

                $totalDebit += $item->debit;
                $totalCredit += $item->credit;

                $sheet->setCellValue("A{$row}", $entryDate);
                $sheet->setCellValue("B{$row}", $entryNumber);
                $sheet->setCellValue("C{$row}", $desc);
                $sheet->setCellValue("D{$row}", $item->debit);
                $sheet->setCellValue("E{$row}", $item->credit);
                $sheet->setCellValue("F{$row}", $runningBalance);

                $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $row++;
            }

            // Totals
            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->setCellValue("A{$row}", 'TOTAL MUTASI');
            $sheet->setCellValue("D{$row}", $totalDebit);
            $sheet->setCellValue("E{$row}", $totalCredit);
            $sheet->setCellValue("F{$row}", $runningBalance);

            $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');
        }

        $this->autoSizeColumns($sheet, ['A', 'B', 'C', 'D', 'E', 'F']);
        $filename = 'Buku_Besar_' . date('Ymd_His') . '.xlsx';

        return $this->streamSpreadsheetResponse($spreadsheet, $filename);
    }

    /**
     * Export General Journal Report to Excel
     */
    public function exportGeneralJournal(?string $dateStart, ?string $dateEnd, ?string $search): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jurnal Umum');

        $dateStartStr = $dateStart ? Carbon::parse($dateStart)->format('d/m/Y') : '-';
        $dateEndStr = $dateEnd ? Carbon::parse($dateEnd)->format('d/m/Y') : '-';

        // Title Header
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'LAPORAN JURNAL UMUM (GENERAL JOURNAL)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', "Periode: {$dateStartStr} s/d {$dateEndStr}");
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Headers
        $sheet->setCellValue('A4', 'Tanggal');
        $sheet->setCellValue('B4', 'No. Jurnal');
        $sheet->setCellValue('C4', 'Keterangan');
        $sheet->setCellValue('D4', 'Kode Akun');
        $sheet->setCellValue('E4', 'Nama Akun');
        $sheet->setCellValue('F4', 'Debit (Rp)');
        $sheet->setCellValue('G4', 'Kredit (Rp)');

        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');

        $query = JournalEntry::with(['items.chartOfAccount']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('items.chartOfAccount', function ($aq) use ($search) {
                        $aq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if ($dateStart) $query->whereDate('entry_date', '>=', $dateStart);
        if ($dateEnd) $query->whereDate('entry_date', '<=', $dateEnd);

        $entries = $query->orderBy('entry_date', 'desc')->orderBy('id', 'desc')->get();

        $row = 5;
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($entries as $entry) {
            $entryDate = $entry->entry_date ? $entry->entry_date->format('d/m/Y') : '';
            foreach ($entry->items as $index => $item) {
                $sheet->setCellValue("A{$row}", $index === 0 ? $entryDate : '');
                $sheet->setCellValue("B{$row}", $index === 0 ? $entry->entry_number : '');
                $sheet->setCellValue("C{$row}", $index === 0 ? $entry->description : '');
                $sheet->setCellValue("D{$row}", $item->chartOfAccount ? $item->chartOfAccount->code : '');
                $sheet->setCellValue("E{$row}", $item->chartOfAccount ? $item->chartOfAccount->name : '');
                $sheet->setCellValue("F{$row}", $item->debit);
                $sheet->setCellValue("G{$row}", $item->credit);

                $sheet->getStyle("F{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');

                $totalDebit += $item->debit;
                $totalCredit += $item->credit;
                $row++;
            }
        }

        // Totals Footer
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->setCellValue("F{$row}", $totalDebit);
        $sheet->setCellValue("G{$row}", $totalCredit);

        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("F{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');

        $this->autoSizeColumns($sheet, ['A', 'B', 'C', 'D', 'E', 'F', 'G']);
        $filename = 'Jurnal_Umum_' . date('Ymd_His') . '.xlsx';

        return $this->streamSpreadsheetResponse($spreadsheet, $filename);
    }

    /**
     * Export Cash Flow Report to Excel
     */
    public function exportCashFlow(?string $dateStart, ?string $dateEnd): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Arus Kas');

        $dateStartStr = $dateStart ? Carbon::parse($dateStart)->format('d/m/Y') : '-';
        $dateEndStr = $dateEnd ? Carbon::parse($dateEnd)->format('d/m/Y') : '-';

        // Title Header
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', 'LAPORAN ARUS KAS (CASH FLOW STATEMENT)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:C2');
        $sheet->setCellValue('A2', "Periode: {$dateStartStr} s/d {$dateEndStr}");
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $pmAccountIds = PaymentMethod::whereNotNull('chart_of_account_id')->pluck('chart_of_account_id')->toArray();
        $cashAccounts = ChartOfAccount::where('category', 'Kas & Setara Kas')
            ->orWhereIn('id', $pmAccountIds)
            ->pluck('id');

        // Initial Cash Balance before dateStart
        $initialBalance = 0;
        if ($dateStart && count($cashAccounts) > 0) {
            $prevItems = JournalEntryItem::whereIn('chart_of_account_id', $cashAccounts)
                ->whereHas('journalEntry', function ($q) use ($dateStart) {
                    $q->whereDate('entry_date', '<', $dateStart);
                })->get();
            $initialBalance = $prevItems->sum('debit') - $prevItems->sum('credit');
        }

        // Inflows
        $inflowItems = JournalEntryItem::with(['journalEntry'])
            ->whereIn('chart_of_account_id', $cashAccounts)
            ->where('debit', '>', 0)
            ->whereHas('journalEntry', function ($q) use ($dateStart, $dateEnd) {
                if ($dateStart) $q->whereDate('entry_date', '>=', $dateStart);
                if ($dateEnd) $q->whereDate('entry_date', '<=', $dateEnd);
            })->get();
        $totalInflow = $inflowItems->sum('debit');

        // Outflows
        $outflowItems = JournalEntryItem::with(['journalEntry'])
            ->whereIn('chart_of_account_id', $cashAccounts)
            ->where('credit', '>', 0)
            ->whereHas('journalEntry', function ($q) use ($dateStart, $dateEnd) {
                if ($dateStart) $q->whereDate('entry_date', '>=', $dateStart);
                if ($dateEnd) $q->whereDate('entry_date', '<=', $dateEnd);
            })->get();
        $totalOutflow = $outflowItems->sum('credit');

        $netCashFlow = $totalInflow - $totalOutflow;
        $endingBalance = $initialBalance + $netCashFlow;

        $row = 4;
        // Initial Balance Row
        $sheet->setCellValue("A{$row}", 'SALDO KAS AWAL PERIODE');
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", $initialBalance);
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0F7FA');

        // Section 1: Inflow
        $row += 2;
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", '1. ARUS KAS MASUK (INFLOW)');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1E7DD');

        $row++;
        if ($inflowItems->isEmpty()) {
            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada arus kas masuk pada periode ini.');
            $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
            $row++;
        } else {
            foreach ($inflowItems as $item) {
                $date = $item->journalEntry ? $item->journalEntry->entry_date->format('d/m/Y') : '';
                $desc = $item->journalEntry ? $item->journalEntry->description : 'Penerimaan Kas';
                if ($item->memo) $desc .= " ({$item->memo})";

                $sheet->setCellValue("A{$row}", $date);
                $sheet->setCellValue("B{$row}", $desc);
                $sheet->setCellValue("C{$row}", $item->debit);
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $row++;
            }
        }

        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL ARUS KAS MASUK');
        $sheet->setCellValue("C{$row}", $totalInflow);
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');

        // Section 2: Outflow
        $row += 2;
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", '2. ARUS KAS KELUAR (OUTFLOW)');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');

        $row++;
        if ($outflowItems->isEmpty()) {
            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada arus kas keluar pada periode ini.');
            $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
            $row++;
        } else {
            foreach ($outflowItems as $item) {
                $date = $item->journalEntry ? $item->journalEntry->entry_date->format('d/m/Y') : '';
                $desc = $item->journalEntry ? $item->journalEntry->description : 'Pengeluaran Kas';
                if ($item->memo) $desc .= " ({$item->memo})";

                $sheet->setCellValue("A{$row}", $date);
                $sheet->setCellValue("B{$row}", $desc);
                $sheet->setCellValue("C{$row}", $item->credit);
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $row++;
            }
        }

        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL ARUS KAS KELUAR');
        $sheet->setCellValue("C{$row}", $totalOutflow);
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');

        // Summary Net & Ending Balance
        $row += 2;
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'KENAIKAN / (PENURUNAN) BERSIH KAS');
        $sheet->setCellValue("C{$row}", $netCashFlow);
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');

        $row++;
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'SALDO KAS AKHIR PERIODE');
        $sheet->setCellValue("C{$row}", $endingBalance);
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CFF4FC');

        $this->autoSizeColumns($sheet, ['A', 'B', 'C']);
        $filename = 'Laporan_Arus_Kas_' . date('Ymd_His') . '.xlsx';

        return $this->streamSpreadsheetResponse($spreadsheet, $filename);
    }

    /**
     * Export Expenses Report to Excel
     */
    public function exportExpenses(?string $dateStart, ?string $dateEnd, ?int $accountId, ?string $search): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pengeluaran Operasional');

        $dateStartStr = $dateStart ? Carbon::parse($dateStart)->format('d/m/Y') : '-';
        $dateEndStr = $dateEnd ? Carbon::parse($dateEnd)->format('d/m/Y') : '-';

        // Title Header
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'LAPORAN PENGELUARAN OPERASIONAL');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', "Periode: {$dateStartStr} s/d {$dateEndStr}");
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Headers
        $sheet->setCellValue('A4', 'No. Pengeluaran');
        $sheet->setCellValue('B4', 'Tanggal');
        $sheet->setCellValue('C4', 'Akun Beban');
        $sheet->setCellValue('D4', 'Metode Pembayaran');
        $sheet->setCellValue('E4', 'Judul / Keperluan');
        $sheet->setCellValue('F4', 'Catatan');
        $sheet->setCellValue('G4', 'Jumlah (Rp)');

        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');

        $query = Expense::with(['account', 'paymentMethod', 'creator']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($accountId) {
            $query->where('chart_of_account_id', $accountId);
        }

        if ($dateStart) $query->whereDate('expense_date', '>=', $dateStart);
        if ($dateEnd) $query->whereDate('expense_date', '<=', $dateEnd);

        $expenses = $query->orderBy('expense_date', 'desc')->get();

        $row = 5;
        $totalAmount = 0;

        foreach ($expenses as $exp) {
            $sheet->setCellValue("A{$row}", $exp->expense_number);
            $sheet->setCellValue("B{$row}", $exp->expense_date ? $exp->expense_date->format('d/m/Y') : '');
            $sheet->setCellValue("C{$row}", $exp->account ? "{$exp->account->code} - {$exp->account->name}" : '');
            $sheet->setCellValue("D{$row}", $exp->paymentMethod ? $exp->paymentMethod->name : '-');
            $sheet->setCellValue("E{$row}", $exp->title);
            $sheet->setCellValue("F{$row}", $exp->notes);
            $sheet->setCellValue("G{$row}", $exp->amount);

            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $totalAmount += $exp->amount;
            $row++;
        }

        // Totals Footer
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL PENGELUARAN');
        $sheet->setCellValue("G{$row}", $totalAmount);

        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');

        $this->autoSizeColumns($sheet, ['A', 'B', 'C', 'D', 'E', 'F', 'G']);
        $filename = 'Pengeluaran_Operasional_' . date('Ymd_His') . '.xlsx';

        return $this->streamSpreadsheetResponse($spreadsheet, $filename);
    }

    /**
     * Auto-size columns in worksheet
     */
    private function autoSizeColumns($sheet, array $columns): void
    {
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Stream spreadsheet download
     */
    private function streamSpreadsheetResponse(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        return response()->stream(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
