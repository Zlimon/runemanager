<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use MongoDB\Laravel\Eloquent\Model;

/**
 * Metadata for one request against the plugin API (`routes/api.php`), written
 * by the LogPluginRequest middleware once the response has been sent. Only
 * metadata is kept, never a request body: who (user, token, the account
 * headers as sent), what (route, status, size, duration) and the abuse
 * `flags` the request raised. A `/api/login` attempt keeps `login_email`
 * only when the address belongs to a user (people paste passwords into the
 * email field), and always a sha256 `login_email_hash`, so credential
 * stuffing still shows as many distinct addresses; the password is never read.
 * Pruned after `PrunePluginRequests::RETENTION_DAYS` by `plugin-traffic:prune`.
 */
#[Connection('mongodb')]
#[Table('plugin_requests', key: '_id')]
#[Fillable([
    'user_id', 'account_id', 'token_id', 'account_hash', 'username', 'account_type', 'login_email', 'login_email_hash',
    'route', 'method', 'path', 'status', 'duration_ms', 'request_bytes', 'ip', 'user_agent', 'flags',
])]
class PluginRequest extends Model
{
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'int',
            'account_id' => 'int',
            'token_id' => 'int',
            'status' => 'int',
            'duration_ms' => 'float',
            'request_bytes' => 'int',
        ];
    }
}
