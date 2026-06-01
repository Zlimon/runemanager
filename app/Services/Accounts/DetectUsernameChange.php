<?php

namespace App\Services\Accounts;

use App\Models\Account;
use App\Services\WiseOldMan\WiseOldManClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;

class DetectUsernameChange
{
    public function __construct(
        protected WiseOldManClient $wom,
        protected RecordUsernameChange $recorder,
    ) {}

    /**
     * Poll WOM's approved name-change feed and apply any that match accounts we track.
     *
     * @return array{checked: int, matched: int, applied: int}
     *
     * @throws GuzzleException
     */
    public function run(int $pages = 1, int $limit = 50): array
    {
        $checked = 0;
        $matched = 0;
        $applied = 0;

        for ($i = 0; $i < $pages; $i++) {
            $response = $this->wom->recentNameChanges('approved', $limit, $i * $limit);
            $entries = json_decode((string) $response->getBody(), true) ?: [];

            if (! is_array($entries) || count($entries) === 0) {
                break;
            }

            $checked += count($entries);

            $oldNames = collect($entries)
                ->pluck('oldName')
                ->filter()
                ->map(fn (string $name) => mb_strtolower($name))
                ->all();

            if (empty($oldNames)) {
                continue;
            }

            $accountsByLowerName = Account::query()
                ->whereIn(DB::raw('LOWER(username)'), collect($oldNames)->unique()->all())
                ->get()
                ->keyBy(fn (Account $a) => mb_strtolower($a->username));

            if ($accountsByLowerName->isEmpty()) {
                continue;
            }

            foreach ($entries as $entry) {
                if (! isset($entry['oldName'], $entry['newName'])) {
                    continue;
                }

                $account = $accountsByLowerName->get(mb_strtolower($entry['oldName']));

                if (! $account) {
                    continue;
                }

                $matched++;

                $result = $this->recorder->record($account, $entry['newName']);

                if ($result !== null) {
                    $applied++;
                }
            }
        }

        return ['checked' => $checked, 'matched' => $matched, 'applied' => $applied];
    }
}
