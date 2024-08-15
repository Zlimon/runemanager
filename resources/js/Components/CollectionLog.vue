<script setup>
import {ref, onMounted} from "vue";
import Loader from "@/Components/Loader.vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);

onMounted(() => {
    getCollectionLog();
});

let collectionLogLoading = ref(true);
let collectionLog = ref([]);
const getCollectionLog = () => {
    collectionLogLoading.value = true;

    axios.post(route('api.collectionlog.collectionlog.user'), {
        username: account.value.username,
        tabs: ['Bosses', 'Raids', 'Clues'],
    }).then((response) => {
        collectionLog.value = response.data;
    }).catch(error => {
        console.error(error)
    }).finally(() => {
        collectionLogLoading.value = false;
    });
};
</script>

<template>
    <div v-if="!collectionLogLoading">
        <div v-if="collectionLog.collectionLog !== undefined">
            <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                <ul class="-mb-px flex flex-wrap gap-2 text-center text-sm font-medium"
                    id="default-tab"
                    data-tabs-toggle="#default-tab-content"
                    role="tablist">
                    <li v-for="(bosses, tab) in collectionLog.collectionLog.tabs" role="presentation">
                        <button class="inline-block rounded-t-lg bg-gray-100 p-4 text-blue-600 active dark:bg-gray-800 dark:text-blue-500"
                                :id="`${tab}-tab`"
                                :data-tabs-target="`#${tab}`"
                                type="button"
                                role="tab"
                                :aria-controls="tab"
                                aria-selected="false">
                            {{ tab }}
                        </button>
                    </li>
                </ul>
            </div>

            <div id="default-tab-content">
                <div v-for="(bosses, tab) in collectionLog.collectionLog.tabs"
                     class="hidden rounded-lg bg-gray-50 dark:bg-gray-800"
                     :id="tab"
                     role="tabpanel"
                     :aria-labelledby="`${tab}-tab`">
                    <div class="h-[454px] md:flex">
                        <ul class="overflow-y-scroll text-sm font-medium text-gray-500 flex-column dark:text-gray-400 md:me-4 md:mb-0"
                            id="default-sub-tab"
                            data-tabs-toggle="#default-sub-tab-content"
                            role="tablist">
                            <li v-for="(row, collection) in collectionLog.collectionLog.tabs[tab]" role="presentation">
                                <button class="inline-block w-full border-b-2 p-4 text-left active hover:bg-gray-100 dark:border-gray-700 dark:text-gray-500 dark:hover:bg-gray-800"
                                        :class="{
                                            'text-green-500': row.obtained === row.total,
                                            'text-red-500': row.obtained === 0,
                                            'text-yellow-500': row.obtained >= 1 && row.obtained !== row.total
                                        }"
                                        :id="`${collection}-tab`"
                                        :data-tabs-target="`#${collection}`"
                                        type="button"
                                        role="tab"
                                        :aria-controls="`${collection}`"
                                        aria-selected="false">
                                    {{ row.name }}
                                </button>
                            </li>
                        </ul>
                        <div class="overflow-y-scroll" id="default-sub-tab-content">
                            <div v-for="(row, collection) in collectionLog.collectionLog.tabs[tab]"
                                 class="hidden rounded-lg bg-gray-50 p-4 dark:bg-gray-800"
                                 :id="collection"
                                 role="tabpanel"
                                 :aria-labelledby="`${collection}-tab`">
                                <h5>{{ row.name }}</h5>
                                <p class="text-gray-500 dark:text-gray-400">
                                    Obtained:
                                    <span :class="{
                                        'text-green-500': row.obtained === row.total,
                                        'text-red-500': row.obtained === 0,
                                        'text-yellow-500': row.obtained >= 1 && row.obtained !== row.total
                                    }">
                                        {{ row.obtained }} / {{ row.total }}
                                    </span>
                                </p>
                                <div v-for="killCount in row.killCount">
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ killCount.name }}: <span class="font-bold">{{ killCount.amount }}</span>
                                    </p>
                                </div>

                                <ul class="grid grid-cols-5 gap-4">
                                    <li v-for="item in row.items" class="flex items-center justify-between">
                                        <button :data-tooltip-target="`${collection}-${item.id}-tooltip-bottom`"
                                                data-tooltip-placement="bottom"
                                                type="button"
                                                class="relative h-20 w-20 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                            <span v-if="item.quantity > 0"
                                                  class="absolute top-0 left-0 p-1 text-sm">{{ item.quantity }}</span>
                                            <img v-if="item.icon"
                                                 :src="`data:image/jpeg;base64,${item.icon}`"
                                                 class="mx-auto h-10 w-10 object-contain"
                                                 :class="{ 'opacity-50': item.obtained === false }">
                                            <span v-else>{{ item.name }}</span>
                                        </button>

                                        <div :id="`${collection}-${item.id}-tooltip-bottom`"
                                             role="tooltip"
                                             class="invisible absolute z-10 inline-block rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white opacity-0 shadow-sm tooltip dark:bg-gray-700">
                                            <p>{{ item.name }}</p>
                                            <p>{{ item.examine }}</p>
                                            <p v-if="item.obtainedAt !== null">{{ item.obtainedAt }}</p>
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="flex h-96 items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">No collection log found for this user</p>
        </div>
    </div>
    <Loader :loading="collectionLogLoading" :component="true"></Loader>
</template>
