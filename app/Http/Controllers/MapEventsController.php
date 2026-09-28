<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterMapEventsRequest;
use App\Services\Map\EventMap;
use Illuminate\Http\JsonResponse;

class MapEventsController extends Controller
{
    /**
     * The Live Map's events layer: positioned feed events of the selected
     * accounts, grouped by tile, as plain JSON (`{markers, items, truncated}`).
     */
    public function index(FilterMapEventsRequest $request, EventMap $eventMap): JsonResponse
    {
        $validated = $request->validated();
        $result = $eventMap->markers($request->user(), $validated['accounts'] ?? [], $validated['type'] ?? null);

        // An id-keyed map, so an empty one must still encode as {}.
        return response()->json([...$result, 'items' => (object) $result['items']]);
    }
}
