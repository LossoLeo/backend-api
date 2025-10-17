<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\FakeStoreApiService;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fakeStoreService = app(FakeStoreApiService::class);
        $externalData = $fakeStoreService->getProductById($this->external_id);

        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'title' => $this->title,
            'image' => $this->image,
            'price' => $externalData['price'] ?? null,
            'rating' => $externalData['rating'] ?? null,
            'favorited_at' => $this->when(
                $this->pivot,
                fn() => $this->pivot->created_at->format('Y-m-d H:i:s')
            ),
        ];
    }
}
