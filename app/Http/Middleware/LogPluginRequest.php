<?php

namespace App\Http\Middleware;

use App\Models\Account;
use App\Models\PluginRequest;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Records the metadata of every plugin API request (the whole `api` group,
 * including `/api/login` and every failure) as a PluginRequest for the admin
 * traffic monitor. Bodies are never stored.
 *
 * The write happens in terminate(), after the response has been sent, and
 * every step is wrapped so a logging fault can neither fail nor slow a push.
 * Clean successes on the live routes are not logged (isLivePing()), and
 * floods of rejected requests are sampled per IP (overRejectionBudget()), so
 * logging volume stays bounded; LimitPluginRequestsByIp caps the rest.
 * Middleware further in can mark a request with flag() — ResolvePluginAccount
 * does so for a hijack attempt, which a plain 403 status cannot tell apart.
 */
class LogPluginRequest
{
    public const FLAG_UNAUTHENTICATED = 'unauthenticated';

    public const FLAG_FORBIDDEN = 'forbidden';

    public const FLAG_ACCOUNT_CONFLICT = 'account_conflict';

    public const FLAG_VALIDATION_FAILED = 'validation_failed';

    public const FLAG_MISSING_HEADERS = 'missing_headers';

    public const FLAG_THROTTLED = 'throttled';

    public const FLAG_OVERSIZED = 'oversized';

    public const FLAG_SERVER_ERROR = 'server_error';

    public const FLAG_SUSPENDED = 'suspended';

    public const FLAGS = [
        self::FLAG_UNAUTHENTICATED, self::FLAG_FORBIDDEN, self::FLAG_ACCOUNT_CONFLICT,
        self::FLAG_VALIDATION_FAILED, self::FLAG_MISSING_HEADERS, self::FLAG_THROTTLED,
        self::FLAG_OVERSIZED, self::FLAG_SERVER_ERROR, self::FLAG_SUSPENDED,
    ];

    /**
     * Largest body (bytes) a route's real plugin push reaches, with headroom;
     * anything bigger is flagged `oversized`. Container snapshots are a few KB
     * at most (28 inventory slots, 14 equipment slots), so the default is
     * generous. A full bank is ~1,400 [id, quantity] pairs (~30 KB), the quest
     * list ~170 named entries and a clan roster up to 500 members; the uploads
     * mirror their Form Request `max:` rules (screenshot 4 MB; avatar 16 + 4 +
     * 64 + 8 MB).
     */
    public const DEFAULT_MAX_BYTES = 64 * 1024;

    public const MAX_BYTES = [
        'api.plugin.bank' => 512 * 1024,
        'api.plugin.group-bank' => 512 * 1024,
        'api.plugin.quests' => 512 * 1024,
        'api.plugin.clan.roster' => 512 * 1024,
        'api.plugin.feed.screenshot' => 4096 * 1024,
        'api.plugin.avatar' => (16384 + 4096 + 65536 + 8192) * 1024,
    ];

    /** Rejected (401/429) requests logged per IP per minute; the rest are dropped. */
    public const REJECTIONS_LOGGED_PER_MINUTE = 30;

    private const FLAGS_ATTRIBUTE = 'plugin_request.flags';

    private const DURATION_ATTRIBUTE = 'plugin_request.duration_ms';

    /**
     * Mark the current request with an abuse flag the status code alone
     * cannot express.
     */
    public static function flag(Request $request, string $flag): void
    {
        $flags = $request->attributes->get(self::FLAGS_ATTRIBUTE, []);
        $flags[] = $flag;
        $request->attributes->set(self::FLAGS_ATTRIBUTE, $flags);
    }

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $started = microtime(true);
        $response = $next($request);
        $request->attributes->set(self::DURATION_ATTRIBUTE, round((microtime(true) - $started) * 1000, 1));

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            if ($this->overRejectionBudget($request, $response)) {
                return;
            }

            $record = $this->record($request, $response);

            if ($record['status'] < 400 && $record['flags'] === [] && $this->isLivePing($request)) {
                return;
            }

