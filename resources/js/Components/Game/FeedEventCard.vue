<script setup>
import { computed } from "vue";
import dayjs from "dayjs";
import Card from "@/Components/Card.vue";
import ImageLightbox from "@/Components/ImageLightbox.vue";
import LootItems from "@/Components/Game/LootItems.vue";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";
import { feedIcon, feedSentence } from "@/feed";

/*
 * One feed event, drawn the way the achievement showcase draws it: the event's
 * screenshot on top, then its type icon beside the sentence and date, then the
 * dropped items for a loot drop. Shared by the showcase and every feed list, so
 * an event looks the same wherever it appears.
 *
 * The slots carry what only one context needs. A feed row has to say whose
 * event it is and offer pin/delete; the showcase is already on the account's
 * page and only offers unpin.
 *
 *   #header  above the sentence — the feed's account link
 *   #meta    after the sentence — the feed's link to where it happened
 *   #actions the controls, top right of the caption
 */
const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
    /*
     * Fill the image area with a trophy when the event has no screenshot. The
     * showcase wants it — it is a gallery, and an empty tile reads as broken.
     * A feed does not: most events carry no screenshot, and a trophy on every
     * level-up would bury the feed in identical banners.
     */
    placeholder: {
        type: Boolean,
        default: false,
    },
    // "3 hours ago" for a live feed; a fixed date for a curated showcase.
    relativeDate: {
        type: Boolean,
        default: false,
    },
});

const { packIcon } = useResourcePackIcon();

const occurredAt = computed(() => dayjs(props.event.occurred_at));

const hasMedia = computed(() => Boolean(props.event.screenshot_url) || props.placeholder);

const isLoot = computed(() => props.event.type === 'loot_drop' && props.event.payload?.items?.length);
</script>

<template>
    <Card padding="p-0" frame="dialog" class="group relative overflow-hidden">
        <div v-if="hasMedia" class="p-2">
            <ImageLightbox v-if="event.screenshot_url" :src="event.screenshot_url" alt="Event screenshot"
                           thumb-class="h-28 w-full object-cover" class="!w-full" />
            <div v-else class="flex h-28 w-full items-center justify-center bg-base-300/40">
                <img src="/images/trophy.png" alt="" class="h-14 w-14 object-contain [image-rendering:pixelated]">
            </div>
        </div>

        <div class="flex items-start gap-3 px-4" :class="hasMedia ? 'pb-4' : 'py-4'">
            <img v-bind="feedIcon(event, packIcon)" alt=""
                 class="h-8 w-8 shrink-0 object-contain [image-rendering:pixelated]">

            <div class="min-w-0 flex-1">
                <slot name="header" />
                <p class="text-sm font-medium leading-snug text-base-content/90">
                    <span class="capitalize">{{ feedSentence(event) }}</span>
                    <slot name="meta" />
                </p>
                <p class="mt-0.5 text-xs text-base-content/50"
                   :title="occurredAt.format('MMM D, YYYY h:mm A')">
                    {{ relativeDate ? occurredAt.fromNow() : occurredAt.format('MMM D, YYYY') }}
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <slot name="actions" />
            </div>
        </div>

        <LootItems v-if="isLoot"
                   :items="event.payload.items"
                   :total-value="event.payload.total_value"
                   class="px-4 pb-4" />
    </Card>
</template>
