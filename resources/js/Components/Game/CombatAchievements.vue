<script setup>
import { computed } from "vue";
import Freshness from "@/Components/Freshness.vue";

/*
 * Combat Achievements summary: total points + completed tasks per tier, shown as
 * "27 / 41" with red/yellow/green colouring (0 / partial / complete) to mirror
 * the in-game overview. Driven by the {points, tiers} snapshot pushed by the
 * plugin; renders the six tiers in ascending order regardless of key order.
 */
const props = defineProps({
    combatAchievements: {
        type: Object,
        default: null,
    },
    freshness: {
        type: String,
        default: null,
    },
    staleAfter: {
        type: Number,
        default: 60,
    },
});

// Total tasks per tier (kept in sync with the backend CombatAchievements helper).
const TIERS = [
    { key: 'easy', label: 'Easy', total: 41 },
    { key: 'medium', label: 'Medium', total: 60 },
    { key: 'hard', label: 'Hard', total: 85 },
    { key: 'elite', label: 'Elite', total: 162 },
    { key: 'master', label: 'Master', total: 168 },
    { key: 'grandmaster', label: 'Grandmaster', total: 121 },
];

const points = computed(() => props.combatAchievements?.points ?? 0);
const tiers = computed(() => props.combatAchievements?.tiers ?? {});
const hasData = computed(() => props.combatAchievements !== null);

const tierRows = computed(() => TIERS.map((tier) => {
    const done = Math.max(0, tiers.value[tier.key] ?? 0);
    // Guard against the denominator lagging after a Jagex CA batch.
    const total = Math.max(tier.total, done);
    const color = done === 0 ? 'text-error' : (done >= total ? 'text-success' : 'text-warning');
    return { ...tier, done, total, color };
}));
</script>

<template>
    <div v-if="hasData">
        <div class="mb-2 flex items-baseline justify-between">
            <span class="text-xs text-base-content/60">{{ points.toLocaleString() }} points</span>
            <Freshness :updated-at="freshness" :stale-after-minutes="staleAfter" />
        </div>
        <ul class="divide-y divide-base-content/10">
            <li v-for="tier in tierRows" :key="tier.key"
                class="flex items-center justify-between py-2">
                <span class="font-medium">{{ tier.label }}</span>
                <span class="font-mono text-sm" :class="tier.color">{{ tier.done }} / {{ tier.total }}</span>
            </li>
        </ul>
    </div>
    <div v-else class="flex h-40 items-center justify-center text-center text-sm text-base-content/60">
        No combat achievement data yet.
    </div>
</template>
