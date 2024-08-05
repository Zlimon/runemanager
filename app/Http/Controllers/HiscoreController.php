<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Http\Resources\SkillResource;
use App\Models\Category;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HiscoreController extends Controller
{
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param string $skill
     */
    public function showSkill(string $skill)
    {
        // Find the skill row based on the provided skill name
        $skillRecord = Skill::where('name', $skill)->firstOrFail();

        if (!$skillRecord) {
            abort(404, 'Skill not found.');
        }

        // Get the model class name from the "model" column
        $modelClass = $skillRecord->model;

        // Ensure the class exists and then instantiate it
        if (class_exists($modelClass)) {
            $modelInstance = new $modelClass;

            // Get all rows from the dynamically instantiated model
            $hiscores = $modelInstance->with('account.user')->orderByDesc('level')->orderByDesc('xp')->orderByDesc('id')->get();

            $hiscores = SkillResource::collection($hiscores);

            return Inertia::render('Hiscores/Show', [
                'skillProp' => $skillRecord->name,
                'hiscoresProp' => $hiscores,
            ]);
        } else {
            // Handle the error case where the model class does not exist
            abort(404, 'Model not found.');
        }
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
