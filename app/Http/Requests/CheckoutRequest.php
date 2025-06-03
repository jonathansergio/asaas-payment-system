<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'name'           => 'required',
            'email'          => 'required|email',
            'cpf_cnpj'       => 'required',
            'value'          => 'required|numeric',
            'payment_method' => 'required|in:BOLETO,CREDIT_CARD,PIX',
        ];
    }
}
