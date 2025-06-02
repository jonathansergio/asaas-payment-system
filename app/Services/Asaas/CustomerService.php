<?php

namespace App\Services\Asaas;

class CustomerService extends BaseService
{
    public function create(array $data): array
    {
        return $this->request('post', 'customers', $data);
    }
}
