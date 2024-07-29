<?php

namespace App\Traits;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Item;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait CollectionTrait
{
    /**
     * @param Category $category
     * @param string $name
     * @param array<Item> $items
     * @return Collection
     * @throws Exception
     */
    public function createHiscore(Category $category, string $name, array $items = []): Collection
    {
        try {
            $this->createModel($category, $name, $items);
        } catch (Exception $e) {
            throw $e;
        }

        try {
            $collection = $this->getOrCreateCollection($category, $name);
        } catch (Exception $e) {
            throw $e;
        }

        try {
            $this->createImageDirectory($category, $collection);
        } catch (Exception $e) {
            throw $e;
        }

        return $collection;
    }

    /**
     * @param Category $category
     * @param string $name
     * @param array<Item> $items
     * @return void
     * @throws Exception
     */
    public function createModel(Category $category, string $name, array $items = []): void
    {
        $modelName = $this->formatModelName($name);

        if (class_exists($modelName)) {
            return;
        }

        try {
            $model = sprintf("%s/%s", Str::studly($category->slug), $modelName);
            $tableName = Str::snake($modelName);

            $namespace = 'namespace App\Models\\' . Str::studly($category->slug) . ';';
            $table = '$table';
            $fillable = '$fillable';
            $hidden = '$hidden';
            $thisBelongsTo = '$this->belongsTo(Account::class)';

            $modelFileContent = <<<EOD
            <?php

            $namespace

            use App\Models\Account;
            use Illuminate\Database\Eloquent\Model;

            class $modelName extends Model
            {
                protected $table = '$tableName';

                protected $fillable = [
                    'obtained',
                    'kill_count',\r\n
            EOD;
            foreach ($items as $item) {
//                $fillable = str_replace("'", "", str_replace("-", "_", Str::snake(strtolower($unique))));
                $fillable = $item['id'];

                $modelFileContent .= <<<EOD
                        '$fillable',\r\n
                EOD;
            }
            $modelFileContent .= <<<EOD
                ];

                protected $hidden = ['user_id'];

                public function account()
                {
                    return $thisBelongsTo;
                }
            }
            EOD;

            File::put('app/Models/' . $model . '.php', $modelFileContent);
        } catch (Exception $e) {
            throw new Exception(sprintf("Could not create model: '%s'. Message: %s", $modelName, $e->getMessage()));
        }
    }

    /**
     * @param Category $category
     * @param string $name
     * @return Collection
     * @throws Exception
     */
    public function getOrCreateCollection(Category $category, string $name): Collection
    {
        if (!class_exists(sprintf("App\Models\%s\%s", Str::studly($category->slug), $this->formatModelName($name)))) {
            throw new Exception(sprintf("Model does not exist: '%s'.", $this->formatModelName($name)));
        }

        $collection = Collection::whereCategoryId($category->id)->whereName($name)->first();

        if ($collection) {
            return $collection;
        }

        $newestCollection = Collection::whereCategoryId($category->id)->orderByDesc('order')->pluck('order')->first();

        if ($newestCollection) {
            $order = ++$newestCollection;
        } else {
            $order = $category->id * 1000;
        }

        try {
            $collection = new Collection();

            $collection->category_id = $category->id;
            $collection->order = $order;
            $collection->name = $name;
            $collection->slug = Str::slug($name);
            $collection->model = sprintf("App\\Models\\%s\\%s", Str::studly($category->slug), $this->formatModelName($name));

            $collection->save();

            return $collection;
        } catch (Exception $e) {
            throw new Exception(sprintf("Could not create collection: '%s'. Message: %s", $name, $e->getMessage()));
        }
    }

    /**
     * @param Category $category
     * @param Collection $collection
     * @return void
     * @throws Exception
     */
    public function createImageDirectory(Category $category, Collection $collection): void
    {
        try {
            $imageDirectoryPath = sprintf("%s/images/%s/%s", public_path(), $category->slug, $collection->slug);

            if (!File::exists($imageDirectoryPath)) {
                File::makeDirectory($imageDirectoryPath, 0755, true, true);
            }
        } catch (Exception $e) {
            throw new Exception(sprintf("Could not create image directory: '%s'. Message: %s", $collection->slug, $e->getMessage()));
        }
    }

    /**
     * @param string $name
     * @return string
     */
    private function formatModelName(string $name): string
    {
        return Str::studly(Str::slug($name));
    }
}
