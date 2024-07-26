<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Item;
use App\Traits\CollectionTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HiscoreController extends Controller
{
    use CollectionTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request['items'] = array_map('strval', $request['items']);

        $request->validate([
            'name' => ['required', 'string', 'exists:mongodb.monsters,name', 'unique:collections,name'],
            'items' => ['array', 'exists:mongodb.items,id'],
        ]);

        try {
            $category = Category::whereName('npc')->first();
            $collection = $this->createHiscore($category, $request['name']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }

//        TODO Move to MongoDB
//        $itemNames = Item::whereIn('id', $request['items'])->orderBy('name')->pluck('name')->map(function ($item) {
//            return Str::snake($item);
//        })->toArray();

//        $migrationName = Str::snake(Str::lower($request['name']));
//        if (!file_exists(sprintf("%s/database/migrations/*_create_%s_table.php", base_path(), $migrationName))) {
//            try {
//                $npcItems = implode(
//                    ' ',
//                    array_map(
//                        function ($item) {
//                            return (Str::replace(["'", "-"], ["", "_"], Str::snake(Str::lower($item)))) . ':integer:default(0):unsigned,'; // abyssal_whip
//                        },
//                        $itemNames
//                    )
//                );
//
//                $makeMigration = sprintf('make:migration:schema create_%s_table --schema="account_id:integer:unsigned:unique, kill_count:integer:default(0):unsigned, obtained:integer:default(0):unsigned, %s',
//                    $migrationName,
//                    substr($npcItems, 0, -1) // Remove trailing comma
//                );
//                Artisan::call($makeMigration);
//            } catch (Exception $e) {
//                throw new Exception(sprintf("Could not create migration: '%s'. Message: %s", $migrationName, $e->getMessage()));
//            }
//        }

        return response()->json([
            'collection' => $collection,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
