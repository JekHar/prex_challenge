<?php

namespace App\DTO;

use App\Models\GifFavorite;
use DateTime;

class FavoriteGifDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $gifId,
        public readonly string $alias,
        public readonly ?DateTime $createdAt = null,
        public readonly ?DateTime $updatedAt = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: null,
            gifId: $data['gif_id'],
            alias: $data['alias']
        );
    }

    public static function fromModel(GifFavorite $model): self
    {
        return new self(
            id: $model->id,
            gifId: $model->gif_id,
            alias: $model->alias,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }
}
