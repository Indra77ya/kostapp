<?php

namespace App\Livewire;

use App\Models\Payment;
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
    public $paymentId;

    // List & Search & Filters
    public $search = '';
    public $filterLocation = '';
    public $filterDurationType = '';

    // Form fields
    public $registration_id, $payment_method_id, $payment_number;
    public $payment_date, $amount, $notes, $status = 'Lunas';
    public $proof_of_payment;

    protected $listeners = ['echo:stats,DatabaseUpdated' => '$refresh'];

    public function mount()
    {
        $this->payment_date = Carbon::now()->format('Y-m-d');
        $this->generatePaymentNumber();
    }

    public function generatePaymentNumber()
    {
        $date = Carbon::now()->format('dmY');
        $prefix = "PAY-{$date}-";

        $lastPayment = Payment::where('payment_number', 'like', $prefix . '%')
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
        $this->viewMode = 'history';
        $this->resetPage();
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

    public function updatedAmount()
    {
        $this->calculateStatus();
    }

    private function calculateAmountAndStatus()
    {
        if ($this->registration_id) {
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
        if (!$this->registration_id) {
            $this->status = 'Lunas';
            return;
        }

        $registration = Registration::find($this->registration_id);
        if (!$registration) return;

        $totalPaidPrev = Payment::where('registration_id', $this->registration_id)
            ->when($this->paymentId, fn($q) => $q->where('id', '!=', $this->paymentId))
            ->sum('amount');

        $currentAmount = (float) ($this->amount ?: 0);
        $totalPaidNow = $totalPaidPrev + $currentAmount;
        $totalBill = (float) $registration->total_price;

        $diff = $totalBill - $totalPaidNow;

        if ($diff > 0) {
            $formattedDiff = number_format($diff, 0, ',', '.');
            $this->status = "Belum Lunas (Sisa: Rp {$formattedDiff})";
        } elseif ($diff < 0) {
            $formattedDiff = number_format(abs($diff), 0, ',', '.');
            $this->status = "Lunas (Kelebihan: Rp {$formattedDiff})";
        } else {
            $this->status = "Lunas";
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
            $this->payment_method_id = $payment->payment_method_id;
            $this->payment_number = $payment->payment_number;
            $this->payment_date = $payment->payment_date->format('Y-m-d');
            $this->amount = $payment->amount;
            $this->notes = $payment->notes;
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

    private function resetForm()
    {
        $this->paymentId = null;
        $this->registration_id = null;
        $this->payment_method_id = null;
        $this->payment_number = null;
        $this->payment_date = Carbon::now()->format('Y-m-d');
        $this->amount = null;
        $this->notes = null;
        $this->status = 'Lunas';
        $this->proof_of_payment = null;
        $this->generatePaymentNumber();
    }

    public function savePayment()
    {
        $rules = [
            'registration_id' => 'required|exists:registrations,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'proof_of_payment' => 'nullable|image|max:2048',
        ];

        $this->validate($rules);

        DB::transaction(function () {
            $data = [
                'registration_id' => $this->registration_id,
                'payment_method_id' => $this->payment_method_id,
                'payment_number' => $this->payment_number,
                'payment_date' => $this->payment_date,
                'amount' => $this->amount,
                'notes' => $this->notes,
                'status' => $this->status,
            ];

            if ($this->paymentId) {
                $payment = Payment::find($this->paymentId);
                if ($this->proof_of_payment) {
                    if ($payment->proof_of_payment) Storage::disk('public')->delete($payment->proof_of_payment);
                    $data['proof_of_payment'] = $this->proof_of_payment->store('payments', 'public');
                }
                $payment->update($data);
            } else {
                if ($this->proof_of_payment) {
                    $data['proof_of_payment'] = $this->proof_of_payment->store('payments', 'public');
                }
                Payment::create($data);
            }
        });

        $message = "Pembayaran berhasil disimpan.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();
        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function deletePayment($id)
    {
        $payment = Payment::find($id);
        if ($payment->proof_of_payment) {
            Storage::disk('public')->delete($payment->proof_of_payment);
        }
        $payment->delete();

        DatabaseUpdated::dispatch();
        $message = "Pembayaran berhasil dihapus.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterLocation() { $this->resetPage(); }
    public function updatingFilterDurationType() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterLocation', 'filterDurationType']);
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
            $payments = Payment::with('paymentMethod')
                ->where('registration_id', $this->selectedRegistrationId)
                ->latest()
                ->paginate(10);

            return view('livewire.payment-manager', [
                'registration' => $registration,
                'payments' => $payments,
                'paymentMethods' => PaymentMethod::where('is_active', true)->get(),
            ]);
        }

        $query = Registration::with('user', 'location', 'room')
            ->withSum('payments', 'amount')
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

        $registrations = $query->latest()->paginate(10);

        // Calculate paid amount for each registration
        foreach ($registrations as $reg) {
            $reg->paid_amount = $reg->payments_sum_amount ?: 0;
            $reg->remaining_amount = $reg->total_price - $reg->paid_amount;
        }

        return view('livewire.payment-manager', [
            'registrations' => $registrations,
            'locations' => Location::all(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->get(),
        ]);
    }
}
