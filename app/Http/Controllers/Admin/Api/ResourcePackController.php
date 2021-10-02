<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\ResourcePack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ResourcePackController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $this->validate(
            $request,
            [
                'search' => ['required', 'string', 'min:1', 'max:100'],
            ]
        );

        $query = Str::slug(strtolower('pack-' . str_replace('pack-', '', request('search'))), '-');

        $getProperties = Http::get(
            'https://github.com/melkypie/resource-packs/archive/' . $query . '.zip'
        );

        if ($getProperties->failed()) {
            $errors = [
                'message' => 'No search results.',
                'errors' => [
                    'search' => [
                        0 => 'Could not find any resource packs with the name "' . $query . '"!',
                    ],
                ],
            ];

            return response()->json($errors, 404);
        }

        $status = Artisan::call(
            'resourcepack:fetch',
            [
                'name' => $query
            ]
        );

        $return = [
            'status' => $status,
            'message' => Artisan::output(),
            'resourcePacks' => ResourcePack::all(),
            'resourcePack' => ResourcePack::whereName($query)->first(),
        ];

        return response()->json($return, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function switch(ResourcePack $resourcePack)
    {
        $status = Artisan::call(
            'resourcepack:switch',
            [
                'name' => $resourcePack->name,
            ]
        );

        if ($status !== 0) {
            $errors = [
                'message' => 'Could not switch resource pack.',
                'errors' => [
                    'resource_pack' => Artisan::output(),
                ],
            ];

            return response()->json($errors, 404);
        }

        $return = [
            'status' => $status,
            'message' => Artisan::output(),
            'resourcePack' => $resourcePack,
        ];

        return response()->json($return, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ResourcePack $resourcePack)
    {
        $status = Artisan::call(
            'resourcepack:fetch',
            [
                'name' => $resourcePack->name,
                '--update' => 'yes',
                '--use' => 'yes',
            ]
        );

        if ($status !== 0) {
            $errors = [
                'message' => 'Could not update resource pack.',
                'errors' => [
                    'resource_pack' => Artisan::output(),
                ],
            ];

            return response()->json($errors, 404);
        }

        $return = [
            'status' => $status,
            'message' => Artisan::output(),
            'resourcePack' => $resourcePack,
        ];

        return response()->json($return, 200);
    }
}
