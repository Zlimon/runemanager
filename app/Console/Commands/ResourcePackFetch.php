<?php

namespace App\Console\Commands;

use App\Models\ResourcePack;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ResourcePackFetch extends Command implements PromptsForMissingInput
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
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => 'What is the name of the resource pack?',
        ];
    }

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        // Create resource pack directories
        try {
            File::makeDirectory(resource_path('/css/resource-packs-downloaded'), 0755, true, true);
            File::makeDirectory(resource_path('/css/resource-pack-tmp'), 0755, true, true);
            File::makeDirectory(resource_path('/css/resource-pack'), 0755, true, true);
        } catch (\Exception $e) {
            $this->fail(sprintf("Could not create resource pack directories for resource pack '%s'. Message: %s", $name, $e->getMessage()));
        }

        $resourcePack = ResourcePack::firstWhere('name', $name);

        if ($resourcePack && File::exists(resource_path('/css/resource-packs-downloaded/' . $name . '.zip'))) {
            if ($this->option('update') !== 'yes') {
                $this->fail(sprintf("Resource pack '%s' already exists. Use --update=yes to update it.", $name));
            }
        }

        $this->info(sprintf("Downloading '%s'...", $name));

        // Download resource pack
        $url = sprintf("https://github.com/melkypie/resource-packs/archive/%s.zip", $name);
        $resourcePack = @file_get_contents($url);

        if ($resourcePack === false) {
            $this->fail(sprintf("Could not fetch '%s' from '%s'.", $name, $url));
        }

        // Put resource pack file to download directory
        try {
            File::put(
                resource_path(sprintf("/css/resource-packs-downloaded/%s.zip", $name)),
                $resourcePack
            );
        } catch (\Exception $e) {
            $this->fail(sprintf("Could not save resource pack '%s'. Message: %s", $name, $e->getMessage()));
        }

        // Verify resource pack is correctly downloaded, and insert to database
        if (!File::exists(resource_path(sprintf("/css/resource-packs-downloaded/%s.zip", $name)))) {
            $this->fail(sprintf("Resource pack '%s' could not be downloaded.", $name));
        }

        $resourcePack = ResourcePack::firstWhere('name', $name);

        if (!$resourcePack) {
            $this->info(sprintf('Inserting "%s" to database...', $name));

            $getProperties = Http::get(sprintf("https://raw.githubusercontent.com/melkypie/resource-packs/%s/pack.properties", $name));

            if ($getProperties->failed()) {
                $this->warn(sprintf('Could not fetch properties from GitHub! Using default values.'));
            } else {
                $properties = preg_split('/\r\n|\n|\r/', trim($getProperties->body()));

                $values = [];
                foreach ($properties as $property) {
                    $data = explode('=', $property);
                    $values[$data[0]] = $data[1];
                }
            }

            $resourcePack = new ResourcePack();

            $tags = explode(',', $values['tags'] ?? '');
            $tags = array_map('Str::lower', $tags);

            $resourcePack->name = $name;
            $resourcePack->alias = $values['displayName'] ??
                Str::replaceFirst(
                    ' ',
                    '',
                    Str::title(str_replace(['pack-', '-'], ' ', $name))
                );
            $resourcePack->version = $values['compatibleVersion'] ?? '1.0.0';
            $resourcePack->author = $values['author'] ?? '<unknown>';
            $resourcePack->url = $url;
            $resourcePack->tags = implode(',', $tags);
            $resourcePack->dark_mode = in_array('dark', $tags);

            $resourcePack->save();
        } else {
            $this->info(sprintf("Updating '%s' in database...", $name));

            $resourcePack->touch();
        }

        if ($this->option('use') == 'yes') {
            $this->call('resourcepack:switch', [
                'name' => $name,
            ]);

            return CommandAlias::SUCCESS;
        }

        $this->info(sprintf("Resource pack '%s' is now ready for use.", $resourcePack->alias));
        $this->info(sprintf("Use 'php artisan resourcepack:switch %s' to apply it.", $name));

        return CommandAlias::SUCCESS;
    }
}
