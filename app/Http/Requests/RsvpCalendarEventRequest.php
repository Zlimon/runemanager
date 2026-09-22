<?php

namespace App\Http\Requests;

use App\Models\CalendarEvent;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Sign an account up for, or withdraw it from, a calendar event. Ownership of
 * the named account is checked by the controller once the id is validated.
 */
class RsvpCalendarEventRequest extends FormRequest
{
    /**
     * Attendance on an event that has already happened is a record of who turned
     * up, so it is closed to further change — both signing up and withdrawing.
     * Checked here so it answers 403 before the payload is validated.
     */
    public function authorize(): Response
    {
        /** @var CalendarEvent $calendarEvent */
        $calendarEvent = $this->route('calendarEvent');

        return $calendarEvent->hasPassed()
            ? Response::deny('This event has already taken place.')
            : Response::allow();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
        ];
    }
}
