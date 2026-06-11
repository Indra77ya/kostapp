<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\Bill;
use App\Models\Deposit;
use App\Models\Registration;
use App\Models\Location;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use Carbon\Carbon;

class PaymentManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';
    public $viewMode = 'residents'; // 'residents' or 'history'
    public $selectedRegistrationId;
    public $isModalOpen = false;
    public $isBillModalOpen = false;
    public $paymentId;
    public $billId;
    public $billsPerPage = 10;

    // List & Search & Filters
    public $search = '';
    public $filterLocation = '';
    public $filterDurationType = '';
    public $filterPaymentStatus = '';
    public $sort = 'name_asc';

    // Form fields (Payment)
    public $registration_id, $bill_id, $payment_method_id, $payment_number;
    public $payment_date, $amount, $notes, $status = 'Lunas';
    public $excess_amount = 0;
    public $proof_of_payment;
    public $sender_bank_name, $sender_account_number, $sender_account_name;

    // Form fields (Bill)
    public $bill_number, $bill_description, $bill_discount = 0, $bill_amount, $bill_due_date;

    public function getListeners()
    {
        return [
            'echo:stats,DatabaseUpdated' => '$refresh',
        ];
    }

    public function mount()
    {
        $this->payment_date = Carbon::now()->format('Y-m-d');
        $this->generatePaymentNumber();
    }

    public function generatePaymentNumber()
    {
        if (is_numeric($this->bill_id)) {
            $bill = Bill::find($this->bill_id);
            if ($bill) {
                $billSuffix = str_replace('BILL-', '', $bill->bill_number);
                $prefix = "PAY-{$billSuffix}-";

                $lastPayment = Payment::where('payment_number', 'like', $prefix . '%')
                    ->when($this->paymentId, fn($q) => $q->where('id', '!=', $this->paymentId))
                    ->orderBy('payment_number', 'desc')
                    ->first();

                if ($lastPayment) {
                    $lastNumber = (int) substr($lastPayment->payment_number, -2);
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }

                $this->payment_number = $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
                return;
            }
        }

        $date = Carbon::now()->format('dmY');
        $prefix = "PAY-{$date}-";

        $lastPayment = Payment::where('payment_number', 'like', $prefix . '%')
            ->when($this->paymentId, fn($q) => $q->where('id', '!=', $this->paymentId))
            ->whereRaw('length(payment_number) = ?', [strlen($prefix) + 4])
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $this->payment_number = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function selectRegistration($id)
    {
        $this->selectedRegistrationId = $id;
        $registration = Registration::find($id);
        if ($registration) {
            $registration->syncBills();

            $this->billsPerPage = $registration->getBatchSize();

            // Calculate last page for bills
            $billsCount = Bill::where('registration_id', $id)->count();
            $lastPage = ceil($billsCount / $this->billsPerPage);
            $this->setPage($lastPage, 'billsPage');

            // Calculate last page for payments
            $paymentsCount = Payment::where('registration_id', $id)->count();
            $lastPaymentPage = ceil($paymentsCount / 12); // Using 12 as requested
            $this->setPage($lastPaymentPage, 'paymentsPage');
        }
        $this->viewMode = 'history';
        $this->resetPage(); // Reset main paginator (for payments)
    }

    public function backToList()
    {
        $this->viewMode = 'residents';
        $this->selectedRegistrationId = null;
        $this->resetPage();
    }

    public function updatedRegistrationId($value)
    {
        $this->calculateAmountAndStatus();
    }

    public function updatedBillId($value)
    {
        $this->calculateAmountAndStatus();
        $this->generatePaymentNumber();
    }

    public function updatedAmount()
    {
        $this->calculateStatus();
    }

    private function calculateAmountAndStatus()
    {
        if (is_numeric($this->bill_id)) {
            $bill = Bill::find($this->bill_id);
            if ($bill) {
                $totalPaidOnThisBill = Payment::where('bill_id', $this->bill_id)
                    ->when($this->paymentId, fn($q) => $q->where('id', '!=', $this->paymentId))
                    ->sum('amount');

                $this->amount = $bill->amount - $totalPaidOnThisBill;
                if ($this->amount < 0) $this->amount = 0;
            }
        } elseif ($this->registration_id) {
            $registration = Registration::find($this->registration_id);
            if ($registration) {
                $totalPaid = Payment::where('registration_id', $this->registration_id)
                    ->when($this->paymentId, fn($q) => $q->where('id', '!=', $this->paymentId))
                    ->sum('amount');

                $this->amount = $registration->total_price - $totalPaid;
                if ($this->amount < 0) $this->amount = 0;
            }
        }
        $this->calculateStatus();
    }

    private function calculateStatus()
    {
        $this->excess_amount = 0;
        if ($this->bill_id === 'umum') {
            $this->status = "";
        } elseif ($this->bill_id === 'deposit') {
            $this->status = "Setor Deposit";
        } elseif (is_numeric($this->bill_id)) {
            $bill = Bill::find($this->bill_id);
            if (!$bill) return;

            $totalPaidPrev = Payment::where('bill_id', $this->bill_id)
                ->when($this->paymentId, fn($q) => $q->where('id', '!=', $this->paymentId))
                ->sum('amount');

            $currentAmount = (float) ($this->amount ?: 0);
            $totalPaidNow = $totalPaidPrev + $currentAmount;
            $totalBill = (float) $bill->amount;

            $diff = $totalBill - $totalPaidNow;

            if ($diff > 0) {
                $formattedDiff = number_format($diff, 0, ',', '.');
                $this->status = "Belum Lunas (Sisa: Rp {$formattedDiff})";
            } elseif ($diff < 0) {
                $this->excess_amount = abs($diff);
                $formattedDiff = number_format($this->excess_amount, 0, ',', '.');
                $this->status = "Lunas (Deposit: Rp {$formattedDiff})";
            } else {
                $this->status = "Lunas";
            }
        } else {
            $this->status = "";
        }
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->paymentId = $id;
            $payment = Payment::find($id);
            $this->registration_id = $payment->registration_id;

            if ($payment->bill_id) {
                $this->bill_id = $payment->bill_id;
            } else {
                if ($payment->notes && strpos($payment->notes, '[DEPOSIT]') !== false) {
                    $this->bill_id = 'deposit';
                } else {
                    $this->bill_id = 'umum';
                }
            }
            $this->payment_method_id = $payment->payment_method_id;
            $this->payment_number = $payment->payment_number;
            $this->payment_date = $payment->payment_date->format('Y-m-d');
            $this->amount = $payment->amount;
            $this->notes = $payment->notes;
            $this->sender_bank_name = $payment->sender_bank_name;
            $this->sender_account_number = $payment->sender_account_number;
            $this->sender_account_name = $payment->sender_account_name;
            $this->calculateStatus();
        } else {
            if ($this->viewMode === 'history' && $this->selectedRegistrationId) {
                $this->registration_id = $this->selectedRegistrationId;
                $this->calculateAmountAndStatus();
            }
            $this->generatePaymentNumber();
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function openBillModal($id = null)
    {
        $this->resetValidation();
        $this->resetBillForm();

        if ($id) {
            $this->billId = $id;
            $bill = Bill::find($id);
            $this->bill_number = $bill->bill_number;
            $this->bill_description = $bill->description;
            $this->bill_discount = $bill->discount;
            $this->bill_amount = $bill->amount;
            $this->bill_due_date = $bill->due_date->format('Y-m-d');
        } else {
            $this->bill_due_date = Carbon::now()->format('Y-m-d');
            $this->generateBillNumber();
        }

        $this->isBillModalOpen = true;
    }

    public function closeBillModal()
    {
        $this->isBillModalOpen = false;
        $this->resetBillForm();
    }

    private function generateBillNumber()
    {
        $date = Carbon::now()->format('dmY');
        $prefix = "BILL-M-{$date}-"; // M for Manual

        $lastBill = Bill::where('bill_number', 'like', $prefix . '%')
            ->orderBy('bill_number', 'desc')
            ->first();

        if ($lastBill) {
            $lastNumber = (int) substr($lastBill->bill_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $this->bill_number = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function resetForm()
    {
        $this->paymentId = null;
        $this->registration_id = null;
        $this->bill_id = 'umum';
        $this->payment_method_id = null;
        $this->payment_number = null;
        $this->payment_date = Carbon::now()->format('Y-m-d');
        $this->amount = null;
        $this->notes = null;
        $this->status = '';
        $this->excess_amount = 0;
        $this->proof_of_payment = null;
        $this->sender_bank_name = null;
        $this->sender_account_number = null;
        $this->sender_account_name = null;
        $this->generatePaymentNumber();
    }

    private function resetBillForm()
    {
        $this->billId = null;
        $this->bill_number = null;
        $this->bill_description = null;
        $this->bill_discount = 0;
        $this->bill_amount = null;
        $this->bill_due_date = Carbon::now()->format('Y-m-d');
    }

    public function savePayment()
    {
        $rules = [
            'registration_id' => 'required|exists:registrations,id',
            'bill_id' => 'nullable',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'proof_of_payment' => 'nullable|image|max:2048',
        ];

        $this->validate($rules);

        DB::transaction(function () {
            $actualBillId = is_numeric($this->bill_id) ? $this->bill_id : null;
            $isDeposit = $this->bill_id === 'deposit';

            $cleanNotes = str_replace('[DEPOSIT] ', '', $this->notes ?: '');

            $paymentStatus = $this->status;
            if (!$actualBillId && !$isDeposit && !$paymentStatus) {
                $paymentStatus = "Pembayaran Umum";
            }

            $data = [
                'registration_id' => $this->registration_id,
                'bill_id' => $actualBillId,
                'payment_method_id' => $this->payment_method_id,
                'payment_number' => $this->payment_number,
                'payment_date' => $this->payment_date,
                'amount' => $this->amount,
                'notes' => ($isDeposit ? '[DEPOSIT] ' : '') . $cleanNotes,
                'status' => $paymentStatus ?: 'Lunas',
                'sender_bank_name' => $this->sender_bank_name,
                'sender_account_number' => $this->sender_account_number,
                'sender_account_name' => $this->sender_account_name,
            ];

            $oldBillId = null;
            if ($this->paymentId) {
                $payment = Payment::find($this->paymentId);
                $oldBillId = $payment->bill_id;
                if ($this->proof_of_payment) {
                    if ($payment->proof_of_payment) Storage::disk('public')->delete($payment->proof_of_payment);
                    $data['proof_of_payment'] = $this->proof_of_payment->store('payments', 'public');
                }
                $payment->update($data);
            } else {
                if ($this->proof_of_payment) {
                    $data['proof_of_payment'] = $this->proof_of_payment->store('payments', 'public');
                }
                $payment = Payment::create($data);
            }

            // Update Bill status and paid_amount
            if ($this->bill_id) {
                $this->syncBillStatus($this->bill_id);
            }
            if ($oldBillId && $oldBillId != $this->bill_id) {
                $this->syncBillStatus($oldBillId);
            }

            $this->syncDeposit($payment);
        });

        $message = "Pembayaran berhasil disimpan.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();

        $registration = Registration::find($this->registration_id);
        DatabaseUpdated::dispatch($registration ? $registration->user_id : null);

        $this->closeModal();
    }

    public function saveBill()
    {
        $rules = [
            'bill_number' => 'required|string|unique:bills,bill_number' . ($this->billId ? ',' . $this->billId : ''),
            'bill_description' => 'required|string',
            'bill_amount' => 'required|numeric|min:0',
            'bill_due_date' => 'required|date',
        ];

        $this->validate($rules);

        $data = [
            'registration_id' => $this->selectedRegistrationId,
            'bill_number' => $this->bill_number,
            'description' => $this->bill_description,
            'discount' => $this->bill_discount,
            'amount' => $this->bill_amount,
            'due_date' => $this->bill_due_date,
        ];

        if ($this->billId) {
            Bill::find($this->billId)->update($data);
            $this->syncBillStatus($this->billId);
        } else {
            Bill::create($data);
        }

        $message = "Tagihan berhasil disimpan.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();

        $registration = Registration::find($this->selectedRegistrationId);
        DatabaseUpdated::dispatch($registration ? $registration->user_id : null);

        $this->closeBillModal();
    }

    public function deleteBill($id)
    {
        $bill = Bill::find($id);
        $tenantId = $bill ? $bill->registration->user_id : null;
        $bill->delete();

        DatabaseUpdated::dispatch($tenantId);
        $message = "Tagihan berhasil dihapus.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();
    }

    private function syncDeposit($payment)
    {
        // Delete existing deposit linked to this payment
        Deposit::where('payment_id', $payment->id)->delete();

        if ($payment->status === 'Menunggu Konfirmasi' || $payment->status === 'Ditolak') {
            return;
        }

        $pm = PaymentMethod::find($payment->payment_method_id);
        if ($pm && $pm->name === 'Saldo Deposit') {
            // Debit deposit (usage)
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

        // Handle credit deposit (overpayment or general deposit)
        $excess = 0;
        if ($payment->bill_id) {
            $bill = $payment->bill;
            $totalPaidOnBill = Payment::where('bill_id', $payment->bill_id)
                ->where('status', '!=', 'Menunggu Konfirmasi')
                ->where('status', '!=', 'Ditolak')
                ->sum('amount');
            $excess = $totalPaidOnBill - $bill->amount;
        } else {
            // Only if it's marked as [DEPOSIT]
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

    public function deletePayment($id)
    {
        $payment = Payment::find($id);
        $billId = $payment->bill_id;
        if ($payment->proof_of_payment) {
            Storage::disk('public')->delete($payment->proof_of_payment);
        }
        $payment->delete();

        if ($billId) {
            $this->syncBillStatus($billId);
        }

        DatabaseUpdated::dispatch($payment->registration->user_id);
        $message = "Pembayaran berhasil dihapus.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterLocation() { $this->resetPage(); }
    public function updatingFilterDurationType() { $this->resetPage(); }
    public function updatingFilterPaymentStatus() { $this->resetPage(); }
    public function updatingSort() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterLocation', 'filterDurationType', 'filterPaymentStatus', 'sort']);
        $this->sort = 'name_asc';
        $this->resetPage();
    }

    public function render()
    {
        return $this->renderView();
    }

    private function renderView()
    {
        if ($this->viewMode === 'history') {
            $registration = Registration::with('user', 'room', 'location')->find($this->selectedRegistrationId);

            $bills = Bill::where('registration_id', $this->selectedRegistrationId)
                ->orderBy('due_date', 'asc')
                ->paginate($this->billsPerPage, ['*'], 'billsPage');

            $payments = Payment::with(['paymentMethod', 'bill'])
                ->where('registration_id', $this->selectedRegistrationId)
            ->orderBy('created_at', 'asc')
            ->paginate(12, ['*'], 'paymentsPage');

            return view('livewire.payment-manager', [
                'registration' => $registration,
                'bills' => $bills,
                'payments' => $payments,
                'paymentMethods' => PaymentMethod::where('is_active', true)->get(),
            ]);
        }

        $query = Registration::query()
            ->select('registrations.*')
            ->with('user', 'location', 'room')
            ->withSum('bills as total_bill', 'amount')
            ->withSum(['payments as total_paid' => function($q) {
                $q->where('status', '!=', 'Menunggu Konfirmasi');
            }], 'amount')
            ->where('registrations.status', 'active');

        if ($this->search) {
            $query->whereHas('user', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterLocation) {
            $query->where('location_id', $this->filterLocation);
        }

        if ($this->filterDurationType) {
            $query->where('duration_type', $this->filterDurationType);
        }

        if ($this->filterPaymentStatus === 'lunas') {
            $query->whereRaw('(select COALESCE(sum(amount - paid_amount), 0) from bills where bills.registration_id = registrations.id and amount > paid_amount) = 0');
        } elseif ($this->filterPaymentStatus === 'tunggakan') {
            $query->whereRaw('(select COALESCE(sum(amount - paid_amount), 0) from bills where bills.registration_id = registrations.id and amount > paid_amount) > 0');
        }

        // Sorting
        switch ($this->sort) {
            case 'name_desc':
                $query->join('users', 'registrations.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'desc');
                break;
            case 'balance_desc':
                $query->orderByRaw('((select COALESCE(sum(amount), 0) from bills where bills.registration_id = registrations.id) - (select COALESCE(sum(amount), 0) from payments where payments.registration_id = registrations.id and status != "Menunggu Konfirmasi")) DESC');
                break;
            case 'balance_asc':
                $query->orderByRaw('((select COALESCE(sum(amount), 0) from bills where bills.registration_id = registrations.id) - (select COALESCE(sum(amount), 0) from payments where payments.registration_id = registrations.id and status != "Menunggu Konfirmasi")) ASC');
                break;
            case 'name_asc':
            default:
                $query->join('users', 'registrations.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'asc');
                break;
        }

        $registrations = $query->paginate(10);

        // Map aggregated sums to friendly names
        foreach ($registrations as $reg) {
            $reg->total_bill = $reg->total_bill ?: 0;
            $reg->paid_amount = $reg->total_paid ?: 0;
        }

        return view('livewire.payment-manager', [
            'registrations' => $registrations,
            'locations' => Location::all(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->get(),
        ]);
    }
}
