<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateIntegrationsRequest;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationsController extends Controller
{
    /** Outbound integrations: the instance webhook (Discord etc.) and Wise Old Man. */
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/Integrations/Index', [
            'settings' => [
                'webhook_url' => (string) SettingHelper::getSetting('webhook_url', ''),
                'wom_enabled' => Instance::womEnabled(),
                'wom_active_within_days' => Instance::womActiveWithinDays(),
                'wom_progress_public' => Instance::womProgressPublic(),
            ],
        ]);
    }

    public function update(UpdateIntegrationsRequest $request): RedirectResponse
    {
        SettingHelper::setSetting('webhook_url', $request->validated('webhook_url') ?? '');
        SettingHelper::setSetting('wom_enabled', $request->boolean('wom_enabled'), 'bool');
        SettingHelper::setSetting('wom_active_within_days', (int) ($request->validated('wom_active_within_days') ?? Instance::womActiveWithinDays()), 'int');
        SettingHelper::setSetting('wom_progress_public', $request->boolean('wom_progress_public'), 'bool');

        return back()->with('status', 'Integrations updated.');
    }
}
