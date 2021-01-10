<?php

namespace App\Console\Commands;

use App\Collection;
use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NpcCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'npc:create {npc} {unique*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new NPC model and migration';

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
        $modelName = Str::studly(Str::singular(class_basename($this->argument('npc'))));

        $uniques = implode(
            ' ',
            array_map(
                function($unique) {
                    return(str_replace("-","_", Str::snake($unique)) . ':integer:default(0):unsigned,');
                },
                $this->argument('unique')
            )
        );

        $command = 'make:migration:schema create_' . Str::snake(Str::singular($this->argument('npc'))) . '_table --schema="account_id:integer:unsigned:unique, kill_count:integer:default(0):unsigned, obtained:integer:default(0):unsigned, ' .substr($uniques, 0, -1) . '"';

        $execute = Artisan::call($command);

//        $oldPath = 'app/' . ucfirst($this->argument('npc') . '.php');
        $model = 'app/npc/' . $modelName . '.php';

//        $move = \File::move($oldPath, $newPath);

        $modelFile = fopen($model,
            'w') or die ('Unable to open or create model file! You have to manually edit this for this hiscore to work!');

        fwrite($modelFile, "<?php\n");
        fwrite($modelFile, "\r\n");
        fwrite($modelFile, "namespace App\\Npc;\r\n");
        fwrite($modelFile, "\r\n");
        fwrite($modelFile, "use Illuminate\\Database\\Eloquent\\Model;\r\n");
        fwrite($modelFile, "\r\n");
        fwrite($modelFile, "class " . $modelName . " extends Model\r\n");
        fwrite($modelFile, "{\r\n");
        fwrite($modelFile, '    protected $table = \'' . Str::snake(Str::singular($this->argument('npc'))) . '\';' . "\r\n");
        fwrite($modelFile, "\r\n");
        fwrite($modelFile, '    protected $fillable = [' . "\r\n");
        fwrite($modelFile, "        'obtained',\r\n");
        fwrite($modelFile, "        'kill_count',\r\n");
        foreach ($this->argument('unique') as $unique) fwrite($modelFile, "        '" . str_replace("-","_", Str::snake($unique)) . "',\r\n");
        fwrite($modelFile, "    ];\r\n");
        fwrite($modelFile, "\r\n");
        fwrite($modelFile, '    protected $hidden = [\'user_id\'];' . "\r\n");
        fwrite($modelFile, "\r\n");
        fwrite($modelFile, "    public function account()\r\n");
        fwrite($modelFile, "    {\r\n");
        fwrite($modelFile, '        return $this->belongsTo(\App\Account::class);' . "\r\n");
        fwrite($modelFile, "    }\r\n");
        fwrite($modelFile, "}\r\n");

        fclose($modelFile);

        Artisan::call('migrate');

        Collection::create([
            'category_id' => 4,
            'name' => str_replace("_"," ", Str::snake(Str::singular($this->argument('npc')))),
            'alias' => ucfirst(str_replace("_"," ", Str::snake(Str::singular($this->argument('npc'))))),
            'model' => "App\Npc\\" . $modelName,
        ]);
    }
}
