<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NpcResource;
use App\Models\Npc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NpcController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'search' => ['required', 'string', 'min:3', 'max:75'],
            'per_page' => ['nullable', 'integer', 'min:3', 'max:21'],
        ]);

        $perPage = $request->get('per_page', 18);

        $npcs = Npc::where('name', 'LIKE', '%' . $request->get('search') . '%')
            ->orderByDesc('combat_level')
            ->paginate($perPage)
            ->through(function ($npc) {
                return new NpcResource($npc);
            });

        return response()->json([
            'npcs' => $npcs,
        ], 200);
    }
}
