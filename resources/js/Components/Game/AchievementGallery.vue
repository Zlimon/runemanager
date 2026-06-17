<script setup>
import { computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import Card from "@/Components/Card.vue";
import ImageLightbox from "@/Components/ImageLightbox.vue";
import { feedSentence } from "@/feed";

/*
 * The account's curated achievement gallery — the feed events its owner has
 * pinned, shown as a grid that features each event's screenshot. The owner can
 * unpin straight from a card; pinning happens from the feed.
 */
const props = defineProps({
    account: { type: Object, required: true },
    events: { type: Array, default: () => [] },
});

const isOwner = computed(() => usePage().props.auth?.user?.id != null
    && usePage().props.auth.user.id === props.account.user_id);

// Shown if there's anything to show, or to the owner so they see how to fill it.
const visible = computed(() => props.events.length > 0 || isOwner.value);

const unpin = (event) => {
    router.put(route('feed.pin', event.id), {}, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <div v-if="visible">
        <h3>Achievement Showcase</h3>

        <Card v-if="!events.length" padding="p-6" class="mt-2 text-center text-sm text-base-content/60">
            Pin events from the feed below to showcase them here.
        </Card>

        <div v-else class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <Card v-for="event in events" :key="event.id" padding="p-0" class="group relative overflow-hidden">
                <ImageLightbox v-if="event.screenshot_url" :src="event.screenshot_url" alt="Achievement"
                               thumb-class="h-28 w-full object-cover" class="!w-full" />
                <div v-else class="flex h-28 w-full items-center justify-center bg-base-300/40 text-3xl">🏆</div>

                <div class="p-2">
                    <p class="text-xs font-medium leading-snug text-base-content/90">{{ feedSentence(event) }}</p>
                    <p class="mt-0.5 text-[10px] text-base-content/50">{{ dayjs(event.occurred_at).format('MMM D, YYYY') }}</p>
                </div>

                <button v-if="isOwner" type="button" @click="unpin(event)" title="Unpin"
                        class="absolute right-1 top-1 hidden h-6 w-6 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 group-hover:flex">
                    &times;
                </button>
            </Card>
        </div>
    </div>
</template>
