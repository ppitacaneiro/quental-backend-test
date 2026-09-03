<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Character\CharacterIndexRequest;
use App\Models\Character;
use App\Services\CharacterService;
use Illuminate\Http\JsonResponse;

class CharacterController extends Controller
{
    public function __construct(
        private readonly CharacterService $characterService,
    ) {
    }

    public function index(CharacterIndexRequest $request): JsonResponse
    {
        $characters = $this->characterService->paginate($request->validated());

        return response()->json($characters);
    }

    public function show(Character $character): JsonResponse
    {
        return response()->json($this->characterService->show($character));
    }
}
