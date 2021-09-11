<?php

namespace App\Console\Commands;

use App\ResourcePack;
use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $name = $this->argument('name');

        if (File::exists(public_path('storage/resource-packs-downloaded/' . $name . '.zip'))) {
            if ($this->option('update') !== 'yes') {
                $this->info(
                    sprintf('Resource pack "%s" already exists! Use --update=yes to update it', $name)
                );

                return 1;
            }
        }

        $this->info(sprintf('Downloading "%s"', $name));

        $url = 'https://github.com/melkypie/resource-packs/archive/' . $name . '.zip';
        // Download resource pack
        $resourcePack = @file_get_contents($url);

        if ($resourcePack === false) {
            $this->info(sprintf('Could not fetch "%s" from "%s"', $name, $url));

            return 1;
        }

        // Put resource pack file to download directory
        Storage::disk('public')->put(
            'resource-packs-downloaded/' . $name . '.zip',
            $resourcePack
        );

        // Verify resource pack is correctly downloaded, and insert to database
        if (!File::exists(public_path('storage/resource-packs-downloaded/'.$name.'.zip'))) {
            $this->info(sprintf('Something went wrong. Most likely due to storage directory not existing'));

            $this->info(sprintf('Executing storage:link...'));

            Artisan::call('storage:link');

            $this->info(sprintf('Completed. Try fetching again'));

            return 1;
        }

        $resourcePack = ResourcePack::firstWhere('name', $name);

        if (!$resourcePack) {
            $this->info(sprintf('Inserting "%s" to database', $name));

            $resourcePack = new ResourcePack();

            $getProperties = Http::get('https://raw.githubusercontent.com/melkypie/resource-packs/' . $name . '/pack.properties');

            if ($getProperties->failed()) {
                $this->info(sprintf('Could not fetch properties from GitHub. Using default values'));
            } else {
                $properties = preg_split('/\r\n|\n|\r/', trim($getProperties->body()));

                $values = [];
                foreach ($properties as $property) {
                    $data = explode('=', $property);
                    $values[$data[0]] = $data[1];
                }
            }

            $resourcePack->name = $name;
            $resourcePack->alias = isset($values['displayName']) ? $values['displayName'] : Str::replaceFirst(' ', '', Str::title(str_replace(['pack-', '-'], ' ', $name)));
            $resourcePack->version = isset($values['compatibleVersion']) ? $values['compatibleVersion'] : '1.0.0';
            $resourcePack->author = isset($values['author']) ? $values['author'] : '<unknown>';
            $resourcePack->url = $url;

            $resourcePack->save();
        } else {
            $this->info(sprintf('Updating "%s" in database', $name));

            $resourcePack->touch();
        }

        if ($this->option('use') == 'yes') {
            $this->info(sprintf('Applying new textures'));

            Artisan::call('resourcepack:switch ' . $name);
        }

        $this->info(sprintf('Finished!'));

        return 0;
    }
}
