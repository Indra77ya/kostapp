<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Room;
use App\Models\JournalLine;

class ReportController extends Controller
{
    /**
     * 1) Laporan Ketersediaan Kamar per Lokasi
     */
    public function availability(Request $req)
    {
        $byLocation = Room::select(
                'location_id',
                DB::raw("SUM(CASE WHEN status='available' THEN 1 ELSE 0 END) AS available"),
                DB::raw("SUM(CASE WHEN status='occupied' THEN 1 ELSE 0 END) AS occupied"),
                DB::raw("SUM(CASE WHEN status='maintenance' THEN 1 ELSE 0 END) AS maintenance")
            )
            ->groupBy('location_id')
            ->with('location')
            ->orderBy('location_id')
            ->get();

        return view('reports.availability', compact('byLocation'));
    }

    /**
     * 2) Laporan Laba Rugi (Profit & Loss)
     *    - total revenue = Σ(credit - debit) untuk akun type 'revenue'
     *    - total expense = Σ(debit - credit) untuk akun type 'expense'
     */
    public function profitLoss(Request $req)
    {
        $from = $req->get('from', now()->startOfMonth()->toDateString());
        $to   = $req->get('to',   now()->endOfMonth()->toDateString());

        // Agregasi aman dalam satu query
        $agg = JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->whereBetween('journal_lines.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN accounts.type='revenue' THEN credit - debit ELSE 0 END), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN accounts.type='expense' THEN debit - credit ELSE 0 END), 0) AS total_expense
            ")
            ->first();

        $totalRevenue = (float)($agg->total_revenue ?? 0);
        $totalExpense = (float)($agg->total_expense ?? 0);
        $netProfit    = $totalRevenue - $totalExpense;

        return view('reports.profit_loss', compact('from','to','totalRevenue','totalExpense','netProfit'));
    }

    /**
     * 3) Laporan Neraca (Balance Sheet)
     *    - saldo akun:
     *        asset/expense  => debit - credit
     *        liability/equity/revenue => credit - debit
     *    - as_of = tanggal posisi
     */
    public function balanceSheet(Request $req)
    {
        $asOf = $req->get('as_of', now()->toDateString());

        $balances = JournalLine::join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->whereDate('journal_lines.created_at', '<=', $asOf)
            ->select(
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.type',
                DB::raw('COALESCE(SUM(debit), 0)  AS deb'),
                DB::raw('COALESCE(SUM(credit), 0) AS cred')
            )
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type')
            ->orderBy('accounts.code')
            ->get()
            ->map(function ($r) {
                $isDebitNature = in_array($r->type, ['asset', 'expense'], true);
                $r->balance = $isDebitNature
                    ? (float)$r->deb - (float)$r->cred
                    : (float)$r->cred - (float)$r->deb;
                return $r;
            })
            ->groupBy('type');

        return view('reports.balance_sheet', compact('asOf','balances'));
    }
}
