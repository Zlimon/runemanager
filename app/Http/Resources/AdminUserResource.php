<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * A row of the admin user list. A resource rather than an inline shape because
 * the list is paginated, and the page's DataTable reads the collection's
 * `meta.links`. Callers load `roles`, `withCount(['account', 'tokens'])` and
 * `withMax('tokens', 'last_used_at')`.
 */
class AdminUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lastTokenUse = $this->tokens_max_last_used_at;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'verified' => $this->email_verified_at !== null,
            'roles' => $this->roles->pluck('name')->all(),
            'accounts_count' => (int) $this->account_count,
            'tokens_count' => (int) $this->tokens_count,
            'last_token_used_at' => $lastTokenUse ? Carbon::parse($lastTokenUse)->toIso8601String() : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'suspended' => $this->isSuspended(),
        ];
    }
}
