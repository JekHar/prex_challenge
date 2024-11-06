<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\GifController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {

    Route::post('login', [ApiAuthController::class, 'login'])->name('login');
    Route::post('refresh-token', [ApiAuthController::class, 'refreshToken']);

    Route::middleware(['auth:api', 'log.api'])->group(function () {

        Route::post('logout', [ApiAuthController::class, 'logout']);
        Route::get('user', [ApiAuthController::class, 'user']);

        Route::prefix('gifs')->group(function () {

            Route::get('search', [GifController::class, 'search']);
            Route::get('{id}', [GifController::class, 'show']);

            Route::prefix('favorites')->group(function () {
                Route::get('/', [GifController::class, 'getFavorites']);
                Route::post('/', [GifController::class, 'storeFavorite']);
                Route::delete('{favorite}', [GifController::class, 'deleteFavorite'])
                    ->whereNumber('favorite');
            });
        });
    });

});
