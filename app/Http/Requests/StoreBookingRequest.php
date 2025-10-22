<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required','exists:tenants,id'],
            'room_id'   => ['required','exists:rooms,id'],
            'start_date'=> ['required','date'],
            'end_date'  => ['nullable','date','after_or_equal:start_date'],
            'rate'      => ['required','numeric','min:0'],
            'mode'      => ['required','in:monthly,daily'], // bantu bedakan kost vs homestay
        ];
    }
}
