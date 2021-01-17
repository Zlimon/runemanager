<?php

namespace App\Console\Commands;

use App\Account;
use App\Collection;
use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
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
        $modelName = Str::studly(Str::singular(class_basename(strtolower($this->argument('npc'))))); // AbyssalDemon

        $migrationName = str_replace("-", "_", Str::snake(Str::singular(strtolower($this->argument('npc'))))); // abyssal_demon

        $normalName = str_replace("_", " ", $migrationName); // abyssal demon

        $aliasName = ucwords(str_replace("_", " ", $migrationName)); // Abyssal Demon

        $this->info(sprintf("Creating collection entry for %s!", $normalName));

        Collection::create([
            'category_id' => 4,
            'name' => $normalName,
            'alias' => $aliasName,
            'model' => "App\Npc\\" . $modelName,
        ]);

        $this->info(sprintf("Created collection entry for %s!", $aliasName));

        $uniques = implode(
            ' ',
            array_map(
                function ($unique) {
                    return (str_replace("-", "_", Str::snake(strtolower($unique))) . ':integer:default(0):unsigned,'); // abyssal_whip
                },
                $this->argument('unique')
            )
        );

        $command = 'make:migration:schema create_' . $migrationName . '_table --schema="account_id:integer:unsigned:unique, kill_count:integer:default(0):unsigned, obtained:integer:default(0):unsigned, ' . substr($uniques,
                0, -1) . '"';

        $this->info(sprintf("Creating migration file for %s!", $aliasName));

        Artisan::call($command);

        $this->info(sprintf("Created migration file for %s!", $aliasName));

        $model = 'app/npc/' . $modelName . '.php';

        $table = '$table';
        $fillable = '$fillable';
        $hidden = '$hidden';
        $thisBelongsTo = '$this->belongsTo(\App\Account::class)';

        $modelFile = <<<EOD
        <?php

        namespace App\Npc;

        use Illuminate\Database\Eloquent\Model;

        class $modelName extends Model
        {
            protected $table = '$migrationName';

            protected $fillable = [
                'obtained',
                'kill_count',\r\n
        EOD;
        foreach ($this->argument('unique') as $unique) {
            $fillable = str_replace("-", "_", Str::snake(strtolower($unique)));

            $modelFile .= <<<EOD
                    '$fillable',\r\n
            EOD;
        }
        $modelFile .= <<<EOD
            ];

            protected $hidden = ['user_id'];

            public function account()
            {
                return $thisBelongsTo;
            }
        }
        EOD;

        $this->info(sprintf("Creating model file for %s!", $aliasName));

        File::put($model, $modelFile);

        $this->info(sprintf("Created model file for %s!", $aliasName));

        $this->info(sprintf("Migrating %s to database!", $aliasName));

        Artisan::call('migrate');

        $this->info(sprintf("Migrated %s to database!", $aliasName));

        $accounts = Account::get();

        $this->info(sprintf("Creating %s collection entries for accounts!", $aliasName));

        foreach ($accounts as $account) {
            $collection = Collection::findByNameAndCategory($normalName, 4);

            $collectionLog = new $collection->model;

            $collectionLog->account_id = $account->id;

            $collectionLog->save();

            $this->info(sprintf("Created %s collection entry for %s!", $aliasName, $account->username));
        }

        $this->info(sprintf("Downloading icons for %s!", $aliasName));

        $i = 0;
        foreach ($this->argument('unique') as $key => $unique) {
            $this->info(sprintf("Fetching item data for %s!", $unique));

            $handle = curl_init("https://api.osrsbox.com/items?where=" . urlencode('{"name":"' . ucfirst(str_replace("-", " ", Str::snake(strtolower($unique)))) . '","duplicate":false}'));
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

            /* Get the content of $url. */
            $response = curl_exec($handle);

            /* Check for errors (content not found). */
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);

            /* If the document has loaded successfully without any redirection or error */
            if ($httpCode >= 200 && $httpCode < 300) {
                $json = json_decode($response, true);

                if (isset($json["_items"][0])) {
                    $url = 'https://www.osrsbox.com/osrsbox-db/items-icons/' . (int)$json["_items"][0]["id"] . '.png'; // 4151

                    $dir = public_path() . '/images/npc/' . $migrationName . '/'; // /images/npc/abyssal_demon/
                    $imgName = str_replace("-", "_", Str::snake(strtolower($unique))) . '.png'; // abyssal_whip.png

                    if (!File::exists($dir)) {
                        File::makeDirectory($dir, 0777, true);
                    }

                    $img = $dir . $imgName;

                    file_put_contents($img, file_get_contents($url));

                    $this->info(sprintf("Downloaded icon for %s!", $unique));

                    // If first iteration, make an icon for npc. This will be the first item in uniques
                    if ($i === 0) {
                        File::copy($img,
                            public_path() . '/images/npc/' . $migrationName . '.png');

                        $this->info(sprintf("Downloaded index icon for %s!", $aliasName));
                    }

                    $i++;
                } else {
                    $this->info(sprintf("Could not find item %s!", $unique));
                }
            } else {
                $this->info(sprintf("Could not find item %s!", $unique));
            }
        }

        $this->info(sprintf("Successfully created %s!", $aliasName));
    }
}
