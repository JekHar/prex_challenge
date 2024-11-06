<?php

namespace App\Repositories;

use App\Contracts\FavoriteRepositoryInterface;
use App\DTO\FavoriteGifDTO;
use App\Models\GifFavorite;
use Illuminate\Pagination\LengthAwarePaginator;

class FavoriteRepository implements FavoriteRepositoryInterface
{
    private GifFavorite $model;

    public function __construct(GifFavorite $model)
    {
        $this->model = $model;
    }

    public function createFavorite(FavoriteGifDTO $favoriteData, int $userId): FavoriteGifDTO
    {
        $favorite = $this->model->create([
            'gif_id' => $favoriteData->gifId,
            'alias' => $favoriteData->alias,
            'user_id' => $userId
        ]);

        return FavoriteGifDTO::fromModel($favorite);
    }

    public function getUserFavorites(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn ($favorite) => FavoriteGifDTO::fromModel($favorite));
    }

    public function deleteFavorite(int $favoriteId, int $userId): bool
    {
        return $this->model
            ->where('id', $favoriteId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function findFavorite(int $favoriteId, int $userId): ?FavoriteGifDTO
    {
        $favorite = $this->model
            ->where('id', $favoriteId)
            ->where('user_id', $userId)
            ->first();

        return $favorite ? FavoriteGifDTO::fromModel($favorite) : null;
    }
}
