<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * A per-IP ceiling on the whole plugin API, ahead of authentication, so a
 * flood of requests with no or a bad token is refused before any token
 * lookup. The per-route `throttle:plugin-*` limits only apply once a request
 * is authenticated. It is generous: one player's client peaks at a few
 * hundred requests a minute, and a household or NAT may share an IP.
 *
 * Deliberately not a `throttle:` middleware: Laravel's middleware priority
 * sorts ThrottleRequests after authentication, which would defeat this.
 */
class LimitPluginRequestsByIp
{
    public const PER_MINUTE = 1200;

    public static function key(?string $ip): string
    {
        return 'plugin-ip:'.$ip;
    }

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = self::key($request->ip());

        if (RateLimiter::tooManyAttempts($key, self::PER_MINUTE)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json(['message' => 'Too Many Attempts.'], 429, [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => self::PER_MINUTE,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
