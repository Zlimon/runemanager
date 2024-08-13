<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CollectionHiscoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param string $category
     * @param string $collection
     * @return Response
     */
    public function index(string $category,string $collection): Response
    {
        $collectionRecord = Collection::byCategorySlug($category)->whereSlug($collection)->firstOrFail();

        $modelClass = $collectionRecord->model;

        if (!class_exists($modelClass)) {
            abort(404, 'Model not found.');
        }

        $modelInstance = new $modelClass;

        $hiscores = $modelInstance->with('account.user')
            ->orderByRaw('CASE WHEN "rank" = 0 THEN 0 ELSE 1 END DESC, "rank" ASC')
            ->orderByDesc('kill_count')
            ->orderByDesc('obtained')
            ->orderByDesc('id')
            ->get();

        $hiscores = CollectionResource::collection($hiscores)->resolve();

        return Inertia::render('Hiscores/Bosses/Show', [
            'bossProp' => $collectionRecord->name,
            'hiscoresProp' => $hiscores,
        ]);
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
    public function store(Request $request)
    {
        //
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
