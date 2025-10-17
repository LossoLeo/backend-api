<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function findByExternalId(int $externalId): ?Product
    {
        return Product::where('external_id', $externalId)->first();
    }

    public function createOrUpdate(array $data): Product
    {
        return Product::updateOrCreate(
            ['external_id' => $data['external_id']],
            [
                'title' => $data['title'],
                'image' => $data['image'],
            ]
        );
    }
}
