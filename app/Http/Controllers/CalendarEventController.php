<?php

namespace App\Http\Controllers;

use App\Enums\CalendarEventType;
use App\Http\Requests\RsvpCalendarEventRequest;
use App\Http\Requests\StoreCalendarEventRequest;
use App\Http\Requests\UpdateCalendarEventRequest;
use App\Http\Resources\CalendarEventResource;
use App\Jobs\DeliverWebhook;
use App\Models\Account;
use App\Models\CalendarEvent;
use App\Models\User;
use App\Services\Webhooks\WebhookPayload;
use App\Support\Instance;
use App\Support\Roles;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CalendarEventController extends Controller
{
    /**
     * Viewing is public; the calendar renders as a month grid
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
                CalendarEvent::query()->overlapping($from, $to)->orderBy('starts_at')->get(),
            )->resolve(),
            'upcoming' => CalendarEventResource::collection(
                CalendarEvent::query()->upcoming()->limit(5)->get(),
            )->resolve(),
            'eventTypes' => CalendarEventType::options(),
            'highlightColors' => Instance::calendarHighlightColors(),
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
        $event = $request->user()->calendarEvents()->create($request->validated());

        // Forward to the instance's configured webhook (e.g. Discord).
        if ($url = Instance::webhookUrl()) {
            DeliverWebhook::dispatch($url, WebhookPayload::forCalendarEvent($event), 'calendar');
        }

        return redirect()->route('calendar.index');
    }

    public function update(UpdateCalendarEventRequest $request, CalendarEvent $calendarEvent): RedirectResponse
    {
        $this->abortUnlessManages($calendarEvent, $request->user());

        $calendarEvent->update($request->validated());

        return redirect()->route('calendar.index');
    }

    public function destroy(Request $request, CalendarEvent $calendarEvent): RedirectResponse
    {
        $this->abortUnlessManages($calendarEvent, $request->user());

        $calendarEvent->delete();

        return redirect()->route('calendar.index');
    }

    /**
     * Sign one of your accounts up for an event. RSVP is per account,
     * so the payload names which one; it has to be yours.
     *
     * A member attends as exactly one account, so naming a different one moves
     * the attendance rather than adding a second. Doing it as a delete-then-create
     * inside a transaction keeps it atomic against the unique index the schema
     * carries — two concurrent requests cannot leave the member attending twice.
     */
    public function rsvp(RsvpCalendarEventRequest $request, CalendarEvent $calendarEvent): RedirectResponse
    {
        $account = $this->ownedAccount($request);
        $userId = $request->user()->id;

        DB::transaction(function () use ($calendarEvent, $account, $userId): void {
            $calendarEvent->rsvps()->where('user_id', $userId)->delete();

            $calendarEvent->rsvps()->create([
                'account_id' => $account->id,
                'user_id' => $userId,
            ]);
        });

        return back();
    }

    /** Withdraw an account's attendance; the row's absence *is* "not going". */
    public function destroyRsvp(RsvpCalendarEventRequest $request, CalendarEvent $calendarEvent): RedirectResponse
    {
        $account = $this->ownedAccount($request);

        $calendarEvent->rsvps()->where('account_id', $account->id)->delete();

        return back();
    }

    /**
     * Resolve the `account_id` in the request, rejecting accounts the caller
     * doesn't own — otherwise anyone could RSVP on someone else's behalf.
     */
    private function ownedAccount(RsvpCalendarEventRequest $request): Account
    {
        $account = Account::findOrFail($request->validated('account_id'));

        abort_if($account->user_id !== $request->user()?->id, HttpResponse::HTTP_FORBIDDEN);

        return $account;
    }

    /**
     * An event is managed by its creator or by anyone holding
     * `manage calendar`, so an admin can clean up someone else's event.
     */
    private function abortUnlessManages(CalendarEvent $calendarEvent, ?User $user): void
    {
        abort_unless(
            $user !== null
                && ($user->id === $calendarEvent->user_id || $user->can(Roles::MANAGE_CALENDAR)),
            HttpResponse::HTTP_FORBIDDEN,
        );
    }
}
