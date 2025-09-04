<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function password_is_hashed_when_user_created()
    {
        // Generate a unique email to avoid duplicate errors
        $uniqueEmail = 'unittest+' . time() . '@example.com';

        $user = User::factory()->create([
            'name' => 'Unit Test User',
            'email' => $uniqueEmail,
            'password' => 'password123',
        ]);

        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function jwt_identifier_returns_user_id()
    {
        $user = User::factory()->make(['id' => 99]);
        $this->assertEquals(99, $user->getJWTIdentifier());
    }

    /** @test */
    public function jwt_custom_claims_is_empty_array()
    {
        $user = User::factory()->make();
        $this->assertEquals([], $user->getJWTCustomClaims());
    }
}
