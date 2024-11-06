<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;
    protected Client $personalAccessClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('passport:keys');

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $clientRepository = new ClientRepository();

        $this->client = $clientRepository->create(
            null, 'Test Password Grant Client', 'http://localhost', true, false, true
        );

        $this->personalAccessClient = $clientRepository->create(
            null, 'Test Personal Access Client', 'http://localhost'
        );

        DB::table('oauth_personal_access_clients')->insert([
            'client_id' => $this->personalAccessClient->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        config([
            'auth.guards.api.provider' => 'users',
            'passport.password_grant_client' => [
                'id' => $this->client->id,
                'secret' => $this->client->secret,
            ],
            'passport.private_key' => storage_path('oauth-private.key'),
            'passport.public_key' => storage_path('oauth-public.key')
        ]);
    }

    /** @test */
    public function user_cannot_access_protected_routes_without_token(): void
    {
        $response = $this->getJson('/api/v1/user');
        $response->assertUnauthorized();
    }

    /** @test */
    public function user_can_login_and_access_protected_routes(): void
    {
        Passport::actingAs($this->user);
        $response = $this->getJson('/api/v1/user');
        $response->assertOk()->assertJson(['email' => 'test@example.com']);
    }

    /** @test */
    public function user_can_refresh_token(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertOk();
        $refreshToken = $response->json('refresh_token');

        $response = $this->postJson('/api/v1/refresh-token', [
            'refresh_token' => $refreshToken
        ]);

        $response->assertOk()->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
        ]);
    }

    /** @test */
    public function invalid_tokens_are_rejected(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer invalid-token'])
            ->getJson('/api/v1/user')
            ->assertUnauthorized();
    }
}
