<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\User;
use Database\Factories\FeedEventFactory;
use Illuminate\Database\Seeder;

/**
 * Gives demo feed events the position the plugin would have attached, so the
 * Live Map's events layer has something to show, and opts a few other
 * members' accounts in to showing their events there.
 *
 * Idempotent: only events without a position are touched, and every eighth
 * one is left without, like events recorded before the plugin sent positions.
 * The opt-ins are only made while no account has opted in yet. Never runs in
 * production.
 */
class DemoEventPositionsSeeder extends Seeder
{
    /** Demo accounts of other members opted in to the event map. */
    private const OPTED_IN_ACCOUNTS = 6;

    /** The demo login, whose own accounts are always visible to them anyway. */
    private const DEMO_OWNER_EMAIL = 'zlimon@runemanager.com';

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $positioned = 0;

        FeedEvent::query()
            ->whereNull('world_x')
            ->chunkById(200, function ($events) use (&$positioned): void {
                foreach ($events as $event) {
                    if ($event->id % 8 === 0) {
                        continue;
                    }

                    $payload = is_array($event->payload) ? $event->payload : [];
                    $event->payload = [...$payload, 'position' => FeedEventFactory::positionFor((string) $event->type, $payload, fake())];
                    $event->save();
                    $positioned++;
                }
            });

        $this->command?->info(sprintf('DemoEventPositionsSeeder: positioned %d feed events.', $positioned));

        $this->optIn();
    }

    private function optIn(): void
    {
        if (Account::where('show_events_on_map', true)->exists()) {
            return;
        }

        $ownerId = User::where('email', self::DEMO_OWNER_EMAIL)->value('id');

        $ids = Account::query()
            ->whereNotNull('user_id')
            ->when($ownerId !== null, fn ($query) => $query->where('user_id', '!=', $ownerId))
            ->whereHas('feedEvents', fn ($query) => $query->positioned())
            ->orderBy('id')
            ->limit(self::OPTED_IN_ACCOUNTS)
            ->pluck('id');

        Account::whereIn('id', $ids)->update(['show_events_on_map' => true]);

        $this->command?->info(sprintf('DemoEventPositionsSeeder: opted %d accounts in to the event map.', $ids->count()));
    }
}
