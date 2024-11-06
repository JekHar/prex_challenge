<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GifFavorite;
use App\Contracts\GiphyServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GifFavoritesFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function authenticateUser(): void
    {
        Passport::actingAs($this->user);
    }

    public function user_can_add_gif_to_favorites(): void
    {
        $this->authenticateUser();

        $this->mock(GiphyServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getById')
                ->with('test123')
                ->andReturn(['data' => ['id' => 'test123']]);
        });

        $response = $this->postJson('/api/v1/gifs/favorites', [
            'gif_id' => 'test123',
            'alias' => 'Test Favorite'
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'gif_id', 'alias']]);

        $this->assertDatabaseHas('gif_favorites', [
            'gif_id' => 'test123',
            'alias' => 'Test Favorite',
            'user_id' => $this->user->id
        ]);
    }

    public function user_can_only_see_their_own_favorites(): void
    {
        $this->authenticateUser();

        $otherUser = User::factory()->create();

        GifFavorite::factory()->create([
            'user_id' => $this->user->id,
            'gif_id' => 'user_gif'
        ]);

        GifFavorite::factory()->create([
            'user_id' => $otherUser->id,
            'gif_id' => 'other_gif'
        ]);

        $response = $this->getJson('/api/v1/gifs/favorites');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.gif_id', 'user_gif');
    }

    public function user_can_delete_their_own_favorite(): void
    {
        $this->authenticateUser();

        $favorite = GifFavorite::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->deleteJson("/api/v1/gifs/favorites/{$favorite->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('gif_favorites', ['id' => $favorite->id]);
    }

    public function user_cant_delete_others_favorites(): void
    {
        $this->authenticateUser();

        $otherUser = User::factory()->create();
        $favorite = GifFavorite::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->deleteJson("/api/v1/gifs/favorites/{$favorite->id}");

        $response->assertNotFound();
        $this->assertDatabaseHas('gif_favorites', ['id' => $favorite->id]);
    }

    public function favorites_are_paginated(): void
    {
        $this->authenticateUser();

        GifFavorite::factory()->count(15)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson('/api/v1/gifs/favorites');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => ['current_page', 'last_page', 'per_page', 'total']
            ]);
    }

    public function adding_non_existent_gif_returns_404(): void
    {
        $this->authenticateUser();

        $this->mock(GiphyServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getById')
                ->andThrow(new \App\Exceptions\GiphyNotFoundException('GIF not found'));
        });

        $response = $this->postJson('/api/v1/gifs/favorites', [
            'gif_id' => 'nonexistent',
            'alias' => 'Test'
        ]);

        $response->assertNotFound();
    }
}
