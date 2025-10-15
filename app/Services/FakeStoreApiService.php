<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FakeStoreApiService
{
    private string $baseUrl = 'https://fakestoreapi.com';

    public function getAllProducts(): ?array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/products");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro ao buscar produtos da API externa', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao buscar produtos da API externa', [
                'message' => $e->getMessage()
            ]);

            return null;
        }
    }

    public function getProductById(int $productId): ?array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/products/{$productId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro ao buscar produto da API externa', [
                'product_id' => $productId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao buscar produto da API externa', [
                'product_id' => $productId,
                'message' => $e->getMessage()
            ]);

            return null;
        }
    }
}
