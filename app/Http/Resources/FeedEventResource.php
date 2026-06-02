<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Plain wire shape for SPEC §8 feed events. Account is included slim — the
 * UI links back to the account page, so we just need username + type for
 * the ironman badge.
 */
class FeedEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'payload' => $this->payload,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'account' => [
                'username' => $this->account?->username,
                'account_type' => $this->account?->account_type?->value
                    ?? $this->account?->account_type,
            ],
        ];
    }
}
