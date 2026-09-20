<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Typeahead over the static OSRS item catalogue, backing the calendar's item
 * picker (SPEC §10). Read-only and members-only — the catalogue is large, so
 * results are capped rather than paginated.
 */
class ItemSearchController extends Controller
{
    public const MAX_RESULTS = 20;

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:64'],
        ]);

        return response()->json(
            Item::searchByName((string) ($validated['q'] ?? ''), self::MAX_RESULTS),
        );
    }
}
