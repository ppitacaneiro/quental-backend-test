<?php

namespace Tests\Unit;

use App\Dtos\CharacterDto;
use App\Mappers\CharacterMapper;
use InvalidArgumentException;
use Tests\TestCase;

class CharacterMapperTest extends TestCase
{
    public function test_it_maps_a_character(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Rick Sanchez',
            'status' => 'Alive',
            'species' => 'Human',
            'type' => '',
            'gender' => 'Male',
            'image' => 'https://example.com/rick.jpg',
            'origin' => [
                'url' => 'https://rickandmortyapi.com/api/location/1',
            ],
            'location' => [
                'url' => 'https://rickandmortyapi.com/api/location/20',
            ],
            'episode' => [
                'https://rickandmortyapi.com/api/episode/1',
                'https://rickandmortyapi.com/api/episode/2',
            ],
        ];

        $result = (new CharacterMapper())->map($data);

        $this->assertInstanceOf(CharacterDto::class, $result);
        $this->assertSame(1, $result->externalId);
        $this->assertSame('Rick Sanchez', $result->name);
        $this->assertSame('Alive', $result->status);
        $this->assertSame('Human', $result->species);
        $this->assertSame('', $result->type);
        $this->assertSame('Male', $result->gender);
        $this->assertSame('https://example.com/rick.jpg', $result->image);
        $this->assertSame(1, $result->originLocationExternalId);
        $this->assertSame(20, $result->currentLocationExternalId);
        $this->assertSame([1, 2], $result->episodeExternalIds);
    }

    public function test_it_rejects_a_character_without_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CharacterMapper())->map([
            'name' => 'Rick Sanchez',
        ]);
    }

    public function test_it_rejects_a_character_without_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CharacterMapper())->map([
            'id' => 1,
        ]);
    }
}