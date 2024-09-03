<?php

namespace App\Http\Controllers;

use App\Http\Resources\SkillResource;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkillHiscoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param string $skill
     * @return Response
     */
    public function index(string $skill): Response
    {
        $skillRecord = Skill::whereSlug($skill)->firstOrFail();

        $modelClass = $skillRecord->model;

        if (!class_exists($modelClass)) {
            abort(404, 'Model not found.');
        }

        $modelInstance = new $modelClass;

        $hiscores = $modelInstance->with('account.user')
            ->orderByRaw('CASE WHEN "rank" = 0 THEN 0 ELSE 1 END DESC, "rank" ASC')
            ->orderByDesc('level')
            ->orderByDesc('xp')
            ->orderByDesc('id')
            ->get();

        $hiscores = SkillResource::collection($hiscores)->resolve();

        return Inertia::render('Hiscores/Skills/Show', [
            'recordTypeProp' => 'skill',
            'skillNameProp' => $skillRecord->name,
            'skillSlugProp' => $skillRecord->slug,
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
