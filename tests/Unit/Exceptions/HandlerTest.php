<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\GiphyNotFoundException;
use App\Exceptions\GiphySearchException;
use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class HandlerTest extends TestCase
{
    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = app(Handler::class);

        Route::get('/api/test-route', function () {
            throw new GiphyNotFoundException('Test GIF not found');
        })->middleware('api');
    }

    /** @test */
    public function it_handles_giphy_not_found_exceptions(): void
    {
        $response = $this->getJson('/api/test-route');

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Test GIF not found',
                'error_type' => 'giphy_not_found'
            ]);
    }

    /** @test */
    public function it_handles_giphy_search_exceptions(): void
    {
        Route::get('/api/test-search', function () {
            throw new GiphySearchException('Search failed');
        })->middleware('api');

        $response = $this->getJson('/api/test-search');

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Search failed',
                'error_type' => 'giphy_search_error'
            ]);
    }

    /** @test */
    public function it_handles_validation_exceptions(): void
    {
        Route::post('/api/test-validation', function () {
            request()->validate([
                'required_field' => 'required'
            ]);
        })->middleware('api');

        $response = $this->postJson('/api/test-validation', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['required_field']);
    }

    /** @test */
    public function it_handles_authentication_exceptions(): void
    {
        Route::get('/api/test-auth', function () {
            throw new AuthenticationException();
        })->middleware('api');

        $response = $this->getJson('/api/test-auth');

        $response->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function it_includes_error_id_in_production_for_unexpected_errors(): void
    {
        config(['app.debug' => false]);

        Route::get('/api/test-error', function () {
            throw new \Exception('Unexpected error');
        })->middleware('api');

        $response = $this->getJson('/api/test-error');

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'An unexpected error occurred.'
            ])
            ->assertJsonStructure(['error_id']);
    }
}
