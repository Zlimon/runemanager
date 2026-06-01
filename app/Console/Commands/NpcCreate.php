<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Collection;
use App\Models\Item;
use App\Models\Monster;
use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NpcCreate extends Command
{
    protected $signature = 'npc:create {npc} {unique*}';

    protected $description = 'Create a new NPC model and migration';

    public function handle(): int
    {
        $modelName = Str::studly(Str::singular(class_basename(strtolower($this->argument('npc')))));

        $migrationName = str_replace('-', '_',
            Str::snake(Str::singular(strtolower($this->argument('npc')))));

        $normalName = str_replace('_', ' ', $migrationName);

        $aliasName = ucwords(str_replace('_', ' ', $migrationName));

        if ($this->argument('unique')[0] === 'drops' && count($this->argument('unique')) === 1) {
            $monsterName = ucfirst(str_replace(['_', '-'], ' ', $this->argument('npc')));

            $monster = Monster::where('name', $monsterName)->first();

            if (! $monster || empty($monster->drops)) {
                $this->info(sprintf('Could not find any drops for %s! Try to manually type the items you wish to track instead',
                    $this->argument('npc')));

                return 1;
            }

            $uniques = array_map(fn ($drop) => $drop['name'], $monster->drops);
        } else {
            $uniques = $this->argument('unique');
        }

        $uniques = array_unique($uniques);

        $uniqueColumns = array_map(
            fn ($unique) => str_replace("'", '', str_replace('-', '_', Str::snake(strtolower($unique)))),
            $uniques
        );

        $columnLines = '';
        foreach ($uniqueColumns as $column) {
            $columnLines .= "            \$table->unsignedInteger('$column')->default(0);\n";
        }

        $this->info(sprintf('Creating migration file for %s!', $aliasName));

        $migrationStub = <<<PHP
        <?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            public function up(): void
            {
                Schema::create('$migrationName', function (Blueprint \$table) {
                    \$table->id();
                    \$table->unsignedInteger('account_id')->unique();
                    \$table->unsignedInteger('kill_count')->default(0);
                    \$table->unsignedInteger('obtained')->default(0);
        $columnLines            \$table->timestamps();
                });
            }

            public function down(): void
            {
                Schema::dropIfExists('$migrationName');
            }
        };
        PHP;

        $migrationFile = database_path('migrations/'.date('Y_m_d_His').'_create_'.$migrationName.'_table.php');

        File::put($migrationFile, $migrationStub);

        $this->info(sprintf('Created migration file for %s!', $aliasName));

        $npcDir = app_path('Npc');
        if (! File::exists($npcDir)) {
            File::makeDirectory($npcDir, 0755, true);
        }

        $model = $npcDir.'/'.$modelName.'.php';

        $table = '$table';
        $fillable = '$fillable';
        $hidden = '$hidden';
        $thisBelongsTo = '$this->belongsTo(\App\Models\Account::class)';

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
        foreach ($uniques as $unique) {
            $fillable = str_replace("'", '', str_replace('-', '_', Str::snake(strtolower($unique))));

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

        $this->info(sprintf('Creating model file for %s!', $aliasName));

        File::put($model, $modelFile);

        $this->info(sprintf('Created model file for %s!', $aliasName));

        $iconsCollection = DB::connection('mongodb-static')->getDatabase()->selectCollection('icons_items');

        $i = 0;
        foreach ($uniques as $key => $unique) {
            $this->info(sprintf('Fetching item data for %s!', $unique));

            $lookupName = ucfirst(str_replace(['_', '-'], ' ', Str::snake(strtolower($unique))));

            $item = Item::where('name', $lookupName)->where('duplicate', false)->first();

            if (! $item) {
                $this->info(sprintf('Could not find item %s!', $unique));

                continue;
            }

            $iconDoc = $iconsCollection->findOne(['id' => (int) $item->id]);

            if (! $iconDoc || empty($iconDoc->icon)) {
                $this->info(sprintf('No icon available for %s!', $unique));

                continue;
            }

            $dir = public_path().'/images/npc/'.$migrationName.'/';
            $imgName = str_replace("'", '', str_replace('-', '_', Str::snake(strtolower($unique)))).'.png';

            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0777, true);
            }

            $img = $dir.$imgName;

            file_put_contents($img, base64_decode($iconDoc->icon));

            $this->info(sprintf('Wrote icon for %s!', $unique));

            if ($i === 0) {
                File::copy($img, public_path().'/images/npc/'.$migrationName.'.png');

                $this->info(sprintf('Wrote index icon for %s!', $aliasName));
            }

            $i++;
        }

        $this->info(sprintf('Migrating %s to database!', $aliasName));

        Artisan::call('migrate', [
            '--force' => true,
        ]);

        $this->info(sprintf('Migrated %s to database!', $aliasName));

        $this->info(sprintf('Creating collection entry for %s!', $aliasName));

        $collection = Collection::create([
            'category_id' => 4,
            'order' => (Collection::where('category_id', 4)->max('order') ?? 0) + 1,
            'name' => $normalName,
            'slug' => Str::slug($normalName),
            'model' => "App\Npc\\".$modelName,
        ]);

        $this->info(sprintf('Created collection entry for %s!', $aliasName));

        $accounts = Account::get();

        $this->info(sprintf('Creating %s collection entries for accounts!', $aliasName));

        foreach ($accounts as $account) {
            $collectionLog = new $collection->model;

            $collectionLog->account_id = $account->id;

            $collectionLog->save();

            $this->info(sprintf('Created %s collection entry for %s!', $aliasName, $account->username));
        }

        $this->info(sprintf('Successfully created %s!', $aliasName));

        return 0;
    }
}
