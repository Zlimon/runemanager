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

const props = defineProps({
    hiscoreTypesProp: Object,
});

//----------------------------------------------------;
// searchNpcs
//----------------------------------------------------;
let page = ref(1);
let loading = ref(false);

let searchNpcForm = useForm({
    search: '',
    per_page: 16,
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

let selectedNpc = ref(null);

// Sort drops by lowest rarity
const sortDrops = (drops) => {
    return drops.sort((a, b) => a.rarity - b.rarity);
};
//----------------------------------------------------;
// End of searchNpcs
//----------------------------------------------------;

//----------------------------------------------------;
// createHiscore
//----------------------------------------------------;
let hiscoreTypes = ref(props.hiscoreTypesProp);

let createHiscoreForm = useForm({
    name: '',
    type: '',
    uniques: [],
});

const createHiscore = () => {
    axios.post(route('admin.api.hiscores.store'), createHiscoreForm).then((response) => {
        console.log(response)

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
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid sm:grid-cols-3 gap-4 mt-12">
                    <div class="col-span-1">
                        <div class="card-lg resource-pack-dialog">
                            <h3 class="text-center header-chatbox-sword">Create hiscore</h3>

                            <div class="max-w-md mx-auto mt-6 lg:mt-6">
                                <div class="max-w-md mx-auto">
                                    <div class="mt-6 lg:mt-6">
                                        <InputLabel for="searchNpcForm-search" value="Search" class="sr-only" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                                </svg>
                                            </div>
                                            <TextInput v-model="searchNpcForm.search"
                                                       type="search"
                                                       id="searchNpcForm-search"
                                                       name="searchNpcForm-search"
                                                       class="block w-full ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            />
                                        </div>
                                        <InputError v-if="searchNpcForm.errors?.search?.length > 0" :messages="searchNpcForm.errors.search" />
                                    </div>

                                    <div class="mt-4 lg:mt-2">
                                        <InputLabel for="createHiscoreForm-name" value="Name" />
                                        <TextInput v-model="createHiscoreForm.name"
                                                   type="text"
                                                   id="createHiscoreForm-name"
                                                   name="createHiscoreForm-name"
                                                   :error="createHiscoreForm.errors.name !== undefined"
                                                   placeholder="Name"
                                        />
                                        <InputError v-if="createHiscoreForm.errors?.name?.length > 0" :messages="createHiscoreForm.errors.name" />
                                        <InputLabel v-else for="name" :value="createHiscoreForm.name" />

                                        <PrimaryButton @click="createHiscore()" class="mt-4">
                                            Create
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="selectedNpc !== null" class="card-lg resource-pack-dialog mt-4">
                            <h4 class="text-center header-chatbox-sword">{{ selectedNpc.name }} ({{ selectedNpc.combat_level }})</h4>

                            <h5>Drops</h5>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div v-for="drop in sortDrops(selectedNpc.drops)" :key="drop.id"
                                     class="hover:bg-gray-100 dark:hover:bg-gray-700 card-sm resource-pack-dialog !p-1">
                                    <div class="flex flex-col justify-between p-4 leading-normal text-center">
                                        <img :src="`data:image/jpeg;base64,${drop.item.icon}`"
                                             class="object-contain h-8 w-8 m-auto">
<!--                                        <img :src="'data:image/jpeg;base64,'+imageBytes" />-->
<!--                                        <img :src="`https://www.osrsbox.com/osrsbox-db/items-icons/${drop.id}.png`"-->
<!--                                             class="object-contain h-8 w-8 m-auto">-->

                                        <p class="text-xs mt-1">{{ drop.name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-2">
                        <div v-if="!loading && npcs.total > 0">
                            <InputLabel for="searchNpcForm.per_page" value="Per page" />
                            <TextInput v-model="searchNpcForm.per_page"
                                       type="number"
                                       id="searchNpcForm-per_page"
                                       name="searchNpcForm-per_page"
                                       class="!w-20"
                                       min="4"
                                       max="20"
                                       step="4"
                            />
                            <InputError v-if="searchNpcForm.errors?.per_page?.length > 0" :messages="searchNpcForm.errors.per_page" />

                            <div class="grid grid-cols-4 gap-4 mt-4">
                                <div v-for="npc in npcs.data" :key="npc.id">
                                    <div @click="selectedNpc = npc"
                                         class="flex items-center hover:bg-gray-100 dark:hover:bg-gray-700 card-sm resource-pack-dialog">
            <!--                            <img :src="`https://www.osrsbox.com/osrsbox-db/items-icons/${account.user.icon_id}.png`"-->
            <!--                                 class="object-contain h-16 w-16 m-4">-->
                                        <div class="flex flex-col justify-between p-4 leading-normal">
                                            <div class="flex">
                                                <h5 class="">{{ npc.name }} ({{ npc.combat_level }})</h5>
                                            </div>

                                            <div class="flex">
                                                <img src="/images/skill/total.webp"
                                                     class="object-contain h-8 w-8">
                                                <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">{{ npc.examine }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
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
