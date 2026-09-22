<?php

namespace App\Services\Instance;

use App\Models\Account;

/**
 * Headline counts for the instance, shown on the dashboard and the public
 * landing page.
 */
class InstanceStats
{
    /**
     * @return array{accounts: int, members: int, online: int}
     */
    public function counts(): array
    {
        return [
            'accounts' => Account::count(),
            'members' => Account::query()->whereNotNull('user_id')->distinct()->count('user_id'),
            'online' => Account::query()->online()->count(),
        ];
    }
}
