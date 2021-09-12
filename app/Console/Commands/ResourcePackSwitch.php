<?php

namespace App\Console\Commands;

use App\Helpers\SettingHelper;
use App\ResourcePack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use VIPSoft\Unzip\Unzip;

class ResourcePackSwitch extends Command
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

        $resourcePack = ResourcePack::firstWhere('name', $name);

        if (!$resourcePack || !File::exists(public_path('storage/resource-packs-downloaded/' . $name . '.zip'))) {
            $this->info(sprintf('Resource pack "%s" does not exist! Try downloading it again.', $name));

            return 1;
        }

        $extractFrom = public_path('storage/resource-packs-downloaded/' . $name . '.zip');
        $extractTo = public_path('storage/resource-pack-tmp');

        // Clean tmp dir
        File::cleanDirectory(public_path('storage/resource-pack-tmp'));

        $unZipper = new Unzip();
        $filenames = $unZipper->extract($extractFrom, $extractTo);

        $this->info(sprintf('Applying new textures.'));

        // Remove current icon image in case the new resource pack does not contain any icon image
        File::delete(public_path('storage/resource-pack/icon.png'));

        // Copy resource pack from parent dir in tmp dir, and extract files one level up
        File::copyDirectory(
            public_path('storage/resource-pack-tmp/' . $filenames[0]),
            public_path('storage/resource-pack')
        );

        $resourcePack = ResourcePack::whereName($name)->first();
        SettingHelper::getSetting(['resource_pack_id', $resourcePack->id]);

        // Just display a default image if resource pack has no icon image
        if (!File::exists(public_path('storage/resource-pack/icon.png'))) {
            File::copy(public_path('images/background.png'), public_path('storage/resource-pack/icon.png'));
        }

        SettingHelper::getSetting(['site_hash', Str::random(20)]);

        // Clean tmp dir
        File::cleanDirectory(public_path('storage/resource-pack-tmp'));

        $this->info(sprintf('Finished! Resource pack "%s" is now ready for use.', $resourcePack->alias));

        return 0;
    }
}
