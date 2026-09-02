<?php

namespace App\Integrations\RickAndMorty;

use InvalidArgumentException;

class RickAndMortyResponseValidator
{
    public function validate(array $response): void
    {
        if (!isset($response['info']) || !is_array($response['info'])) {
            throw new InvalidArgumentException(
                'Invalid Rick and Morty response: missing info.'
            );
        }

        if (!isset($response['info']['count']) || !is_int($response['info']['count'])) {
            throw new InvalidArgumentException(
                'Invalid Rick and Morty response: invalid info.count.'
            );
        }

        if (!isset($response['info']['pages']) || !is_int($response['info']['pages'])) {
            throw new InvalidArgumentException(
                'Invalid Rick and Morty response: invalid info.pages.'
            );
        }

        if (!isset($response['results']) || !is_array($response['results'])) {
            throw new InvalidArgumentException(
                'Invalid Rick and Morty response: missing results.'
            );
        }

        foreach ($response['results'] as $result) {
            if (!is_array($result)) {
                throw new InvalidArgumentException(
                    'Invalid Rick and Morty response: invalid result.'
                );
            }
        }
    }
}