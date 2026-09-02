<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\RickAndMorty\RickAndMortyClient;
use App\Services\RickAndMorty\RickAndMortyHttpClient;

class RickAndMortyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            RickAndMortyClient::class,
            RickAndMortyHttpClient::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
