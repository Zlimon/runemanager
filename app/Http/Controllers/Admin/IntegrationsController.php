<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateIntegrationsRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationsController extends Controller
{
    /** Outbound integrations: the instance webhook (Discord etc.). */
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/Integrations/Index', [
            'settings' => [
                'webhook_url' => (string) SettingHelper::getSetting('webhook_url', ''),
            ],
        ]);
    }

    public function update(UpdateIntegrationsRequest $request): RedirectResponse
    {
        SettingHelper::setSetting('webhook_url', $request->validated('webhook_url') ?? '');

        return back()->with('status', 'Integrations updated.');
    }
}
