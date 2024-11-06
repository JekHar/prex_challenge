<?php

namespace App\Http\Controllers;

use App\Contracts\GiphyServiceInterface;
use App\Contracts\FavoriteRepositoryInterface;
use App\DTO\FavoriteGifDTO;
use App\Exceptions\FavoriteOperationException;
use App\Http\Requests\SearchGifsRequest;
use App\Http\Requests\StoreGifFavoriteRequest;
use App\Http\Resources\GifFavoriteResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GifController extends Controller
{
    public function __construct(
        private readonly GiphyServiceInterface $giphyService,
        private readonly FavoriteRepositoryInterface $favoriteRepository
    ) {}

    public function search(SearchGifsRequest $request): JsonResponse
    {
        $results = $this->giphyService->search(
            query: $request->input('query'),
            limit: $request->input('limit', 25),
            offset: $request->input('offset', 0)
        );

        return response()->json($results);
    }

    public function show(string $id): JsonResponse
    {
        $gif = $this->giphyService->getById($id);
        return response()->json($gif);
    }

    public function storeFavorite(StoreGifFavoriteRequest $request): JsonResponse
    {
        $this->giphyService->getById($request->gif_id);

        $favoriteDto = FavoriteGifDTO::fromRequest($request->validated());
        $favorite = $this->favoriteRepository->createFavorite(
            $favoriteDto,
            auth()->id()
        );

        return response()->json(new GifFavoriteResource($favorite), 201);
    }

    public function getFavorites(): ResourceCollection
    {
        $favorites = $this->favoriteRepository->getUserFavorites(auth()->id());
        return GifFavoriteResource::collection($favorites);
    }

    public function deleteFavorite(int $id): JsonResponse
    {
        $deleted = $this->favoriteRepository->deleteFavorite($id, auth()->id());

        if (!$deleted) {
            throw FavoriteOperationException::notFound($id);
        }

        return response()->json(['message' => 'Favorite deleted successfully']);
    }
}
