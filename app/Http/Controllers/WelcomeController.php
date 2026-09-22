<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Http\Resources\CalendarEventResource;
use App\Models\Account;
use App\Models\CalendarEvent;
use App\Services\Instance\InstanceStats;
use App\Support\Instance;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    /**
     * The public landing page — a recruitment-facing digest of the instance:
     * headline counts, upcoming events and the top accounts by total level.
     * When the owner enables it, usernames in the showcase are masked so the
     * leaderboard can be shown publicly without revealing who's who.
     */
    public function index(InstanceStats $stats): Response
    {
        $anonymize = Instance::publicAnonymizeAccounts();

        return Inertia::render('Welcome/Index', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'anonymized' => $anonymize,
            'stats' => $stats->counts(),
            'upcoming' => CalendarEventResource::collection(
                CalendarEvent::with('user:id,name')->upcoming()->limit(5)->get(),
            )->resolve(),
            'topAccounts' => $this->topAccounts($anonymize),
        ]);
    }

    /**
     * Top five accounts by total level. When anonymising, only what the
     * showcase draws leaves the server: a masked username, the account type and
     * the total level. Everything else (ids, exact XP, ranks, clan titles, dates)
     * is left out, since exact XP alone can be looked up on the OSRS hiscores.
     *
     * @return list<array<string, mixed>>
     */
    private function topAccounts(bool $anonymize): array
    {
        $accounts = AccountResource::collection(
            Account::query()->with('user')->topByLevel(5)->get(),
        )->resolve();

        if (! $anonymize) {
            return $accounts;
        }

        return array_map(fn (array $account): array => [
            'username' => $this->maskUsername((string) $account['username']),
            'account_type' => $account['account_type'],
            'level' => $account['level'],
        ], $accounts);
    }

    /** Keep the first letter, mask the rest — e.g. "Zezima" → "Z•••••". */
    private function maskUsername(string $username): string
    {
        if ($username === '') {
            return 'Anonymous';
        }

        return Str::substr($username, 0, 1).str_repeat('•', max(2, Str::length($username) - 1));
    }
}
