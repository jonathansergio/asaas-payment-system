<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AsaasService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('ASAAS_API_KEY');
        $this->baseUrl = 'https://sandbox.asaas.com/api/v3/';
    }

    private function request($method, $endpoint, $data = [])
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'access_token' => $this->apiKey
        ])->$method($this->baseUrl . $endpoint, $data);
    }

    public function createCustomer($data)
    {
        return $this->request('post', 'customers', $data);
    }

    public function createPayment($data)
    {
        return $this->request('post', 'payments', $data);
    }

    public function getBillingInfo($paymentId)
    {
        return $this->request('get', "payments/{$paymentId}/billingInfo");
    }
}
