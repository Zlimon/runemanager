<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Http\Resources\CalendarEventResource;
use App\Models\Account;
use App\Models\CalendarEvent;
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
    public function index(): Response
    {
        $anonymize = Instance::publicAnonymizeAccounts();

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'anonymized' => $anonymize,
            'stats' => [
                'accounts' => Account::count(),
                'members' => Account::query()->whereNotNull('user_id')->distinct()->count('user_id'),
                'online' => Account::query()
                    ->where('last_seen_at', '>=', now()->subMinutes((int) config('runemanager.presence.online_within_minutes')))
                    ->count(),
            ],
            'upcoming' => CalendarEventResource::collection(
                CalendarEvent::with('user:id,name')->upcoming()->limit(5)->get(),
            )->resolve(),
            'topAccounts' => $this->topAccounts($anonymize),
        ]);
    }

    /**
     * Top five accounts by total level. When anonymising, real usernames never
     * leave the server — each is replaced with a masked label.
     *
     * @return list<array<string, mixed>>
     */
    private function topAccounts(bool $anonymize): array
    {
        $accounts = AccountResource::collection(
            Account::query()->with('user')
                ->where('level', '>', 0)
                ->orderByDesc('level')
                ->orderByDesc('xp')
                ->limit(5)
                ->get(),
        )->resolve();

        if (! $anonymize) {
            return $accounts;
        }

        return array_map(function (array $account): array {
            $account['username'] = $this->maskUsername((string) $account['username']);

            return $account;
        }, $accounts);
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
