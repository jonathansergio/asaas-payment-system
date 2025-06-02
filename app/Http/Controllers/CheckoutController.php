<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\AsaasService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function showForm()
    {
        return view('checkout');
    }

    public function process(Request $request, AsaasService $asaas)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'cpf_cnpj' => 'required',
            'value' => 'required|numeric',
            'payment_method' => 'required|in:BOLETO,CREDIT_CARD,PIX',
        ]);

        if ($validated['payment_method'] === 'CREDIT_CARD') {
            $request->validate([
                'credit_card_holder_name' => 'required',
                'credit_card_number' => 'required',
                'credit_card_expiry' => 'required',
                'credit_card_cvv' => 'required',
                'installment_count' => 'required|integer|min:1|max:12',
                'postal_code' => 'required',
                'address_number' => 'required',
                'address_complement' => 'nullable',
                'phone' => 'required',
            ]);
        }

        $existingByCpf = Customer::where('cpf_cnpj', $validated['cpf_cnpj'])->first();
        $existingByEmail = Customer::where('email', $validated['email'])->first();

        if (!$existingByCpf && !$existingByEmail) {
            $customer = Customer::create([
                'cpf_cnpj' => $validated['cpf_cnpj'],
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);
        } elseif ($existingByEmail && !$existingByCpf) {
            return back()->withErrors('Já existe um usuário com este e-mail.');
        } elseif ($existingByCpf && !$existingByEmail) {
            return back()->withErrors('Já existe um usuário com este CPF/CNPJ.');
        } elseif ($existingByCpf->email !== $validated['email']) {
            return back()->withErrors('O CPF informado está associado a outro e-mail.');
        } else {
            $customer = $existingByCpf;
        }

        $customerResponse = $asaas->createCustomer([
            'name' => $customer->name,
            'email' => $customer->email,
            'cpfCnpj' => $customer->cpf_cnpj,
        ]);

        if (!$customerResponse->successful()) {
//            dd($customerResponse->json());
            $errors = collect($customerResponse->json()['errors'] ?? [])
                ->pluck('description')
                ->implode(' ');
            return back()->withErrors($errors ?: 'Erro ao criar cliente no Asaas.');
        }

        $customerAsaasId = $customerResponse->json()['id'];

        $paymentPayload = [
            'customer' => $customerAsaasId,
            'billingType' => $validated['payment_method'],
            'value' => $validated['value'],
            'dueDate' => now()->addDays(3)->toDateString(),
            'description' => 'Pagamento via API',
        ];

        if ($validated['payment_method'] === 'CREDIT_CARD') {
            $expiry = explode('/', $request->credit_card_expiry);
            $installmentCount = (int) $request->input('installment_count', 1);

            $paymentPayload['installmentCount'] = $installmentCount;
            if ($installmentCount > 1) {
                $paymentPayload['installmentValue'] = round($validated['value'] / $installmentCount, 2);
            }

            $paymentPayload['creditCard'] = [
                'holderName' => $request->credit_card_holder_name,
                'number' => preg_replace('/\D/', '', $request->credit_card_number),
                'expiryMonth' => trim($expiry[0]),
                'expiryYear' => trim($expiry[1]),
                'ccv' => $request->credit_card_cvv,
            ];

            $paymentPayload['creditCardHolderInfo'] = [
                'name' => $customer->name,
                'email' => $customer->email,
                'cpfCnpj' => preg_replace('/\D/', '', $customer->cpf_cnpj),
                'postalCode' => preg_replace('/\D/', '', $request->postal_code),
                'address' => $request->address,
                'addressNumber' => $request->address_number,
                'addressComplement' => $request->address_complement,
                'phone' => preg_replace('/\D/', '', $request->phone),
            ];
        }

        $paymentResponse = $asaas->createPayment($paymentPayload);

        if (!$paymentResponse->successful()) {
            dd($paymentResponse->json());
            $errors = collect($paymentResponse->json()['errors'] ?? [])
                ->pluck('description')
                ->implode(' ');
            return back()->withErrors($errors ?: 'Erro ao criar pagamento no Asaas.');
        }

        $paymentData = $paymentResponse->json();

        $billingResponse = $asaas->getBillingInfo($paymentData['id']);

        if (!$billingResponse->successful()) {
            dd($billingResponse->json());
            $errors = collect($billingResponse->json()['errors'] ?? [])
                ->pluck('description')
                ->implode(' ');
            return back()->withErrors($errors ?: 'Erro ao buscar informações de cobrança no Asaas.');
        }

        $billingData = $billingResponse->json();

        return view('thanks', [
            'paymentData' => $paymentData,
            'billingData' => $billingData
        ]);
    }
}
