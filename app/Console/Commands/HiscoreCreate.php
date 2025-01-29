<?php

namespace App\Console\Commands;

use App\Category;
use App\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HiscoreCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hiscore:create
                            {type : Category type of new hiscore}
                            {name : Name of new hiscore (must be in snake case)}
                            {--migrate= : Whether the hiscore should be migrated immediately or not}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates model, migration, collection and image directory for new hiscore based on type. Should be used when a new hiscore entry is added to the official hiscores';

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
        $hiscoreType = $this->argument('type');
        $hiscoreName = $this->argument('name');

        try {
            $makeModel = "make:model ".ucfirst($hiscoreType)."/".Str::studly($hiscoreName)."";
            Artisan::call($makeModel);
        } catch (\Exception $e) {
            $this->info(sprintf("Could not create model: '%s'", Str::studly($hiscoreName)));
            $this->info(sprintf("%s", $e->getMessage()));

            return 1;
        }

        try {
            $makeMigration = "make:migration create_".Str::snake($hiscoreName)."_table";
            Artisan::call($makeMigration);
        } catch (\Exception $e) {
            $this->info(sprintf("Could not create migration: '%s'", Str::snake($hiscoreName)));
            $this->info(sprintf("%s", $e->getMessage()));

            return 1;
        }

        $categoryId = Category::whereCategory(strtolower($hiscoreType))->pluck('id')->first();

        if (!$categoryId) {
            $this->info(sprintf("Could not find category: '%s'", strtolower($hiscoreType)));

            return 1;
        }

        $collection = new Collection();

        $collection->category_id = $categoryId;
        $collection->order = 666;
        $collection->name = Str::title(str_replace('_', ' ', $hiscoreName));
        $collection->slug = Str::slug(($hiscoreName));
        $collection->model = "App\\".ucfirst($hiscoreType)."\\".Str::studly($hiscoreName);

        $collection->save();

        $imageDirectoryPath = public_path().'/images/'.strtolower($hiscoreType).'/'.Str::slug(($hiscoreName));
        if (!File::exists($imageDirectoryPath)) {
            File::makeDirectory($imageDirectoryPath);
        }

        if ($this->option('migrate') == "yes") {
            Artisan::call("migrate");
        }

        $this->info(sprintf("Successfully created model, migration, collection and image directory for %s hiscore: '%s'", $hiscoreType, Str::title(str_replace('_', ' ', $hiscoreName))));

        return 0;
    }
}
