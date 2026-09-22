<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FeedSyncController extends Controller
{
    /** Hiscores cadence + live-feed thresholds. */
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/FeedSync/Index', [
            'settings' => [
                'hiscore_refresh_minutes' => Instance::hiscoreRefreshMinutes(),
                'feed_level_up_thresholds' => implode(', ', Instance::feedLevelUpThresholds()),
                'feed_loot_min_value' => Instance::feedLootMinValue(),
            ],
        ]);
    }

    /**
     * Operational config: hiscores refresh cadence and live-feed
     * milestone thresholds. The thresholds string is stored raw and sanitised on
     * read (see Instance::feedLevelUpThresholds).
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hiscore_refresh_minutes' => ['required', 'integer', Rule::in([30, 60, 180, 360, 720, 1440])],
            'feed_level_up_thresholds' => ['nullable', 'string', 'max:200', 'regex:/^[0-9,\s]*$/'],
            'feed_loot_min_value' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ]);

        SettingHelper::setSetting('hiscore_refresh_minutes', (int) $validated['hiscore_refresh_minutes'], 'int');
        SettingHelper::setSetting('feed_level_up_thresholds', $validated['feed_level_up_thresholds'] ?? '');
        SettingHelper::setSetting('feed_loot_min_value', (int) $validated['feed_loot_min_value'], 'int');

        return back()->with('status', 'Feed & sync settings updated.');
    }
}
