<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
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
            ]);
        }

        $customer = Customer::firstOrCreate(
            ['cpf_cnpj' => $validated['cpf_cnpj']],
            ['name' => $validated['name'], 'email' => $validated['email']]
        );

        $customerResponse = $asaas->createCustomer([
            'name' => $customer->name,
            'email' => $customer->email,
            'cpfCnpj' => $customer->cpf_cnpj,
        ]);

        if (!$customerResponse->successful()) {
            dd($customerResponse->json());
            return back()->withErrors('Erro ao criar cliente no Asaas.');
        }

        $customerAsaasId = $customerResponse->json()['id'];

        $paymentPayload = [
            'customer' => $customerAsaasId,
            'billingType' => $validated['payment_method'],
            'value' => $validated['value'],
            'dueDate' => now()->addDays(3)->toDateString(),
            'description' => 'Pagamento via API'
        ];

        if ($validated['payment_method'] === 'CREDIT_CARD') {
            $expiry = explode('/', $request->credit_card_expiry);
            $paymentPayload['creditCard'] = [
                'holderName' => $request->credit_card_holder_name,
                'number' => $request->credit_card_number,
                'expiryMonth' => trim($expiry[0]),
                'expiryYear' => trim($expiry[1]),
                'ccv' => $request->credit_card_cvv
            ];
            $paymentPayload['creditCardHolderInfo'] = [
                'name' => $customer->name,
                'email' => $customer->email,
                'cpfCnpj' => $customer->cpf_cnpj,
                'postalCode' => '86010110',
                'addressNumber' => '123',
                'addressComplement' => 'Apto 45',
            ];
        }

        $paymentResponse = $asaas->createPayment($paymentPayload);

        if (!$paymentResponse->successful()) {
            dd($paymentResponse->json());
            return back()->withErrors('Erro ao criar pagamento no Asaas.');
        }

        $paymentData = $paymentResponse->json();

        // 🔥 Consulta o billingInfo para obter QRCode Pix, boleto, etc.
        $billingResponse = $asaas->getBillingInfo($paymentData['id']);

        if (!$billingResponse->successful()) {
            dd($billingResponse->json());
            return back()->withErrors('Erro ao buscar informações de cobrança no Asaas.');
        }

        $billingData = $billingResponse->json();

        return view('thanks', [
            'paymentData' => $paymentData,
            'billingData' => $billingData
        ]);
    }
}
