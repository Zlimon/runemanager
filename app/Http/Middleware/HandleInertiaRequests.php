<?php

namespace App\Http\Middleware;

use App\Models\Collection;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'skills' => fn () => Skill::pluck('slug')->toArray() ?? [],
            'bosses' => fn () => Collection::distinct()->where('category_id', 2)->orWhere('category_id', 3)->pluck('slug')->toArray() ?? [],
            'clues' =>  fn () => Collection::where('category_id', 5)->pluck('slug')->toArray() ?? [],
        ]);
    }
}
