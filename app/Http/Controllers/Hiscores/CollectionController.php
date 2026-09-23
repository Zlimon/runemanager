<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterHiscoresRequest;
use App\Services\Hiscores\HiscoreLeaderboard;
use App\Services\Hiscores\LeaderboardPage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController extends Controller
{
    /**
     * Boss and clue boards. Both routes render the same page from the same
     * activity entries; they differ only in the URL the reader arrived by, so
     * the route's `category` default is not read here.
     */
    public function index(FilterHiscoresRequest $request, string $collection, HiscoreLeaderboard $leaderboard, LeaderboardPage $page): Response
    {
        $hiscores = $leaderboard->activity(str_replace('-', '_', $collection));

        return Inertia::render('Hiscores/Bosses/Show', [
            'collectionName' => Str::title(str_replace('-', ' ', $collection)),
            'collectionSlug' => $collection,
            'hiscores' => $page->of($hiscores, $request),
            'filters' => $request->filters(),
        ]);
    }
}
