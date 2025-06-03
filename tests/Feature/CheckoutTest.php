<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_processes_credit_card_payment()
    {
        Http::fake([
            'https://www.asaas.com/api/v3/customers' => Http::response(['id' => 'cus_123'], 200),
            'https://www.asaas.com/api/v3/payments' => Http::response(['success' => true, 'invoiceUrl' => 'https://asaas.com/invoice/123'], 200),
        ]);

        $response = $this->post('/checkout', [
            'name' => 'Jonathan Silva',
            'email' => 'jonathan@example.com',
            'cpf_cnpj' => '06080971950',
            'value' => 444,
            'payment_method' => 'CREDIT_CARD',
            'credit_card_holder_name' => 'Jonathan Silva',
            'credit_card_number' => '4111111111111111',
            'credit_card_expiry' => '12/28',
            'credit_card_cvv' => '123',
            'installment_count' => '1',
            'postal_code' => '86010110',
            'address' => 'Rua A',
            'address_number' => '123',
            'city' => 'Londrina',
            'state' => 'PR',
            'address_complement' => '',
            'phone' => '(43) 99999-9999',
        ]);

        $response->assertRedirectContains('/thanks');
    }

    /** @test */
    public function it_processes_pix_payment()
    {
        Http::fake([
            'https://www.asaas.com/api/v3/customers' => Http::response(['id' => 'cus_456'], 200),
            'https://www.asaas.com/api/v3/payments' => Http::response(['success' => true, 'invoiceUrl' => 'https://asaas.com/invoice/456'], 200),
        ]);

        $response = $this->post('/checkout', [
            'name' => 'Maria Oliveira',
            'email' => 'maria@example.com',
            'cpf_cnpj' => '12345678901',
            'value' => 100,
            'payment_method' => 'PIX',
        ]);

        $response->assertRedirectContains('/thanks');
    }

    /** @test */
    public function it_processes_boleto_payment()
    {
        Http::fake([
            'https://www.asaas.com/api/v3/customers' => Http::response(['id' => 'cus_789'], 200),
            'https://www.asaas.com/api/v3/payments' => Http::response(['success' => true, 'invoiceUrl' => 'https://asaas.com/invoice/789'], 200),
        ]);

        $response = $this->post('/checkout', [
            'name' => 'Carlos Lima',
            'email' => 'carlos@example.com',
            'cpf_cnpj' => '98765432100',
            'value' => 150,
            'payment_method' => 'BOLETO',
        ]);

        $response->assertRedirectContains('/thanks');
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->post('/checkout', []);
        $response->assertSessionHasErrors([
            'name',
            'email',
            'cpf_cnpj',
            'value',
            'payment_method',
        ]);
    }
}
