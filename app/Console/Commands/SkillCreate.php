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
        $skills = Helper::listSkills();
        $skillCount = count($skills);

        // This method create a skill migration and model file for each skill
        foreach (Helper::listSkills() as $key => $skill) {
            $count = ($key + 1).'/'.$skillCount;

            $this->info(sprintf("[%s] Making '%s' migration", $count, $skill));

            $makeMigration = 'make:migration create_'.$skill.'_table';
            Artisan::call($makeMigration);

            $this->info(sprintf("[%s] Making '%s' model", $count, $skill));

            $makeModel = 'make:model Skill/'.ucfirst($skill);
            Artisan::call($makeModel);
        }

        return 0;
    }
}
