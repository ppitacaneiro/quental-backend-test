<?php

namespace Tests\Unit;

use App\Integrations\RickAndMorty\RickAndMortyHttpClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RickAndMortyHttpClientTest extends TestCase
{
    public function test_it_gets_characters(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/character*' => Http::response([
                'info' => [
                    'count' => 1,
                    'pages' => 1,
                ],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Rick Sanchez',
                    ],
                ],
            ], 200),
        ]);

        $response = (new RickAndMortyHttpClient())->getCharacters();

        $this->assertSame(1, $response['info']['count']);
        $this->assertSame('Rick Sanchez', $response['results'][0]['name']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://rickandmortyapi.com/api/character?page=1';
        });
    }

    public function test_it_gets_episodes(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/episode*' => Http::response([
                'info' => [
                    'count' => 1,
                    'pages' => 1,
                ],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Pilot',
                    ],
                ],
            ], 200),
        ]);

        $response = (new RickAndMortyHttpClient())->getEpisodes();

        $this->assertSame('Pilot', $response['results'][0]['name']);
    }

    public function test_it_gets_locations(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/location*' => Http::response([
                'info' => [
                    'count' => 1,
                    'pages' => 1,
                ],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Earth',
                    ],
                ],
            ], 200),
        ]);

        $response = (new RickAndMortyHttpClient())->getLocations();

        $this->assertSame('Earth', $response['results'][0]['name']);
    }

    public function test_it_throws_an_exception_for_an_unsuccessful_response(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/character*' =>
                Http::response([], 500),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        (new RickAndMortyHttpClient())->getCharacters();
    }

    public function test_it_throws_an_exception_when_the_connection_fails(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/character*' =>
                function () {
                    throw new ConnectionException('Connection failed');
                },
        ]);

        $this->expectException(ConnectionException::class);

        (new RickAndMortyHttpClient())->getCharacters();
    }

    public function test_it_retries_and_succeeds_after_a_429_response(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/character*' => Http::sequence()
                ->push(['error' => 'rate limited'], 429, ['Retry-After' => '0'])
                ->push([
                    'info' => ['count' => 1, 'pages' => 1],
                    'results' => [
                        ['id' => 1, 'name' => 'Rick Sanchez'],
                    ],
                ], 200),
        ]);

        $response = (new RickAndMortyHttpClient())->getCharacters();

        $this->assertSame('Rick Sanchez', $response['results'][0]['name']);
        Http::assertSentCount(2);
    }

    public function test_it_throws_after_exhausting_retries_on_repeated_429_responses(): void
    {
        Http::fake([
            'https://rickandmortyapi.com/api/character*' =>
                Http::response(['error' => 'rate limited'], 429, ['Retry-After' => '0']),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        (new RickAndMortyHttpClient())->getCharacters();

        Http::assertSentCount(4);
    }
}