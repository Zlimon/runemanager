<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import Loader from "@/Components/Loader.vue";
import dayjs from "dayjs";

const props = defineProps({
    collectionLog: {
        type: Object,
        default: null,
    },
});

const reloading = ref(false);
const activeItem = ref(null);

const categories = computed(() => props.collectionLog?.categories ?? []);
const activeSlug = computed(() => props.collectionLog?.activeSlug ?? null);
const activeCollection = computed(() => props.collectionLog?.activeCollection ?? null);

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
            <!-- Left: category list -->
            <div class="h-[454px] w-1/3 overflow-y-scroll">
                <ul class="menu">
                    <li v-for="category in categories" :key="category.slug">
                        <div class="flex justify-between"
                             :class="{ active: activeSlug === category.slug }"
                             @click="switchTo(category.slug)">
                            <span class="max-w-32 truncate">{{ category.name }}</span>
                            <span class="text-right text-success">{{ category.obtained }}</span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Right: obtained items for the selected category -->
            <div class="h-[454px] w-2/3 overflow-y-scroll border-l border-base-300">
                <Loader v-if="reloading" :component="true" :loading="true" />
                <div v-else-if="activeCollection" class="p-4">
                    <h5 class="font-semibold">{{ activeCollection.name }}</h5>
                    <p class="text-sm text-base-content/70">{{ activeCollection.obtained }} obtained</p>

                    <ul v-if="activeCollection.items.length" class="m-2 grid grid-cols-6 gap-2">
                        <li v-for="(slotItem, slot) in activeCollection.items" :key="slot">
                            <div class="relative flex h-14 w-14 items-center justify-center rounded hover:bg-white/10"
                                 @mouseleave="activeItem = null"
                                 @mouseover="slotItem.item ? activeItem = slot : null">
                                <template v-if="slotItem.item">
                                    <span v-if="slotItem.quantity > 1"
                                          class="absolute left-0 top-0 z-10 px-0.5 text-xs font-bold text-yellow-300"
                                          style="text-shadow: 0 0 2px #000, 0 0 2px #000">
                                        {{ slotItem.quantity }}
                                    </span>
                                    <img v-if="slotItem.item.icon"
                                         :src="`data:image/jpeg;base64,${slotItem.item.icon}`"
                                         class="object-contain" loading="lazy">
                                    <span v-else class="text-center text-[10px] leading-tight">{{ slotItem.item.name }}</span>

                                    <div v-if="activeItem === slot"
                                         class="box-tooltip bottom-full left-1/2 mb-1 -translate-x-1/2 whitespace-nowrap">
                                        <p class="font-semibold">{{ slotItem.item.name }}</p>
                                        <p v-if="slotItem.item.examine" class="opacity-80">{{ slotItem.item.examine }}</p>
                                        <p v-if="slotItem.date" class="opacity-60">
                                            {{ dayjs(slotItem.date).format('MMM D, YYYY') }}
                                        </p>
                                    </div>
                                </template>
                            </div>
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
