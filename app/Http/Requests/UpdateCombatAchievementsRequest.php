<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Combat Achievement points and per-tier status pushed by the RuneLite plugin. */
class UpdateCombatAchievementsRequest extends FormRequest
{
    /** Access is enforced by the route middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'points' => ['required', 'integer', 'min:0'],
            'tiers' => ['present', 'array'],
        ];
    }
}
