<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ResourcePackUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resourcepack:update
                            {name : Filename of resource pack located on GitHub}
                            {--use= : Whether the resource pack should be used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update resource pack, and optionally apply it as currently used textures';

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
        if (!File::exists(public_path('storage/resource-packs-downloaded/'.$this->argument('name').'.zip'))) {
            $this->info(sprintf("Resource pack '%s' does not exist!", $this->argument('name')));

            return 1;
        }

        $this->info(sprintf("Updating '%s'", $this->argument('name')));

        Artisan::call("resourcepack:fetch ".$this->argument('name')." ".($this->option('use') == "yes" ? "--use=yes" : ""));

        if ($this->option('use') == "yes") {
            $this->info(sprintf("Applying new textures"));
        }

        $this->info(sprintf("Finished!"));

        return 0;
    }
}
