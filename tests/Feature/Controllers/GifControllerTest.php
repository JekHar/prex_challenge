<?php

namespace Tests\Feature\Controllers;

use App\Contracts\GiphyServiceInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Mockery;

class GifControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private $mockGiphyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function it_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/gifs/search?query=test');
        $response->assertUnauthorized();
    }

    protected function setUpGiphyMock(): void
    {
        $this->mockGiphyService = Mockery::mock(GiphyServiceInterface::class);
        $this->app->instance(GiphyServiceInterface::class, $this->mockGiphyService);
        Passport::actingAs($this->user);
    }

    public function it_validates_search_input(): void
    {
        $this->setUpGiphyMock();
        $response = $this->getJson('/api/v1/gifs/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['query']);
    }

    public function it_handles_search_errors(): void
    {
        $this->setUpGiphyMock();
        $this->mockGiphyService
            ->shouldReceive('search')
            ->once()
            ->with('test', 25, 0)
            ->andThrow(new \App\Exceptions\GiphySearchException('Search failed'));

        $response = $this->getJson('/api/v1/gifs/search?query=test');

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Search failed',
                'error_type' => 'giphy_search_error'
            ]);
    }

    public function it_can_search_gifs(): void
    {
        $this->setUpGiphyMock();
        $expectedResults = [
            'data' => [
                [
                    'id' => '1',
                    'title' => 'Test GIF',
                    'type' => 'gif',
                    'url' => 'https://test.com/1',
                    'rating' => 'g',
                    'images' => []
                ]
            ],
            'pagination' => [
                'total_count' => 1,
                'count' => 1,
                'offset' => 0
            ],
            'meta' => [
                'status' => 200,
                'msg' => 'OK'
            ]
        ];

        $this->mockGiphyService
            ->shouldReceive('search')
            ->once()
            ->with('test', 25, 0)
            ->andReturn($expectedResults);

        $response = $this->getJson('/api/v1/gifs/search?query=test');

        $response->assertOk()
            ->assertJson($expectedResults);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
