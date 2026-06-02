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

const activeTab = computed(() => props.collectionLog?.activeTab ?? null);
const activeCollection = computed(() => props.collectionLog?.activeCollection ?? null);
const tabs = computed(() => props.collectionLog?.tabs ?? {});

const switchTo = (tab, slug = null) => {
    const data = { ctab: tab };
    if (slug) {
        data.ccollection = slug;
    }

    router.reload({
        only: ['collectionLog'],
        data,
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
        <div class="flex flex-col">
            <!-- Top tabs -->
            <div class="tabs tabs-lifted" role="tablist">
                <a v-for="(_, tab) in tabs"
                   :key="tab"
                   :class="{ 'tab-active !bg-base-200': activeTab === tab }"
                   class="tab"
                   role="tab"
                   @click="switchTo(tab)">
                    {{ tab }}
                </a>
            </div>

            <!-- Tab content -->
            <div class="flex bg-base-200 border-x border-b border-base-300 rounded-b">
                <!-- Left panel: collections list -->
                <div class="h-[454px] overflow-y-scroll rounded-b-lg w-1/3">
                    <ul v-if="activeTab" class="menu">
                        <li v-for="collection in tabs[activeTab]"
                            :key="collection.slug">
                            <div :class="{ active: activeCollection && activeCollection.slug === collection.slug }"
                                 class="flex justify-between"
                                 @click="switchTo(activeTab, collection.slug)">
                                <span class="max-w-32 truncate">{{ collection.name }}</span>
                                <span :class="{
                                    'text-success': collection.obtained === collection.total,
                                    'text-error': collection.obtained === 0,
                                    'text-warning': collection.obtained >= 1 && collection.obtained !== collection.total,
                                }" class="text-right">
                                    {{ collection.obtained }}
                                    <span class="text-black"> / </span>
                                    <span>{{ collection.total }}</span>
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Right panel: selected collection items -->
                <div class="h-[454px] overflow-y-scroll w-2/3">
                    <Loader v-if="reloading" :component="true" :loading="true" />
                    <div v-else-if="activeCollection" class="p-4">
                        <h5>{{ activeCollection.name }}</h5>
                        <p class="text-gray-500 dark:text-gray-400">
                            <span>Obtained: </span>
                            <span :class="{
                                'text-success': activeCollection.obtained === activeCollection.total,
                                'text-error': activeCollection.obtained === 0,
                                'text-warning': activeCollection.obtained >= 1 && activeCollection.obtained !== activeCollection.total,
                            }">
                                {{ activeCollection.obtained }}
                                <span class="text-black">/</span>
                                {{ activeCollection.total }}
                            </span>
                        </p>
                        <div v-for="killCount in (activeCollection.killCount ?? [])" :key="killCount.name">
                            <p class="text-gray-500 dark:text-gray-400">
                                <span>{{ killCount.name }}: </span>
                                <span class="font-bold">{{ killCount.amount }}</span>
                            </p>
                        </div>

                        <ul v-if="activeCollection.items && activeCollection.items.length > 0"
                            class="m-2 grid grid-cols-6 gap-2">
                            <li v-for="(slotItem, slot) in activeCollection.items" :key="slot">
                                <div class="box h-14 w-14 hover:bg-base-200"
                                     @mouseleave="activeItem = null"
                                     @mouseover="slotItem.item !== null ? activeItem = slotItem.item?._id : null">
                                    <div v-if="slotItem.item !== null">
                                        <span v-if="slotItem.quantity > 1"
                                              class="absolute p-1 text-xs font-bold">
                                            {{ slotItem.quantity }}
                                        </span>
                                        <div class="flex justify-center items-center h-14">
                                            <img v-if="slotItem.item.icon"
                                                 :class="{ 'opacity-50': slotItem.obtained === false || slotItem.obtained === 0 }"
                                                 :src="`data:image/jpeg;base64,${slotItem.item.icon}`"
                                                 class="object-contain"
                                                 loading="lazy">
                                            <span v-else>{{ slotItem.item.name }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="activeItem === slotItem.item?._id" class="box-tooltip">
                                    <p>{{ slotItem.item.name }}</p>
                                    <p>{{ slotItem.item.examine }}</p>
                                    <p v-if="slotItem.item.obtainedAt !== undefined">
                                        {{ dayjs(slotItem.item.obtainedAt).format('MMM D, YYYY h:mm A') }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="flex h-96 items-center justify-center">
        <p class="text-gray-500 dark:text-gray-400">No collection log found for this user</p>
    </div>
</template>
