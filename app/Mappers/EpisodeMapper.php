<?php

namespace App\Mappers;

use InvalidArgumentException;
use App\Dtos\EpisodeDto;

class EpisodeMapper
{
    public function map(array $data): EpisodeDto
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

        return new EpisodeDto(
            externalId: $data['id'],
            name: $data['name'],
            airDate: $data['air_date'] ?? null,
            episodeCode: $data['episode'] ?? null,
        );
    }
}