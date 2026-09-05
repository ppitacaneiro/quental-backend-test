<?php

namespace Tests\Feature\Api;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_401_when_no_token_is_provided(): void
    {
        $this->getJson('/api/favorites')->assertUnauthorized();
    }

    public function test_store_returns_401_when_no_token_is_provided(): void
    {
        $character = Character::factory()->create();

        $this->postJson("/api/favorites/{$character->id}")->assertUnauthorized();
    }

    public function test_destroy_returns_401_when_no_token_is_provided(): void
    {
        $character = Character::factory()->create();

        $this->deleteJson("/api/favorites/{$character->id}")->assertUnauthorized();
    }

    public function test_index_returns_only_authenticated_users_paginated_favorites(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $firstFavorite = Character::factory()->create();
        $secondFavorite = Character::factory()->create();
        $otherUsersFavorite = Character::factory()->create();
        $user->favoriteCharacters()->attach([$firstFavorite->id, $secondFavorite->id]);
        $otherUser->favoriteCharacters()->attach($otherUsersFavorite->id);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/favorites?per_page=1&page=1');

        $response->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'per_page', 'total', 'last_page'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonMissing(['id' => $otherUsersFavorite->id]);
    }

    public function test_store_creates_favorite_and_returns_character(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/favorites/{$character->id}");

        $response->assertCreated()->assertJsonPath('id', $character->id);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);
    }

    public function test_store_is_idempotent_when_character_is_already_favorite(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create();
        $user->favoriteCharacters()->attach($character->id);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/favorites/{$character->id}")
            ->assertCreated()
            ->assertJsonPath('id', $character->id);

        $this->assertSame(1, DB::table('favorites')
            ->where('user_id', $user->id)
            ->where('character_id', $character->id)
            ->count());
    }

    public function test_destroy_removes_only_authenticated_users_favorite(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $character = Character::factory()->create();
        $user->favoriteCharacters()->attach($character->id);
        $otherUser->favoriteCharacters()->attach($character->id);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/favorites/{$character->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $otherUser->id,
            'character_id' => $character->id,
        ]);
    }

    public function test_destroy_is_idempotent_when_character_is_not_favorite(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/favorites/{$character->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);
    }

    public function test_store_returns_404_when_character_does_not_exist(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/favorites/999999')
            ->assertNotFound();
    }
}