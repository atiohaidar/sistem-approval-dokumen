<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_fillable_attributes()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('user', $user->role);
    }

    public function test_user_has_hidden_attributes()
    {
        $user = User::factory()->create();

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    public function test_is_admin_method_returns_true_for_admin_role()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    public function test_user_has_sanctum_traits()
    {
        $user = User::factory()->create();

        // Test that user can create tokens
        $token = $user->createToken('test-token');
        $this->assertNotNull($token);

        // Test that token has plainTextToken
        $this->assertIsString($token->plainTextToken);
    }

    public function test_user_email_is_unique()
    {
        User::create([
            'name' => 'User 1',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'name' => 'User 2',
            'email' => 'test@example.com', // Same email
            'password' => 'password123',
            'role' => 'user',
        ]);
    }
}
