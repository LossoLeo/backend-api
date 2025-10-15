<?php

namespace App\Http\Controllers;

use App\Services\FakeStoreApiService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private FakeStoreApiService $fakeStoreApiService
    ) {}

    public function index(): JsonResponse
    {
        $products = $this->fakeStoreApiService->getAllProducts();

        if (!$products) {
            return response()->json([
                'message' => 'Erro ao buscar produtos'
            ], 500);
        }

        return response()->json($products);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->fakeStoreApiService->getProductById($id);

        if (!$product) {
            return response()->json([
                'message' => 'Produto não encontrado'
            ], 404);
        }

        return response()->json($product);
    }
}
