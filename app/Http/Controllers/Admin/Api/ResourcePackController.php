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
    public function search(Request $request)
    {
        $request->validate([
            'search' => ['required', 'max:50'],
        ]);

        $query = Str::slug(strtolower('pack-' . str_replace('pack-', '', request('search'))), '-');

        $getProperties = Http::get(
            'https://github.com/melkypie/resource-packs/archive/' . $query . '.zip'
        );

        if ($getProperties->failed()) {
            $errors = [
                'message' => 'No search results.',
                'errors' => [
                    'search' => [
                        'Could not find any resource packs with the name "'.$query.'".',
                    ]
                ],
            ];

            return response($errors, 404);
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

        return response($return, 201);
    }

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

            return response($errors, 500);
        }

        $return = [
            'status' => $status,
            'message' => Artisan::output(),
            'resourcePack' => $resourcePack,
        ];

        return response($return, 200);
    }

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

            return response($errors, 500);
        }

        $return = [
            'status' => $status,
            'message' => Artisan::output(),
            'resourcePack' => $resourcePack,
        ];

        return response($return, 200);
    }
}
