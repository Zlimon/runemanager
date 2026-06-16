<script setup>
import { Link } from "@inertiajs/vue3";
import dayjs from "dayjs";
import LootItems from "@/Components/Game/LootItems.vue";

/*
 * One row of the live feed — the account (with ironman badge), a relative
 * timestamp, the event sentence and, for loot drops, the dropped items.
 * Shared by the Live Feed page and the homepage activity widget.
 */
defineProps({
    event: {
        type: Object,
        required: true,
    },
});

const formatSkill = (slug) => slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const sentence = (event) => {
    const { type, payload } = event;
    switch (type) {
        case 'level_up':
            return `reached ${payload.milestone} ${formatSkill(payload.skill)} (now level ${payload.level})`;
        case 'loot_drop':
            return `received a drop from ${payload.source}`;
        case 'quest_complete':
            return `completed ${payload.quest}`;
        case 'combat_achievement':
            return payload.tier
                ? `completed the ${formatSkill(payload.tier)} combat task: ${payload.task}`
                : `completed the combat task: ${payload.task}`;
        default:
            return type;
    }
};
</script>

<template>
    <div class="rounded p-3 pack-bg-card resource-pack-border">
        <div class="flex items-baseline justify-between">
            <Link :href="route('accounts.show', { account: event.account.username })"
                  class="flex items-center gap-2 font-semibold hover:underline">
                <img v-if="event.account.account_type === 'ironman'"
                     src="/images/ironman.png"
                     class="h-5 w-5 object-contain"
                     alt="">
                <img v-else-if="event.account.account_type && event.account.account_type !== 'normal'"
                     :src="`/images/${event.account.account_type}_ironman.png`"
                     class="h-5 w-5 object-contain"
                     alt="">
                {{ event.account.username }}
            </Link>
            <span class="text-xs text-base-content/60"
                  :title="dayjs(event.occurred_at).format('MMM D, YYYY h:mm A')">
                {{ dayjs(event.occurred_at).fromNow() }}
            </span>
        </div>
        <div class="mt-1 text-sm text-base-content/80">
            <p>{{ sentence(event) }}</p>
            <LootItems v-if="event.type === 'loot_drop'"
                       :items="event.payload.items"
                       :total-value="event.payload.total_value"
                       class="mt-1.5" />
        </div>
    </div>
</template>
