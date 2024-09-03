<script setup>
import {useForm} from '@inertiajs/vue3';
import {ref, watch} from "vue";
import debounce from 'lodash/debounce';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";
import {TailwindPagination} from 'laravel-vue-pagination';
import Loader from "@/Components/Loader.vue";
import Checkbox from "@/Components/Checkbox.vue";

//----------------------------------------------------;
// searchNpcs
//----------------------------------------------------;
let page = ref(1);
let loading = ref(false);

let searchNpcForm = useForm({
    search: '',
    per_page: 18,
});

watch(() => [searchNpcForm.search, searchNpcForm.per_page], debounce((value) => {
    searchNpcs();
}, 500));

let npcs = ref({});
const searchNpcs = (page = 1) => {
    if (searchNpcForm.search) {
        loading.value = true;

        axios.post(route('api.npc.search'), {
            ...searchNpcForm,
            'page': page,
        }).then((response) => {
            npcs.value = response.data.npcs;

            searchNpcForm.errors = {};
        }).catch(error => {
            searchNpcForm.errors = error.response.data.errors || {};

            console.error(error)
        }).finally(() => {
            loading.value = false;
        });
    }
};
//----------------------------------------------------;
// End of searchNpcs
//----------------------------------------------------;

//----------------------------------------------------;
// createHiscore
//----------------------------------------------------;
let createHiscoreForm = useForm({
    name: '',
    items: [],
    all_items: false,
});

let selectedNpc = ref(null);

const selectNpc = (npc) => {
    selectedNpc.value = npc;

    createHiscoreForm.reset('items', 'all_items');
    createHiscoreForm.name = npc.name;

    createHiscoreForm.errors = {};
};

// Filter duplicate items and sort drops by lowest rarity
const sortDrops = (drops) => {
    return drops.filter((drop, index, self) =>
        index === self.findIndex((t) => (
            t.item.id === drop.item.id
        ))
    ).sort((a, b) => a.rarity - b.rarity);
};

watch(() => createHiscoreForm.all_items, () => {
    if (createHiscoreForm.all_items === true) {
        createHiscoreForm.items = selectedNpc.value.drops.map(drop => drop.id);
    } else {
        createHiscoreForm.items = [];
    }
});

const createHiscore = () => {
    axios.post(route('admin.api.hiscores.store'), createHiscoreForm).then((response) => {
        createHiscoreForm.errors = {};
    }).catch(error => {
        createHiscoreForm.errors = error.response.data.errors || {};

        console.error(error)
    });
};
//----------------------------------------------------;
// End of createHiscore
//----------------------------------------------------;
</script>

