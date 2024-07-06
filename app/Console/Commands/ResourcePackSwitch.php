<?php

namespace App\Console\Commands;

use App\Helpers\SettingHelper;
use App\Models\ResourcePack;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use function Laravel\Prompts\search;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;
use ZanySoft\Zip\Zip;
use ZanySoft\Zip\ZipManager;

class ResourcePackSwitch extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resourcepack:switch
                            {name : Filename of resource pack located on GitHub}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Switch and apply resource pack to current textures';

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => fn () => search(
                label: 'Search for a resource pack:',
                options: fn ($value) => strlen($value) > 0
                    ? (ResourcePack::where('name', 'like', "%{$value}%")->orWhere('alias', 'like', "%{$value}%")->pluck('name', 'id')->all()) ?: ([ResourcePack::pluck('name', 'id')->first()])
                    : [ResourcePack::pluck('name', 'id')->first()],
            ),
        ];
    }

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        $resourcePack = ResourcePack::where('name', 'like', "%{$name}%")->orWhere('alias', 'like', "%{$name}%")->orWhere('id', $name)->pluck('name')->first();
        $name = $resourcePack;

        if (!$resourcePack || !File::exists(resource_path(sprintf("/css/resource-packs-downloaded/%s.zip", $name)))) {
            $this->fail(sprintf("Resource pack '%s' does not exist! Try downloading it again.", $name));
        }

        $extractFrom = resource_path(sprintf("/css/resource-packs-downloaded/%s.zip", $name));
        $extractTo = resource_path('/css/resource-pack-tmp');

        // Clean tmp dir
        File::cleanDirectory(resource_path('/css/resource-pack-tmp'));

        try {
            $manager = new ZipManager();
            $manager->addZip((new Zip)->open($extractFrom));
            $extract = $manager->extract($extractTo, true);
        } catch (\Exception $e) {
            $this->fail(sprintf("Could not extract resource pack '%s'. Message: %s", $name, $e->getMessage()));
        }

        if ($extract !== true) {
            $this->fail(sprintf("Could not extract resource pack '%s'.", $name));
        }

        $this->info(sprintf('Applying new textures...'));

        // Remove current icon image in case the new resource pack does not contain any icon image
        try {
            File::delete(resource_path('/css/resource-pack/icon.png'));
        } catch (\Exception $e) {
            $this->error(sprintf("Could not delete current icon image. Message: %s", $e->getMessage()));
        }

        // First file in the resource pack dir is the actual resource pack dir
        $resourcePackDir = File::allFiles(resource_path('/css/resource-pack-tmp'))[0]->getRelativePath();

        // Copy resource pack from parent dir in tmp dir, and extract files one level up
        try {
            File::copyDirectory(
                resource_path('/css/resource-pack-tmp/' . $resourcePackDir),
                resource_path('/css/resource-pack')
            );
        } catch (\Exception $e) {
            $this->fail(sprintf("Could not copy resource pack '%s' to resource pack dir. Message: %s", $name, $e->getMessage()));
        }

        $resourcePack = ResourcePack::whereName($name)->first();
        SettingHelper::setSetting('resource_pack_id', $resourcePack->id, 'int');

        // Just display a default image if resource pack has no icon image
        try {
            if (!File::exists(resource_path('/css/resource-pack/icon.png'))) {
                File::copy(public_path('/images/background.png'), resource_path('/css/resource-pack/icon.png'));
            }
        } catch (\Exception $e) {
            $this->error(sprintf("Could not copy default icon image. Message: %s", $e->getMessage()));
        }

        SettingHelper::setSetting('site_hash', Str::random(20));

        // Clean tmp dir
        try {
            File::cleanDirectory(resource_path('/css/resource-pack-tmp'));
        } catch (\Exception $e) {
            $this->error(sprintf("Could not clean tmp dir. Message: %s", $e->getMessage()));
        }

        $this->info(sprintf("Resource pack '%s' is now ready for use.", $resourcePack->alias));

        return CommandAlias::SUCCESS;
    }
}
