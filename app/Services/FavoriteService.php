<?php

namespace App\Services;

use App\Models\Character;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FavoriteService
{
    /**
     * @param array{page?: int, per_page?: int} $filters
     */
    public function paginate(User $user, array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return $user->favoriteCharacters()
            ->orderByDesc('favorites.created_at')
            ->orderByDesc('characters.id')
            ->paginate(perPage: $perPage, page: $filters['page'] ?? null);
    }

    public function add(User $user, Character $character): void
    {
        $user->favoriteCharacters()->syncWithoutDetaching($character->id);
    }

    public function remove(User $user, Character $character): void
    {
        $user->favoriteCharacters()->detach($character->id);
    }
}