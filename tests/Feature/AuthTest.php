<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    /** @test */
    public function user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User ' . uniqid(),
            'email' => uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email'],
                     'token'
                 ]);
    }

    /** @test */
    public function user_can_login()
    {
        $email = uniqid() . '@example.com';

        // Create user
        $this->postJson('/api/register', [
            'name' => 'Login User',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Login
        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email'],
                     'token'
                 ]);
    }

    /** @test */
    public function user_can_update()
    {
        $email = uniqid() . '@example.com';

        // Register user
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Old Name',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $token = $registerResponse['token'];
        $userId = $registerResponse['user']['id'];

        // Update user
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->putJson("/api/users/{$userId}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => 'Updated Name',
                 ]);
    }

    /** @test */
    public function user_can_delete()
    {
        $email = uniqid() . '@example.com';

        // Register user
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Delete Me',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $token = $registerResponse['token'];
        $userId = $registerResponse['user']['id'];

        // Delete user
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->deleteJson("/api/users/{$userId}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'message' => 'User deleted successfully',
                 ]);
    }
}
