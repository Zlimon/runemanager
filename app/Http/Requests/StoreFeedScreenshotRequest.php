<?php

namespace App\Http\Requests;

use App\Services\Feed\RecordFeedEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** A screenshot for a feed event, uploaded by the RuneLite plugin. */
class StoreFeedScreenshotRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::in(RecordFeedEvent::SCREENSHOT_TYPES)],
            'image' => ['required', 'image', 'max:4096'],
        ];
    }
}
