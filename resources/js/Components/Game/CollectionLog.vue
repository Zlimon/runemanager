<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import Loader from "@/Components/Loader.vue";
import ItemSlot from "@/Components/Game/ItemSlot.vue";
import dayjs from "dayjs";

const props = defineProps({
    collectionLog: {
        type: Object,
        default: null,
    },
});

const reloading = ref(false);
const filter = ref('all');    // all | obtained | missing
const order = ref('default'); // default | date

const GROUPS = [
    { key: 'bosses', label: 'Bosses' },
    { key: 'raids', label: 'Raids' },
    { key: 'clues', label: 'Clues' },
    { key: 'minigames', label: 'Minigames' },
    { key: 'other', label: 'Other' },
];

const activeSlug = computed(() => props.collectionLog?.activeSlug ?? null);
const activeCollection = computed(() => props.collectionLog?.activeCollection ?? null);

// Group the flat category list into the TempleOSRS sections, each with a rolled-up
// obtained/total so the section header shows progress at a glance.
const groupedCategories = computed(() => {
    const cats = props.collectionLog?.categories ?? [];
    return GROUPS
        .map(({ key, label }) => {
            const categories = cats.filter((c) => c.group === key);
            return {
                key,
                label,
                categories,
                obtained: categories.reduce((sum, c) => sum + c.obtained, 0),
                total: categories.reduce((sum, c) => sum + c.total, 0),
            };
        })
        .filter((g) => g.categories.length > 0);
});

// Accordion: only one group open at a time (TempleOSRS-style), defaulting to the
// group that holds the active category. The open group's list scrolls within its
// own region so the other group headers stay visible.
const activeGroup = computed(() =>
    (props.collectionLog?.categories ?? []).find((c) => c.slug === activeSlug.value)?.group ?? null);
const expandedGroup = ref(activeGroup.value);
const toggleGroup = (key) => { expandedGroup.value = expandedGroup.value === key ? null : key; };

// Progress colour: none = red, some = yellow, complete = green.
const countClass = (obtained, total) => {
    if (!obtained) {
        return 'text-error';
    }
    if (total && obtained >= total) {
        return 'text-success';
    }
    return 'text-warning';
};

// Lazy-load one category's items at a time from the stored log (keeps payload small).
const switchTo = (slug) => {
    router.reload({
        only: ['collectionLog'],
        data: { ccollection: slug },
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => { reloading.value = true; },
        onFinish: () => { reloading.value = false; },
    });
};

const visibleItems = computed(() => {
    let items = activeCollection.value?.items ?? [];

    if (filter.value === 'obtained') {
        items = items.filter((i) => i.obtained);
    } else if (filter.value === 'missing') {
        items = items.filter((i) => !i.obtained);
    }

    if (order.value === 'date') {
        items = [...items].sort((a, b) => {
            if (!a.date) return 1;        // un-obtained (no date) sink to the end
            if (!b.date) return -1;
            return new Date(b.date) - new Date(a.date); // most recent first
        });
    }

    return items;
});

const obtainedDate = (slotItem) =>
    slotItem.date ? `Obtained ${dayjs(slotItem.date).format('MMM D, YYYY')}` : null;

// Boss categories reuse the hiscore boss icons (dash-separated slugs); a missing
// icon (e.g. a multi-boss category) just hides itself.
const bossIcon = (slug) => `/images/boss/${slug.replaceAll('_', '-')}.png`;
</script>

