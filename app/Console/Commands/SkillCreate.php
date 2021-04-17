<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use Artisan;
use Illuminate\Console\Command;

class SkillCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'skill:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create skill migration and model files';

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
        // This method create a skill migration and model file for each skill
        foreach (Helper::listSkills() as $skill) {
            $this->info(sprintf("Making '%s' migration", $skill));

            $makeMigration = "make:migration create_".$skill."_table";
            $this->call($makeMigration);

            $this->info(sprintf("Making '%s' model", $skill));

            $makeModel = "make:model ".ucfirst($skill);
            $this->call($makeModel);
        }

        return 0;
    }
}
