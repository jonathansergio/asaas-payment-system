<?php

namespace App\Services\Asaas;

class PaymentService extends BaseService
{
    public function create(array $data): array
    {
        return $this->request('post', 'payments', $data);
    }

    public function getBillingInfo(string $paymentId): array
    {
        return $this->request('get', "payments/{$paymentId}/billingInfo");
    }
}
