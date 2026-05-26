<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'room_id',
        'registration_number',
        'registration_date',
        'stay_start_date',
        'duration_type',
        'duration_value',
        'is_open_ended',
        'room_price',
        'discount_type',
        'discount_value',
        'discount_duration',
        'is_discount_open_ended',
        'total_price',
        'initial_deposit',
        'identity_type',
        'identity_number',
        'gender',
        'birth_place',
        'birth_date',
        'photo_self',
        'photo_identity',
        'family_card_number',
        'photo_family_card',
        'institution_name',
        'institution_address',
        'institution_phone',
        'status',
        'check_out_date',
        'check_out_notes',
    ];

    protected $casts = [
        'registration_date' => 'date:Y-m-d',
        'stay_start_date' => 'date:Y-m-d',
        'birth_date' => 'date:Y-m-d',
        'check_out_date' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function getDepositBalanceAttribute()
    {
        $credit = $this->deposits()->where('type', 'credit')->sum('amount');
        $debit = $this->deposits()->where('type', 'debit')->sum('amount');
        return $credit - $debit;
    }

    public function syncBills()
    {
        $startDate = \Carbon\Carbon::parse($this->stay_start_date);

        // Handle Deposit Bill
        if ($this->initial_deposit > 0) {
            $depositBill = $this->bills()->where('description', 'like', 'Deposit Awal%')->first();
            if ($depositBill) {
                if ($depositBill->status !== 'Lunas') {
                    $depositBill->update([
                        'amount' => $this->initial_deposit,
                    ]);
                }
            } else {
                Bill::create([
                    'registration_id' => $this->id,
                    'bill_number' => 'BILL-DEP-' . $this->id . '-' . $startDate->format('dmY'),
                    'description' => 'Deposit Awal (Uang Jaminan)',
                    'discount' => 0,
                    'amount' => $this->initial_deposit,
                    'due_date' => $startDate,
                    'status' => 'Belum Lunas',
                ]);
            }
        }

        $count = (int) $this->duration_value;

        if ($this->is_open_ended) {
            $count = 12;
        }

        $existingBills = $this->bills()
            ->where('description', 'not like', 'Deposit Awal%')
            ->orderBy('due_date', 'asc')
            ->get();

        for ($i = 0; $i < $count; $i++) {
            $billDate = clone $startDate;
            $description = "Tagihan Sewa Kamar";

            switch ($this->duration_type) {
                case 'daily':
                    $billDate->addDays($i);
                    $description .= " (Harian: " . $billDate->format('d M Y') . ")";
                    break;
                case 'weekly':
                    $billDate->addWeeks($i);
                    $description .= " (Mingguan: " . $billDate->format('d M Y') . ")";
                    break;
                case 'yearly':
                    $billDate->addYears($i);
                    $description .= " (Tahunan: " . $billDate->format('Y') . ")";
                    break;
                default: // monthly
                    $billDate->addMonths($i);
                    $description .= " (" . $billDate->translatedFormat('F Y') . ")";
                    break;
            }

            $billAmount = (float) $this->room_price;
            $billDiscount = 0;
            if ($this->is_discount_open_ended || $i < (int) $this->discount_duration) {
                if ($this->discount_type === 'percent') {
                    $billDiscount = ($billAmount * ((float) $this->discount_value / 100));
                } else {
                    $billDiscount = (float) $this->discount_value;
                }
            }
            $billAmount = max(0, $billAmount - $billDiscount);

            if (isset($existingBills[$i])) {
                $bill = $existingBills[$i];
                if ($bill->status !== 'Lunas') {
                    $bill->update([
                        'description' => $description,
                        'discount' => $billDiscount,
                        'amount' => $billAmount,
                        'due_date' => $billDate,
                    ]);

                    if ($bill->paid_amount >= $bill->amount && $bill->amount > 0) {
                        $bill->update(['status' => 'Lunas']);
                    } elseif ($bill->paid_amount > 0) {
                        $bill->update(['status' => 'Cicilan']);
                    } else {
                        $bill->update(['status' => 'Belum Lunas']);
                    }
                }
            } else {
                $billNumber = 'BILL-' . $this->id . '-' . $billDate->format('dmY') . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
                Bill::create([
                    'registration_id' => $this->id,
                    'bill_number' => $billNumber,
                    'description' => $description,
                    'discount' => $billDiscount,
                    'amount' => $billAmount,
                    'due_date' => $billDate,
                    'status' => 'Belum Lunas',
                ]);
            }
        }
    }
}
