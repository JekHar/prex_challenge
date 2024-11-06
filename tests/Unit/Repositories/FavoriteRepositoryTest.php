<?php

namespace Tests\Unit\Repositories;

use App\DTO\FavoriteGifDTO;
use App\Models\GifFavorite;
use App\Models\User;
use App\Repositories\FavoriteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteRepository $repository;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FavoriteRepository(new GifFavorite());
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_favorite(): void
    {
        $dto = new FavoriteGifDTO(
            id: null,
            gifId: 'test123',
            alias: 'Test GIF'
        );

        $result = $this->repository->createFavorite($dto, $this->user->id);

        $this->assertInstanceOf(FavoriteGifDTO::class, $result);
        $this->assertDatabaseHas('gif_favorites', [
            'gif_id' => 'test123',
            'alias' => 'Test GIF',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function it_can_get_user_favorites(): void
    {
        GifFavorite::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $result = $this->repository->getUserFavorites($this->user->id, 2);

        $this->assertEquals(2, $result->perPage());
        $this->assertEquals(3, $result->total());
        $this->assertInstanceOf(FavoriteGifDTO::class, $result->items()[0]);
    }

    /** @test */
    public function it_can_delete_favorite(): void
    {
        $favorite = GifFavorite::factory()->create([
            'user_id' => $this->user->id
        ]);

        $result = $this->repository->deleteFavorite($favorite->id, $this->user->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('gif_favorites', ['id' => $favorite->id]);
    }

    /** @test */
    public function it_returns_false_when_deleting_non_existent_favorite(): void
    {
        $result = $this->repository->deleteFavorite(999, $this->user->id);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_find_favorite(): void
    {
        $favorite = GifFavorite::factory()->create([
            'user_id' => $this->user->id
        ]);

        $result = $this->repository->findFavorite($favorite->id, $this->user->id);

        $this->assertInstanceOf(FavoriteGifDTO::class, $result);
        $this->assertEquals($favorite->id, $result->id);
    }
}
