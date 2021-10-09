<?php

namespace Database\Seeders;

use App\ResourcePack;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ResourcePackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $resourcePacks = ['pack-sand-casino', 'pack-purple', 'pack-2012-interface', 'pack-reaper-chroma', 'pack-crazed'];

        foreach ($resourcePacks as $resourcePack) {
            Artisan::call(
                'resourcepack:fetch',
                [
                    'name' => $resourcePack
                ]
            );
        }

        Artisan::call(
            'resourcepack:switch',
            [
                'name' => $resourcePacks[rand(0, count($resourcePacks) - 1)]
            ]
        );
    }
}
