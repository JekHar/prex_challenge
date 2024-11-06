<?php

namespace Tests\Unit\Controllers\Auth;

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Laravel\Passport\Token;
use Mockery;

class ApiAuthControllerTest extends TestCase
{
    protected ApiAuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ApiAuthController();
    }

    /** @test */
    public function login_returns_token_response_when_credentials_are_valid(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response([
                'token_type' => 'Bearer',
                'expires_in' => 31536000,
                'access_token' => 'fake-access-token',
                'refresh_token' => 'fake-refresh-token'
            ], 200)
        ]);

        $request = new LoginRequest([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response = $this->controller->login($request);

        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('access_token', $response->getData(true));
    }

    /** @test */
    public function login_returns_unauthorized_when_credentials_are_invalid(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['error' => 'invalid_credentials'], 401)
        ]);

        $request = new LoginRequest([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response = $this->controller->login($request);

        $this->assertEquals(401, $response->status());
        $this->assertEquals('Unauthorized', $response->getData(true)['message']);
    }

    /** @test */
    public function logout_revokes_token(): void
    {
        // Create mock token
        $token = Mockery::mock(Token::class);
        $token->shouldReceive('revoke')->once()->andReturn(true);

        // Create mock user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('token')->once()->andReturn($token);

        // Create request with mock user
        $request = Request::create('/logout', 'POST');
        $request->setUserResolver(fn () => $user);

        $response = $this->controller->logout($request);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Successfully logged out', $response->getData(true)['message']);
    }

    /** @test */
    public function user_returns_authenticated_user(): void
    {
        $user = new User([
            'id' => 1,
            'email' => 'test@example.com'
        ]);

        $request = Request::create('/user', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->controller->user($request);

        $this->assertEquals(200, $response->status());
        $this->assertEquals($user->toArray(), $response->getData(true));
    }

    /** @test */
    public function refresh_token_returns_new_tokens_when_valid(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response([
                'token_type' => 'Bearer',
                'expires_in' => 31536000,
                'access_token' => 'new-fake-access-token',
                'refresh_token' => 'new-fake-refresh-token'
            ], 200)
        ]);

        $request = Request::create('/refresh-token', 'POST', [
            'refresh_token' => 'valid-refresh-token'
        ]);

        $response = $this->controller->refreshToken($request);

        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('access_token', $response->getData(true));
    }

    /** @test */
    public function refresh_token_returns_unauthorized_when_invalid(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['error' => 'invalid_token'], 401)
        ]);

        $request = Request::create('/refresh-token', 'POST', [
            'refresh_token' => 'invalid-refresh-token'
        ]);

        $response = $this->controller->refreshToken($request);

        $this->assertEquals(401, $response->status());
        $this->assertEquals('Invalid refresh token', $response->getData(true)['message']);
    }
}
