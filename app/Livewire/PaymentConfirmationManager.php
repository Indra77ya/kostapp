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
        $payments = Payment::with(['registration.user', 'bill', 'paymentMethod'])
            ->where('status', 'Menunggu Konfirmasi')
            ->whereHas('registration.user', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        $selectedPayment = $this->selectedPaymentId ? Payment::with(['registration.user', 'registration.room', 'registration.location', 'bill', 'paymentMethod'])->find($this->selectedPaymentId) : null;

        return view('livewire.payment-confirmation-manager', [
            'payments' => $payments,
            'selectedPayment' => $selectedPayment
        ]);
    }
}