<template>
    <div v-if="collectionLog">
        <div class="mb-2 flex items-baseline justify-between">
            <span class="font-semibold">
                {{ collectionLog.obtained }} / {{ collectionLog.total }} slots unlocked
            </span>
            <span v-if="collectionLog.fetchedAt" class="text-xs text-base-content/60">
                via TempleOSRS · {{ dayjs(collectionLog.fetchedAt).format('MMM D, YYYY') }}
            </span>
        </div>

        <!-- Padding insets the columns so the pack's textured frame wraps the
             whole panel instead of being covered by the scroll columns. -->
        <div class="flex rounded bg-base-200 p-4 resource-pack-dialog">
            <!-- Left: accordion category list — group headers stay visible while
                 the open group's subcategories scroll within their own region. -->
            <div class="flex h-[454px] w-1/3 flex-col">
                <template v-for="group in groupedCategories" :key="group.key">
                    <button type="button"
                            class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left font-semibold hover:bg-base-content/5"
                            @click="toggleGroup(group.key)">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3 w-3 transition-transform"
                                 :class="{ 'rotate-90': expandedGroup === group.key }"
                                 viewBox="0 0 20 20" fill="currentColor">
                                <path d="M7 5l6 5-6 5V5z" />
                            </svg>
                            {{ group.label }}
                        </span>
                        <span :class="countClass(group.obtained, group.total)">
                            {{ group.obtained }} / {{ group.total }}
                        </span>
                    </button>
                    <ul v-if="expandedGroup === group.key"
                        class="menu min-h-0 w-full flex-1 flex-nowrap overflow-y-auto py-0">
                        <li v-for="category in group.categories" :key="category.slug">
                            <div class="flex w-full items-center justify-between gap-2"
                                 :class="{ active: activeSlug === category.slug }"
                                 @click="switchTo(category.slug)">
                                <span class="flex min-w-0 items-center gap-2">
                                    <img v-if="group.key === 'bosses'"
                                         :src="bossIcon(category.slug)"
                                         class="h-5 w-5 shrink-0 object-contain"
                                         onerror="this.style.visibility='hidden'" alt="">
                                    <span class="truncate">{{ category.name }}</span>
                                </span>
                                <span :class="countClass(category.obtained, category.total)">
                                    {{ category.obtained }} / {{ category.total }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </template>
            </div>

            <!-- Right: the selected category's items (obtained + missing) -->
            <div class="h-[454px] w-2/3 overflow-y-scroll border-l border-base-300">
                <Loader v-if="reloading" :component="true" :loading="true" />
                <div v-else-if="activeCollection" class="p-4">
                    <div class="flex items-baseline justify-between gap-2">
                        <h5 class="font-semibold">{{ activeCollection.name }}</h5>
                        <span class="text-sm" :class="countClass(activeCollection.obtained, activeCollection.total)">
                            {{ activeCollection.obtained }} / {{ activeCollection.total }}
                        </span>
                    </div>

                    <div class="relative z-20 mt-2 flex flex-wrap items-center gap-2">
                        <div class="join">
                            <button v-for="f in ['all', 'obtained', 'missing']" :key="f"
                                    type="button"
                                    class="btn btn-xs join-item capitalize"
                                    :class="{ 'btn-active': filter === f }"
                                    @click="filter = f">
                                {{ f }}
                            </button>
                        </div>
                        <div class="join">
                            <button type="button" class="btn btn-xs join-item"
                                    :class="{ 'btn-active': order === 'default' }"
                                    @click="order = 'default'">
                                Default
                            </button>
                            <button type="button" class="btn btn-xs join-item"
                                    :class="{ 'btn-active': order === 'date' }"
                                    @click="order = 'date'">
                                Date
                            </button>
                        </div>
                    </div>

                    <ul class="m-2 grid grid-cols-6 gap-2">
                        <li v-for="slotItem in visibleItems" :key="slotItem.id">
                            <ItemSlot :icon="slotItem.item?.icon ?? null"
                                      :name="slotItem.item?.name ?? null"
                                      :quantity="slotItem.quantity"
                                      :examine="slotItem.item?.examine ?? null"
                                      :subtext="obtainedDate(slotItem)"
                                      :dimmed="!slotItem.obtained" />
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="flex h-96 items-center justify-center">
        <p class="text-base-content/70">
            No collection log synced — track this account on TempleOSRS to populate it.
        </p>
    </div>
</template>
