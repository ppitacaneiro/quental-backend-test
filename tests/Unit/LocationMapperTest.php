<?php

namespace Tests\Unit;

use App\Dtos\LocationDto;
use App\Mappers\LocationMapper;
use InvalidArgumentException;
use Tests\TestCase;

class LocationMapperTest extends TestCase
{
    public function test_it_maps_a_location(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Earth',
            'type' => 'Planet',
            'dimension' => 'Dimension C-137',
        ];

        $result = (new LocationMapper())->map($data);

        $this->assertInstanceOf(LocationDto::class, $result);
        $this->assertSame(1, $result->externalId);
        $this->assertSame('Earth', $result->name);
        $this->assertSame('Planet', $result->type);
        $this->assertSame('Dimension C-137', $result->dimension);
    }

    public function test_it_rejects_a_location_without_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocationMapper())->map([
            'name' => 'Earth',
        ]);
    }

    public function test_it_rejects_a_location_without_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocationMapper())->map([
            'id' => 1,
        ]);
    }
}