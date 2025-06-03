<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Services\Asaas\CustomerService;
use App\Services\Asaas\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    protected CustomerService $customerService;
    protected PaymentService $paymentService;

    public function __construct(CustomerService $customerService, PaymentService $paymentService)
    {
        $this->customerService = $customerService;
        $this->paymentService = $paymentService;
    }

    public function showForm(): View|Factory|Application
    {
        return view('checkout');
    }

    public function process(Request $request): View|Factory|Application|RedirectResponse
    {
        $validated = $this->validateBaseFields($request);

        if ($validated['payment_method'] === 'CREDIT_CARD') {
            $this->validateCreditCardFields($request);
        }

        $customer = $this->resolveOrCreateCustomer($validated);
        if ($customer instanceof RedirectResponse) {
            return $customer;
        }

        $asaasCustomer = $this->customerService->create([
            'name' => $customer->name,
            'email' => $customer->email,
            'cpfCnpj' => $customer->cpf_cnpj
        ]);

        if (!$asaasCustomer['success']) {
            return back()->withErrors($this->extractErrors($paymentResponse['data'] ?? []));
        }

        $paymentPayload = $this->buildPaymentPayload($request, $validated, $customer, $asaasCustomer['data']['id']);
        $paymentResponse = $this->paymentService->create($paymentPayload);

        if (!$paymentResponse['success']) {
            return back()->withErrors($this->extractErrors($paymentResponse['data'] ?? []));
        }

        $billingResponse = $this->paymentService->getBillingInfo($paymentResponse['data']['id']);

        if (!$billingResponse['success']) {
            return back()->withErrors($this->extractErrors($paymentResponse['data'] ?? []));
        }

        $this->storePayment($customer, $validated, $paymentResponse['data'], $billingResponse['data']);

        return view('thanks', [
            'paymentData' => $paymentResponse['data'],
            'billingData' => $billingResponse['data']
        ]);
    }

    private function validateBaseFields(Request $request): array
    {
        return $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'cpf_cnpj' => 'required',
            'value' => 'required|numeric',
            'payment_method' => 'required|in:BOLETO,CREDIT_CARD,PIX',
        ]);
    }

    private function validateCreditCardFields(Request $request): void
    {
        $request->validate([
            'credit_card_holder_name' => 'required',
            'credit_card_number' => 'required',
            'credit_card_expiry' => 'required',
            'credit_card_cvv' => 'required',
            'installment_count' => 'required|integer|min:1|max:12',
            'postal_code' => 'required',
            'address' => 'required',
            'address_number' => 'required',
            'city' => 'required',
            'state' => 'required',
            'address_complement' => 'nullable',
            'phone' => 'required',
        ]);
    }

    private function resolveOrCreateCustomer(array $data): Customer|RedirectResponse
    {
        $existingByCpf = Customer::where('cpf_cnpj', $data['cpf_cnpj'])->first();
        $existingByEmail = Customer::where('email', $data['email'])->first();

        if (!$existingByCpf && !$existingByEmail) {
            return Customer::create([
                'cpf_cnpj' => $data['cpf_cnpj'],
                'name' => $data['name'],
                'email' => $data['email'],
            ]);
        }

        if ($existingByEmail && !$existingByCpf) {
            return back()->withErrors('Já existe um usuário com este e-mail.');
        }

        if ($existingByCpf && !$existingByEmail) {
            return back()->withErrors('Já existe um usuário com este CPF/CNPJ.');
        }

        if ($existingByCpf->email !== $data['email']) {
            return back()->withErrors('O CPF informado está associado a outro e-mail.');
        }

        return $existingByCpf;
    }

    private function buildPaymentPayload(Request $request, array $validated, Customer $customer, string $asaasId): array
    {
        $payload = [
            'customer' => $asaasId,
            'billingType' => $validated['payment_method'],
            'value' => $validated['value'],
            'dueDate' => now()->addDays(3)->toDateString(),
            'description' => 'Pagamento via API',
        ];

        if ($validated['payment_method'] === 'CREDIT_CARD') {
            $expiry = explode('/', $request->credit_card_expiry);
            $installments = (int) $request->installment_count;

            if ($installments > 1) {
                $payload['installmentCount'] = $installments;
                $payload['installmentValue'] = round($validated['value'] / $installments, 2);
            }

            $payload['creditCard'] = [
                'holderName' => $request->credit_card_holder_name,
                'number' => preg_replace('/\D/', '', $request->credit_card_number),
                'expiryMonth' => trim($expiry[0]),
                'expiryYear' => trim($expiry[1]),
                'ccv' => $request->credit_card_cvv,
            ];

            $payload['creditCardHolderInfo'] = [
                'name' => $customer->name,
                'email' => $customer->email,
                'cpfCnpj' => preg_replace('/\D/', '', $customer->cpf_cnpj),
                'postalCode' => preg_replace('/\D/', '', $request->postal_code),
                'address' => $request->address,
                'addressNumber' => $request->address_number,
                'addressComplement' => $request->address_complement,
                'city' => $request->city,
                'state' => $request->state,
                'phone' => preg_replace('/\D/', '', $request->phone),
            ];
        }

        return $payload;
    }

    private function storePayment(Customer $customer, array $validated, array $paymentData, array $billingData): void
    {
        Payment::create([
            'customer_id'    => $customer->id,
            'value'          => $validated['value'],
            'payment_method' => $validated['payment_method'],
            'status'         => $paymentData['status'],
            'invoice_url'    => $paymentData['invoiceUrl'] ?? null,
            'pix_qr_code'    => $billingData['pix']['encodedImage'] ?? null,
            'pix_code'       => $billingData['pix']['payload'] ?? null,
        ]);
    }

    private function extractErrors(array $response = []): array
    {
        if (empty($response)) {
            return ['Erro desconhecido'];
        }

        return collect($response['errors'] ?? [$response['error'] ?? 'Erro desconhecido'])
            ->pluck('description')
            ->toArray();
    }
}
