<?php

namespace App\Http\Requests;

use App\Enums\CalendarEventType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCalendarEventRequest extends FormRequest
{
    /**
     * SPEC §10.2 — admins / clan leaders / discretionary members create events.
     * Roles aren't modelled yet (§3.4 + §12), so for now any authenticated user
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
