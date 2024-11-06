<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GiphyIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
    }

    /** @test */
    public function it_can_search_gifs_through_giphy_api(): void
    {
        if (!config('services.giphy.api_key')) {
            $this->markTestSkipped('GIPHY API key not configured.');
        }

        $response = $this->getJson('/api/v1/gifs/search?query=test');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'url'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_get_specific_gif_from_giphy_api(): void
    {
        if (!config('services.giphy.api_key')) {
            $this->markTestSkipped('GIPHY API key not configured.');
        }

        $searchResponse = $this->getJson('/api/v1/gifs/search?query=test');
        $gifId = $searchResponse->json('data.0.id');

        $response = $this->getJson("/api/v1/gifs/{$gifId}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'url'
                ]
            ]);
    }
}
