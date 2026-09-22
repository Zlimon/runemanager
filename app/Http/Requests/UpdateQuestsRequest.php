<?php

namespace App\Http\Requests;

use App\Services\Feed\RecordFeedEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** A quest-state snapshot pushed by the RuneLite plugin. */
class UpdateQuestsRequest extends FormRequest
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
            'quests' => ['required', 'array'],
            'quests.*' => ['required', 'array'],
            'quests.*.0' => ['required', 'string'],  // quest name
            'quests.*.1' => ['required', 'integer'], // status (0 = not started, 1 = in progress, 2 = finished)
            ...RecordFeedEvent::positionRules(),
        ];
    }
}
