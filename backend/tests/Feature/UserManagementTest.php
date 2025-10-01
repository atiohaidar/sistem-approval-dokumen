<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function createRegularUser()
    {
        return User::factory()->create(['role' => 'user']);
    }

    private function getAuthToken(User $user)
    {
        return $user->createToken('test-token')->plainTextToken;
    }

    public function test_admin_can_list_all_users()
    {
        $admin = $this->createAdminUser();
        $user1 = $this->createRegularUser();
        $user2 = $this->createRegularUser();

        $token = $this->getAuthToken($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        $response->assertStatus(200)
                ->assertJsonCount(3) // admin + 2 users
                ->assertJsonStructure([
                    '*' => ['id', 'name', 'email', 'role', 'created_at']
                ]);
    }

    public function test_regular_user_cannot_list_users()
    {
        $user = $this->createRegularUser();
        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user()
    {
        $admin = $this->createAdminUser();
        $token = $this->getAuthToken($admin);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/users', $userData);

        $response->assertStatus(201)
                ->assertJson([
                    'name' => 'New User',
                    'email' => 'newuser@example.com',
                    'role' => 'user',
                ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'user',
        ]);
    }

    public function test_regular_user_cannot_create_user()
    {
        $user = $this->createRegularUser();
        $token = $this->getAuthToken($user);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/users', $userData);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_user()
    {
        $admin = $this->createAdminUser();
        $user = $this->createRegularUser();
        $token = $this->getAuthToken($admin);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                    'role' => 'admin',
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_delete_user()
    {
        $admin = $this->createAdminUser();
        $user = $this->createRegularUser();
        $token = $this->getAuthToken($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_themselves()
    {
        $admin = $this->createAdminUser();
        $token = $this->getAuthToken($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$admin->id}");

        $response->assertStatus(403)
                ->assertJson(['message' => 'Cannot delete your own account']);
    }

    public function test_admin_can_change_user_role()
    {
        $admin = $this->createAdminUser();
        $user = $this->createRegularUser();
        $token = $this->getAuthToken($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/users/{$user->id}/change-role", [
            'role' => 'admin',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Role updated successfully',
                    'user' => [
                        'id' => $user->id,
                        'role' => 'admin',
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin',
        ]);
    }

    public function test_validation_errors_on_create_user()
    {
        $admin = $this->createAdminUser();
        $token = $this->getAuthToken($admin);

        // Test missing required fields
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/users', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }

    public function test_validation_errors_on_update_user()
    {
        $admin = $this->createAdminUser();
        $user = $this->createRegularUser();
        $token = $this->getAuthToken($admin);

        // Test invalid email
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user->id}", [
            'name' => 'Test',
            'email' => 'invalid-email',
            'role' => 'user',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }
}
