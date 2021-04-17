<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use VIPSoft\Unzip\Unzip;

class ResourcePackFetch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resourcepack:fetch {name} {update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch or update resource pack, and apply it as currently used textures';

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
        if (!File::exists(public_path('storage/resource-packs-downloaded/'.$this->argument('name').'.zip')) || $this->argument('update') == "yes") {
            $this->info(sprintf($this->argument('update')  == "yes" ? "Updating %s" : "Downloading %s", $this->argument('name')));

            // Download resource pack
            $resourcePack = file_get_contents(
                'https://github.com/melkypie/resource-packs/archive/' . $this->argument('name') . '.zip'
            );

            // Put resource pack file to download directory
            Storage::disk('public')->put(
                'resource-packs-downloaded/' . $this->argument('name') . '.zip',
                $resourcePack
            );
        } else {
            $this->info(sprintf("Using %s resource pack", $this->argument('name')));
        }

        $extractFrom = public_path('storage/resource-packs-downloaded/'.$this->argument('name').'.zip');
        $extractTo = public_path('storage/resource-pack-tmp');

        // Clean tmp dir
        File::cleanDirectory(public_path('storage/resource-pack-tmp'));

        $unzipper  = new Unzip();
        $filenames = $unzipper->extract($extractFrom, $extractTo);

        $this->info(sprintf("Applying new textures"));

        File::copyDirectory(public_path('storage/resource-pack-tmp/'.$filenames[0]), public_path('storage/resource-pack'));

        // Clean tmp dir
        File::cleanDirectory(public_path('storage/resource-pack-tmp'));

        $this->info(sprintf("Finished!"));

        return 0;
    }
}
