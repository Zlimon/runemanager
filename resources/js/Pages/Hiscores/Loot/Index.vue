<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from "@/Components/Card.vue";
import ClickableCard from "@/Components/ClickableCard.vue";

defineProps({
    groups: {
        type: Array,
        default: () => [],
    },
});

const formatValue = (gp) => {
    if (!gp) {
        return '0';
    }
    if (gp >= 1_000_000) {
        return `${(gp / 1_000_000).toFixed(1)}M`;
    }
    if (gp >= 1_000) {
        return `${(gp / 1_000).toFixed(1)}K`;
    }
    return gp.toLocaleString('en-US');
};
</script>

<template>
    <AppLayout title="Loot">
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-base-content">Loot</h1>

                <Card v-if="groups.length === 0" padding="p-8" class="text-center text-base-content/60">
                    No loot has been tracked yet.
                </Card>

                <section v-for="group in groups" :key="group.label" class="space-y-3">
                    <h2 class="text-lg font-semibold text-base-content">{{ group.label }}</h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        <ClickableCard v-for="source in group.sources" :key="`${source.type}:${source.name}`"
                                       :href="route('hiscores.loot.show', [source.type, source.name])">
                            <!-- The layout lives on a child: ClickableCard sets its own
                                 display, and two display utilities on one element would
                                 be decided by stylesheet order rather than intent. -->
                            <div class="flex flex-col gap-1">
                                <span class="font-semibold text-base-content">{{ source.name }}</span>
                                <span class="text-sm text-base-content/70">{{ formatValue(source.total_value) }} gp</span>
                                <span class="text-xs text-base-content/50">{{ source.drops.toLocaleString('en-US') }} drops</span>
                            </div>
                        </ClickableCard>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
