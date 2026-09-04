<?php

namespace App\Livewire;

use App\Models\Room;
use App\Models\User;
use App\Models\Booking;
use App\Models\Registration;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Deposit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use Livewire\Component;

class DashboardStats extends Component
{
    // Admin / Owner KPI properties
    public $totalRooms = 0;
    public $availableRooms = 0;
    public $occupiedRooms = 0;
    public $occupancyRate = 0;
    public $activeTenantsCount = 0;
    public $monthlyRevenue = 0;
    public $outstandingBillsAmount = 0;
    public $outstandingBillsCount = 0;
    public $pendingConfirmationsCount = 0;

    // Tenant properties
    public $tenantRegistration = null;
    public $tenantTotalOutstanding = 0;
    public $tenantUnpaidBillsCount = 0;
    public $tenantDepositBalance = 0;
    public $tenantPendingConfirmations = 0;

    protected $listeners = ['echo:stats,DatabaseUpdated' => 'refreshStats'];

    public function mount()
    {
        $this->refreshStats();
    }

    public function refreshStats()
    {
        $user = Auth::user();

        if ($user && $user->hasRole('tenant')) {
            $this->loadTenantStats($user);
        } else {
            $this->loadAdminStats();
        }
    }

    private function loadAdminStats()
    {
        $this->totalRooms = Room::count();
        $this->availableRooms = Room::where('status', 'available')->count();
        $this->occupiedRooms = Room::where('status', 'occupied')->count();
        $this->occupancyRate = $this->totalRooms > 0 ? round(($this->occupiedRooms / $this->totalRooms) * 100, 1) : 0;

        $this->activeTenantsCount = Registration::where('status', 'active')->count();

        $this->monthlyRevenue = Payment::whereNotIn('status', ['Menunggu Konfirmasi', 'Ditolak'])
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('amount');

        $this->outstandingBillsCount = Bill::whereIn('status', ['Belum Lunas', 'Cicilan'])->count();
        $this->outstandingBillsAmount = Bill::whereIn('status', ['Belum Lunas', 'Cicilan'])
            ->get()
            ->sum(function ($bill) {
                return max(0, $bill->amount - $bill->paid_amount);
            });

        $this->pendingConfirmationsCount = Payment::where('status', 'Menunggu Konfirmasi')->count();
    }

