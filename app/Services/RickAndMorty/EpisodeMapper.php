<?php

namespace App\Services\RickAndMorty;

use InvalidArgumentException;

class EpisodeMapper
{
    public function map(array $data): EpisodeData
    {
        if (!isset($data['id']) || !is_int($data['id'])) {
            throw new InvalidArgumentException(
                'Invalid episode: missing or invalid id.'
            );
        }

        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new InvalidArgumentException(
                'Invalid episode: missing or invalid name.'
            );
        }

        return new EpisodeData(
            externalId: $data['id'],
            name: $data['name'],
            airDate: $data['air_date'] ?? null,
            episodeCode: $data['episode'] ?? null,
        );
    }
}