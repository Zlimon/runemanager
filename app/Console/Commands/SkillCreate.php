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
    protected $description = 'Command description';

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
        // This method create a migration file for each collection model in the collections table
        foreach (Helper::listSkills() as $collection) {
            $command = "make:model ".$collection." -m";

            Artisan::call($command);
        }

        return 0;
    }
}
