<?php

namespace App\Contracts;

use App\DTO\FavoriteGifDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface FavoriteRepositoryInterface
{
    public function createFavorite(FavoriteGifDTO $favoriteData, int $userId): FavoriteGifDTO;
    public function getUserFavorites(int $userId, int $perPage = 15): LengthAwarePaginator;
    public function deleteFavorite(int $favoriteId, int $userId): bool;
    public function findFavorite(int $favoriteId, int $userId): ?FavoriteGifDTO;
}