            PluginRequest::query()->create($record);
        } catch (Throwable $exception) {
            Log::warning('Could not log a plugin request.', ['exception' => $exception->getMessage()]);
        }
    }

    /**
     * A `throttle:plugin-live` route (heartbeat, position, vitals, status).
     * The plugin sends these every second or two for every online player, so
     * only their failures and flags are logged, never a clean success.
     */
    private function isLivePing(Request $request): bool
    {
        return in_array('throttle:plugin-live', $request->route()?->gatherMiddleware() ?? [], true);
    }

    /**
     * Whether this rejection (401 or 429) goes past the per-IP logging budget.
     * A flood of rejected requests is then sampled, REJECTIONS_LOGGED_PER_MINUTE
     * per IP, instead of writing one document per request; it still shows in
     * the monitor, only its counts are floors.
     */
    private function overRejectionBudget(Request $request, Response $response): bool
    {
        if (! in_array($response->getStatusCode(), [401, 429], true)) {
            return false;
        }

        $key = 'plugin-log-rejections:'.$request->ip();
        Cache::add($key, 0, 60);

        return Cache::increment($key) > self::REJECTIONS_LOGGED_PER_MINUTE;
    }

    /**
     * @return array<string, mixed>
     */
    private function record(Request $request, Response $response): array
    {
        $status = $response->getStatusCode();
        $route = $request->route()?->getName();
        $user = $this->user($request);
        $account = $request->attributes->get('account');
        $bytes = $this->requestBytes($request);
        $token = $user?->currentAccessToken();
        $email = $route === 'api.login' && is_string($request->input('email')) ? $request->input('email') : null;
        $emailOwner = $email !== null ? User::query()->where('email', $email)->first() : null;

        // A login has no authenticated user yet; attribute it to the address's
        // owner whatever the outcome, so a suspended user's refused login and
        // attempts against an account show on that user's traffic page.
        $user ??= $emailOwner;

        return [
            'user_id' => $user?->id,
            'account_id' => $account instanceof Account ? $account->id : null,
            'token_id' => $token instanceof PersonalAccessToken ? $token->id : null,
            'account_hash' => $this->text($request->header('X-Account-Hash')),
            'username' => $this->text($request->header('X-Account-Username')),
            'account_type' => $this->text($request->header('X-Account-Type')),
            // Typed text is kept only when it is a real user's address: people
            // paste passwords into the email field. The hash still counts
            // distinct addresses per IP.
            'login_email' => $emailOwner?->email,
            'login_email_hash' => $email !== null ? hash('sha256', mb_strtolower(trim(mb_scrub($email, 'UTF-8')))) : null,
            'route' => $route,
            'method' => $request->method(),
            'path' => $this->text('/'.ltrim($request->path(), '/')),
            'status' => $status,
            'duration_ms' => (float) $request->attributes->get(self::DURATION_ATTRIBUTE, 0),
            'request_bytes' => $bytes,
            'ip' => $this->text($request->ip()),
            'user_agent' => $this->text($request->userAgent()),
            'flags' => $this->flags($request, $response, $route, $bytes),
        ];
    }

    /**
     * The authenticated user, if the auth middleware resolved one. Read from
     * the sanctum guard directly: /api/login never runs it, and the default
     * web guard would reach for a session the API group does not start.
     */
    private function user(Request $request): ?User
    {
        $user = $request->user('sanctum');

        return $user instanceof User ? $user : null;
    }

    /**
     * @return array<int, string>
     */
    private function flags(Request $request, Response $response, ?string $route, int $bytes): array
    {
        $flags = $request->attributes->get(self::FLAGS_ATTRIBUTE, []);
        $status = $response->getStatusCode();

        if ($status === 403 && $this->isSuspensionResponse($response)) {
            $flags[] = self::FLAG_SUSPENDED;
        }

        $flags[] = match (true) {
            $status === 401 => self::FLAG_UNAUTHENTICATED,
            $status === 403 && $flags === [] => self::FLAG_FORBIDDEN,
            $status === 422 && ! in_array(self::FLAG_MISSING_HEADERS, $flags, true) => self::FLAG_VALIDATION_FAILED,
            $status === 429 => self::FLAG_THROTTLED,
            $status >= 500 => self::FLAG_SERVER_ERROR,
            default => null,
        };

        if ($bytes > (self::MAX_BYTES[$route] ?? self::DEFAULT_MAX_BYTES)) {
            $flags[] = self::FLAG_OVERSIZED;
        }

        return array_values(array_unique(array_filter($flags)));
    }

    /**
     * EnsureUserIsNotSuspended (and the API login refusing a suspended user)
     * answer with its fixed message, so the body tells this 403 apart.
     */
    private function isSuspensionResponse(Response $response): bool
    {
        $body = json_decode((string) $response->getContent(), true);

        return is_array($body) && ($body['message'] ?? null) === EnsureUserIsNotSuspended::MESSAGE;
    }

    /**
     * Body size from the declared Content-Length (which covers a multipart
     * upload too), or the uploaded files when it is missing. The body itself
     * is never read, so a huge one costs no memory here.
     */
    private function requestBytes(Request $request): int
    {
        $declared = $request->header('Content-Length');
        $files = array_sum(array_map(
            fn ($file): int => (int) $file->getSize(),
            Arr::flatten($request->allFiles()),
        ));

        return max(is_numeric($declared) ? (int) $declared : 0, $files);
    }

    /**
     * A client-supplied string made safe to store: invalid UTF-8 would make
     * the Mongo insert throw and let the request go unlogged.
     */
    private function text(?string $value): ?string
    {
        return $value === null || $value === '' ? null : mb_substr(mb_scrub($value, 'UTF-8'), 0, 255);
    }
}
