<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";

/*
 * One Recent Events line: collapsed it reads as a headline with its date,
 * expanded it adds the first-person sentence. A feed milestone owned by the
 * viewer can be pinned to the achievement gallery from here; the list owns the
 * pinned flag, so the row only reports the change.
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
        <button type="button" class="flex w-full items-center gap-3 px-2 py-2 text-left hover:bg-base-300/40"
                :aria-expanded="open" :aria-controls="bodyId" @click="open = !open">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center">
                <img v-bind="icon" class="max-h-6 max-w-6 object-contain" alt="">
            </span>
            <span class="min-w-0 flex-1 truncate text-sm font-semibold text-base-content">{{ event.title }}</span>
            <span class="shrink-0 text-xs text-base-content/60" :title="date.format('MMM D, YYYY h:mm A')">
                {{ event.approximate ? '~' : '' }}{{ date.format('D-MMM-YYYY') }}
            </span>
            <svg class="h-3 w-3 shrink-0 text-base-content/50 transition-transform" :class="{ 'rotate-180': open }"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
            </svg>
        </button>

        <div v-show="open" :id="bodyId" class="flex items-start gap-3 px-2 pb-3 pl-11 text-sm text-base-content/80">
            <div class="flex-1">
                <p>{{ event.body }}</p>
                <div v-if="event.screenshot_url || mapHref" class="mt-1 flex gap-3 text-xs text-base-content/60">
                    <a v-if="event.screenshot_url" :href="event.screenshot_url" target="_blank" rel="noopener"
                       class="link link-hover">
                        Screenshot<span v-if="event.screenshot_hidden"
                                        class="ms-1 text-base-content/50"
                                        title="Only you and admins see this screenshot">(hidden)</span>
                    </a>
                    <Link v-if="mapHref" :href="mapHref" class="link link-hover"
                          :title="event.position.location || undefined">
                        Show on map
                    </Link>
                </div>
            </div>
            <button v-if="canPin" type="button" @click="togglePin"
                    class="shrink-0 text-xs"
                    :class="event.pinned ? 'text-accent' : 'text-base-content/50 hover:text-accent'"
                    :title="event.pinned ? 'Unpin from gallery' : 'Pin to gallery'">
                {{ event.pinned ? 'Pinned' : 'Pin to gallery' }}
            </button>
        </div>
    </li>
</template>
