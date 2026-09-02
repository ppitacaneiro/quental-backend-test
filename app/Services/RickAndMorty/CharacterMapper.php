<?php

namespace App\Services\RickAndMorty;

use InvalidArgumentException;

class CharacterMapper
{
    public function map(array $data): CharacterData
    {
        if (!isset($data['id']) || !is_int($data['id'])) {
            throw new InvalidArgumentException(
                'Invalid character: missing or invalid id.'
            );
        }

        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new InvalidArgumentException(
                'Invalid character: missing or invalid name.'
            );
        }

        return new CharacterData(
            externalId: $data['id'],
            name: $data['name'],
            status: $data['status'] ?? null,
            species: $data['species'] ?? null,
            type: $data['type'] ?? null,
            gender: $data['gender'] ?? null,
            image: $data['image'] ?? null,
            originLocationExternalId: $this->extractLocationId(
                $data['origin']['url'] ?? null
            ),
            currentLocationExternalId: $this->extractLocationId(
                $data['location']['url'] ?? null
            ),
        );
    }

    private function extractLocationId(?string $url): ?int
    {
        if (!$url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (!$path) {
            return null;
        }

        $id = basename($path);

        return is_numeric($id) ? (int) $id : null;
    }
}