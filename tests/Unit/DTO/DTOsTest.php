<?php

namespace Tests\Unit\DTO;

use App\DTO\FavoriteGifDTO;
use App\DTO\GiphyGifDTO;
use App\Models\GifFavorite;
use DateTime;
use Tests\TestCase;

class DTOsTest extends TestCase
{
    /** @test */
    public function it_creates_favorite_dto_from_request(): void
    {
        $requestData = [
            'gif_id' => '12345',
            'alias' => 'Funny Cat',
            'user_id' => 1
        ];

        $dto = FavoriteGifDTO::fromRequest($requestData);

        $this->assertNull($dto->id);
        $this->assertEquals('12345', $dto->gifId);
        $this->assertEquals('Funny Cat', $dto->alias);
    }

    /** @test */
    public function it_creates_favorite_dto_from_model(): void
    {
        $model = new GifFavorite([
            'gif_id' => '12345',
            'alias' => 'Funny Cat',
            'user_id' => 1
        ]);
        $model->id = 1;
        $model->created_at = now();
        $model->updated_at = now();

        $dto = FavoriteGifDTO::fromModel($model);

        $this->assertEquals(1, $dto->id);
        $this->assertEquals('12345', $dto->gifId);
        $this->assertEquals('Funny Cat', $dto->alias);
    }

    /** @test */
    public function it_creates_giphy_gif_dto_from_api_response(): void
    {
        $apiData = [
            'id' => '12345',
            'title' => 'Funny Cat GIF',
            'url' => 'https://giphy.com/gifs/12345',
            'images' => [
                'original' => [
                    'url' => 'https://media.giphy.com/media/12345/giphy.gif',
                    'width' => '480',
                    'height' => '270',
                ],
                'preview' => [
                    'url' => 'https://media.giphy.com/media/12345/giphy-preview.gif',
                ]
            ]
        ];

        $dto = GiphyGifDTO::fromApiResponse($apiData);

        $this->assertEquals('12345', $dto->id);
        $this->assertEquals('Funny Cat GIF', $dto->title);
        $this->assertEquals('https://giphy.com/gifs/12345', $dto->url);
        $this->assertArrayHasKey('original', $dto->images);
        $this->assertArrayHasKey('preview', $dto->images);
    }
}
