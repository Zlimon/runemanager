<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** The palette colours that mark a calendar event as "important". */
class UpdateCalendarHighlightsRequest extends FormRequest
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
            'calendar_highlight_colors' => ['present', 'array'],
            'calendar_highlight_colors.*' => [Rule::in(StoreCalendarEventRequest::COLORS)],
        ];
    }
}
