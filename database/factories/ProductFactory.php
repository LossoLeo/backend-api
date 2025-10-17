<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'image' => $this->faker->imageUrl(),
            'external_id' => $this->faker->unique()->numberBetween(1, 1000),
        ];
    }
}
