<?php

namespace App\Http\Controllers;

use App\Enums\CalendarEventType;
use App\Http\Requests\StoreCalendarEventRequest;
use App\Http\Resources\CalendarEventResource;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CalendarEventController extends Controller
{
    /**
     * SPEC §10.3 — viewing is public; the calendar renders as a month grid
     * (V-Calendar). The visible week range is sent as ?from/?to as the user
     * navigates; we return every event overlapping it (so multi-day events show
     * on each day they span) plus a short upcoming list for the side widget.
     */
    public function index(Request $request): Response
    {
        $from = ($this->parseDate($request->query('from'))
            ?? Carbon::now()->startOfMonth()->startOfWeek(CarbonInterface::SUNDAY))->startOfDay();
        $to = ($this->parseDate($request->query('to'))
            ?? $from->copy()->addDays(41))->endOfDay();

        return Inertia::render('Calendar/Index', [
            'events' => CalendarEventResource::collection(
                CalendarEvent::with('user:id,name')
                    ->where('starts_at', '<=', $to)
                    ->where(function ($query) use ($from) {
                        $query->where('ends_at', '>=', $from)
                            ->orWhere(fn ($q) => $q->whereNull('ends_at')->where('starts_at', '>=', $from));
                    })
                    ->orderBy('starts_at')
                    ->get(),
            )->resolve(),
            'upcoming' => CalendarEventResource::collection(
                CalendarEvent::with('user:id,name')->upcoming()->limit(5)->get(),
            )->resolve(),
            'eventTypes' => CalendarEventType::options(),
        ]);
    }

    /** Parse a ?from/?to=YYYY-MM-DD param, or null when absent/invalid. */
    private function parseDate(?string $date): ?Carbon
    {
        try {
            return $date ? Carbon::createFromFormat('Y-m-d', $date) : null;
        } catch (\Throwable) {
            return null;
        }
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
