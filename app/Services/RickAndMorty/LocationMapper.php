<?php

namespace App\Services\RickAndMorty;

use InvalidArgumentException;

class LocationMapper
{
    public function map(array $data): LocationData
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

        return new LocationData(
            externalId: $data['id'],
            name: $data['name'],
            type: $data['type'] ?? null,
            dimension: $data['dimension'] ?? null,
        );
    }
}