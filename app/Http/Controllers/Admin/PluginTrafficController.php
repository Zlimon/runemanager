<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\LogPluginRequest;
use App\Models\User;
use App\Services\Plugin\PluginTraffic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The plugin API traffic monitor. The window and feed filters are optional
 * query parameters that fall back to a default on bad input, so they are read
 * directly rather than through a Form Request. The request feeds are
 * already-shaped rows, wrapped as a resource collection for `links`/`meta`.
 * Every aggregate prop is a closure, so a partial reload (paging the feed,
 * changing a filter) only runs the reads it asks for.
 */
class PluginTrafficController extends Controller
{
    public function index(Request $request, PluginTraffic $traffic): Response
    {
        $window = PluginTraffic::window($request->query('window'));
        $filters = [
            'flag' => $this->flagFilter($request),
            'user' => $request->integer('user') ?: null,
            'route' => is_string($request->query('route')) ? $request->query('route') : null,
        ];
        $overview = $this->overview($traffic, $window);

        return Inertia::render('Admin/Traffic/Index', [
            'window' => $window,
            'windows' => array_keys(PluginTraffic::WINDOWS),
            'flags' => LogPluginRequest::FLAGS,
            'filters' => $filters,
            'summary' => fn () => $overview()['summary'],
            'timeline' => fn () => $traffic->timeline($window),
            'routes' => fn () => $overview()['routes'],
            'topUsers' => fn () => $overview()['topUsers'],
            'suspicious' => fn () => $traffic->suspiciousUsers($window),
            'flagged' => fn () => JsonResource::collection($traffic->requests($window, $filters + ['flagged' => true])),
        ]);
    }

    public function show(Request $request, User $user, PluginTraffic $traffic): Response
    {
        $window = PluginTraffic::window($request->query('window'));
        $flag = $this->flagFilter($request);
        $overview = $this->overview($traffic, $window, $user->id);

        return Inertia::render('Admin/Traffic/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tokens' => $user->tokens()->count(),
            ],
            'window' => $window,
            'windows' => array_keys(PluginTraffic::WINDOWS),
            'flags' => LogPluginRequest::FLAGS,
            'flag' => $flag,
            'summary' => fn () => $overview()['summary'],
            'timeline' => fn () => $traffic->timeline($window, $user->id),
            'routes' => fn () => $overview()['routes'],
            'flagCounts' => fn () => $traffic->flagCounts($window, $user->id),
            'identities' => fn () => $traffic->identities($window, $user->id),
            'requests' => fn () => JsonResource::collection($traffic->requests($window, ['user' => $user->id, 'flag' => $flag])),
        ]);
    }

    /**
     * PluginTraffic::overview() run at most once per request, and only when
     * a prop that needs it is resolved: a partial reload of the feed skips it.
     *
     * @return \Closure(): array<string, mixed>
     */
    private function overview(PluginTraffic $traffic, string $window, ?int $userId = null): \Closure
    {
        $overview = null;

        return function () use (&$overview, $traffic, $window, $userId): array {
            return $overview ??= $traffic->overview($window, $userId);
        };
    }

    private function flagFilter(Request $request): ?string
    {
        $flag = $request->query('flag');

        return in_array($flag, LogPluginRequest::FLAGS, true) ? $flag : null;
    }
}
