<script setup>
import { computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Card from "@/Components/Cards/Card.vue";
import FeedEventCard from "@/Components/Game/Feed/FeedEventCard.vue";

/*
 * The account's curated achievement gallery — the feed events its owner has
 * pinned, shown as a grid that features each event's screenshot. The owner can
 * unpin straight from a card; pinning happens from Recent Events.
 */
const props = defineProps({
    account: { type: Object, required: true },
    events: { type: Array, default: () => [] },
});

const isOwner = computed(() => usePage().props.auth?.user?.id != null
    && usePage().props.auth.user.id === props.account.user_id);

// Shown if there's anything to show, or to the owner so they see how to fill it.
const visible = computed(() => props.events.length > 0 || isOwner.value);

const emit = defineEmits(['unpinned']);

// Only the gallery reloads; the page flips the Recent Events row itself so
// older events loaded there are kept.
const unpin = (event) => {
    router.put(route('feed.pin', event.id), {}, {
        only: ['pinnedFeed'],
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => emit('unpinned', event.id),
    });
};
</script>

<template>
    <div v-if="visible">
        <Card v-if="!events.length" padding="p-6" class="mt-2 text-center text-sm text-base-content/60">
            Pin events from Recent Events to showcase them here.
        </Card>

        <div class="flex flex-wrap gap-2">
            <FeedEventCard v-for="event in events" :key="event.id" :event="event" placeholder class="w-sm">
                <template v-if="isOwner" #actions>
                    <button type="button" @click="unpin(event)" title="Unpin" aria-label="Unpin"
                            class="hidden h-6 w-6 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 group-hover:flex">
                        &times;
                    </button>
                </template>
            </FeedEventCard>
        </div>
    </div>
</template>
