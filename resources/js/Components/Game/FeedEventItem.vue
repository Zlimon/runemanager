<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import FeedEventCard from "@/Components/Game/FeedEventCard.vue";

/*
 * One row of the live feed. It is drawn by FeedEventCard, the same card the
 * achievement showcase uses, so an event looks the same in the feed as it does
 * once pinned. What this adds is what only a feed needs: whose event it is, a
 * relative timestamp, where it happened, and the pin and delete controls.
 * Shared by the Live Feed page, the homepage activity widget and the account's
 * own feed.
 */
const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['deleted']);

const page = usePage();

const isOwner = computed(() => page.props.auth?.user?.id != null
    && page.props.auth.user.id === props.event.account?.user_id);

// Instance admins, or the owner of the account the entry belongs to, may delete.
const canDelete = computed(() => page.props.is_admin || isOwner.value);

// Local pin state so the icon flips instantly; the redirect-back refreshes the
// account's pinned gallery prop.
const pinned = ref(props.event.pinned ?? false);

const remove = () => {
    if (!confirm('Delete this feed entry?')) {
        return;
    }
    router.delete(route('feed.destroy', props.event.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => emit('deleted', props.event.id),
    });
};

const togglePin = () => {
    router.put(route('feed.pin', props.event.id), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => { pinned.value = !pinned.value; },
    });
};

// Where the event happened (plugin-supplied). Links to the live map centred
// on the spot; the area name falls back to raw coordinates for unmapped regions.
const position = computed(() => props.event.payload?.position ?? null);
const positionLabel = computed(() => position.value
    ? (position.value.location ?? `${position.value.x}, ${position.value.y}`)
    : null);
</script>

<template>
    <FeedEventCard :event="event" relative-date>
        <template #header>
            <Link :href="route('accounts.show', { account: event.account.username })"
                  class="flex w-fit items-center gap-1.5 text-sm font-semibold hover:underline">
                <img v-if="event.account.account_type === 'ironman'"
                     src="/images/ironman.png"
                     class="h-4 w-4 object-contain"
                     alt="">
                <img v-else-if="event.account.account_type && event.account.account_type !== 'normal'"
                     :src="`/images/${event.account.account_type}_ironman.png`"
                     class="h-4 w-4 object-contain"
                     alt="">
                {{ event.account.username }}
            </Link>
        </template>

        <template v-if="position" #meta>
            <Link :href="route('map.index', { x: position.x, y: position.y })"
                  class="ms-1 inline-flex items-center gap-0.5 align-baseline text-xs font-normal text-base-content/50 hover:text-base-content hover:underline"
                  :title="`${position.x}, ${position.y} (plane ${position.plane})`">
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 21s-7-5.5-7-11a7 7 0 1 1 14 0c0 5.5-7 11-7 11Z" />
                    <circle cx="12" cy="10" r="2.5" />
                </svg>
                {{ positionLabel }}
            </Link>
        </template>

        <template #actions>
            <button v-if="isOwner" type="button" @click="togglePin"
                    :class="pinned ? 'text-accent' : 'text-base-content/40 hover:text-accent'"
                    :aria-label="pinned ? 'Unpin from gallery' : 'Pin to gallery'"
                    :title="pinned ? 'Unpin from gallery' : 'Pin to gallery'">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" :fill="pinned ? 'currentColor' : 'none'"
                     stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 4h6l-1 6 3 3v2H7v-2l3-3-1-6Zm3 11v5" />
                </svg>
            </button>
            <button v-if="canDelete" type="button" @click="remove" aria-label="Delete entry"
                    class="text-base-content/40 hover:text-error" title="Delete entry">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6 7h12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-7 0v12a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V7" />
                </svg>
            </button>
        </template>
    </FeedEventCard>
</template>
