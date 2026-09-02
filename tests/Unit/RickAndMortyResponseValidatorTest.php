<?php

namespace Tests\Unit;

use App\Integrations\RickAndMorty\RickAndMortyResponseValidator;
use InvalidArgumentException;
use Tests\TestCase;

class RickAndMortyResponseValidatorTest extends TestCase
{
    public function test_it_accepts_a_valid_response(): void
    {
        $response = [
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
        ];

        (new RickAndMortyResponseValidator())->validate($response);

        $this->assertTrue(true);
    }

    public function test_it_rejects_a_response_without_info(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RickAndMortyResponseValidator())->validate([
            'results' => [],
        ]);
    }

    public function test_it_rejects_a_response_with_invalid_count(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RickAndMortyResponseValidator())->validate([
            'info' => [
                'count' => '1',
                'pages' => 1,
            ],
            'results' => [],
        ]);
    }

    public function test_it_rejects_a_response_with_invalid_pages(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RickAndMortyResponseValidator())->validate([
            'info' => [
                'count' => 1,
                'pages' => '1',
            ],
            'results' => [],
        ]);
    }

    public function test_it_rejects_a_response_without_results(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RickAndMortyResponseValidator())->validate([
            'info' => [
                'count' => 1,
                'pages' => 1,
            ],
        ]);
    }

    public function test_it_rejects_a_response_with_invalid_result(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RickAndMortyResponseValidator())->validate([
            'info' => [
                'count' => 1,
                'pages' => 1,
            ],
            'results' => [
                'invalid result',
            ],
        ]);
    }
}