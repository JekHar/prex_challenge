<?php

namespace Tests\Traits;

use Laravel\Passport\Client;

trait PassportTestHelper
{
    protected function setUpPassport(): void
    {
        $client = Client::factory()->create([
            'user_id' => null,
            'name' => 'Test Personal Access Client',
            'secret' => 'secret',
            'provider' => 'users',
            'redirect' => 'http://localhost',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
        ]);

        config([
            'passport.personal_access_client.id' => $client->id,
            'passport.personal_access_client.secret' => $client->secret
        ]);
    }
}
