<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchItemsRequest;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

/**
 * Typeahead over the static OSRS item catalogue, backing the calendar's item
 * picker. Read-only and members-only — the catalogue is large, so
 * results are capped rather than paginated.
 */
class ItemSearchController extends Controller
{
    public const MAX_RESULTS = 20;

    public function __invoke(SearchItemsRequest $request): JsonResponse
    {
        return response()->json(
            Item::searchByName((string) $request->validated('q', ''), self::MAX_RESULTS),
        );
    }
}
