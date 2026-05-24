<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\Bill;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentConfirmationManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search = '';
    public $filterLocation = '';
    public $filterPaymentMethod = '';
    public $filterDateStart = '';
    public $filterDateEnd = '';
    public $sort = 'latest';
    public $selectedPaymentId;
    public $isDetailModalOpen = false;

    public function getListeners()
    {
        return [
            'echo:stats,DatabaseUpdated' => '$refresh',
        ];
    }

    public function approve($id)
    {
        $payment = Payment::find($id);
        if (!$payment) return;

        DB::transaction(function () use ($payment) {
            // Determine status based on remainder
            if ($payment->bill_id) {
                $bill = $payment->bill;
                $totalPaidPrev = Payment::where('bill_id', $payment->bill_id)
                    ->where('id', '!=', $payment->id)
                    ->where('status', '!=', 'Menunggu Konfirmasi')
                    ->sum('amount');

                $totalPaidNow = $totalPaidPrev + $payment->amount;
                $totalBill = $bill->amount;

                if ($totalPaidNow < $totalBill) {
                    $status = "Lunas (Cicilan)";
                } else {
                    $status = "Lunas";
                }
            } else {
                $status = "Lunas";
            }

            $payment->update(['status' => $status]);

            if ($payment->bill_id) {
                $this->syncBillStatus($payment->bill_id);
            }
        });

        $billDescription = $payment->bill ? $payment->bill->description : 'Pembayaran Umum';
        $message = "Pembayaran untuk {$billDescription} telah disetujui.";

        $this->dispatch('notify', message: 'Pembayaran disetujui.', type: 'success');

        // Notify the tenant privately
        $tenantId = $payment->registration->user_id;
        broadcast(new NotificationSent($message, 'success', $tenantId));

        DatabaseUpdated::dispatch($tenantId);
    }

    public function reject($id)
    {
        $payment = Payment::find($id);
        if ($payment) {
            $billDescription = $payment->bill ? $payment->bill->description : 'Pembayaran Umum';
            $tenantId = $payment->registration->user_id;

            $payment->delete();

            $this->dispatch('notify', message: 'Pembayaran ditolak dan dihapus.', type: 'info');

            // Notify the tenant privately
            broadcast(new NotificationSent("Pembayaran untuk {$billDescription} ditolak.", 'warning', $tenantId));

            DatabaseUpdated::dispatch($tenantId);
        }
    }

    public function showDetail($id)
    {
        $this->selectedPaymentId = $id;
        $this->isDetailModalOpen = true;
    }

    public function closeModal()
    {
        $this->isDetailModalOpen = false;
        $this->selectedPaymentId = null;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterLocation', 'filterPaymentMethod', 'filterDateStart', 'filterDateEnd', 'sort']);
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterLocation() { $this->resetPage(); }
    public function updatingFilterPaymentMethod() { $this->resetPage(); }
    public function updatingFilterDateStart() { $this->resetPage(); }
    public function updatingFilterDateEnd() { $this->resetPage(); }
    public function updatingSort() { $this->resetPage(); }

    private function syncBillStatus($billId)
    {
        $bill = Bill::find($billId);
        if (!$bill) return;

        $paidAmount = Payment::where('bill_id', $billId)
            ->where('status', '!=', 'Menunggu Konfirmasi')
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
        $query = Payment::with(['registration.user', 'registration.room', 'registration.location', 'bill', 'paymentMethod'])
            ->select('payments.*')
            ->join('registrations', 'payments.registration_id', '=', 'registrations.id')
            ->join('users', 'registrations.user_id', '=', 'users.id')
            ->where('payments.status', 'Menunggu Konfirmasi');

        if ($this->search) {
            $query->where('users.name', 'like', '%' . $this->search . '%');
        }

        if ($this->filterLocation) {
            $query->where('registrations.location_id', $this->filterLocation);
        }

        if ($this->filterPaymentMethod) {
            $query->where('payments.payment_method_id', $this->filterPaymentMethod);
        }

        if ($this->filterDateStart) {
            $query->whereDate('payments.payment_date', '>=', $this->filterDateStart);
        }

        if ($this->filterDateEnd) {
            $query->whereDate('payments.payment_date', '<=', $this->filterDateEnd);
        }

        switch ($this->sort) {
            case 'oldest':
                $query->orderBy('payments.payment_date', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('users.name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('users.name', 'desc');
                break;
            default:
                $query->orderBy('payments.payment_date', 'desc');
                break;
        }

        $payments = $query->paginate(10);

        $selectedPayment = $this->selectedPaymentId ? Payment::with(['registration.user', 'registration.room', 'registration.location', 'bill', 'paymentMethod'])->find($this->selectedPaymentId) : null;

        return view('livewire.payment-confirmation-manager', [
            'payments' => $payments,
            'selectedPayment' => $selectedPayment,
            'locations' => \App\Models\Location::orderBy('name')->get(),
            'paymentMethods' => \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get()
        ]);
    }
}
