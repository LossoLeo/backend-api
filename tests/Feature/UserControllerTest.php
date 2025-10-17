<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_all_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->count(5)->create()->each(function ($user) {
            $user->assignRole('client');
        });

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'created_at',
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                    'from',
                    'to',
                ]
            ]);

        $this->assertEquals(6, $response->json('pagination.total')); // 5 + 1 admin
    }

    public function test_client_cannot_list_all_users(): void
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $token = $client->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Acesso não autorizado'
            ]);
    }

    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $user->assignRole('client');

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'client',
            ]);
    }

    public function test_admin_can_view_any_user_profile(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $otherUser = User::factory()->create();
        $otherUser->assignRole('client');

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/' . $otherUser->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $otherUser->id,
            ]);
    }

    public function test_user_cannot_view_other_user_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        $otherUser = User::factory()->create();
        $otherUser->assignRole('client');

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users/' . $otherUser->id);

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
        ]);
        $user->assignRole('client');

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/' . $user->id, [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Usuário atualizado com sucesso',
                'user' => [
                    'name' => 'New Name',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    }

    public function test_user_can_update_email(): void
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
        ]);
        $user->assignRole('client');

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/' . $user->id, [
            'email' => 'new@example.com',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com',
        ]);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword'),
        ]);
        $user->assignRole('client');

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/' . $user->id, [
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(200);

        // Tentar login com nova senha
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'newpassword123',
        ]);

        $loginResponse->assertStatus(200);
    }

    public function test_user_cannot_update_email_to_existing_one(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user1->assignRole('client');

        User::factory()->create(['email' => 'user2@example.com']);

        $token = $user1->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/' . $user1->id, [
            'email' => 'user2@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_update_other_user_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        $otherUser = User::factory()->create();
        $otherUser->assignRole('client');

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/' . $otherUser->id, [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Usuário removido com sucesso'
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_client_cannot_delete_user(): void
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $user = User::factory()->create();

        $token = $client->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(403);
    }

    public function test_cannot_delete_nonexistent_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/users/99999');

        $response->assertStatus(404);
    }
}
