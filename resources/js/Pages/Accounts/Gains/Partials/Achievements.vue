<script setup>
import dayjs from "dayjs";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";
import Card from "@/Components/Cards/Card.vue";
import Badge from "@/Components/Display/Badge.vue";

/*
 * Wise Old Man achievements, newest first. Undated ones (WOM could not tell
 * when) come last; `approximate` dates are only known to within days.
 */
defineProps({
    // WomAccountView::achievements()
    achievements: { type: Array, default: () => [] },
});

const { packIcon } = useResourcePackIcon();
const iconAttrs = (icon) => (icon?.kind ? packIcon(icon.kind, icon.slug) : icon);
</script>

<template>
    <Card padding="p-4">
        <h3 class="mb-2">Achievements</h3>

        <ul v-if="achievements.length" class="max-h-96 divide-y divide-base-300 overflow-auto">
            <li v-for="achievement in achievements" :key="achievement.name"
                class="flex items-center gap-3 py-1.5 text-sm">
                <span class="flex h-5 w-5 shrink-0 items-center justify-center">
                    <img v-if="achievement.icon" v-bind="iconAttrs(achievement.icon)" class="max-h-5 max-w-5 object-contain" alt="">
                </span>
                <span class="min-w-0 flex-1 truncate" :class="{ 'text-base-content/60': achievement.legacy }">
                    {{ achievement.name }}
                </span>
                <Badge v-if="achievement.legacy" size="xs" title="From an older version of the hiscores">legacy</Badge>
                <span class="shrink-0 text-xs text-base-content/60">
                    <template v-if="achievement.achieved_at">
                        {{ achievement.approximate ? '~' : '' }}{{ dayjs(achievement.achieved_at).format('MMM D, YYYY') }}
                    </template>
                    <template v-else>Date unknown</template>
                </span>
            </li>
        </ul>
        <p v-else class="py-6 text-center text-sm text-base-content/60">No achievements yet.</p>
    </Card>
</template>
