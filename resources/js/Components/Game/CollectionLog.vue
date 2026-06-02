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

const tabs = ['Bosses', 'Raids', 'Clues'];
let getCollectionLogLoading = ref(true);
let collectionLog = ref(null);

const getCollectionLog = () => {
    getCollectionLogLoading.value = true;

    axios.post(route('api.accounts.collectionlog.index', account.value), {
        tabs: tabs,
    })
        .then((response) => {
            collectionLog.value = response.data;
            showCollectionLog(tabs[0]);
        }).catch(error => {
        console.error(error)
        getCollectionLogLoading.value = false;
    }).finally(() => {
        //
    });
};

let showCollectionLogLoading = ref(true);

const showCollectionLog = (tab, collection = null) => {
    // Select first collection if none is selected
    if (collection === null) {
        collection = Object.keys(collectionLog.value.collection_log[tab])[0];
    }

    // Do not fetch if already loaded
    if (collectionLog.value.collection_log[tab][collection].items !== undefined) {
        activeCollection.value = collectionLog.value.collection_log[tab][collection];

        return;
    }

    showCollectionLogLoading.value = true;

    axios.get(route('api.accounts.collectionlog.show', [account.value, tab, collection]))
        .then((response) => {
            collectionLog.value.collection_log[tab][collection] = response.data.collection_log;
            activeCollection.value = collectionLog.value.collection_log[tab][collection];
        }).catch(error => {
        console.error(error)
    }).finally(() => {
        getCollectionLogLoading.value = false;
        showCollectionLogLoading.value = false;
    });
};

let activeTab = ref(tabs[0]);
let activeCollection = ref(null);
let activeItem = ref(null);

function setActiveTab(tab) {
    activeTab.value = tab;

    showCollectionLog(tab);
}
</script>

<template>
    <div v-if="!getCollectionLogLoading">
        <div v-if="collectionLog !== null">
            <div class="flex flex-col">
                <!-- Vertical tabs -->
                <div class="tabs tabs-lifted" role="tablist">
                    <a v-for="tab in Object.keys(collectionLog.collection_log)"
                       :class="{ 'tab-active !bg-base-200': activeTab === tab }"
                       class="tab"
                       role="tab"
                       @click="setActiveTab(tab)">
                        {{ tab }}
                    </a>
                </div>

                <!-- Tab content -->
                <div class="flex bg-base-200 border-x border-b border-base-300 rounded-b">
                    <!-- Tab left panel -->
                    <div class="h-[454px] overflow-y-scroll rounded-b-lg w-1/3">
                        <div v-for="tab in Object.keys(collectionLog.collection_log)"
                             :key="tab">
                            <div v-show="activeTab === tab">
                                <ul class="menu">
                                    <li v-for="collection in collectionLog.collection_log[tab]"
                                        :key="collection.slug">
                                        <div :class="{ 'active': activeCollection === collection }"
                                             class="flex justify-between"
                                             @click="showCollectionLog(tab, collection.slug)">
                                            <span class="max-w-32 truncate">{{ collection.name }}</span>
                                            <span :class="{
                                                'text-success': collection.obtained === collection.total,
                                                'text-error': collection.obtained === 0,
                                                'text-warning': collection.obtained >= 1 && collection.obtained !== collection.total
                                            }" class="text-right">
                                                {{ collection.obtained }}
                                                <span class="text-black"> / </span>
                                                <span>{{ collection.total }}</span>
                                            </span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Tab right panel -->
                    <div class="h-[454px] overflow-y-scroll w-2/3">
                        <div v-if="showCollectionLogLoading" class="pt-4">
                            <Loader :component="true" :loading="showCollectionLogLoading"></Loader>
                        </div>
                        <div v-else class="p-4">
                            <!-- Tab right panel header -->
                            <h5>{{ activeCollection.name }}</h5>
                            <p class="text-gray-500 dark:text-gray-400">
                                <span>Obtained: </span>
                                <span :class="{
                                    'text-success': activeCollection.obtained === activeCollection.total,
                                    'text-error': activeCollection.obtained === 0,
                                    'text-warning': activeCollection.obtained >= 1 && activeCollection.obtained !== activeCollection.total
                                }">
                                    {{ activeCollection.obtained }} <span
                                    class="text-black">/</span> {{ activeCollection.total }}
                            </span>
                            </p>
                            <div v-for="killCount in activeCollection.killCount">
                                <p class="text-gray-500 dark:text-gray-400">
                                    <span>{{ killCount.name }}: </span>
                                    <span class="font-bold">{{ killCount.amount }}</span>
                                </p>
                            </div>

                            <!-- Tab right panel items -->
                            <ul v-if="activeCollection.items !== null"
                                class="m-2 grid grid-cols-6 gap-2">
                                <li v-for="(item, slot) in activeCollection.items">
                                    <div class="box h-14 w-14 hover:bg-base-200"
                                         @mouseleave="activeItem = null"
                                         @mouseover="item.item !== null ? activeItem = item.item?._id : null">
                                        <div v-if="item.item !== null">
                                            <span v-if="item.quantity > 1"
                                                  class="absolute p-1 text-xs font-bold">
                                                {{ item.quantity }}
                                            </span>
                                            <div class="flex justify-center items-center h-14">
                                                <img v-if="item.item.icon"
                                                     :class="{ 'opacity-50': item.obtained === false || item.obtained === 0 }"
                                                     :src="`data:image/jpeg;base64,${item.item.icon}`"
                                                     class="object-contain"
                                                     loading="lazy">
                                                <span v-else>
                                                    {{ item.item.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="activeItem === item.item?._id"
                                         class="box-tooltip">
                                        <p>{{ item.item.name }}</p>
                                        <p>{{ item.item.examine }}</p>
                                        <p v-if="item.item.obtainedAt !== undefined">
                                            {{ dayjs(item.item.obtainedAt).format('MMM D, YYYY h:mm A') }}
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
    </div>
    <Loader :component="true" :loading="getCollectionLogLoading"></Loader>
</template>
