<?php

namespace Tests\Feature\Api;

use App\Models\Character;
use App\Models\Episode;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_characters(): void
    {
        Character::factory()->count(3)->create();

        $response = $this->getJson('/api/characters');

        $response->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'per_page', 'total', 'last_page'])
            ->assertJsonCount(3, 'data');
    }

    public function test_index_filters_by_name_partial_match(): void
    {
        Character::factory()->create(['name' => 'Rick Sanchez']);
        Character::factory()->create(['name' => 'Morty Smith']);

        $response = $this->getJson('/api/characters?name=Rick');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Rick Sanchez', $response->json('data.0.name'));
    }

    public function test_index_filters_by_status(): void
    {
        Character::factory()->create(['status' => 'Alive']);
        Character::factory()->create(['status' => 'Dead']);

        $response = $this->getJson('/api/characters?status=Dead');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Dead', $response->json('data.0.status'));
    }

    public function test_index_filters_by_species(): void
    {
        Character::factory()->create(['species' => 'Human']);
        Character::factory()->create(['species' => 'Alien']);

        $response = $this->getJson('/api/characters?species=Alien');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Alien', $response->json('data.0.species'));
    }

    public function test_index_filters_by_gender(): void
    {
        Character::factory()->create(['gender' => 'Male']);
        Character::factory()->create(['gender' => 'Female']);

        $response = $this->getJson('/api/characters?gender=Female');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Female', $response->json('data.0.gender'));
    }

    public function test_index_combines_multiple_filters(): void
    {
        Character::factory()->create(['name' => 'Rick Sanchez', 'status' => 'Alive', 'species' => 'Human', 'gender' => 'Male']);
        Character::factory()->create(['name' => 'Rick Prime', 'status' => 'Dead', 'species' => 'Human', 'gender' => 'Male']);
        Character::factory()->create(['name' => 'Morty Smith', 'status' => 'Alive', 'species' => 'Human', 'gender' => 'Male']);

        $response = $this->getJson('/api/characters?name=Rick&status=Alive');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Rick Sanchez', $response->json('data.0.name'));
    }

    public function test_index_paginates_with_page_and_per_page(): void
    {
        Character::factory()->count(5)->create();

        $response = $this->getJson('/api/characters?per_page=2&page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('per_page', 2)
            ->assertJsonPath('total', 5);
    }

    public function test_index_per_page_defaults_to_15_when_not_specified(): void
    {
        Character::factory()->count(20)->create();

        $response = $this->getJson('/api/characters');

        $response->assertOk()->assertJsonPath('per_page', 15);
    }

    public function test_index_rejects_per_page_below_minimum(): void
    {
        $response = $this->getJson('/api/characters?per_page=0');

        $response->assertUnprocessable()->assertJsonValidationErrors('per_page');
    }

    public function test_index_rejects_per_page_above_maximum(): void
    {
        $response = $this->getJson('/api/characters?per_page=101');

        $response->assertUnprocessable()->assertJsonValidationErrors('per_page');
    }

    public function test_show_returns_character_detail_with_expected_shape(): void
    {
        $origin = Location::factory()->create();
        $currentLocation = Location::factory()->create();
        $character = Character::factory()->create([
            'origin_location_id' => $origin->id,
            'current_location_id' => $currentLocation->id,
        ]);
        $episode = Episode::factory()->create();
        $character->episodes()->attach($episode);

        $response = $this->getJson("/api/characters/{$character->id}");

        $response->assertOk()->assertJson([
            'id' => $character->id,
            'external_id' => $character->external_id,
            'name' => $character->name,
            'status' => $character->status,
            'species' => $character->species,
            'type' => $character->type,
            'gender' => $character->gender,
            'image' => $character->image,
            'origin' => [
                'id' => $origin->id,
                'external_id' => $origin->external_id,
                'name' => $origin->name,
                'type' => $origin->type,
                'dimension' => $origin->dimension,
            ],
            'current_location' => [
                'id' => $currentLocation->id,
                'external_id' => $currentLocation->external_id,
                'name' => $currentLocation->name,
                'type' => $currentLocation->type,
                'dimension' => $currentLocation->dimension,
            ],
            'episodes' => [
                [
                    'id' => $episode->id,
                    'external_id' => $episode->external_id,
                    'name' => $episode->name,
                    'air_date' => $episode->air_date,
                    'episode_code' => $episode->episode_code,
                ],
            ],
        ]);
    }

    public function test_show_returns_null_relations_when_character_has_no_location(): void
    {
        $character = Character::factory()->create([
            'origin_location_id' => null,
            'current_location_id' => null,
        ]);

        $response = $this->getJson("/api/characters/{$character->id}");

        $response->assertOk()
            ->assertJsonPath('origin', null)
            ->assertJsonPath('current_location', null)
            ->assertJsonPath('episodes', []);
    }

    public function test_show_returns_404_for_nonexistent_character(): void
    {
        $response = $this->getJson('/api/characters/999999');

        $response->assertNotFound();
    }
}
