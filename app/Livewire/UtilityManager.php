<?php

namespace App\Livewire;

use App\Models\Bill;
use App\Models\Location;
use App\Models\Registration;
use App\Models\Room;
use App\Models\UtilityReading;
use App\Models\UtilityType;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Events\NotificationSent;
use App\Events\DatabaseUpdated;

class UtilityManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $activeTab = 'readings'; // 'readings' or 'tariffs'

    // Tariff form fields
    public $isTariffModalOpen = false;
    public $tariffId;
    public $tariff_location_id;
    public $tariff_name;
    public $tariff_unit = 'kWh';
    public $tariff_rate_per_unit;
    public $tariff_is_active = true;

    // Reading form fields
    public $isReadingModalOpen = false;
    public $readingId;
    public $reading_location_id;
    public $reading_room_id;
    public $reading_utility_type_id;
    public $reading_date;
    public $period_month;
    public $period_year;
    public $previous_reading = 0;
    public $current_reading = 0;
    public $usage_amount = 0;
    public $rate_per_unit = 0;
    public $total_amount = 0;
    public $reading_image;
    public $existing_image_path;
    public $notes;
    public $auto_generate_bill = true;

    // Filters for readings
    public $filterLocation = '';
    public $filterMonth = '';
    public $filterYear = '';
    public $searchRoom = '';

    protected $listeners = ['echo:stats,DatabaseUpdated' => '$refresh'];

    public function mount()
    {
        $this->period_month = (int) now()->format('m');
        $this->period_year = (int) now()->format('Y');
        $this->reading_date = now()->format('Y-m-d');
        $this->filterMonth = (string) now()->format('m');
        $this->filterYear = (string) now()->format('Y');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // --- Tariff Actions ---
    public function openTariffModal($id = null)
    {
        $this->resetValidation();
        $this->resetTariffForm();

        if ($id) {
            $this->tariffId = $id;
            $tariff = UtilityType::findOrFail($id);
            $this->tariff_location_id = $tariff->location_id;
            $this->tariff_name = $tariff->name;
            $this->tariff_unit = $tariff->unit;
            $this->tariff_rate_per_unit = $tariff->rate_per_unit;
            $this->tariff_is_active = $tariff->is_active;
        } else {
            $firstLocation = Location::first();
            if ($firstLocation) {
                $this->tariff_location_id = $firstLocation->id;
            }
        }

        $this->isTariffModalOpen = true;
    }

    public function closeTariffModal()
    {
        $this->isTariffModalOpen = false;
        $this->resetTariffForm();
    }

    private function resetTariffForm()
    {
        $this->tariffId = null;
        $this->tariff_location_id = null;
        $this->tariff_name = '';
        $this->tariff_unit = 'kWh';
        $this->tariff_rate_per_unit = '';
        $this->tariff_is_active = true;
    }

    public function saveTariff()
    {
        $this->validate([
            'tariff_location_id' => 'required|exists:locations,id',
            'tariff_name' => 'required|string|max:255',
            'tariff_unit' => 'required|string|max:50',
            'tariff_rate_per_unit' => 'required|numeric|min:0',
            'tariff_is_active' => 'boolean',
        ], [
            'tariff_location_id.required' => 'Lokasi wajib dipilih.',
            'tariff_name.required' => 'Nama utilitas wajib diisi.',
            'tariff_unit.required' => 'Satuan wajib diisi.',
            'tariff_rate_per_unit.required' => 'Tarif per unit wajib diisi.',
            'tariff_rate_per_unit.numeric' => 'Tarif per unit harus berupa angka.',
        ]);

        $data = [
            'location_id' => $this->tariff_location_id,
            'name' => $this->tariff_name,
            'unit' => $this->tariff_unit,
            'rate_per_unit' => $this->tariff_rate_per_unit,
            'is_active' => $this->tariff_is_active,
        ];

        if ($this->tariffId) {
            $tariff = UtilityType::findOrFail($this->tariffId);
            $tariff->update($data);
            $msg = "Tarif utilitas '{$tariff->name}' berhasil diperbarui.";
        } else {
            $tariff = UtilityType::create($data);
            $msg = "Tarif utilitas '{$tariff->name}' berhasil ditambahkan.";
        }

        $this->dispatch('notify', message: $msg, type: 'success', hideInBell: true);
        broadcast(new NotificationSent($msg, 'success', hideInBell: true))->toOthers();
        DatabaseUpdated::dispatch();

        $this->closeTariffModal();
    }

    public function deleteTariff($id)
    {
        $tariff = UtilityType::findOrFail($id);
        if ($tariff->readings()->count() > 0) {
            $this->dispatch('notify', message: "Gagal menghapus! Tariff '{$tariff->name}' telah digunakan pada pencatatan meteran.", type: 'error');
            return;
        }

        $name = $tariff->name;
        $tariff->delete();

        $msg = "Tarif utilitas '{$name}' telah dihapus.";
        $this->dispatch('notify', message: $msg, type: 'warning', hideInBell: true);
        broadcast(new NotificationSent($msg, 'warning', hideInBell: true))->toOthers();
        DatabaseUpdated::dispatch();
    }

    // --- Reading Actions ---
    public function openReadingModal($id = null)
    {
        $this->resetValidation();
        $this->resetReadingForm();

        if ($id) {
            $this->readingId = $id;
            $reading = UtilityReading::findOrFail($id);
            $this->reading_location_id = $reading->location_id;
            $this->reading_room_id = $reading->room_id;
            $this->reading_utility_type_id = $reading->utility_type_id;
            $this->reading_date = $reading->reading_date ? $reading->reading_date->format('Y-m-d') : now()->format('Y-m-d');
            $this->period_month = $reading->period_month;
            $this->period_year = $reading->period_year;
            $this->previous_reading = $reading->previous_reading;
            $this->current_reading = $reading->current_reading;
            $this->usage_amount = $reading->usage_amount;
            $this->rate_per_unit = $reading->rate_per_unit;
            $this->total_amount = $reading->total_amount;
            $this->existing_image_path = $reading->image_path;
            $this->notes = $reading->notes;
            $this->auto_generate_bill = false;
        } else {
            $firstLocation = Location::first();
            if ($firstLocation) {
                $this->reading_location_id = $firstLocation->id;
                $this->updatedReadingLocationId($firstLocation->id);
            }
        }

        $this->isReadingModalOpen = true;
    }

    public function closeModal()
    {
        $this->isReadingModalOpen = false;
        $this->resetReadingForm();
    }

    private function resetReadingForm()
    {
        $this->readingId = null;
        $this->reading_location_id = null;
        $this->reading_room_id = null;
        $this->reading_utility_type_id = null;
        $this->reading_date = now()->format('Y-m-d');
        $this->period_month = (int) now()->format('m');
        $this->period_year = (int) now()->format('Y');
        $this->previous_reading = 0;
        $this->current_reading = 0;
        $this->usage_amount = 0;
        $this->rate_per_unit = 0;
        $this->total_amount = 0;
        $this->reading_image = null;
        $this->existing_image_path = null;
        $this->notes = '';
        $this->auto_generate_bill = true;
    }

    public function updatedReadingLocationId($locationId)
    {
        $this->reading_room_id = null;
        $this->reading_utility_type_id = null;
        $this->previous_reading = 0;

        $firstType = UtilityType::where('location_id', $locationId)->where('is_active', true)->first();
        if ($firstType) {
            $this->reading_utility_type_id = $firstType->id;
            $this->rate_per_unit = $firstType->rate_per_unit;
        }

        $firstRoom = Room::where('location_id', $locationId)->orderBy('room_number')->first();
        if ($firstRoom) {
            $this->reading_room_id = $firstRoom->id;
            $this->fetchPreviousReading();
        }
    }

    public function updatedReadingRoomId($roomId)
    {
        $this->fetchPreviousReading();
    }

    public function updatedReadingUtilityTypeId($typeId)
    {
        if ($typeId) {
            $type = UtilityType::find($typeId);
            if ($type) {
                $this->rate_per_unit = $type->rate_per_unit;
            }
        }
        $this->fetchPreviousReading();
        $this->calculateTotals();
    }

    public function updatedCurrentReading($val)
    {
        $this->calculateTotals();
    }

    public function updatedPreviousReading($val)
    {
        $this->calculateTotals();
    }

    public function updatedRatePerUnit($val)
    {
        $this->calculateTotals();
    }

    private function fetchPreviousReading()
    {
        if ($this->reading_room_id && $this->reading_utility_type_id && !$this->readingId) {
            $lastReading = UtilityReading::where('room_id', $this->reading_room_id)
                ->where('utility_type_id', $this->reading_utility_type_id)
                ->orderBy('reading_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastReading) {
                $this->previous_reading = $lastReading->current_reading;
            } else {
                $this->previous_reading = 0;
            }
            $this->calculateTotals();
        }
    }

    private function calculateTotals()
    {
        $prev = (float) $this->previous_reading;
        $curr = (float) $this->current_reading;
        $rate = (float) $this->rate_per_unit;

        $usage = max(0, $curr - $prev);
        $this->usage_amount = $usage;
        $this->total_amount = $usage * $rate;
    }

    public function saveReading()
    {
        $this->validate([
            'reading_location_id' => 'required|exists:locations,id',
            'reading_room_id' => 'required|exists:rooms,id',
            'reading_utility_type_id' => 'required|exists:utility_types,id',
            'reading_date' => 'required|date',
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2020',
            'previous_reading' => 'required|numeric|min:0',
            'current_reading' => 'required|numeric|gte:previous_reading',
            'rate_per_unit' => 'required|numeric|min:0',
            'reading_image' => 'nullable|image|max:2048',
        ], [
            'reading_location_id.required' => 'Lokasi wajib dipilih.',
            'reading_room_id.required' => 'Kamar wajib dipilih.',
            'reading_utility_type_id.required' => 'Jenis utilitas wajib dipilih.',
            'reading_date.required' => 'Tanggal pencatatan wajib diisi.',
            'previous_reading.required' => 'Angka awal meteran wajib diisi.',
            'current_reading.required' => 'Angka akhir meteran wajib diisi.',
            'current_reading.gte' => 'Angka akhir meteran tidak boleh lebih kecil dari angka awal.',
            'reading_image.max' => 'Foto bukti meteran maksimal 2 MB.',
        ]);

        $this->calculateTotals();

        // Check active registration for the room
        $activeReg = Registration::where('room_id', $this->reading_room_id)
            ->where('status', 'active')
            ->first();

        DB::transaction(function () use ($activeReg) {
            $imagePath = $this->existing_image_path;
            if ($this->reading_image) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $this->reading_image->store('utility_readings', 'public');
            }

            $utilityType = UtilityType::findOrFail($this->reading_utility_type_id);
            $room = Room::findOrFail($this->reading_room_id);

            $data = [
                'location_id' => $this->reading_location_id,
                'room_id' => $this->reading_room_id,
                'registration_id' => $activeReg ? $activeReg->id : null,
                'utility_type_id' => $this->reading_utility_type_id,
                'reading_date' => $this->reading_date,
                'period_month' => $this->period_month,
                'period_year' => $this->period_year,
                'previous_reading' => $this->previous_reading,
                'current_reading' => $this->current_reading,
                'usage_amount' => $this->usage_amount,
                'rate_per_unit' => $this->rate_per_unit,
                'total_amount' => $this->total_amount,
                'image_path' => $imagePath,
                'notes' => $this->notes,
            ];

            if ($this->readingId) {
                $reading = UtilityReading::findOrFail($this->readingId);
                $reading->update($data);
                $readingObj = $reading;

                if ($readingObj->bill_id && $readingObj->bill) {
                    $monthName = \Carbon\Carbon::createFromDate($this->period_year, $this->period_month, 1)->locale('id')->isoFormat('MMMM YYYY');
                    $description = "Tagihan Utilitas {$utilityType->name} Kamar {$room->room_number} ({$monthName}): {$this->usage_amount} {$utilityType->unit}";

                    $bill = $readingObj->bill;
                    $bill->update([
                        'amount' => $this->total_amount,
                        'description' => $description,
                    ]);

                    // Sync bill status
                    $paidAmount = $bill->paid_amount;
                    if ($paidAmount <= 0) {
                        $bill->status = 'Belum Lunas';
                    } elseif ($paidAmount < $bill->amount) {
                        $bill->status = 'Cicilan';
                    } else {
                        $bill->status = 'Lunas';
                    }
                    $bill->save();
                }
            } else {
                $readingObj = UtilityReading::create($data);
            }

            // Auto-generate Bill if enabled, registration is active, and total_amount > 0 and not already billed
            if ($this->auto_generate_bill && $activeReg && $this->total_amount > 0 && $readingObj->status !== 'billed') {
                $monthName = \Carbon\Carbon::createFromDate($this->period_year, $this->period_month, 1)->locale('id')->isoFormat('MMMM YYYY');
                $billNumber = 'UTIL-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
                $description = "Tagihan Utilitas {$utilityType->name} Kamar {$room->room_number} ({$monthName}): {$this->usage_amount} {$utilityType->unit}";

                $bill = Bill::create([
                    'registration_id' => $activeReg->id,
                    'bill_number' => $billNumber,
                    'description' => $description,
                    'discount' => 0,
                    'amount' => $this->total_amount,
                    'paid_amount' => 0,
                    'due_date' => now()->addDays(7),
                    'status' => 'Belum Lunas',
                ]);

                $readingObj->update([
                    'bill_id' => $bill->id,
                    'status' => 'billed',
                ]);
            }
        });

        $msg = $this->readingId ? "Pencatatan meteran berhasil diperbarui." : "Pencatatan meteran berhasil disimpan & tagihan telah dibuat.";
        $this->dispatch('notify', message: $msg, type: 'success', hideInBell: true);
        broadcast(new NotificationSent($msg, 'success', hideInBell: true))->toOthers();
        DatabaseUpdated::dispatch();

        $this->closeModal();
    }

    public function generateBillForReading($id)
    {
        $reading = UtilityReading::with(['room', 'registration', 'utilityType'])->findOrFail($id);

        if ($reading->status === 'billed' || $reading->bill_id) {
            $this->dispatch('notify', message: "Tagihan untuk catatan meteran ini sudah dibuat sebelumnya.", type: 'error');
            return;
        }

        if (!$reading->registration_id) {
            // Attempt to find active registration
            $activeReg = Registration::where('room_id', $reading->room_id)->where('status', 'active')->first();
            if (!$activeReg) {
                $this->dispatch('notify', message: "Gagal membuat tagihan! Kamar ini tidak memiliki penghuni aktif saat ini.", type: 'error');
                return;
            }
            $reading->registration_id = $activeReg->id;
        }

        $utilityType = $reading->utilityType;
        $room = $reading->room;
        $monthName = \Carbon\Carbon::createFromDate($reading->period_year, $reading->period_month, 1)->locale('id')->isoFormat('MMMM YYYY');
        $billNumber = 'UTIL-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
        $description = "Tagihan Utilitas {$utilityType->name} Kamar {$room->room_number} ({$monthName}): {$reading->usage_amount} {$utilityType->unit}";

        $bill = Bill::create([
            'registration_id' => $reading->registration_id,
            'bill_number' => $billNumber,
            'description' => $description,
            'discount' => 0,
            'amount' => $reading->total_amount,
            'paid_amount' => 0,
            'due_date' => now()->addDays(7),
            'status' => 'Belum Lunas',
        ]);

        $reading->update([
            'bill_id' => $bill->id,
            'status' => 'billed',
        ]);

        $msg = "Tagihan utilitas #{$bill->bill_number} sebesar Rp " . number_format($reading->total_amount, 0, ',', '.') . " berhasil dibuat.";
        $this->dispatch('notify', message: $msg, type: 'success', hideInBell: true);
        broadcast(new NotificationSent($msg, 'success', hideInBell: true))->toOthers();
        DatabaseUpdated::dispatch();
    }

    public function deleteReading($id)
    {
        $reading = UtilityReading::findOrFail($id);

        if ($reading->bill && $reading->bill->paid_amount > 0) {
            $this->dispatch('notify', message: "Gagal menghapus! Tagihan utilitas sudah ada pembayaran.", type: 'error');
            return;
        }

        DB::transaction(function () use ($reading) {
            if ($reading->image_path) {
                Storage::disk('public')->delete($reading->image_path);
            }

            if ($reading->bill) {
                $reading->bill->delete();
            }

            $reading->delete();
        });

        $msg = "Catatan meteran berhasil dihapus.";
        $this->dispatch('notify', message: $msg, type: 'warning', hideInBell: true);
        broadcast(new NotificationSent($msg, 'warning', hideInBell: true))->toOthers();
        DatabaseUpdated::dispatch();
    }

    public function updatingFilterLocation() { $this->resetPage(); }
    public function updatingFilterMonth() { $this->resetPage(); }
    public function updatingFilterYear() { $this->resetPage(); }
    public function updatingSearchRoom() { $this->resetPage(); }

    public function render()
    {
        $locations = Location::orderBy('name')->get();

        // Fetch Tariffs Query
        $tariffs = UtilityType::with('location')
            ->orderBy('location_id')
            ->orderBy('name')
            ->paginate(10, ['*'], 'tariffsPage');

        // Fetch Utility Readings Query
        $readingsQuery = UtilityReading::with(['location', 'room', 'registration.user', 'utilityType', 'bill']);

        if ($this->filterLocation) {
            $readingsQuery->where('location_id', $this->filterLocation);
        }

        if ($this->filterMonth) {
            $readingsQuery->where('period_month', $this->filterMonth);
        }

        if ($this->filterYear) {
            $readingsQuery->where('period_year', $this->filterYear);
        }

        if ($this->searchRoom) {
            $readingsQuery->whereHas('room', function ($q) {
                $q->where('room_number', 'like', '%' . $this->searchRoom . '%');
            });
        }

        $readings = $readingsQuery->orderBy('reading_date', 'desc')->orderBy('id', 'desc')->paginate(12, ['*'], 'readingsPage');

        // Available Utility Types & Rooms for Modal
        $modalUtilityTypes = collect();
        $modalRooms = collect();

        if ($this->reading_location_id) {
            $modalUtilityTypes = UtilityType::where('location_id', $this->reading_location_id)->where('is_active', true)->orderBy('name')->get();
            $modalRooms = Room::where('location_id', $this->reading_location_id)->orderBy('room_number')->get();
        }

        return view('livewire.utility-manager', [
            'locations' => $locations,
            'tariffs' => $tariffs,
            'readings' => $readings,
            'modalUtilityTypes' => $modalUtilityTypes,
            'modalRooms' => $modalRooms,
        ]);
    }
}
