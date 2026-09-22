<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Services\Hiscores\HiscoreLeaderboard;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController extends Controller
{
    /**
     * Boss and clue boards. `$category` comes from the route's defaults, which
     * Laravel passes after the URL parameters, so it must stay the second
     * parameter.
     */
    public function index(string $collection, string $category, HiscoreLeaderboard $leaderboard): Response
    {
        return Inertia::render('Hiscores/Bosses/Show', [
            'recordType' => $category,
            'collectionName' => Str::title(str_replace('-', ' ', $collection)),
            'collectionSlug' => $collection,
            'hiscores' => $leaderboard->activity(str_replace('-', '_', $collection)),
        ]);
    }
}
