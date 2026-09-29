<script setup>
import dayjs from "dayjs";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";
import Card from "@/Components/Cards/Card.vue";

/*
 * Personal bests from Wise Old Man: the most gained of one metric in a single
 * day, week, month and year, with the date each was set.
 */
defineProps({
    // WomAccountView::records()
    records: { type: Array, default: () => [] },
});

const { packIcon } = useResourcePackIcon();
const iconAttrs = (icon) => (icon?.kind ? packIcon(icon.kind, icon.slug) : icon);

const PERIODS = ['day', 'week', 'month', 'year'];
</script>

<template>
    <Card padding="p-4">
        <h3 class="mb-2">Personal records</h3>

        <div v-if="records.length" class="max-h-96 overflow-auto">
            <table class="table table-sm">
                <thead>
                    <tr class="text-base-content/70">
                        <th>Metric</th>
                        <th v-for="period in PERIODS" :key="period" class="text-right capitalize">{{ period }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="record in records" :key="record.metric">
                        <td>
                            <span class="flex items-center gap-2">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center">
                                    <img v-if="record.icon" v-bind="iconAttrs(record.icon)" class="max-h-5 max-w-5 object-contain" alt="">
                                </span>
                                {{ record.label }}
                            </span>
                        </td>
                        <td v-for="period in PERIODS" :key="period" class="text-right">
                            <span v-if="record[period]" :title="dayjs(record[period].achieved_at).format('MMM D, YYYY')">
                                {{ record[period].value.toLocaleString('en-US') }}
                            </span>
                            <span v-else class="text-base-content/40">—</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="py-6 text-center text-sm text-base-content/60">No records yet.</p>
    </Card>
</template>
