<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function availability(Request $req)
    {
        $byLocation = Room::select('location_id',
                DB::raw("SUM(CASE WHEN status='available' THEN 1 ELSE 0 END) as available"),
                DB::raw("SUM(CASE WHEN status='occupied' THEN 1 ELSE 0 END) as occupied"),
                DB::raw("SUM(CASE WHEN status='maintenance' THEN 1 ELSE 0 END) as maintenance")
            )
            ->groupBy('location_id')
            ->with('location')
            ->get();

        return view('reports.availability', compact('byLocation'));
    }

    public function profitLoss(Request $req)
    {
        $from = $req->get('from', now()->startOfMonth()->toDateString());
        $to   = $req->get('to', now()->endOfMonth()->toDateString());

        $rows = JournalLine::join('accounts','accounts.id','=','journal_lines.account_id')
            ->whereBetween('journal_lines.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->whereIn('accounts.type',['revenue','expense'])
            ->select('accounts.type', DB::raw('SUM(debit) as deb'), DB::raw('SUM(credit) as cred'))
            ->groupBy('accounts.type')
            ->get()
            ->keyBy('type');

        $totalRevenue = ($rows['revenue']->cred ?? 0) - ($rows['revenue']->deb ?? 0);
        $totalExpense = ($rows['expense']->deb ?? 0) - ($rows['expense']->cred ?? 0);
        $netProfit    = $totalRevenue - $totalExpense;

        return view('reports.profit_loss', compact('from','to','totalRevenue','totalExpense','netProfit'));
    }

    public function balanceSheet(Request $req)
    {
        $asOf = $req->get('as_of', now()->toDateString());

        $balances = JournalLine::join('accounts','accounts.id','=','journal_lines.account_id')
            ->whereDate('journal_lines.created_at','<=',$asOf)
            ->select('accounts.id','accounts.code','accounts.name','accounts.type',
                     DB::raw('SUM(debit) as deb'), DB::raw('SUM(credit) as cred'))
            ->groupBy('accounts.id','accounts.code','accounts.name','accounts.type')
            ->orderBy('accounts.code')
            ->get()
            ->map(function($r){
                $r->balance = ($r->type==='asset' || $r->type==='expense')
                    ? $r->deb - $r->cred
                    : $r->cred - $r->deb;
                return $r;
            })
            ->groupBy('type');

        return view('reports.balance_sheet', compact('asOf','balances'));
    }
}
