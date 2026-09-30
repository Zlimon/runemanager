<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_expired' => $this->expires_at !== null && $this->expires_at->isPast(),
            'created_at' => $this->created_at?->toIso8601String(),
            'created_by' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            // Only present when the caller eager-loaded the count (the admin
            // listing does, via `withCount('acknowledgedBy')`); the public
            // announcements page never sees this key.
            'acknowledged_count' => $this->when(
                $this->acknowledged_by_count !== null,
                fn (): int => $this->acknowledged_by_count,
            ),
        ];
    }
}
