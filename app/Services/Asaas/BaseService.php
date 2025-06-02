<?php

namespace App\Services\Asaas;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    protected mixed $apiKey;
    protected mixed $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('ASAAS_API_KEY');
        $this->baseUrl = env('ASAAS_API_URL', 'https://sandbox.asaas.com/api/v3/');
    }

    protected function request($method, $endpoint, $data = []): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'access_token' => $this->apiKey
            ])->$method($this->baseUrl . $endpoint, $data);

            if ($response->failed()) {
                Log::error("Asaas API error", [
                    'user_id' => auth()->id(),
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'data_sent' => $data,
                    'response' => $response->json(),
                ]);
            }

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
                'status' => $response->status(),
                'error' => $response->successful() ? null : $response->json()['errors'] ?? null
            ];
        } catch (\Exception $e) {
            Log::critical("Asaas API exception", [
                'user_id' => auth()->id(),
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
                'data_sent' => $data
            ]);

            return [
                'success' => false,
                'data' => null,
                'status' => 500,
                'error' => 'Erro na comunicação com a API do Asaas.'
            ];
        }
    }
}
