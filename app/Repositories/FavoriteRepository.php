<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function getAllUsersWithFavorites(int $perPage = 10): LengthAwarePaginator
    {
        return User::with('products')
            ->has('products')
            ->paginate($perPage);
    }

    public function getUserWithFavorites(int $userId): ?User
    {
        return User::with('products')->find($userId);
    }

    public function addFavorite(User $user, int $productId): bool
    {
        if ($this->userHasFavorite($user->id, $productId)) {
            return false;
        }

        $user->products()->attach($productId);
        return true;
    }

    public function removeFavorite(User $user, int $productId): bool
    {
        $removed = $user->products()->detach($productId);
        return $removed > 0;
    }

    public function isFavorited(User $user, int $productId): bool
    {
        return $user->products()->where('product_id', $productId)->exists();
    }

    public function getFavoriteProduct(User $user, int $productId): ?Product
    {
        return $user->products()->where('product_id', $productId)->first();
    }

    public function userHasFavorite(int $userId, int $productId): bool
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        return $user->products()->where('product_id', $productId)->exists();
    }
}
