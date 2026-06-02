<?php

namespace App\Http\Controllers;

use App\Enums\CalendarEventType;
use App\Http\Requests\StoreCalendarEventRequest;
use App\Http\Resources\CalendarEventResource;
use App\Models\CalendarEvent;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CalendarEventController extends Controller
{
    /**
     * SPEC §10.2 — viewing is public. Show split lists for upcoming + past
     * so the v1 page doesn't need a month-grid component yet.
     */
    public function index(): Response
    {
        return Inertia::render('Calendar/Index', [
            'upcoming' => CalendarEventResource::collection(
                CalendarEvent::with('user:id,name')->upcoming()->get(),
            )->resolve(),
            'past' => CalendarEventResource::collection(
                CalendarEvent::with('user:id,name')->past()->limit(20)->get(),
            )->resolve(),
            'eventTypes' => CalendarEventType::options(),
        ]);
    }

    public function store(StoreCalendarEventRequest $request): RedirectResponse
    {
        $request->user()->calendarEvents()->create($request->validated());

        return redirect()->route('calendar.index');
    }

    public function destroy(CalendarEvent $calendarEvent): RedirectResponse
    {
        abort_if(
            $calendarEvent->user_id !== request()->user()?->id,
            HttpResponse::HTTP_FORBIDDEN,
        );

        $calendarEvent->delete();

        return redirect()->route('calendar.index');
    }
}
