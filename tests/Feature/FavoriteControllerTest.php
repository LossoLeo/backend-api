<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function mockFakeStoreApi(): void
    {
        Http::fake([
            'fakestoreapi.com/products/1' => Http::response([
                'id' => 1,
                'title' => 'Test Product',
                'image' => 'https://example.com/image.jpg',
                'price' => 99.99,
                'rating' => ['rate' => 4.5, 'count' => 100],
            ], 200),
            'fakestoreapi.com/products/999' => Http::response(null, 404),
        ]);
    }

    public function test_user_can_add_product_to_favorites(): void
    {
        $this->mockFakeStoreApi();

        $user = User::factory()->create();
        $user->assignRole('client');
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites', [
            'product_id' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'product' => [
                    'id',
                    'external_id',
                    'title',
                    'image',
                    'price',
                    'rating',
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'external_id' => 1,
            'title' => 'Test Product',
        ]);

        $this->assertDatabaseHas('product_user', [
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_add_nonexistent_product_to_favorites(): void
    {
        $this->mockFakeStoreApi();

        $user = User::factory()->create();
        $user->assignRole('client');
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites', [
            'product_id' => 999,
        ]);

        $response->assertStatus(409);
    }

    public function test_user_cannot_add_duplicate_favorite(): void
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        $product = Product::factory()->create(['external_id' => 1]);
        $user->products()->attach($product->id);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites', [
            'product_id' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_user_can_list_own_favorites(): void
    {
        Http::fake([
            'fakestoreapi.com/products/*' => Http::response([
                'id' => 1,
                'title' => 'Test Product',
                'price' => 99.99,
                'rating' => ['rate' => 4.5, 'count' => 100],
            ], 200),
        ]);

        $user = User::factory()->create();
        $user->assignRole('client');

        $product1 = Product::factory()->create(['external_id' => 1]);
        $product2 = Product::factory()->create(['external_id' => 2]);
        $user->products()->attach([$product1->id, $product2->id]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'external_id',
                        'title',
                        'image',
                        'price',
                        'rating',
                        'favorited_at',
                    ]
                ],
                'pagination',
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_user_can_remove_favorite(): void
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        $product = Product::factory()->create();
        $user->products()->attach($product->id);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/favorites/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Produto removido dos favoritos com sucesso'
            ]);

        $this->assertDatabaseMissing('product_user', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_user_cannot_remove_nonexistent_favorite(): void
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/favorites/999');

        $response->assertStatus(404);
    }

    public function test_admin_can_view_all_users_favorites(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user1 = User::factory()->create();
        $user1->assignRole('client');
        $product1 = Product::factory()->create();
        $user1->products()->attach($product1->id);

        $user2 = User::factory()->create();
        $user2->assignRole('client');
        $product2 = Product::factory()->create();
        $user2->products()->attach($product2->id);

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites/all');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'user_id',
                        'user_name',
                        'user_email',
                        'favorites_count',
                        'favorites',
                    ]
                ],
                'pagination',
            ]);
    }

    public function test_client_cannot_view_all_users_favorites(): void
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $token = $client->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites/all');

        $response->assertStatus(403);
    }

    public function test_user_can_view_own_favorites_by_user_id(): void
    {
        Http::fake([
            'fakestoreapi.com/products/*' => Http::response([
                'id' => 1,
                'price' => 99.99,
                'rating' => ['rate' => 4.5, 'count' => 100],
            ], 200),
        ]);

        $user = User::factory()->create();
        $user->assignRole('client');

        $product = Product::factory()->create(['external_id' => 1]);
        $user->products()->attach($product->id);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites/user/' . $user->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user_id',
                'user_name',
                'user_email',
                'favorites_count',
                'favorites',
            ]);
    }

    public function test_user_cannot_view_other_user_favorites(): void
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        $otherUser = User::factory()->create();
        $otherUser->assignRole('client');

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites/user/' . $otherUser->id);

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_user_favorites(): void
    {
        Http::fake([
            'fakestoreapi.com/products/*' => Http::response([
                'id' => 1,
                'price' => 99.99,
                'rating' => ['rate' => 4.5, 'count' => 100],
            ], 200),
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('client');

        $product = Product::factory()->create();
        $user->products()->attach($product->id);

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites/user/' . $user->id);

        $response->assertStatus(200);
    }
}
