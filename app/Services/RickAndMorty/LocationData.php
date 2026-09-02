<?php

namespace App\Services\RickAndMorty;

class LocationData
{
    public function __construct(
        public readonly int $externalId,
        public readonly string $name,
        public readonly ?string $type,
        public readonly ?string $dimension,
    ) {
    }
}