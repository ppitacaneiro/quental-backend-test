<?php

namespace App\Mappers;

use InvalidArgumentException;
use App\Dtos\LocationDto;

class LocationMapper
{
    public function map(array $data): LocationDto
    {
        if (!isset($data['id']) || !is_int($data['id'])) {
            throw new InvalidArgumentException(
                'Invalid location: missing or invalid id.'
            );
        }

        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new InvalidArgumentException(
                'Invalid location: missing or invalid name.'
            );
        }

        return new LocationDto(
            externalId: $data['id'],
            name: $data['name'],
            type: $data['type'] ?? null,
            dimension: $data['dimension'] ?? null,
        );
    }
}