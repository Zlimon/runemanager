<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class UpdateCalendarEventRequest extends StoreCalendarEventRequest
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
     * The same as {@see StoreCalendarEventRequest} except that `starts_at` may be
     * in the past — an event that has already started still has to be editable
     * (fixing a typo, extending the window).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'starts_at' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return Arr::except(parent::messages(), 'starts_at.after_or_equal');
    }
}
