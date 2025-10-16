<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFavoriteRequest;
use App\Models\Product;
use App\Models\User;
use App\Services\FakeStoreApiService;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    public function __construct(
        private FakeStoreApiService $fakeStoreApiService
    ) {}

    public function index(): JsonResponse
    {
        $user = auth()->user();
        $favorites = $user->products;

        return response()->json($favorites);
    }

    public function indexAll(): JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $users = User::with('products')->get();

        return response()->json($users->map(function ($user) {
            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'favorites' => $user->products,
            ];
        }));
    }

    public function show(int $userId): JsonResponse
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('admin') && $authUser->id != $userId) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $user = User::with('products')->find($userId);

        if (!$user) {
            return response()->json([
                'message' => trans('favorites.user_not_found', [], 'pt_BR')
            ], 404);
        }

        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'favorites' => $user->products,
        ]);
    }

    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        $user = auth()->user();
        $productExternalId = $request->product_id;

        $productData = $this->fakeStoreApiService->getProductById($productExternalId);

        if (!$productData) {
            return response()->json([
                'message' => trans('favorites.product_not_found', [], 'pt_BR')
            ], 404);
        }

        $product = Product::updateOrCreate(
            ['external_id' => $productData['id']],
            [
                'title' => $productData['title'],
                'image' => $productData['image'],
                'price' => $productData['price'],
                'rating' => $productData['rating'] ?? null,
            ]
        );

        if ($user->products()->where('product_id', $product->id)->exists()) {
            return response()->json([
                'message' => trans('favorites.already_exists', [], 'pt_BR')
            ], 409);
        }

        $user->products()->attach($product->id);

        return response()->json([
            'message' => trans('favorites.added', [], 'pt_BR'),
            'product' => $product
        ], 201);
    }

    public function destroy(int $productId): JsonResponse
    {
        $user = auth()->user();

        $product = $user->products()->where('product_id', $productId)->first();

        if (!$product) {
            return response()->json([
                'message' => trans('favorites.not_found', [], 'pt_BR')
            ], 404);
        }

        $user->products()->detach($productId);

        return response()->json([
            'message' => trans('favorites.removed', [], 'pt_BR')
        ]);
    }
}
