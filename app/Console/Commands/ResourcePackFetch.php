<?php

namespace App\Console\Commands;

use App\ResourcePack;
use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ResourcePackFetch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resourcepack:fetch
                            {name : Filename of resource pack located on GitHub}
                            {--use= : Whether the resource pack should be used}
                            {--update= : Whether the resource pack should be updated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch resource pack, and optionally apply it as currently used textures';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (File::exists(public_path('storage/resource-packs-downloaded/' . $this->argument('name') . '.zip'))) {
            if ($this->option('update') !== "yes") {
                $this->info(
                    sprintf("Resource pack '%s' already exists! Use --update=yes to update it", $this->argument('name'))
                );

                return 1;
            }
        }

        $this->info(sprintf("Downloading '%s'", $this->argument('name')));

        // Download resource pack
        $resourcePack = @file_get_contents(
            'https://github.com/melkypie/resource-packs/archive/' . $this->argument('name') . '.zip'
        );

        if ($resourcePack === false) {
            $this->info(sprintf("Could not fetch '%s' from '%s'", $this->argument('name'), 'https://github.com/melkypie/resource-packs/archive/' . $this->argument('name') . '.zip'));

            return 1;
        }

        // Put resource pack file to download directory
        Storage::disk('public')->put(
            'resource-packs-downloaded/' . $this->argument('name') . '.zip',
            $resourcePack
        );

        // Verify resource pack is correctly downloaded, and insert to database
        if (File::exists(public_path('storage/resource-packs-downloaded/'.$this->argument('name').'.zip'))) {
            $resourcePack = ResourcePack::firstWhere('name', $this->argument('name'));

            if (!$resourcePack) {
                $this->info(sprintf("Inserting '%s' to database", $this->argument('name')));

                $resourcePack = new ResourcePack();

                $resourcePack->name = $this->argument('name');
                $resourcePack->alias = ucfirst(str_replace("pack-", "", $this->argument('name')));
                $resourcePack->url = 'https://github.com/melkypie/resource-packs/archive/' . $this->argument(
                        'name'
                    ) . '.zip';

                $resourcePack->save();
            } else {
                $this->info(sprintf("Updating '%s' in database", $this->argument('name')));

                $resourcePack->touch();
            }
        } else {
            $this->info(sprintf("Something went wrong. Most likely due to storage directory not existing"));

            $this->info(sprintf("Executing storage:link..."));

            Artisan::call("storage:link");

            $this->info(sprintf("Completed. Try fetching again"));

            return 1;
        }

        if ($this->option('use') == "yes") {
            $this->info(sprintf("Applying new textures"));

            Artisan::call("resourcepack:switch " . $this->argument('name'));
        }

        $this->info(sprintf("Finished!"));

        return 0;
    }
}
