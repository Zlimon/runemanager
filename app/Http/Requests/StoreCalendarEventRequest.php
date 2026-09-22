<?php

namespace App\Http\Requests;

use App\Enums\CalendarEventType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCalendarEventRequest extends FormRequest
{
    /**
     * Admins / clan leaders / discretionary members create events.
     * Roles aren't modelled yet, so for now any authenticated user
     * can create. Swap in a real policy when those land.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'event_type' => ['required', Rule::enum(CalendarEventType::class)],
            'color' => ['nullable', Rule::in(self::COLORS)],
            'item_id' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['required', 'date', 'after_or_equal:today'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }

    /** Allowed event colours (V-Calendar palette names). */
    public const COLORS = ['gray', 'red', 'orange', 'yellow', 'green', 'teal', 'blue', 'indigo', 'purple', 'pink'];

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'starts_at.after_or_equal' => "Events can't be scheduled before today.",
            'ends_at.after_or_equal' => 'The end time must be at or after the start time.',
        ];
    }
}
