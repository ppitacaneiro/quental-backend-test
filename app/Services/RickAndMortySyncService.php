<?php

namespace App\Services;

use App\Integrations\RickAndMorty\RickAndMortyClient;
use App\Integrations\RickAndMorty\RickAndMortyResponseValidator;
use App\Mappers\LocationMapper;
use App\Mappers\CharacterMapper;
use App\Models\Location;
use App\Models\Character;

class RickAndMortySyncService
{
    public function __construct(
        private readonly RickAndMortyClient $client,
        private readonly RickAndMortyResponseValidator $validator,
        private readonly LocationMapper $locationMapper,
        private readonly CharacterMapper $characterMapper,
    ) {
    }

    public function syncLocations(): void
    {
        $page = 1;

        do {
            $response = $this->client->getLocations($page);

            $this->validator->validate($response);

            foreach ($response['results'] as $result) {
                $location = $this->locationMapper->map($result);

                Location::updateOrCreate(
                    ['external_id' => $location->externalId],
                    [
                        'name' => $location->name,
                        'type' => $location->type,
                        'dimension' => $location->dimension,
                    ]
                );
            }

            $pages = $response['info']['pages'];
            $page++;
        } while ($page <= $pages);
    }

    public function syncCharacters(): void
    {
        $page = 1;

        do {
            $response = $this->client->getCharacters($page);

            $this->validator->validate($response);

            foreach ($response['results'] as $result) {
                $character = $this->characterMapper->map($result);

                $originLocationId = $this->findLocationId(
                    $character->originLocationExternalId
                );

                $currentLocationId = $this->findLocationId(
                    $character->currentLocationExternalId
                );

                Character::updateOrCreate(
                    ['external_id' => $character->externalId],
                    [
                        'name' => $character->name,
                        'status' => $character->status,
                        'species' => $character->species,
                        'type' => $character->type,
                        'gender' => $character->gender,
                        'image' => $character->image,
                        'origin_location_id' => $originLocationId,
                        'current_location_id' => $currentLocationId,
                    ]
                );
            }

            $pages = $response['info']['pages'];
            $page++;
        } while ($page <= $pages);
    }

    private function findLocationId(?int $externalId): ?int
    {
        if ($externalId === null) {
            return null;
        }

        return Location::where('external_id', $externalId)->value('id');
    }
}