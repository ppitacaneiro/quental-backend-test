<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Integrations\RickAndMorty\RickAndMortyClient;
use App\Integrations\RickAndMorty\RickAndMortyHttpClient;

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
