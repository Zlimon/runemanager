<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Hiscores refresh cadence and live-feed thresholds. */
class UpdateFeedSyncSettingsRequest extends FormRequest
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
            'hiscore_refresh_minutes' => ['required', 'integer', Rule::in([30, 60, 180, 360, 720, 1440])],
            'feed_level_up_thresholds' => ['nullable', 'string', 'max:200', 'regex:/^[0-9,\s]*$/'],
            'feed_loot_min_value' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ];
    }
}
