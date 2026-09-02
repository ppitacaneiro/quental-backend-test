<?php

namespace App\Services\RickAndMorty;

interface RickAndMortyClient
{
    public function getCharacters(int $page = 1): array;

    public function getEpisodes(int $page = 1): array;

    public function getLocations(int $page = 1): array;
}