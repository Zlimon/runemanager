<?php

namespace App\Http\Middleware;

use App\Helpers\SettingHelper;
use App\Models\Collection;
use App\Models\ResourcePack;
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
        // Resource pack CSS is rendered server-side from app.blade.php as a <link>;
        // we only forward `dark_mode` to drive Tailwind's `dark:` variants in Vue.
        $packId = $request->user()?->effectiveResourcePackId()
            ?? SettingHelper::getSetting('resource_pack_id');
        $darkMode = $packId
            ? (bool) (ResourcePack::find($packId)?->dark_mode)
            : false;

        return array_merge(parent::share($request), [
            'app' => [
                'name' => config('app.name'),
                //                'url' => config('app.url'),
            ],
            'dark_mode' => $darkMode,
            'skills' => fn () => Skill::distinct()->select('name', 'slug')->get()->toArray() ?? [],
            'bosses' => fn () => Collection::distinct()->select('name', 'slug')->where('category_id', 5)->get()->toArray() ?? [],
            'clues' => fn () => Collection::distinct()->select('name', 'slug')->where('category_id', 3)->get()->toArray() ?? [],
        ]);
    }
}
