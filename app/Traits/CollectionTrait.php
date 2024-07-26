<?php

namespace App\Traits;

use App\Models\Category;
use App\Models\Collection;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait CollectionTrait
{
    /**
     * @param Category $category
     * @param string $name
     * @return Collection
     * @throws Exception
     */
    public function createHiscore(Category $category, string $name): Collection
    {
        $this->createModel($category, $name);

        $collection = $this->getOrCreateCollection($category, $name);

        $this->createImageDirectory($category, $collection);

        return $collection;
    }

    /**
     * @param Category $category
     * @param string $name
     * @return void
     * @throws Exception
     */
    public function createModel(Category $category, string $name): void
    {
        $modelName = $this->formatModelName($name);

        if (class_exists(sprintf("App\Models\%s\%s", Str::studly($category->slug), $modelName))) {
            throw new Exception(sprintf("Model '%s' already exists.", $modelName));
        }

        try {
            $model = sprintf("%s/%s", Str::studly($category->slug), $modelName);
            $makeModel = sprintf("make:model %s", $model);

            Artisan::call($makeModel);
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
    public function createImageDirectory(Category $category, Collection $collection)
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
