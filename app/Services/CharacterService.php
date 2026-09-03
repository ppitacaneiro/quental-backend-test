<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CharacterService
{
    /**
     * @param array{name?: string, status?: string, species?: string, gender?: string, page?: int, per_page?: int} $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return Character::query()
            ->when($filters['name'] ?? null, fn ($query, $name) => $query->where('name', 'like', "%{$name}%"))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['species'] ?? null, fn ($query, $species) => $query->where('species', $species))
            ->when($filters['gender'] ?? null, fn ($query, $gender) => $query->where('gender', $gender))
            ->paginate(perPage: $perPage, page: $filters['page'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    public function show(Character $character): array
    {
        $character->load(['originLocation', 'currentLocation', 'episodes']);

        return [
            'id' => $character->id,
            'external_id' => $character->external_id,
            'name' => $character->name,
            'status' => $character->status,
            'species' => $character->species,
            'type' => $character->type,
            'gender' => $character->gender,
            'image' => $character->image,
            'origin' => $this->formatLocation($character->originLocation),
            'current_location' => $this->formatLocation($character->currentLocation),
            'episodes' => $character->episodes->map(fn ($episode) => [
                'id' => $episode->id,
                'external_id' => $episode->external_id,
                'name' => $episode->name,
                'air_date' => $episode->air_date,
                'episode_code' => $episode->episode_code,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatLocation(?Location $location): ?array
    {
        if (! $location) {
            return null;
        }

        return [
            'id' => $location->id,
            'external_id' => $location->external_id,
            'name' => $location->name,
            'type' => $location->type,
            'dimension' => $location->dimension,
        ];
    }
}
