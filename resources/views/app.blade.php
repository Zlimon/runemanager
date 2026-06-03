<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title data-inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/app.css', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        @php
            // Resolve the effective resource pack for the current viewer.
            // Authenticated user override → instance default → none.
            $pack = null;
            if ($user = auth()->user()) {
                $pack = $user->effectiveResourcePack();
            } else {
                $globalId = \App\Helpers\SettingHelper::getSetting('resource_pack_id');
                $pack = $globalId ? \App\Models\ResourcePack::find($globalId) : null;
            }
        @endphp
        @if ($pack && file_exists(public_path("resource-packs/{$pack->name}/resource-pack.css")))
            <link rel="stylesheet" href="{{ asset("resource-packs/{$pack->name}/resource-pack.css") }}?v={{ optional($pack->updated_at)->timestamp }}">
            @if ($pack->background_color || $pack->accent_color)
                {{-- Pack palette extracted at install time — emitted as CSS variables so
                     flat container backgrounds can pick up the pack's colour without
                     reaching for textured tiles. See app.css for the consumers. --}}
                <style>
                    :root {
                        @if ($pack->background_color) --pack-bg: {{ $pack->background_color }}; @endif
                        @if ($pack->accent_color) --pack-accent: {{ $pack->accent_color }}; @endif
                    }
                </style>
            @endif
        @endif
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
