<?php

namespace App\Http\Requests;

use App\Enums\CalendarEventType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCalendarEventRequest extends FormRequest
{
    /**
     * Who may edit which event is ownership-dependent, so the
     * controller decides (creator or `manage calendar`); the route already keeps
     * everyone else out.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Mirrors {@see StoreCalendarEventRequest} except that `starts_at` may be in
     * the past — an event that has already started still has to be editable
     * (fixing a typo, extending the window).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'event_type' => ['required', Rule::enum(CalendarEventType::class)],
            'color' => ['nullable', Rule::in(StoreCalendarEventRequest::COLORS)],
            'item_id' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ends_at.after_or_equal' => 'The end time must be at or after the start time.',
        ];
    }
}
