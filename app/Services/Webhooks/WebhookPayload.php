<?php

namespace App\Services\Webhooks;

use App\Models\Announcement;
use App\Models\CalendarEvent;
use Illuminate\Support\Str;

/**
 * Builds Discord-compatible webhook bodies (a single rich embed) for the two
 * instance notifications we forward: new announcements and new calendar events.
 * Generic receivers can parse the same JSON.
 */
class WebhookPayload
{
    private const COLOR_ANNOUNCEMENT = 0xEF9995;

    private const COLOR_CALENDAR = 0xA4CBB4;

    /**
     * @return array<string, mixed>
     */
    public static function forAnnouncement(Announcement $announcement): array
    {
        return self::embed(
            '📢 '.$announcement->title,
            Str::limit((string) $announcement->body, 1800),
            self::COLOR_ANNOUNCEMENT,
            route('announcements.index'),
            $announcement->created_at?->toIso8601String(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function forCalendarEvent(CalendarEvent $event): array
    {
        $when = $event->starts_at?->format('D, M j Y · H:i');
        $description = trim(($when ? "🗓️ {$when}\n\n" : '').Str::limit((string) $event->description, 1600));

        return self::embed(
            $event->title,
            $description !== '' ? $description : 'A new event has been scheduled.',
            self::COLOR_CALENDAR,
            route('calendar.index'),
            $event->starts_at?->toIso8601String(),
            $event->event_type->label(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function ping(string $context): array
    {
        return self::embed(
            config('app.name'),
            "Test message — your {$context} webhook is connected. 🎉",
            0x4CAF50,
            null,
            now()->toIso8601String(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function embed(string $title, string $description, int $color, ?string $url, ?string $timestamp, ?string $footer = null): array
    {
        $embed = array_filter([
            'title' => Str::limit($title, 250),
            'description' => $description,
            'color' => $color,
            'url' => $url,
            'timestamp' => $timestamp,
            'footer' => $footer ? ['text' => $footer] : null,
        ], fn ($value) => $value !== null);

        return [
            'username' => config('app.name'),
            'embeds' => [$embed],
        ];
    }
}
