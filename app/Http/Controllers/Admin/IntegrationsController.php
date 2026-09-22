<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'webhook_url' => ['nullable', 'url', 'max:2000'],
        ]);

        SettingHelper::setSetting('webhook_url', $validated['webhook_url'] ?? '');

        return back()->with('status', 'Integrations updated.');
    }
}
