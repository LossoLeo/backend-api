<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function mockFakeStoreApi(): void
    {
        Http::fake([
            'fakestoreapi.com/products' => Http::response([
                [
                    'id' => 1,
                    'title' => 'Product 1',
                    'price' => 109.95,
                    'description' => 'Description 1',
                    'category' => "men's clothing",
                    'image' => 'https://example.com/image1.jpg',
                    'rating' => ['rate' => 3.9, 'count' => 120]
                ],
                [
                    'id' => 2,
                    'title' => 'Product 2',
                    'price' => 22.3,
                    'description' => 'Description 2',
                    'category' => 'electronics',
                    'image' => 'https://example.com/image2.jpg',
                    'rating' => ['rate' => 4.1, 'count' => 259]
                ],
            ], 200),
            'fakestoreapi.com/products/1' => Http::response([
                'id' => 1,
                'title' => 'Product 1',
                'price' => 109.95,
                'description' => 'Description 1',
                'category' => "men's clothing",
                'image' => 'https://example.com/image1.jpg',
                'rating' => ['rate' => 3.9, 'count' => 120]
            ], 200),
            'fakestoreapi.com/products/999' => Http::response(null, 404),
        ]);
    }

    public function test_authenticated_user_can_list_all_products(): void
    {
        $this->mockFakeStoreApi();

        $user = User::factory()->create();
        $user->assignRole('client');
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'title',
                    'price',
                    'description',
                    'category',
                    'image',
                    'rating',
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_list_products(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_product_details(): void
    {
        $this->mockFakeStoreApi();

        $user = User::factory()->create();
        $user->assignRole('client');
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/products/1');

        $response->assertStatus(200)
            ->assertJson([
                'id' => 1,
                'title' => 'Product 1',
                'price' => 109.95,
            ])
            ->assertJsonStructure([
                'id',
                'title',
                'price',
                'description',
                'category',
                'image',
                'rating' => [
                    'rate',
                    'count',
                ]
            ]);
    }

    public function test_returns_404_when_product_not_found(): void
    {
        $this->mockFakeStoreApi();

        $user = User::factory()->create();
        $user->assignRole('client');
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Produto não encontrado'
            ]);
    }

    public function test_handles_external_api_error_gracefully(): void
    {
        Http::fake([
            'fakestoreapi.com/products' => Http::response(null, 500),
        ]);

        $user = User::factory()->create();
        $user->assignRole('client');
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/products');

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Erro ao buscar produtos'
            ]);
    }
}
