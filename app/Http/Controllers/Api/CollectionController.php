<?php

namespace App\Http\Controllers\Api;

use App\Category;
use App\Collection;
use App\Http\Controllers\Controller;

class CollectionController extends Controller
{
    public function index($collectionCategory)
    {
        $categories = Category::get()->pluck('category')->toArray();
        array_push($categories, "all");

        if (!in_array($collectionCategory, $categories, true)) {
            return response("This collection category could not be found", 404);
        }

        if ($collectionCategory === "all") {
            return response()->json(Collection::get(), 200);
        }

        $collectionList = Category::with('collection')->where('category', $collectionCategory)->first();

        return response()->json($collectionList, 200);
    }
}
