<?php

namespace App\Integrations\RickAndMorty;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RickAndMortyHttpClient implements RickAndMortyClient
{
    private const BASE_URL = 'https://rickandmortyapi.com/api';

    private function client(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->timeout(10)
            ->retry(3, 200);
    }

    public function getCharacters(int $page = 1): array
    {
        return $this->client()
            ->get('/character', ['page' => $page])
            ->throw()
            ->json();
    }

    public function getEpisodes(int $page = 1): array
    {
        return $this->client()
            ->get('/episode', ['page' => $page])
            ->throw()
            ->json();
    }

    public function getLocations(int $page = 1): array
    {
        return $this->client()
            ->get('/location', ['page' => $page])
            ->throw()
            ->json();
    }
}