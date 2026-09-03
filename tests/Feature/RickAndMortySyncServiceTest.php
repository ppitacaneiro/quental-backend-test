<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Episode;
use App\Models\Location;
use App\Services\RickAndMortySyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class RickAndMortySyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private RickAndMortySyncService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(RickAndMortySyncService::class);
    }

    public function test_sync_locations_saves_locations_to_database_and_handles_pagination(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/location?page=1' => Http::response([
                'info' => ['count' => 2, 'pages' => 2],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Earth (C-137)',
                        'type' => 'Planet',
                        'dimension' => 'Dimension C-137',
                    ],
                ],
            ], 200),
            'https://rickandmortyapi.com/api/location?page=2' => Http::response([
                'info' => ['count' => 2, 'pages' => 2],
                'results' => [
                    [
                        'id' => 2,
                        'name' => 'Abadango',
                        'type' => 'Cluster',
                        'dimension' => 'unknown',
                    ],
                ],
            ], 200),
        ]);

        $this->service->syncLocations();

        $this->assertDatabaseCount('locations', 2);
        $this->assertDatabaseHas('locations', [
            'external_id' => 1,
            'name' => 'Earth (C-137)',
            'type' => 'Planet',
            'dimension' => 'Dimension C-137',
        ]);
        $this->assertDatabaseHas('locations', [
            'external_id' => 2,
            'name' => 'Abadango',
            'type' => 'Cluster',
            'dimension' => 'unknown',
        ]);
    }

    public function test_sync_locations_is_idempotent(): void
    {
        $requestCount = 0;

        Http::fake(function ($request) use (&$requestCount) {
            $requestCount++;

            return Http::response([
                'info' => ['count' => 1, 'pages' => 1],
                'results' => [
                    [
                        'id' => 1,
                        'name' => $requestCount === 1
                            ? 'Earth (C-137)'
                            : 'Earth (Replacement)',
                        'type' => 'Planet',
                        'dimension' => $requestCount === 1
                            ? 'Dimension C-137'
                            : 'Replacement Dimension',
                    ],
                ],
            ], 200);
        });

        $this->service->syncLocations();

        $this->assertDatabaseCount('locations', 1);

        $this->service->syncLocations();

        $this->assertDatabaseCount('locations', 1);

        $this->assertDatabaseHas('locations', [
            'external_id' => 1,
            'name' => 'Earth (Replacement)',
            'dimension' => 'Replacement Dimension',
        ]);
    }

    public function test_sync_episodes_saves_episodes_to_database_and_handles_pagination(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/episode?page=1' => Http::response([
                'info' => ['count' => 2, 'pages' => 2],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Pilot',
                        'air_date' => 'December 2, 2013',
                        'episode' => 'S01E01',
                    ],
                ],
            ], 200),
            'https://rickandmortyapi.com/api/episode?page=2' => Http::response([
                'info' => ['count' => 2, 'pages' => 2],
                'results' => [
                    [
                        'id' => 2,
                        'name' => 'Lawnmower Dog',
                        'air_date' => 'December 9, 2013',
                        'episode' => 'S01E02',
                    ],
                ],
            ], 200),
        ]);

        $this->service->syncEpisodes();

        $this->assertDatabaseCount('episodes', 2);
        $this->assertDatabaseHas('episodes', [
            'external_id' => 1,
            'name' => 'Pilot',
            'air_date' => 'December 2, 2013',
            'episode_code' => 'S01E01',
        ]);
        $this->assertDatabaseHas('episodes', [
            'external_id' => 2,
            'name' => 'Lawnmower Dog',
            'air_date' => 'December 9, 2013',
            'episode_code' => 'S01E02',
        ]);
    }

    public function test_sync_episodes_is_idempotent(): void
    {
        $requestCount = 0;

        Http::fake(function ($request) use (&$requestCount) {
            $requestCount++;

            return Http::response([
                'info' => ['count' => 1, 'pages' => 1],
                'results' => [
                    [
                        'id' => 1,
                        'name' => $requestCount === 1
                            ? 'Pilot'
                            : 'Pilot (Updated Name)',
                        'air_date' => 'December 2, 2013',
                        'episode' => 'S01E01',
                    ],
                ],
            ], 200);
        });

        $this->service->syncEpisodes();

        $this->assertDatabaseCount('episodes', 1);

        $this->service->syncEpisodes();

        $this->assertDatabaseCount('episodes', 1);

        $this->assertDatabaseHas('episodes', [
            'external_id' => 1,
            'name' => 'Pilot (Updated Name)',
        ]);
    }

    public function test_sync_characters_maps_locations_and_episodes_correctly_when_they_exist(): void
    {
        $originLocation = Location::create([
            'external_id' => 1,
            'name' => 'Earth (C-137)',
            'type' => 'Planet',
            'dimension' => 'Dimension C-137',
        ]);

        $currentLocation = Location::create([
            'external_id' => 20,
            'name' => 'Earth (Replacement Dimension)',
            'type' => 'Planet',
            'dimension' => 'Replacement Dimension',
        ]);

        $episode1 = Episode::create([
            'external_id' => 1,
            'name' => 'Pilot',
            'air_date' => 'December 2, 2013',
            'episode_code' => 'S01E01',
        ]);

        $episode2 = Episode::create([
            'external_id' => 2,
            'name' => 'Lawnmower Dog',
            'air_date' => 'December 9, 2013',
            'episode_code' => 'S01E02',
        ]);

        Http::fake([
            'https://rickandmortyapi.com/api/character*' => Http::response([
                'info' => ['count' => 1, 'pages' => 1],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Rick Sanchez',
                        'status' => 'Alive',
                        'species' => 'Human',
                        'type' => '',
                        'gender' => 'Male',
                        'image' => 'https://rickandmortyapi.com/api/character/avatar/1.jpeg',
                        'origin' => [
                            'name' => 'Earth (C-137)',
                            'url' => 'https://rickandmortyapi.com/api/location/1',
                        ],
                        'location' => [
                            'name' => 'Earth (Replacement Dimension)',
                            'url' => 'https://rickandmortyapi.com/api/location/20',
                        ],
                        'episode' => [
                            'https://rickandmortyapi.com/api/episode/1',
                            'https://rickandmortyapi.com/api/episode/2',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->service->syncCharacters();

        $this->assertDatabaseCount('characters', 1);

        $character = Character::first();
        $this->assertNotNull($character);
        $this->assertSame(1, $character->external_id);
        $this->assertSame('Rick Sanchez', $character->name);
        $this->assertSame($originLocation->id, $character->origin_location_id);
        $this->assertSame($currentLocation->id, $character->current_location_id);

        $this->assertDatabaseHas('character_episode', [
            'character_id' => $character->id,
            'episode_id' => $episode1->id,
        ]);
        $this->assertDatabaseHas('character_episode', [
            'character_id' => $character->id,
            'episode_id' => $episode2->id,
        ]);
    }

    public function test_sync_characters_handles_missing_locations_and_episodes_gracefully(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/character*' => Http::response([
                'info' => ['count' => 1, 'pages' => 1],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Alien Mortynstein',
                        'status' => 'Unknown',
                        'species' => 'Alien',
                        'type' => 'Monster',
                        'gender' => 'Genderless',
                        'image' => 'https://rickandmortyapi.com/api/character/avatar/99.jpeg',
                        'origin' => [
                            'name' => 'Unknown Location',
                            'url' => 'https://rickandmortyapi.com/api/location/999',
                        ],
                        'location' => [
                            'name' => 'Unknown Location',
                            'url' => 'https://rickandmortyapi.com/api/location/888',
                        ],
                        'episode' => [
                            'https://rickandmortyapi.com/api/episode/777',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->service->syncCharacters();

        $this->assertDatabaseCount('characters', 1);

        $character = Character::first();
        $this->assertNotNull($character);
        $this->assertNull($character->origin_location_id);
        $this->assertNull($character->current_location_id);
        $this->assertCount(0, $character->episodes);
        $this->assertDatabaseCount('character_episode', 0);
    }

    public function test_sync_characters_handles_nullable_fields_and_empty_urls(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/character*' => Http::response([
                'info' => ['count' => 1, 'pages' => 1],
                'results' => [
                    [
                        'id' => 2,
                        'name' => 'Morty Smith',
                        'status' => 'Alive',
                        'species' => 'Human',
                        'type' => '',
                        'gender' => 'Male',
                        'image' => 'https://rickandmortyapi.com/api/character/avatar/2.jpeg',
                        'origin' => [
                            'name' => 'unknown',
                            'url' => '',
                        ],
                        'location' => [
                            'name' => 'unknown',
                            'url' => '',
                        ],
                        'episode' => [],
                    ],
                ],
            ], 200),
        ]);

        $this->service->syncCharacters();

        $this->assertDatabaseHas('characters', [
            'external_id' => 2,
            'name' => 'Morty Smith',
            'origin_location_id' => null,
            'current_location_id' => null,
        ]);
        $this->assertDatabaseCount('character_episode', 0);
    }

    public function test_sync_characters_is_idempotent_and_handles_pagination(): void
    {
        $page1Requests = 0;

        Http::fake(function ($request) use (&$page1Requests) {
            if ($request->url() === 'https://rickandmortyapi.com/api/character?page=2') {
                return Http::response([
                    'info' => ['count' => 2, 'pages' => 2],
                    'results' => [
                        [
                            'id' => 2,
                            'name' => 'Morty Smith',
                            'status' => 'Alive',
                            'species' => 'Human',
                            'type' => '',
                            'gender' => 'Male',
                            'image' => 'https://example.com/morty.jpg',
                            'origin' => ['url' => ''],
                            'location' => ['url' => ''],
                            'episode' => [],
                        ],
                    ],
                ], 200);
            }

            $page1Requests++;

            if ($page1Requests === 1) {
                return Http::response([
                    'info' => ['count' => 2, 'pages' => 2],
                    'results' => [
                        [
                            'id' => 1,
                            'name' => 'Rick Sanchez',
                            'status' => 'Alive',
                            'species' => 'Human',
                            'type' => '',
                            'gender' => 'Male',
                            'image' => 'https://example.com/rick.jpg',
                            'origin' => ['url' => ''],
                            'location' => ['url' => ''],
                            'episode' => [],
                        ],
                    ],
                ], 200);
            }

            return Http::response([
                'info' => ['count' => 2, 'pages' => 1],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Rick Sanchez',
                        'status' => 'Dead',
                        'species' => 'Human',
                        'type' => 'Cyborg',
                        'gender' => 'Male',
                        'image' => 'https://example.com/rick.jpg',
                        'origin' => ['url' => ''],
                        'location' => ['url' => ''],
                        'episode' => [],
                    ],
                ],
            ], 200);
        });

        $this->service->syncCharacters();

        $this->assertDatabaseCount('characters', 2);

        $this->service->syncCharacters();

        $this->assertDatabaseCount('characters', 2);

        $this->assertDatabaseHas('characters', [
            'external_id' => 1,
            'status' => 'Dead',
            'type' => 'Cyborg',
        ]);
    }

    public function test_sync_locations_throws_exception_on_invalid_api_response(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/location*' => Http::response([
                'invalid' => 'envelope',
            ], 200),
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->service->syncLocations();
    }

    public function test_sync_service_propagates_http_errors(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/location*' => Http::response(null, 500),
        ]);

        $this->expectException(RequestException::class);

        $this->service->syncLocations();
    }
}

