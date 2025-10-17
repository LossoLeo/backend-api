<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Product;

class FavoriteRepository
{
    public function getUserFavorites(int $userId, int $perPage = 10)
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        return $user->products()->paginate($perPage);
    }

    public function getAllUsersWithFavorites(int $perPage = 10)
    {
        return User::with('products')->paginate($perPage);
    }

    public function getUserWithFavorites(int $userId): ?User
    {
        return User::with('products')->find($userId);
    }

    public function addFavorite(User $user, int $productId): void
    {
        $user->products()->attach($productId);
    }

    public function removeFavorite(User $user, int $productId): void
    {
        $user->products()->detach($productId);
    }

    public function isFavorited(User $user, int $productId): bool
    {
        return $user->products()->where('product_id', $productId)->exists();
    }

    public function getFavoriteProduct(User $user, int $productId): ?Product
    {
        return $user->products()->where('product_id', $productId)->first();
    }
}
