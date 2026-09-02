<?php

namespace App\Dtos;

class LocationDto
{
    public function __construct(
        public readonly int $externalId,
        public readonly string $name,
        public readonly ?string $type,
        public readonly ?string $dimension,
    ) {
    }
}