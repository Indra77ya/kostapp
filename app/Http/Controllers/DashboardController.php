<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Location;
use App\Models\Room;
use App\Models\Stay;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $locationId = $request->integer('location_id');
        $fromMonth = now()->startOfMonth();
        $toMonth   = now()->endOfMonth();

        $cacheKey = 'dash:' . ($locationId ?: 'all');

        $data = Cache::remember($cacheKey, 300, function () use ($locationId, $fromMonth, $toMonth) {

            $roomsQuery = Room::query()
                ->when($locationId, fn($q) => $q->where('location_id', $locationId));

            $totalRooms = (clone $roomsQuery)->count();
            $available  = (clone $roomsQuery)->where('status','available')->count();
            $occupied   = (clone $roomsQuery)->where('status','occupied')->count();
            $maintenance= (clone $roomsQuery)->where('status','maintenance')->count();
            $occupancyRate = $totalRooms ? round($occupied / $totalRooms * 100, 1) : 0;

            $activeStays = Stay::where('status','active')
                ->when($locationId, fn($q) =>
                    $q->whereHas('room', fn($r)=>$r->where('location_id',$locationId))
                )->count();

            $revenueMTD = Payment::whereBetween('paid_at', [$fromMonth, $toMonth])
                ->when($locationId, fn($q) =>
                    $q->whereHas('invoice.stay.room', fn($r)=>$r->where('location_id',$locationId))
                )->sum('amount');

            $unpaidTotal = Invoice::whereIn('status',['unpaid','partial'])
                ->when($locationId, fn($q) =>
                    $q->whereHas('stay.room', fn($r)=>$r->where('location_id',$locationId))
                )->sum(DB::raw('total - (select coalesce(sum(amount),0) from payments where payments.invoice_id=invoices.id)'));

            $overdueCount = Invoice::whereIn('status',['unpaid','partial'])
                ->whereDate('due_date','<', now()->toDateString())
                ->when($locationId, fn($q) =>
                    $q->whereHas('stay.room', fn($r)=>$r->where('location_id',$locationId))
                )->count();

            $dueSoon = Invoice::with('tenant','stay.room.location')
                ->whereIn('status',['unpaid','partial'])
                ->whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->when($locationId, fn($q) =>
                    $q->whereHas('stay.room', fn($r)=>$r->where('location_id',$locationId))
                )->orderBy('due_date')->limit(10)->get();

            $openTickets = Ticket::whereIn('status',['open','in_progress'])
                ->when($locationId, fn($q) =>
                    $q->whereHas('room', fn($r)=>$r->where('location_id',$locationId))
                )->count();

            $perLocation = Room::select(
                    'location_id',
                    DB::raw("COUNT(*) as total"),
                    DB::raw("SUM(CASE WHEN status='available' THEN 1 ELSE 0 END) as available"),
                    DB::raw("SUM(CASE WHEN status='occupied' THEN 1 ELSE 0 END) as occupied"),
                    DB::raw("SUM(CASE WHEN status='maintenance' THEN 1 ELSE 0 END) as maintenance")
                )
                ->when($locationId, fn($q) => $q->where('location_id',$locationId))
                ->groupBy('location_id')
                ->with('location')
                ->get()
                ->map(function($r){
                    $r->occ = $r->total ? round($r->occupied / $r->total * 100, 1) : 0;
                    return $r;
                });

            $occLabels = $perLocation->pluck('location.name');
            $occData   = $perLocation->pluck('occ');

            $revenuePerLoc = Payment::select('rooms.location_id', DB::raw('SUM(payments.amount) as amt'))
                ->join('invoices','invoices.id','=','payments.invoice_id')
                ->leftJoin('stays','stays.id','=','invoices.stay_id')
                ->leftJoin('rooms','rooms.id','=','stays.room_id')
                ->whereBetween('paid_at', [$fromMonth, $toMonth])
                ->when($locationId, fn($q) => $q->where('rooms.location_id',$locationId))
                ->groupBy('rooms.location_id')
                ->get()
                ->keyBy('location_id');

            $locations = Location::whereIn('id',
                    Room::select('location_id')
                        ->when($locationId, fn($q)=>$q->where('location_id',$locationId))
                        ->groupBy('location_id')
                        ->pluck('location_id')
                )->orderBy('name')->get();

            $revLabels = $locations->pluck('name');
            $revData   = $locations->map(fn($l)=> (float)($revenuePerLoc[$l->id]->amt ?? 0));

            $trendStart = now()->startOfMonth()->subMonths(5);
            $trendRows = Payment::select(
                    DB::raw("DATE_FORMAT(paid_at, '%Y-%m') as ym"),
                    DB::raw('SUM(amount) as amt')
                )
                ->when($locationId, fn($q) =>
                    $q->whereHas('invoice.stay.room', fn($r)=>$r->where('location_id',$locationId))
                )
                ->whereBetween('paid_at', [$trendStart, now()->endOfMonth()])
                ->groupBy('ym')
                ->orderBy('ym')
                ->get()
                ->keyBy('ym');

            $trendLabels = [];
            $trendData = [];
            for ($i=5; $i>=0; $i--) {
                $ym = now()->subMonths($i)->format('Y-m');
                $trendLabels[] = $ym;
                $trendData[] = (float)($trendRows[$ym]->amt ?? 0);
            }

            return [
                'totalRooms' => $totalRooms,
                'available'  => $available,
                'occupied'   => $occupied,
                'maintenance'=> $maintenance,
                'occupancyRate'=> $occupancyRate,
                'activeStays'=> $activeStays,
                'revenueMTD' => $revenueMTD,
                'unpaidTotal'=> $unpaidTotal,
                'overdueCount'=> $overdueCount,
                'dueSoon'    => $dueSoon,
                'openTickets'=> $openTickets,
                'perLocation'=> $perLocation,
                'occLabels'  => $occLabels,
                'occData'    => $occData,
                'revLabels'  => $revLabels,
                'revData'    => $revData,
                'trendLabels'=> $trendLabels,
                'trendData'  => $trendData,
            ];
        });

        $locations = Location::orderBy('name')->get();

        return view('dashboard.index', [
            'data' => $data,
            'locations' => $locations,
            'locationId' => $locationId,
            'fromMonth' => $fromMonth,
            'toMonth' => $toMonth,
        ]);
    }
}