    private function loadTenantStats($user)
    {
        $this->tenantRegistration = Registration::with(['room', 'location'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($this->tenantRegistration) {
            $unpaidBills = Bill::where('registration_id', $this->tenantRegistration->id)
                ->whereIn('status', ['Belum Lunas', 'Cicilan'])
                ->get();

            $this->tenantUnpaidBillsCount = $unpaidBills->count();
            $this->tenantTotalOutstanding = $unpaidBills->sum(function ($b) {
                return max(0, $b->amount - $b->paid_amount);
            });

            $credits = Deposit::where('registration_id', $this->tenantRegistration->id)->where('type', 'credit')->sum('amount');
            $debits = Deposit::where('registration_id', $this->tenantRegistration->id)->where('type', 'debit')->sum('amount');
            $this->tenantDepositBalance = max(0, $credits - $debits);

            $this->tenantPendingConfirmations = Payment::where('registration_id', $this->tenantRegistration->id)
                ->where('status', 'Menunggu Konfirmasi')
                ->count();
        } else {
            $this->tenantUnpaidBillsCount = 0;
            $this->tenantTotalOutstanding = 0;
            $this->tenantDepositBalance = 0;
            $this->tenantPendingConfirmations = 0;
        }
    }

    public function approvePayment($id)
    {
        $payment = Payment::find($id);
        if (!$payment) return;

        DB::transaction(function () use ($payment) {
            if ($payment->bill_id) {
                $bill = $payment->bill;
                $totalPaidPrev = Payment::where('bill_id', $payment->bill_id)
                    ->where('id', '!=', $payment->id)
                    ->whereNotIn('status', ['Menunggu Konfirmasi', 'Ditolak'])
                    ->sum('amount');

                $totalPaidNow = $totalPaidPrev + $payment->amount;

                if ($totalPaidNow < $bill->amount) {
                    $status = "Lunas (Cicilan)";
                } else {
                    $status = "Lunas";
                }
            } else {
                if ($payment->notes && strpos($payment->notes, '[DEPOSIT]') !== false) {
                    $status = "Setor Deposit";
                } else {
                    $status = "Pembayaran Umum";
                }
            }

            $payment->update(['status' => $status]);

            if ($payment->bill_id) {
                $this->syncBillStatus($payment->bill_id);
            }

            $this->syncDeposit($payment);

            \App\Services\AccountingService::recordPaymentJournal($payment);
        });

        if ($payment->registration) {
            $payment->registration->syncBills();
        }

        $billDescription = $payment->bill ? $payment->bill->description : 'Pembayaran Umum';
        $message = "Pembayaran untuk {$billDescription} telah disetujui.";

        $this->dispatch('notify', message: 'Pembayaran disetujui.', type: 'success');

        $tenantId = $payment->registration->user_id ?? null;
        if ($tenantId) {
            broadcast(new NotificationSent($message, 'success', $tenantId, false, route('tenant.payments')));
            DatabaseUpdated::dispatch($tenantId);
        }

        $this->refreshStats();
    }

    public function rejectPayment($id)
    {
        $payment = Payment::find($id);
        if ($payment) {
            $billDescription = $payment->bill ? $payment->bill->description : 'Pembayaran Umum';
            $tenantId = $payment->registration->user_id ?? null;

            $payment->delete();

            $this->dispatch('notify', message: 'Pembayaran ditolak dan dihapus.', type: 'info');

            if ($tenantId) {
                broadcast(new NotificationSent("Pembayaran untuk {$billDescription} ditolak.", 'warning', $tenantId, false, route('tenant.payments')));
                DatabaseUpdated::dispatch($tenantId);
            }

            $this->refreshStats();
        }
    }

    private function syncDeposit($payment)
    {
        Deposit::where('payment_id', $payment->id)->delete();

        if (in_array($payment->status, ['Menunggu Konfirmasi', 'Ditolak'])) {
            return;
        }

        $pm = \App\Models\PaymentMethod::find($payment->payment_method_id);
        if ($pm && $pm->name === 'Saldo Deposit') {
            Deposit::create([
                'registration_id' => $payment->registration_id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'type' => 'debit',
                'description' => 'Pembayaran ' . ($payment->bill_id ? 'Tagihan ' . $payment->bill->bill_number : 'Umum') . ' menggunakan Saldo Deposit',
                'transaction_date' => $payment->payment_date,
            ]);
            return;
        }

        $excess = 0;
        if ($payment->bill_id) {
            $bill = $payment->bill;
            $totalPaidOnBill = Payment::where('bill_id', $payment->bill_id)
                ->whereNotIn('status', ['Menunggu Konfirmasi', 'Ditolak'])
                ->sum('amount');
            $excess = $totalPaidOnBill - $bill->amount;
        } else {
            if ($payment->notes && strpos($payment->notes, '[DEPOSIT]') !== false) {
                $excess = $payment->amount;
            }
        }

        if ($excess > 0) {
            Deposit::create([
                'registration_id' => $payment->registration_id,
                'payment_id' => $payment->id,
                'amount' => $excess,
                'type' => 'credit',
                'description' => $payment->bill_id ? 'Kelebihan pembayaran tagihan ' . $payment->bill->bill_number : 'Penyetoran Deposit',
                'transaction_date' => $payment->payment_date,
            ]);
        }
    }

    private function syncBillStatus($billId)
    {
        $bill = Bill::find($billId);
        if (!$bill) return;

        $paidAmount = Payment::where('bill_id', $billId)
            ->whereNotIn('status', ['Menunggu Konfirmasi', 'Ditolak'])
            ->sum('amount');

        $bill->paid_amount = $paidAmount;

        if ($paidAmount <= 0) {
            $bill->status = 'Belum Lunas';
        } elseif ($paidAmount < $bill->amount) {
            $bill->status = 'Cicilan';
        } else {
            $bill->status = 'Lunas';
        }

        $bill->save();
    }

    public function render()
    {
        $pendingPayments = collect();
        $upcomingBills = collect();
        $tenantBills = collect();

        $user = Auth::user();

        if ($user && $user->hasRole('tenant')) {
            if ($this->tenantRegistration) {
                $tenantBills = Bill::where('registration_id', $this->tenantRegistration->id)
                    ->whereIn('status', ['Belum Lunas', 'Cicilan'])
                    ->orderBy('due_date', 'asc')
                    ->take(5)
                    ->get();
            }
        } else {
            $pendingPayments = Payment::with(['registration.user', 'registration.room', 'registration.location', 'bill', 'paymentMethod'])
                ->where('status', 'Menunggu Konfirmasi')
                ->orderBy('payment_date', 'desc')
                ->take(5)
                ->get();

            $upcomingBills = Bill::with(['registration.user', 'registration.room', 'registration.location'])
                ->whereIn('status', ['Belum Lunas', 'Cicilan'])
                ->orderBy('due_date', 'asc')
                ->take(5)
                ->get();
        }

        return view('livewire.dashboard-stats', [
            'pendingPayments' => $pendingPayments,
            'upcomingBills' => $upcomingBills,
            'tenantBills' => $tenantBills,
        ]);
    }
}
