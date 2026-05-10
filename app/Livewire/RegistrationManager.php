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
use Carbon\Carbon;

class RegistrationManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';
    public $isModalOpen = false;
    public $registrationId;

    // List & Search & Filters
    public $search = '';
    public $filterLocation = '';
    public $filterDateStart = '';
    public $filterDateEnd = '';

    // Form fields - Basic
    public $location_id, $room_id, $registration_number;
    public $registration_date, $stay_start_date, $currentRoomId;

    // Financials
    public $room_price = 0, $discount_type = 'fixed', $discount_value = 0, $total_price = 0;
    public $room_facilities = '';

    // Personal Info
    public $name, $email, $phone_number, $address;
    public $identity_type = 'KTP', $identity_number, $gender = 'Laki-laki';
    public $birth_place, $birth_date;

    // Photos
    public $photo_self, $photo_identity, $photo_family_card;
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
            $this->room_price = $room->price;
            $this->room_facilities = $room->facilities;
        } else {
            $this->room_price = 0;
            $this->room_facilities = '';
        }
        $this->calculateTotal();
    }

    public function updatedDiscountType() { $this->calculateTotal(); }
    public function updatedDiscountValue() { $this->calculateTotal(); }
    public function updatedRoomPrice() { $this->calculateTotal(); }

    public function calculateTotal()
    {
        $price = (float) $this->room_price;
        $discount = (float) $this->discount_value;

        if ($this->discount_type === 'percent') {
            $this->total_price = $price - ($price * ($discount / 100));
        } else {
            $this->total_price = $price - $discount;
        }

        if ($this->total_price < 0) $this->total_price = 0;
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
            $this->room_price = $reg->room_price;
            $this->discount_type = $reg->discount_type;
            $this->discount_value = $reg->discount_value;
            $this->total_price = $reg->total_price;

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

    private function resetForm()
    {
        $this->registrationId = null;
        $this->currentRoomId = null;
        $this->location_id = null;
        $this->room_id = null;
        $this->registration_number = null;
        $this->registration_date = Carbon::now()->format('Y-m-d');
        $this->stay_start_date = null;
        $this->room_price = 0;
        $this->discount_type = 'fixed';
        $this->discount_value = 0;
        $this->total_price = 0;
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

        // Only validate emergency contacts if they exist
        if (!empty($this->emergency_contacts)) {
            $rules['emergency_contacts.*.name'] = 'required|string';
            $rules['emergency_contacts.*.relationship'] = 'required|string';
            $rules['emergency_contacts.*.phone_number'] = 'required|string';
        }

        $this->validate($rules);

        DB::transaction(function () {
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
                'room_price' => $this->room_price,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'total_price' => $this->total_price,
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
            } else {
                if ($this->photo_self) {
                    $path = $this->photo_self->store('registrations/self', 'public');
                    $data['photo_self'] = $path;
                    $user->update(['avatar' => $path]);
                }
                if ($this->photo_identity) $data['photo_identity'] = $this->photo_identity->store('registrations/identity', 'public');
                if ($this->photo_family_card) $data['photo_family_card'] = $this->photo_family_card->store('registrations/family_card', 'public');
                $registration = Registration::create($data);
            }

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

        NotificationSent::dispatch("Check in {$this->name} berhasil disimpan.", 'success');
        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function deleteRegistration($id)
    {
        $reg = Registration::find($id);
        $name = $reg->user->name;

        DB::transaction(function() use ($reg) {
            // Revert room status to available
            Room::where('id', $reg->room_id)->update(['status' => 'available']);
            $reg->delete();
        });

        DatabaseUpdated::dispatch();
        NotificationSent::dispatch("Check in {$name} berhasil dihapus.", 'success');
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterLocation() { $this->resetPage(); }
    public function updatingFilterDateStart() { $this->resetPage(); }
    public function updatingFilterDateEnd() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterLocation', 'filterDateStart', 'filterDateEnd']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Registration::with('user', 'location', 'room')->where('status', 'active');

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
        ]);
    }
}
