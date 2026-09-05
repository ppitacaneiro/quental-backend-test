<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Favorite\FavoriteIndexRequest;
use App\Models\Character;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(
        private readonly FavoriteService $favoriteService,
    ) {
    }

    public function index(FavoriteIndexRequest $request): JsonResponse
    {
        $favorites = $this->favoriteService->paginate($request->user(), $request->validated());

        return response()->json($favorites);
    }

    public function store(Request $request, Character $character): JsonResponse
    {
        $this->favoriteService->add($request->user(), $character);

        return response()->json($character, 201);
    }

    public function destroy(Request $request, Character $character): JsonResponse
    {
        $this->favoriteService->remove($request->user(), $character);

        return response()->json(null, 204);
    }
}