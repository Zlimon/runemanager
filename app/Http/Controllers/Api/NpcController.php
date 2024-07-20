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
        $npcs = Npc::where('name', 'LIKE', '%' . $request['search'] . '%')->get();

        return response()->json([
            'data' => NpcResource::collection($npcs),
        ], 200);
    }
}
