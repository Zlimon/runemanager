<?php

namespace App\Services\Hiscores;

use App\Models\Account;
use App\Models\AccountHiscore;
use App\Services\Feed\RecordFeedEvent;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;
use RuntimeException;

class HiscoresSync
{
    public function __construct(
        protected OsrsHiscoresClient $client,
        protected RecordFeedEvent $feed,
    ) {}

    /**
     * @throws GuzzleException
     */
    public function syncForAccount(Account $account): AccountHiscore
    {
        $response = $this->client->fetch($account->username);

        $payload = json_decode((string) $response->getBody(), true);

        if (! is_array($payload) || (! isset($payload['skills']) && ! isset($payload['activities']))) {
            throw new RuntimeException(sprintf('Unexpected hiscores payload for "%s"', $account->username));
        }

        $entries = $this->normalise($payload);

        // Capture the pre-sync skill snapshot so the feed recorder can diff
        // for level-up milestones. First sync -> empty array, so the recorder
        // sees "no previous levels" and stays quiet (a level can't cross a
        // threshold without a strictly-greater-than comparison against prior).
        $existing = AccountHiscore::where('account_id', $account->id)->first();
        $previousSkills = $existing?->entries['skills'] ?? [];

        $hiscore = AccountHiscore::updateOrCreate(
            ['account_id' => $account->id],
            ['entries' => $entries, 'fetched_at' => now()],
        );

        $this->feed->recordLevelUps($account, $previousSkills, $entries['skills']);

        // Denormalise the "overall" entry onto the parent Account so the Summary
        // and Index cards have something to show without joining account_hiscores.
        $overall = $entries['skills']['overall'] ?? null;
        if (is_array($overall)) {
            $account->forceFill([
                'rank' => max(0, (int) ($overall['rank'] ?? 0)),
                'level' => max(0, (int) ($overall['level'] ?? 0)),
                'xp' => max(0, (int) ($overall['xp'] ?? 0)),
            ])->save();
        }

        return $hiscore;
    }

    /**
     * @param  array{skills?: array<int, array<string, mixed>>, activities?: array<int, array<string, mixed>>}  $payload
     * @return array{skills: array<string, array<string, int>>, activities: array<string, array<string, int>>}
     */
    public function normalise(array $payload): array
    {
        return [
            'skills' => $this->indexByKey($payload['skills'] ?? [], ['rank', 'level', 'xp']),
            'activities' => $this->indexByKey($payload['activities'] ?? [], ['rank', 'score']),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  list<string>  $fields
     * @return array<string, array<string, int>>
     */
    protected function indexByKey(array $items, array $fields): array
    {
        $out = [];

        foreach ($items as $item) {
            if (! isset($item['name'])) {
                continue;
            }

            $key = Str::slug((string) $item['name'], '_');

            if ($key === '') {
                continue;
            }

            $entry = [];
            foreach ($fields as $field) {
                if (array_key_exists($field, $item)) {
                    $entry[$field] = (int) $item[$field];
                }
            }

            $out[$key] = $entry;
        }

        return $out;
    }
}
