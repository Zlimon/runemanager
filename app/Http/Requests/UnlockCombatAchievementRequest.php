<?php

namespace App\Http\Requests;

use App\Services\Feed\RecordFeedEvent;
use App\Support\CombatAchievements;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** A freshly completed Combat Achievement task pushed by the RuneLite plugin. */
class UnlockCombatAchievementRequest extends FormRequest
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
            'task' => ['required', 'string', 'max:255'],
            // Nullable: the in-game popup notification (the default setting) gives
            // the task name but not the tier; only the chat message carries it.
            'tier' => ['nullable', 'string', Rule::in(CombatAchievements::tiers())],
            ...RecordFeedEvent::positionRules(),
        ];
    }
}
