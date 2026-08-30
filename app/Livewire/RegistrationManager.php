<?php

namespace App\Livewire;

use App\Models\Registration;
use App\Models\EmergencyContact;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use App\Helpers\BroadcastHelper;
use App\Models\Bill;
use Carbon\Carbon;

class RegistrationManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';
    public $isModalOpen = false;
    public $isSuccessModalOpen = false;
    public $registrationId;
    public $newlyCreatedRegistrationId;

    // List & Search & Filters
    public $search = '';
    public $filterLocation = '';
    public $filterDateStart = '';
    public $filterDateEnd = '';
    public $filterDurationType = '';
    public $filterIsOpenEnded = '';

    // Form fields - Basic
    public $location_id, $room_id, $registration_number;
    public $registration_date, $stay_start_date, $currentRoomId;
    public $duration_type = 'monthly', $duration_value = 1, $is_open_ended = false;

    // Financials
    public $room_price = 0, $discount_type = 'fixed', $discount_value = 0, $discount_duration = 0, $total_price = 0;
    public $initial_deposit = 0;
    public $is_discount_open_ended = false;
    public $room_facilities = '';

    // Personal Info
    public $name, $email, $phone_number, $address;
    public $identity_type = 'KTP', $identity_number, $gender = 'Laki-laki';
    public $birth_place, $birth_date;

    // Photos
    public $photo_self, $photo_identity, $photo_family_card;
    public $existing_photo_self, $existing_photo_identity, $existing_photo_family_card;
    public $family_card_number;

    // Organization Info
    public $institution_name, $institution_address, $institution_phone;

    // Emergency Contacts
    public $emergency_contacts = [];

    protected $listeners = ['echo:stats,DatabaseUpdated' => '$refresh'];

    public function mount()
    {
        $this->registration_date = Carbon::now()->format('Y-m-d');
        $this->generateRegistrationNumber();
    }

    public function addEmergencyContact()
    {
        $this->emergency_contacts[] = [
            'name' => '',
            'relationship' => '',
            'identity_number' => '',
            'phone_number' => '',
            'email' => '',
            'gender' => 'Laki-laki',
            'birth_place' => '',
            'birth_date' => '',
            'address' => '',
        ];
    }

    public function removeEmergencyContact($index)
    {
        unset($this->emergency_contacts[$index]);
        $this->emergency_contacts = array_values($this->emergency_contacts);
    }

    public function updatedLocationId($value)
    {
        $this->room_id = null;
        $this->room_price = 0;
        $this->room_facilities = '';
        $this->calculateTotal();
    }

    public function updatedRoomId($value)
    {
        if ($value) {
            $room = Room::find($value);
            $this->setRoomPriceByDuration($room);
            $this->room_facilities = $room->facilities;
        } else {
            $this->room_price = 0;
            $this->room_facilities = '';
        }
        $this->calculateTotal();
    }

    public function updatedDurationType()
    {
        if ($this->room_id) {
            $room = Room::find($this->room_id);
            $this->setRoomPriceByDuration($room);
        }
        $this->calculateTotal();
    }

    public function updatedDurationValue()
    {
        $this->calculateTotal();
    }

    public function updatedIsOpenEnded($value)
    {
        if ($value) {
            $this->duration_value = 1;
        }
        $this->calculateTotal();
    }

    private function setRoomPriceByDuration($room)
    {
        if (!$room) return;

        switch ($this->duration_type) {
            case 'daily':
                $this->room_price = $room->price_daily ?: $room->price_monthly;
                break;
            case 'weekly':
                $this->room_price = $room->price_weekly ?: $room->price_monthly;
                break;
            case 'yearly':
                $this->room_price = $room->price_yearly ?: $room->price_monthly;
                break;
            default:
                $this->room_price = $room->price_monthly;
                break;
        }
    }

    public function updatedDiscountType() { $this->calculateTotal(); }
    public function updatedDiscountValue() { $this->calculateTotal(); }
    public function updatedDiscountDuration() { $this->calculateTotal(); }
    public function updatedIsDiscountOpenEnded($value)
    {
        if ($value) {
            $this->discount_duration = 0;
        }
        $this->calculateTotal();
    }
    public function updatedRoomPrice() { $this->calculateTotal(); }

    public function calculateTotal()
    {
        $price = (float) $this->room_price;
        $duration = (int) ($this->duration_value ?: 1);
        $discountVal = (float) ($this->discount_value ?: 0);
        $discountDur = (int) ($this->discount_duration ?: 0);

        if ($this->is_open_ended) {
            $duration = Registration::getBatchSizeByType($this->duration_type);
        }

        $total = 0;
        for ($i = 1; $i <= $duration; $i++) {
            $currentPeriodPrice = $price;
            if ($this->is_discount_open_ended || $i <= $discountDur) {
                if ($this->discount_type === 'percent') {
                    $currentPeriodPrice -= ($price * ($discountVal / 100));
                } else {
                    $currentPeriodPrice -= $discountVal;
                }
            }
            $total += max(0, $currentPeriodPrice);
        }

        $this->total_price = $total;
    }

    public function generateRegistrationNumber()
    {
        $date = Carbon::now()->format('dmY');
        $prefix = "REG-{$date}-";

        $lastRegistration = Registration::where('registration_number', 'like', $prefix . '%')
            ->orderBy('registration_number', 'desc')
            ->first();

        if ($lastRegistration) {
            $lastNumber = (int) substr($lastRegistration->registration_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $this->registration_number = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->registrationId = $id;
            $reg = Registration::with('emergencyContacts', 'user')->find($id);
            $this->location_id = $reg->location_id;
            $this->room_id = $reg->room_id;
            $this->registration_number = $reg->registration_number;
            $this->registration_date = $reg->registration_date->format('Y-m-d');
            $this->stay_start_date = $reg->stay_start_date->format('Y-m-d');
            $this->duration_type = $reg->duration_type;
            $this->duration_value = $reg->duration_value;
            $this->is_open_ended = (bool) $reg->is_open_ended;
            $this->room_price = $reg->room_price;
            $this->discount_type = $reg->discount_type;
            $this->discount_value = $reg->discount_value;
            $this->discount_duration = $reg->discount_duration;
            $this->is_discount_open_ended = (bool) $reg->is_discount_open_ended;
            $this->total_price = $reg->total_price;
            $this->initial_deposit = $reg->initial_deposit;

            $this->name = $reg->user->name;
            $this->email = $reg->user->email;
            $this->phone_number = $reg->user->phone_number;
            $this->address = $reg->user->address;

            $this->identity_type = $reg->identity_type;
            $this->identity_number = $reg->identity_number;
            $this->gender = $reg->gender;
            $this->birth_place = $reg->birth_place;
            $this->birth_date = $reg->birth_date->format('Y-m-d');
            $this->family_card_number = $reg->family_card_number;

            $this->existing_photo_self = $reg->photo_self;
            $this->existing_photo_identity = $reg->photo_identity;
            $this->existing_photo_family_card = $reg->photo_family_card;

            $this->institution_name = $reg->institution_name;
            $this->institution_address = $reg->institution_address;
            $this->institution_phone = $reg->institution_phone;

            $this->emergency_contacts = $reg->emergencyContacts->map(function ($contact) {
                $contactArr = $contact->toArray();
                if ($contactArr['birth_date']) {
                    $contactArr['birth_date'] = Carbon::parse($contactArr['birth_date'])->format('Y-m-d');
                }
                return $contactArr;
            })->toArray();

            $this->currentRoomId = $reg->room_id;
            $room = Room::find($this->room_id);
            $this->room_facilities = $room->facilities;
        } else {
            $this->generateRegistrationNumber();
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function closeSuccessModal()
    {
        $this->isSuccessModalOpen = false;
        $this->newlyCreatedRegistrationId = null;
    }

    private function resetForm()
    {
        $this->registrationId = null;
        $this->currentRoomId = null;
        $this->location_id = null;
        $this->room_id = null;
        $this->registration_number = null;
        $this->registration_date = Carbon::now()->format('Y-m-d');
        $this->stay_start_date = null;
        $this->duration_type = 'monthly';
        $this->duration_value = 1;
        $this->is_open_ended = false;
        $this->room_price = 0;
        $this->discount_type = 'fixed';
        $this->discount_value = 0;
        $this->discount_duration = 0;
        $this->is_discount_open_ended = false;
        $this->total_price = 0;
        $this->initial_deposit = 0;
        $this->room_facilities = '';

        $this->name = '';
        $this->email = '';
        $this->phone_number = '';
        $this->address = '';
        $this->identity_type = 'KTP';
        $this->identity_number = '';
        $this->gender = 'Laki-laki';
        $this->birth_place = '';
        $this->birth_date = '';
        $this->photo_self = null;
        $this->photo_identity = null;
        $this->photo_family_card = null;
        $this->existing_photo_self = null;
        $this->existing_photo_identity = null;
        $this->existing_photo_family_card = null;
        $this->family_card_number = '';

        $this->institution_name = '';
        $this->institution_address = '';
        $this->institution_phone = '';

        $this->emergency_contacts = [];
        $this->generateRegistrationNumber();
    }

    public function saveRegistration()
    {
        $rules = [
            'location_id' => 'required|exists:locations,id',
            'room_id' => 'required|exists:rooms,id',
            'registration_date' => 'required|date',
            'stay_start_date' => 'required|date',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . ($this->registrationId ? ',' . Registration::find($this->registrationId)->user_id : ''),
            'identity_number' => 'required|string',
            'gender' => 'required',
            'birth_date' => 'required|date',
            'photo_self' => $this->registrationId ? 'nullable|image|max:2048' : 'required|image|max:2048',
            'photo_identity' => $this->registrationId ? 'nullable|image|max:2048' : 'required|image|max:2048',
            'photo_family_card' => 'nullable|image|max:2048',
        ];

        if ($this->discount_value > 0 && !$this->is_discount_open_ended) {
            $rules['discount_duration'] = 'required|integer|min:1';
        }

        // Only validate emergency contacts if they exist
        if (!empty($this->emergency_contacts)) {
            $rules['emergency_contacts.*.name'] = 'required|string';
            $rules['emergency_contacts.*.relationship'] = 'required|string';
            $rules['emergency_contacts.*.phone_number'] = 'required|string';
        }

        $this->validate($rules);

        $regId = null;
        DB::transaction(function () use (&$regId) {
            // 1. Handle User
            if ($this->registrationId) {
                $registration = Registration::find($this->registrationId);
                $user = $registration->user;
                $userData = [
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone_number' => $this->phone_number,
                    'address' => $this->address,
                ];
                $user->update($userData);
            } else {
                $password = '12345678';
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($password),
                    'password_plain' => $password,
                    'phone_number' => $this->phone_number,
                    'address' => $this->address,
                ]);
                $user->assignRole('tenant');
            }

            // Handle Room Status Transition
            if ($this->registrationId) {
                $registration = Registration::find($this->registrationId);
                if ($registration->room_id != $this->room_id) {
                    // Revert old room status
                    Room::where('id', $registration->room_id)->update(['status' => 'available']);
                    // Set new room status
                    Room::where('id', $this->room_id)->update(['status' => 'occupied']);
                }
            } else {
                // Set room status to occupied for new registration
                Room::where('id', $this->room_id)->update(['status' => 'occupied']);
            }

            // 2. Handle Photos
            $data = [
                'user_id' => $user->id,
                'location_id' => $this->location_id,
                'room_id' => $this->room_id,
                'registration_number' => $this->registration_number,
                'registration_date' => $this->registration_date,
                'stay_start_date' => $this->stay_start_date,
                'duration_type' => $this->duration_type,
                'duration_value' => $this->duration_value,
                'is_open_ended' => $this->is_open_ended,
                'room_price' => $this->room_price,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'discount_duration' => $this->discount_duration,
                'is_discount_open_ended' => $this->is_discount_open_ended,
                'total_price' => $this->total_price,
                'initial_deposit' => $this->initial_deposit,
                'identity_type' => $this->identity_type,
                'identity_number' => $this->identity_number,
                'gender' => $this->gender,
                'birth_place' => $this->birth_place,
                'birth_date' => $this->birth_date,
                'family_card_number' => $this->family_card_number,
                'institution_name' => $this->institution_name,
                'institution_address' => $this->institution_address,
                'institution_phone' => $this->institution_phone,
            ];

            if ($this->registrationId) {
                if ($this->photo_self) {
                    if ($registration->photo_self) Storage::disk('public')->delete($registration->photo_self);
                    $path = $this->photo_self->store('registrations/self', 'public');
                    $data['photo_self'] = $path;
                    $user->update(['avatar' => $path]);
                }
                if ($this->photo_identity) {
                    if ($registration->photo_identity) Storage::disk('public')->delete($registration->photo_identity);
                    $data['photo_identity'] = $this->photo_identity->store('registrations/identity', 'public');
                }
                if ($this->photo_family_card) {
                    if ($registration->photo_family_card) Storage::disk('public')->delete($registration->photo_family_card);
                    $data['photo_family_card'] = $this->photo_family_card->store('registrations/family_card', 'public');
                }
                $registration->update($data);

                // Sync bills for updated registration
                $registration->syncBills();
            } else {
                if ($this->photo_self) {
                    $path = $this->photo_self->store('registrations/self', 'public');
                    $data['photo_self'] = $path;
                    $user->update(['avatar' => $path]);
                }
                if ($this->photo_identity) $data['photo_identity'] = $this->photo_identity->store('registrations/identity', 'public');
                if ($this->photo_family_card) $data['photo_family_card'] = $this->photo_family_card->store('registrations/family_card', 'public');
                $registration = Registration::create($data);

                // Auto-generate bills for new registration
                $this->generateBillsForRegistration($registration);
            }
            $regId = $registration->id;

            // 3. Emergency Contacts
            $registration->emergencyContacts()->delete();
            foreach ($this->emergency_contacts as $contact) {
                // Ensure birth_date is null if empty string
                if (isset($contact['birth_date']) && $contact['birth_date'] === '') {
                    $contact['birth_date'] = null;
                }
                $registration->emergencyContacts()->create($contact);
            }
        });

        $registration = Registration::find($regId);
        $message = "Check in {$this->name} berhasil disimpan.";
        $type = 'success';

        $isNew = !$this->registrationId;

        $this->dispatch('notify', message: $message, type: $type);
        BroadcastHelper::safeBroadcast(new NotificationSent($message, $type), toOthers: true);
        DatabaseUpdated::dispatch($registration->user_id);
        $this->closeModal();

        if ($isNew) {
            $this->newlyCreatedRegistrationId = $regId;
            $this->isSuccessModalOpen = true;
        }
    }

    private function generateBillsForRegistration($registration)
    {
        $registration->syncBills();
    }

    public function deleteRegistration($id)
    {
        $reg = Registration::withCount('payments')->find($id);

        if ($reg->payments_count > 0) {
            $this->dispatch('notify', message: "Data check in {$reg->user->name} tidak bisa dihapus karena sudah ada riwayat pembayaran.", type: 'warning');
            return;
        }

        $name = $reg->user->name;
        $userId = $reg->user_id;

        DB::transaction(function() use ($reg, $userId) {
            // Revert room status to available
            Room::where('id', $reg->room_id)->update(['status' => 'available']);

            // Delete associated files
            if ($reg->photo_self) Storage::disk('public')->delete($reg->photo_self);
            if ($reg->photo_identity) Storage::disk('public')->delete($reg->photo_identity);
            if ($reg->photo_family_card) Storage::disk('public')->delete($reg->photo_family_card);

            // Delete registration
            $reg->delete();

            // Automatically delete the associated User (tenant)
            User::where('id', $userId)->delete();
        });

        DatabaseUpdated::dispatch();
        $message = "Check in dan data penghuni {$name} berhasil dihapus.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        BroadcastHelper::safeBroadcast(new NotificationSent($message, $type), toOthers: true);
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterLocation() { $this->resetPage(); }
    public function updatingFilterDateStart() { $this->resetPage(); }
    public function updatingFilterDateEnd() { $this->resetPage(); }
    public function updatingFilterDurationType() { $this->resetPage(); }
    public function updatingFilterIsOpenEnded() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterLocation', 'filterDateStart', 'filterDateEnd', 'filterDurationType', 'filterIsOpenEnded']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Registration::with('user', 'location', 'room')->withCount('payments')->where('status', 'active');

        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('user', function($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('registration_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterLocation) {
            $query->where('location_id', $this->filterLocation);
        }

        if ($this->filterDateStart) {
            $query->whereDate('registration_date', '>=', $this->filterDateStart);
        }

        if ($this->filterDateEnd) {
            $query->whereDate('registration_date', '<=', $this->filterDateEnd);
        }

        if ($this->filterDurationType) {
            $query->where('duration_type', $this->filterDurationType);
        }

        if ($this->filterIsOpenEnded !== '') {
            $query->where('is_open_ended', $this->filterIsOpenEnded);
        }

        // Filter rooms based on location and availability
        $rooms = [];
        if ($this->location_id) {
            $rooms = Room::where('location_id', $this->location_id)
                ->where(function($q) {
                    $q->where('status', 'available');
                    if ($this->currentRoomId) {
                        $q->orWhere('id', $this->currentRoomId);
                    }
                })
                ->orderBy('room_number')
                ->get();
        }

        return view('livewire.registration-manager', [
            'registrations' => $query->latest()->paginate(10),
            'locations' => Location::all(),
            'rooms' => $rooms,
            'newReg' => $this->newlyCreatedRegistrationId ? Registration::with(['user', 'location', 'room'])->find($this->newlyCreatedRegistrationId) : null,
        ]);
    }
}