<template>
    <AppLayout title="Hiscore - Create">
        <div class="p-4 sm:p-6 lg:p-12">
            <div class="mx-auto max-w-7xl">
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-3 md:col-span-1">
                        <div class="card-lg">
                            <h3>Create hiscore</h3>

                            <div class="mt-6 lg:mt-8">
                                <div class="mt-2">
                                    <InputLabel for="searchNpcForm-search" value="Search for any monster" />
                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 flex items-center start-0 ps-3">
                                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                            </svg>
                                        </div>
                                        <TextInput v-model="searchNpcForm.search"
                                                   type="search"
                                                   id="searchNpcForm-search"
                                                   name="searchNpcForm-search"
                                                   class="block w-full rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 ps-10 focus:border-blue-500 focus:ring-blue-500 dark:placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                                   :error="searchNpcForm.errors.search !== undefined"
                                        />
                                    </div>
                                    <InputError v-if="searchNpcForm.errors.search !== undefined" :messages="searchNpcForm.errors.search" />
                                </div>

                                <div class="mt-2">
                                    <InputLabel for="createHiscoreForm-name" value="Name" />
                                    <TextInput v-model="createHiscoreForm.name"
                                               type="text"
                                               id="createHiscoreForm-name"
                                               name="createHiscoreForm-name"
                                               :error="createHiscoreForm.errors.name !== undefined"
                                               disabled
                                    />
                                    <InputError v-if="createHiscoreForm.errors.name !== undefined" :messages="createHiscoreForm.errors.name" />
                                </div>

                                <div class="mt-6 lg:mt-8">
                                    <PrimaryButton @click="createHiscore()">
                                        Create
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>

                        <div v-if="selectedNpc !== null" class="mt-4 card-lg">
                            <h4 class="text-center">{{ selectedNpc.name }}</h4>

                            <div class="mt-2 flex justify-between">
                                <h5>Drops</h5>

                                <div class="flex gap-2">
                                    <InputLabel for="createHiscoreForm-all_items" value="All items" class="!m-0" />
                                    <Checkbox v-model="createHiscoreForm.all_items"
                                              id="createHiscoreForm-all_items"
                                              name="createHiscoreForm-all_items"
                                              :error="createHiscoreForm.errors.all_items !== undefined"
                                    />
                                </div>
                            </div>

                            <div class="mt-2 grid grid-cols-5 gap-4 md:grid-cols-3">
                                <div v-for="drop in sortDrops(selectedNpc.drops)" :key="drop.id"
                                     class="hover:bg-gray-100 dark:hover:bg-gray-700 card-sm !p-1 hover:cursor-pointer"
                                     :class="{ '!bg-gray-400': createHiscoreForm.items.includes(drop.id) }">
                                    <div @click="createHiscoreForm.items.includes(drop.id) ? createHiscoreForm.items = createHiscoreForm.items.filter(item => item !== drop.id) : createHiscoreForm.items.push(drop.id)"
                                         class="flex flex-col justify-between p-1 text-center leading-normal">
                                        <img :src="`data:image/jpeg;base64,${drop.item.icon}`"
                                             class="m-auto h-8 w-8 object-contain">

                                        <p class="break-words text-xs text-gray-700 dark:text-gray-400">{{ drop.name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-3 md:col-span-2">
                        <div v-if="!loading && npcs.total > 0">
                            <InputLabel for="searchNpcForm.per_page" value="Per page" />
                            <TextInput v-model="searchNpcForm.per_page"
                                       type="number"
                                       id="searchNpcForm-per_page"
                                       name="searchNpcForm-per_page"
                                       class="!w-20"
                                       min="3"
                                       max="21"
                                       step="3"
                                       :error="searchNpcForm.errors.per_page !== undefined"
                            />
                            <InputError v-if="searchNpcForm.errors.per_page !== undefined" :messages="searchNpcForm.errors.per_page" />

                            <div class="mt-2 grid grid-cols-2 gap-4 md:grid-cols-3">
                                <div v-for="npc in npcs.data" :key="npc.id"
                                     class="hover:bg-gray-100 dark:hover:bg-gray-700 card-sm !p-1 hover:cursor-pointer"
                                     :class="{ '!bg-gray-400': selectedNpc !== null && selectedNpc.id === npc.id }">
                                    <div @click="selectNpc(npc)"
                                         class="flex flex-col justify-between p-4 leading-normal">
                                        <h5 class="">{{ npc.name }}</h5>

                                        <p class="text-gray-700 dark:text-gray-400">{{ npc.examine }}</p>

                                        <div class="mt-2 grid grid-cols-3 gap-2">
                                            <div v-for="drop in sortDrops(npc.drops).slice(0, 3)" :key="drop.id"
                                                 class="card-sm !p-1">
                                                <div class="flex flex-col justify-between text-center leading-normal">
                                                    <img :src="`data:image/jpeg;base64,${drop.item.icon}`"
                                                         class="m-auto h-8 w-8 object-contain">
                                                </div>
                                            </div>
                                        </div>
                                        <p v-if="npc.drops.length > 3" class="mt-1 text-xs">+ {{ npc.drops.length - 3 }} more drops...</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <TailwindPagination :data="npcs" @pagination-change-page="searchNpcs($event)"></TailwindPagination>
                            </div>
                        </div>

                        <Loader :loading="loading" :component="true"></Loader>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
