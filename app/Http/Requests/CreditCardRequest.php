<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreditCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'credit_card_holder_name' => 'required',
            'credit_card_number'      => 'required',
            'credit_card_expiry'      => 'required',
            'credit_card_cvv'         => 'required',
            'installment_count'       => 'required|integer|min:1|max:12',
            'postal_code'             => 'required',
            'address'                 => 'required',
            'address_number'          => 'required',
            'city'                    => 'required',
            'state'                   => 'required',
            'address_complement'      => 'nullable',
            'phone'                   => 'required',
        ];
    }
}
