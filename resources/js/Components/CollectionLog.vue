<script setup>
import {ref, onMounted} from "vue";
import Loader from "@/Components/Loader.vue";
import dayjs from "dayjs";

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

    axios.post(route('api.collectionlog.user'), {
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

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <div v-if="!collectionLogLoading">
        <div v-if="collectionLog.collectionLog !== undefined">
            <div class="">
                <ul class="-mb-px flex flex-wrap gap-2 text-center text-sm font-medium"
                    id="default-tab"
                    data-tabs-toggle="#default-tab-content"
                    role="tablist">
                    <li v-for="(bosses, tab) in collectionLog.collectionLog.tabs" role="presentation">
                        <button
                            class="inline-block rounded-t-lg p-4 !text-black active bg-beige-300 !border-t !border-b !border-b-beige-300 !border-x !border-beige-700 dark:border-gray-700 dark:bg-gray-800 dark:text-blue-500"
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
                     class="hidden rounded-r-lg rounded-b-lg border shadow bg-beige-300 border-beige-700 dark:bg-gray-800"
                     :id="tab"
                     role="tabpanel"
                     :aria-labelledby="`${tab}-tab`">
                    <div class="h-[454px] md:flex">
                        <ul class="overflow-y-scroll text-sm font-medium text-gray-500 flex-column dark:text-gray-400 md:me-4 md:mb-0"
                            id="default-sub-tab"
                            data-tabs-toggle="#default-sub-tab-content"
                            role="tablist">
                            <li v-for="(row, collection) in collectionLog.collectionLog.tabs[tab]" role="presentation">
                                <button
                                    class="inline-block w-full !text-black !border-b-2 !border-beige-700 p-4 text-left active hover:bg-beige-300 hover:text-black dark:border-gray-700 dark:text-gray-500 dark:hover:bg-gray-800"
                                    :id="`${collection}-tab`"
                                    :data-tabs-target="`#${collection}`"
                                    type="button"
                                    role="tab"
                                    :aria-controls="`${collection}`"
                                    aria-selected="false">
                                    {{ row.name }}
                                    <span :class="{
                                        'text-green-500': row.obtained === row.total,
                                        'text-red-500': row.obtained === 0,
                                        'text-yellow-500': row.obtained >= 1 && row.obtained !== row.total
                                    }">
                                        {{ row.obtained }} <span class="text-black">/</span> {{ row.total }}
                                    </span>
                                </button>
                            </li>
                        </ul>
                        <div class="overflow-y-scroll" id="default-sub-tab-content">
                            <div v-for="(row, collection) in collectionLog.collectionLog.tabs[tab]"
                                 class="hidden p-4"
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
                                        {{ row.obtained }} <span class="text-black">/</span> {{ row.total }}
                                    </span>
                                </p>
                                <div v-for="killCount in row.killCount">
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ killCount.name }}: <span class="font-bold">{{ killCount.amount }}</span>
                                    </p>
                                </div>

                                <ul class="mt-4 grid grid-cols-5 gap-4">
                                    <li v-for="item in row.items" class="flex items-center justify-between">
                                        <button :data-tooltip-target="`${collection}-${item.id}-tooltip-bottom`"
                                                data-tooltip-placement="bottom"
                                                type="button"
                                                class="relative h-20 w-20 rounded-lg border p-4 border-beige-700 dark:border-gray-700">
                                            <span v-if="item.quantity > 0"
                                                  class="absolute top-0 left-0 p-1 text-sm">{{ item.quantity }}</span>
                                            <img v-if="item.icon"
                                                 :src="`data:image/jpeg;base64,${item.icon}`"
                                                 class="mx-auto h-10 w-10 object-contain"
                                                 :class="{ 'opacity-50': item.obtained === false }"
                                                 loading="lazy"
                                                 @error="handleImageError">
                                            <span v-else>{{ item.name }}</span>
                                        </button>

                                        <div :id="`${collection}-${item.id}-tooltip-bottom`"
                                             role="tooltip"
                                             class="invisible absolute z-10 inline-block rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white opacity-0 shadow-sm tooltip dark:bg-gray-700">
                                            <p>{{ item.name }}</p>
                                            <p>{{ item.examine }}</p>
                                            <p v-if="item.obtainedAt !== null">
                                                {{ dayjs(item.obtainedAt).format('MMM D, YYYY h:mm A') }}</p>
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
