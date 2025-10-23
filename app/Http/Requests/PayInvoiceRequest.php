<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'amount'    => ['required','numeric','min:1'],
            'method'    => ['required','in:cash,transfer,qris'],
            'reference' => ['nullable','string','max:100'],
        ];
    }
}
