<?php

namespace App\Http\Requests;

use App\Services\Feed\RecordFeedEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** A batch of loot drops pushed by the RuneLite plugin. */
class StoreLootRequest extends FormRequest
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
            'loot' => ['required', 'array', 'min:1', 'max:100'],
            'loot.*.source' => ['required', 'string', 'max:120'],
            'loot.*.type' => ['nullable', 'string', 'max:20'],
            'loot.*.items' => ['required', 'array', 'min:1'],
            'loot.*.items.*.id' => ['required', 'integer'],
            'loot.*.items.*.quantity' => ['required', 'integer', 'min:1'],
            'loot.*.total_value' => ['nullable', 'integer', 'min:0'],
            'loot.*.killed_at' => ['required', 'date'],
            'loot.*.feed' => ['nullable', 'boolean'],
            ...RecordFeedEvent::positionRules('loot.*.position'),
        ];
    }
}
