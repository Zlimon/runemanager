<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import Badge from "@/Components/Display/Badge.vue";
import ImageLightbox from "@/Components/Modals/ImageLightbox.vue";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";

/*
 * One Recent Events line: collapsed it reads as a headline with its date, a
 * camera when the event has a screenshot the viewer may see, and, for the
 * owner of a feed milestone, a star that pins it to the achievement gallery.
 * Expanded it adds the first-person sentence and the screenshot itself. The
 * list owns the pinned flag, so the row only reports the change.
 */
const props = defineProps({
    event: { type: Object, required: true },
    account: { type: Object, required: true },
});

const emit = defineEmits(['pin-changed']);

const page = usePage();
const { packIcon } = useResourcePackIcon();

const open = ref(false);

const isOwner = computed(() => page.props.auth?.user?.id != null
    && page.props.auth.user.id === props.account.user_id);

const canPin = computed(() => isOwner.value && props.event.source === 'feed' && props.event.feed_event_id);

// No picture (pets, unknown items) falls back to the trophy, as on the feed.
const TROPHY = { src: '/images/trophy.png' };
const icon = computed(() => {
    const icon = props.event.icon;
    if (!icon) {
        return TROPHY;
    }
    return icon.kind ? packIcon(icon.kind, icon.slug) : { src: icon.src };
});

// Ids like 'feed:123' are not valid in selectors, so swap the colon.
const bodyId = computed(() => `recent-event-${props.event.id.replace(':', '-')}`);

const date = computed(() => dayjs(props.event.occurred_at));

// Feed events carry where they happened; open the map there with this
// account's events plotted.
const mapHref = computed(() => {
    const position = props.event.position;
    return position
        ? route('map.index', {
            x: position.x,
            y: position.y,
            ...(position.plane > 0 ? { plane: position.plane } : {}),
            events: props.account.id,
        })
        : null;
});

const togglePin = () => {
    router.put(route('feed.pin', props.event.feed_event_id), {}, {
        only: ['pinnedFeed'],
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => emit('pin-changed', props.event.feed_event_id, !props.event.pinned),
    });
};
</script>

<template>
    <li class="border-b border-base-300 last:border-b-0">
        <div class="flex items-center hover:bg-base-300/40">
            <button type="button" class="flex min-w-0 flex-1 items-center gap-3 px-2 py-2 text-left"
                    :aria-expanded="open" :aria-controls="bodyId" @click="open = !open">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center">
                    <img v-bind="icon" class="max-h-6 max-w-6 object-contain" alt="">
                </span>
                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-base-content">{{ event.title }}</span>
                <svg v-if="event.screenshot_url" class="h-4 w-4 shrink-0 text-base-content/50"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     role="img" :aria-label="event.screenshot_hidden ? 'Has a hidden screenshot' : 'Has a screenshot'">
                    <title>{{ event.screenshot_hidden ? 'Screenshot (hidden from others)' : 'Screenshot' }}</title>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 8.5A1.5 1.5 0 0 1 4.5 7h2.3l1.4-2h7.6l1.4 2h2.3A1.5 1.5 0 0 1 21 8.5v10a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.5z" />
                    <circle cx="12" cy="13" r="3.5" />
                </svg>
                <span class="shrink-0 text-xs text-base-content/60" :title="date.format('MMM D, YYYY h:mm A')">
                    {{ event.approximate ? '~' : '' }}{{ date.format('D-MMM-YYYY') }}
                </span>
                <svg class="h-3 w-3 shrink-0 text-base-content/50 transition-transform" :class="{ 'rotate-180': open }"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                </svg>
            </button>
            <button v-if="canPin" type="button" @click="togglePin"
                    class="me-1 shrink-0 rounded p-1.5"
                    :class="event.pinned ? 'text-accent' : 'text-base-content/40 hover:text-accent'"
                    :aria-pressed="event.pinned"
                    :aria-label="event.pinned ? 'Unpin from gallery' : 'Pin to gallery'"
                    :title="event.pinned ? 'Unpin from gallery' : 'Pin to gallery'">
                <svg class="h-4 w-4" viewBox="0 0 24 24" :fill="event.pinned ? 'currentColor' : 'none'"
                     stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linejoin="round"
                          d="m12 3 2.7 5.6 6.1.8-4.5 4.2 1.1 6.1L12 16.8l-5.4 2.9 1.1-6.1-4.5-4.2 6.1-.8z" />
                </svg>
            </button>
        </div>

        <div v-show="open" :id="bodyId" class="px-2 pb-3 pl-11 text-sm text-base-content/80">
            <p>{{ event.body }}</p>
            <div v-if="event.screenshot_url" class="relative mt-2 w-fit">
                <ImageLightbox :src="event.screenshot_url" :alt="`Screenshot: ${event.title}`"
                               thumb-class="h-24 w-auto max-w-full rounded object-cover" />
                <Badge v-if="event.screenshot_hidden" size="sm" class="pointer-events-none absolute left-1.5 top-1.5"
                       title="Only you and admins see this screenshot">
                    Hidden
                </Badge>
            </div>
            <div v-if="mapHref" class="mt-1 text-xs text-base-content/60">
                <Link :href="mapHref" class="link link-hover" :title="event.position.location || undefined">
                    Show on map
                </Link>
            </div>
        </div>
    </li>
</template>
