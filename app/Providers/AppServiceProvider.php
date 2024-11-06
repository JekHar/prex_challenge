<?php

namespace App\Providers;

use App\Contracts\FavoriteRepositoryInterface;
use App\Contracts\GiphyServiceInterface;
use App\Repositories\FavoriteRepository;
use App\Services\GiphyService;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GiphyServiceInterface::class, function ($app) {
            return new GiphyService(
                config('services.giphy.api_key')
            );
        });

        $this->app->bind(FavoriteRepositoryInterface::class, FavoriteRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::enablePasswordGrant();
    }
}
