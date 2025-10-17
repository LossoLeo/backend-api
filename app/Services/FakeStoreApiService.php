<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FakeStoreApiService
{
    private string $baseUrl = 'https://fakestoreapi.com';

    public function getAllProducts(): ?array
    {
        return $this->makeRequest('/products');
    }

    public function getProductById(int $productId): ?array
    {
        return $this->makeRequest("/products/{$productId}");
    }

    private function makeRequest(string $endpoint): ?array
    {
        try {
            $url = $this->baseUrl . $endpoint;
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error(trans('logs.external_api_error', [], 'pt_BR'), [
                'endpoint' => $endpoint,
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error(trans('logs.external_api_exception', [], 'pt_BR'), [
                'endpoint' => $endpoint,
                'url' => $this->baseUrl . $endpoint,
                'message' => $e->getMessage()
            ]);

            return null;
        }
    }
}
