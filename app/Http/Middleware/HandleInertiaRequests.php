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
        $resourcePack = ResourcePack::find(SettingHelper::getSetting('resource_pack_id', 1));

        return array_merge(parent::share($request), [
            'dark_mode' => isset($resourcePack->dark_mode) && $resourcePack->dark_mode == 1,
            'skills' => fn () => Skill::distinct()->select('name', 'slug')->get()->toArray() ?? [],
            'bosses' => fn () => Collection::distinct()->select('name', 'slug')->where('category_id', 5)->get()->toArray() ?? [],
            'clues' =>  fn () => Collection::distinct()->select('name', 'slug')->where('category_id', 3)->get()->toArray() ?? [],
        ]);
    }
}
