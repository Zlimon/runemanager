<?php

use App\Helpers\SettingHelper;
use App\Jobs\DeliverWebhook;
use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Services\Webhooks\WebhookPayload;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

const HOOK = 'https://discord.com/api/webhooks/123/abc';

it('forwards a new announcement to the configured webhook', function () {
    Bus::fake([DeliverWebhook::class]);
    SettingHelper::setSetting('webhook_url', HOOK);

    $this->actingAs(adminUser())
        ->post(route('announcements.store'), ['title' => 'Big news', 'body' => 'Something happened'])
        ->assertRedirect();

    Bus::assertDispatched(fn (DeliverWebhook $job) => $job->url === HOOK && $job->context === 'announcement');
});

it('does not forward an announcement when no webhook is configured', function () {
    Bus::fake([DeliverWebhook::class]);

    $this->actingAs(adminUser())
        ->post(route('announcements.store'), ['title' => 'Quiet news', 'body' => 'No webhook'])
        ->assertRedirect();

    Bus::assertNotDispatched(DeliverWebhook::class);
});

it('forwards a new calendar event to the configured webhook', function () {
    Bus::fake([DeliverWebhook::class]);
    SettingHelper::setSetting('webhook_url', HOOK);

    $this->actingAs(adminUser())
        ->post(route('calendar.store'), [
            'title' => 'Raid night',
            'event_type' => 'pvm_mass',
            'starts_at' => now()->addDay()->toDateTimeString(),
        ])
        ->assertRedirect();

    Bus::assertDispatched(fn (DeliverWebhook $job) => $job->url === HOOK && $job->context === 'calendar');
});

it('saves the webhook URL from the admin settings form', function () {
    $this->actingAs(adminUser())
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_CASUAL,
            'webhook_url' => HOOK,
        ])
        ->assertRedirect();

    expect(Instance::webhookUrl())->toBe(HOOK);
});

it('rejects an invalid webhook URL', function () {
    $this->actingAs(adminUser())
        ->putJson(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_CASUAL,
            'webhook_url' => 'not-a-url',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('webhook_url');
});

it('builds Discord embeds for announcements and calendar events', function () {
    $announcement = Announcement::factory()->create(['title' => 'Server up', 'body' => 'Back online']);
    $payload = WebhookPayload::forAnnouncement($announcement);
    expect($payload['embeds'][0]['title'])->toContain('Server up')
        ->and($payload['embeds'][0]['description'])->toContain('Back online');

    $event = CalendarEvent::factory()->create(['title' => 'Raid night']);
    $calendarPayload = WebhookPayload::forCalendarEvent($event);
    expect($calendarPayload['embeds'][0]['title'])->toBe('Raid night');
});
