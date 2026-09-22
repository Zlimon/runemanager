<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CalendarEventType;
use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCalendarEventRequest;
use App\Http\Resources\CalendarEventResource;
use App\Models\CalendarEvent;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Admin calendar management: full CRUD (create/edit/delete use
     * the same `calendar.*` routes as the public grid) plus the "important
     * event" highlight colours. Unlike the public grid, past events are shown
     * and remain editable/deletable here — there's no past-date guard.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Calendar/Index', [
            'upcoming' => CalendarEventResource::collection(
                CalendarEvent::with('user:id,name')->upcoming()->get(),
            )->resolve(),
            'past' => CalendarEventResource::collection(
                CalendarEvent::with('user:id,name')->past()->get(),
            )->resolve(),
            'eventTypes' => CalendarEventType::options(),
            'highlightColors' => Instance::calendarHighlightColors(),
        ]);
    }

    /** Persist which palette colours mark an event as "important". */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'calendar_highlight_colors' => ['array'],
            'calendar_highlight_colors.*' => [Rule::in(StoreCalendarEventRequest::COLORS)],
        ]);

        SettingHelper::setSetting('calendar_highlight_colors', json_encode($validated['calendar_highlight_colors']));

        return back()->with('status', 'Calendar settings updated.');
    }
}
