<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_token_payload(): void
    {
        $service = new AuthService(new UserRepository());

        $result = $service->register([
            'name' => 'Ava Founder',
            'email' => 'Ava@Example.com',
            'password' => 'Password123!',
        ], 'unit-test');

        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertSame('ava@example.com', $result['user']->email);
        $this->assertSame('Bearer', $result['token_type']);
        $this->assertNotEmpty($result['token']);
    }
}
