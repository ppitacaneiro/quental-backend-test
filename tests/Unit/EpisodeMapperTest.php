<?php

namespace Tests\Unit;

use App\Dtos\EpisodeDto;
use App\Mappers\EpisodeMapper;
use InvalidArgumentException;
use Tests\TestCase;

class EpisodeMapperTest extends TestCase
{
    public function test_it_maps_an_episode(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Pilot',
            'air_date' => 'December 2, 2013',
            'episode' => 'S01E01',
        ];

        $result = (new EpisodeMapper())->map($data);

        $this->assertInstanceOf(EpisodeDto::class, $result);
        $this->assertSame(1, $result->externalId);
        $this->assertSame('Pilot', $result->name);
        $this->assertSame('December 2, 2013', $result->airDate);
        $this->assertSame('S01E01', $result->episodeCode);
    }

    public function test_it_rejects_an_episode_without_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EpisodeMapper())->map([
            'name' => 'Pilot',
        ]);
    }

    public function test_it_rejects_an_episode_without_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EpisodeMapper())->map([
            'id' => 1,
        ]);
    }
}