<?php

namespace App\Services\RickAndMorty;

class EpisodeData
{
    public function __construct(
        public readonly int $externalId,
        public readonly string $name,
        public readonly ?string $airDate,
        public readonly ?string $episodeCode,
    ) {
    }
}