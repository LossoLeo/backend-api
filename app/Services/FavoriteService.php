<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\FavoriteRepository;
use App\Repositories\ProductRepository;

class FavoriteService
{
    public function __construct(
        private FavoriteRepository $favoriteRepository,
        private ProductRepository $productRepository,
        private FakeStoreApiService $fakeStoreApiService
    ) {}

    public function getUserFavorites(int $userId, int $perPage = 10)
    {
        return $this->favoriteRepository->getUserFavorites($userId, $perPage);
    }

    public function getAllUsersWithFavorites(int $perPage = 10)
    {
        return $this->favoriteRepository->getAllUsersWithFavorites($perPage);
    }

    public function getUserWithFavorites(int $userId): ?User
    {
        return $this->favoriteRepository->getUserWithFavorites($userId);
    }

    public function addFavorite(User $user, int $productExternalId): ?array
    {
        $productData = $this->fakeStoreApiService->getProductById($productExternalId);

        if (!$productData) {
            return null;
        }

        $product = $this->productRepository->createOrUpdate([
            'external_id' => $productData['id'],
            'title' => $productData['title'],
            'image' => $productData['image'],
        ]);

        $this->favoriteRepository->addFavorite($user, $product->id);

        return [
            'product' => [
                'id' => $product->id,
                'external_id' => $product->external_id,
                'title' => $product->title,
                'image' => $product->image,
                'price' => $productData['price'] ?? null,
                'rating' => $productData['rating'] ?? null,
            ]
        ];
    }

    public function removeFavorite(User $user, int $productId): bool
    {
        $product = $this->favoriteRepository->getFavoriteProduct($user, $productId);

        if (!$product) {
            return false;
        }

        $this->favoriteRepository->removeFavorite($user, $productId);

        return true;
    }
}
