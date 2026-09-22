<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BrandingController extends Controller
{
    /** Branding (logo + banner). */
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/Branding/Index', [
            'branding' => [
                'logo_url' => Instance::logoUrl(),
                'banner_url' => Instance::bannerUrl(),
            ],
        ]);
    }

    /**
     * Instance branding (logo + banner) image uploads, stored on
     * the public disk. POST (multipart); a `remove_*` flag clears an asset.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['nullable', 'image', 'max:1024'],
            'banner' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_banner' => ['nullable', 'boolean'],
        ]);

        $this->storeBrandingAsset($request, 'logo', 'logo_path', $request->boolean('remove_logo'));
        $this->storeBrandingAsset($request, 'banner', 'banner_path', $request->boolean('remove_banner'));

        return back()->with('status', 'Branding updated.');
    }

    private function storeBrandingAsset(Request $request, string $field, string $key, bool $remove): void
    {
        if (($remove || $request->hasFile($field)) && ($old = SettingHelper::getSetting($key))) {
            Storage::disk('public')->delete($old);
            SettingHelper::setSetting($key, '');
        }

        if ($request->hasFile($field)) {
            SettingHelper::setSetting($key, $request->file($field)->store('branding', 'public'));
        }
    }
}
