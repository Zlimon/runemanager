<?php

namespace App\Services\Accounts;

use App\Models\Account;
use App\Services\WiseOldMan\UpdateWomPreferences;
use App\Support\Instance;

/**
 * Apply an account owner's choices from Settings → Privacy: whether other
 * members see where its events happened, whether new screenshots arrive
 * hidden, and (while the integration is on) its Wise Old Man choices, which
 * go through {@see UpdateWomPreferences} for their consent and cache rules.
 * A choice left out is unchanged.
 */
class UpdateAccountPrivacy
{
    public function __construct(private UpdateWomPreferences $wom) {}

    /**
     * @param  array{show_events_on_map?: mixed, hide_new_screenshots?: mixed, show_on_progress?: mixed, tracking_opt_in?: mixed}  $choices
     */
    public function update(Account $account, array $choices): void
    {
        $settings = array_map(
            fn ($value): bool => (bool) $value,
            array_intersect_key($choices, array_flip(['show_events_on_map', 'hide_new_screenshots'])),
        );

        if ($settings !== []) {
            $account->forceFill($settings)->save();
        }

        if (! Instance::womEnabled()) {
            return;
        }

        $showOnProgress = array_key_exists('show_on_progress', $choices) ? (bool) $choices['show_on_progress'] : null;
        $trackingOptIn = array_key_exists('tracking_opt_in', $choices) ? (bool) $choices['tracking_opt_in'] : null;

        if ($showOnProgress !== null || $trackingOptIn !== null) {
            $this->wom->update($account, $showOnProgress, $trackingOptIn);
        }
    }
}
