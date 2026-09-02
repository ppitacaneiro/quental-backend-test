<?php

namespace App\Services\RickAndMorty;

class CharacterData
{
    public function __construct(
        public readonly int $externalId,
        public readonly string $name,
        public readonly ?string $status,
        public readonly ?string $species,
        public readonly ?string $type,
        public readonly ?string $gender,
        public readonly ?string $image,
        public readonly ?int $originLocationExternalId,
        public readonly ?int $currentLocationExternalId,
    ) {
    }
}