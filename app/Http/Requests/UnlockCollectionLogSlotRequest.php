<?php

namespace App\Http\Requests;

use App\Services\Feed\RecordFeedEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** A new collection log slot pushed by the RuneLite plugin. */
class UnlockCollectionLogSlotRequest extends FormRequest
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
            'item' => ['required', 'string', 'max:255'],
            ...RecordFeedEvent::positionRules(),
        ];
    }
}
