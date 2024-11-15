<script setup>
import {ref, watch, onMounted} from "vue";
import debounce from 'lodash/debounce';
import * as THREE from 'three';
import {OBJLoader} from 'three/examples/jsm/loaders/OBJLoader';
import {MTLLoader} from 'three/examples/jsm/loaders/MTLLoader';
import {OrbitControls} from 'three/examples/jsm/controls/OrbitControls';
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link, useForm, usePage} from '@inertiajs/vue3';
import CollectionLog from "@/Components/CollectionLog.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import dayjs from "dayjs";
import InputError from "@/Components/InputError.vue";
import Bank from "@/Components/RuneManager/Bank.vue";
import Quests from "@/Components/RuneManager/Quests.vue";
import Inventory from "@/Components/RuneManager/Inventory.vue";
import LootingBag from "@/Components/RuneManager/LootingBag.vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);

//----------------------------------------------------;
// searchAccounts
//----------------------------------------------------;
let accounts = ref([]);
let showSearchAccountsDropdown = ref(true);

let searchAccountForm = useForm({
    username: '',
});

watch(() => searchAccountForm.username, debounce((value) => {
    searchAccounts();
}, 500));

let loading = ref(false);

const searchAccounts = (load = true) => {
    loading.value = load;

    searchAccountForm.per_page = 10;

    axios.post(route('api.accounts.search'), searchAccountForm)
        .then((response) => {
            accounts.value = response.data;

            searchAccountForm.errors = {};
        }).catch(error => {
        searchAccountForm.errors = error.response.data.errors || {};

        console.error(error)
    }).finally(() => {
        loading.value = false;

        showSearchAccountsDropdown.value = true;
    });
};
//----------------------------------------------------;
// End of searchAccounts
//----------------------------------------------------;

const sceneContainer = ref(null);

onMounted(() => {
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, sceneContainer.value.clientWidth / sceneContainer.value.clientHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer();
    renderer.setSize(sceneContainer.value.clientWidth, sceneContainer.value.clientHeight);
    sceneContainer.value.appendChild(renderer.domElement);

    const ambientLight = new THREE.AmbientLight(0xffffff, 1);
    scene.add(ambientLight);

    const directionalLight = new THREE.DirectionalLight(0xffffff, 1.5);
    directionalLight.position.set(2, 1, 1).normalize();
    scene.add(directionalLight);

    const mtlLoader = new MTLLoader();

    const player = 'Player Annihilation 2024-09-01_12-03-23';

    mtlLoader.load('/models/' + player + '.mtl', (materials) => {
        materials.preload();

        const objLoader = new OBJLoader();
        objLoader.setMaterials(materials);
        objLoader.load('/models/' + player + '.obj', (object) => {
            object.position.set(25, -100, 25);
            object.scale.set(1, 1, 1);
            scene.add(object);
            animate();
        });
    });

    camera.position.set(100, 50, 150);
    camera.lookAt(0, 0, 0);

    // Orbit Controls for interactivity
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.update();

    const animate = () => {
        requestAnimationFrame(animate);
        // Enable transparency in the renderer
        if (sceneContainer.value !== null) {
            renderer.setSize(sceneContainer.value.clientWidth, sceneContainer.value.clientHeight);

            // Set background to transparent
            renderer.setClearColor(0x000000, 0); // The second parameter controls the opacity, set to 0 for transparency

            renderer.render(scene, camera);
            controls.update(); // Needed to keep controls in sync
        }
    };
});
</script>

<template>
    <AppLayout title="Account">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex justify-end">
                    <div class="flex flex-col"
                         @mouseover="showSearchAccountsDropdown = true"
                         @mouseleave="showSearchAccountsDropdown = false">
                        <div class="flex flex-wrap items-center justify-end flex-column md:flex-row">
                            <InputLabel for="searchAccountForm-username"
                                        value="Search for any account by username"
                                        class="sr-only"/>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 flex items-center start-0 ps-3">
                                    <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                    </svg>
                                </div>
                                <TextInput v-model="searchAccountForm.username"
                                           type="search"
                                           id="searchAccountForm-search"
                                           name="searchAccountForm-search"
                                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 ps-10 focus:border-blue-500 focus:ring-blue-500 dark:placeholder-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                           :error="searchAccountForm.errors.username !== undefined"
                                           placeholder="Search for any account by username"
                                />
                            </div>
                        </div>
                        <InputError v-if="searchAccountForm.errors.username !== undefined"
                                    :messages="searchAccountForm.errors.username"/>

                        <div class="relative z-50 flex justify-end">
                            <div
                                v-if="showSearchAccountsDropdown && (accounts.data !== undefined && accounts.data.length > 0)"
                                class="w-[calc(100%+4rem)] absolute overflow-y-auto bg-beige-600 border-2 border-beige-700 rounded-lg dark:border-gray-700 dark:bg-gray-800 ">
                                <div v-for="account in accounts.data" :key="account.id">
                                    <Link :href="route('accounts.show', account)"
                                          class="flex flex-row items-center border-b p-2 border-beige-700 space-x-2 hover:bg-beige-200 dark:hover:bg-gray-700">
                                        <img :src="`data:image/jpeg;base64,${account.icon}`"
                                             class="h-16 w-16 rounded-full p-2 ring-2 ring-beige-600 dark:ring-gray-500">
                                        <div class="flex flex-col justify-between">
                                            <div class="flex items-center space-x-1">
                                                <img v-if="account.account_type === 'ironman'"
                                                     :src="`/images/ironman.png`"
                                                     class="h-6 w-6 object-contain">
                                                <img v-else-if="account.account_type !== 'normal'"
                                                     :src="`/images/${account.account_type}_ironman.png`"
                                                     class="h-6 w-6 object-contain">
                                                <p class="text-xl">
                                                    {{ account.username }}
                                                </p>
                                            </div>

                                            <div class="flex items-center space-x-1">
                                                <img src="/images/skill/total.webp"
                                                     class="h-6 w-6 object-contain">
                                                <p class="font-normal text-gray-700 text-md dark:text-gray-400">
                                                    {{ account.level }}
                                                </p>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 card-lg resource-pack-dialog">
                    <div class="grid grid-cols-3 gap-6">
                        <div class="col-span-1">
                            <div class="flex flex-col justify-between gap-y-7">
                                <div class="flex items-center gap-x-5">
                                    <img :src="`data:image/jpeg;base64,${account.icon}`"
                                         class="h-16 w-16 rounded-full p-2 ring-2 ring-beige-600 dark:ring-gray-500">
                                    <div class="flex flex-col">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <img v-if="account.account_type === 'ironman'"
                                                     :src="`/images/ironman.png`"
                                                     class="h-8 w-8 object-contain">
                                                <img v-else-if="account.account_type !== 'normal'"
                                                     :src="`/images/${account.account_type}_ironman.png`"
                                                     class="h-8 w-8 object-contain">
                                                <h1 class="text-xl font-bold md:text-3xl">
                                                    {{ account.username }}
                                                </h1>
                                            </div>

                                            <div class="flex shrink-0 items-center">
                                                <PrimaryButton>
                                                    Update
                                                </PrimaryButton>
                                            </div>
                                        </div>

                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="capitalize">
                                                {{ account.account_type }}
                                            </span>
                                            ·
                                            <span>
                                                Last updated
                                                {{ dayjs(account.updated_at).format('MMMM D, YYYY h:mm A') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-center">
                                <div class="my-4 grid grid-cols-2 gap-4">
                                    <div ref="sceneContainer"
                                         class="h-64 rounded-lg border bg-beige-300 border-beige-700 dark:border-gray-700 dark:bg-gray-800"/>

                                    <div class="mx-auto p-6 w-[168px]"
                                         style="background-image: url('/images/equipment_slots.png'); background-repeat: no-repeat; background-position: center;">
                                        <div v-if="account.equipment !== null">
                                            <div class="ml-1 flex justify-center mt-[8px]">
                                                <img v-if="account.equipment.head_item !== null"
                                                     :src="`data:image/jpeg;base64,${account.equipment.head_item.icon}`"
                                                     :title="account.equipment.head_item.name">
                                            </div>

                                            <div class="ml-1 flex justify-center mt-[8px]">
                                                <img v-if="account.equipment.cape_item !== null"
                                                     :src="`data:image/jpeg;base64,${account.equipment.cape_item.icon}`"
                                                     :title="account.equipment.cape_item.name"
                                                     style="margin-right: 5px;">
                                                <img v-if="account.equipment.neck_item !== null"
                                                     :src="`data:image/jpeg;base64,${account.equipment.neck_item.icon}`"
                                                     :title="account.equipment.neck_item.name">
                                                <img v-if="account.equipment.ammo_item !== null"
                                                     :src="`data:image/jpeg;base64,${account.equipment.ammo_item.icon}`"
                                                     :title="account.equipment.ammo_item.name"
                                                     style="margin-left: 5px;">
                                            </div>

                                            <div class="ml-1 flex justify-center mt-[8px]">
                                                <img v-if="account.equipment.weapon_item !== null"
                                                     :src="`data:image/jpeg;base64,${account.equipment.weapon_item.icon}`"
                                                     :title="account.equipment.weapon_item.name"
                                                     style="margin-right: 20px;">
                                                <img v-if="account.equipment.body_item !== null"
                                                     :src="`data:image/jpeg;base64,${account.equipment.body_item.icon}`"
                                                     :title="account.equipment.body_item.name">
                                                <img v-if="account.equipment.shield_item !== null"
                                                     :src="`data:image/jpeg;base64,${account.equipment.shield_item.icon}`"
                                                     :title="account.equipment.shield_item.name"
                                                     style="margin-left: 20px;">
                                            </div>

                                            <div class="ml-1 flex justify-center mt-[8px]">
                                                <img v-if="account.equipment.legs_item !== null"
                                                     :src="`data:image/jpeg;base64,${account.equipment.legs_item.icon}`"
                                                     :title="account.equipment.legs_item.name">
                                            </div>

                                            <div class="ml-1 flex justify-center mt-[8px]">
                                                <img v-if="account.equipment.hands_item !== null"
                                                     :src="`data:image/jpeg;base64,${account.equipment.hands_item.icon}`"
                                                     :title="account.equipment.hands_item.name"
                                                     style="margin-right: 20px;">
                                                <img v-if="account.equipment.feet_item !== null"
                                                     :src="`data:image/jpeg;base64,${account.equipment.feet_item.icon}`"
                                                     :title="account.equipment.feet_item.name">
                                                <img v-if="account.equipment.ring_item !== null"
                                                     :src="`data:image/jpeg;base64,${account.equipment.ring_item.icon}`"
                                                     :title="account.equipment.ring_item.name"
                                                     style="margin-left: 20px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col justify-between">
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="col-span-1">
                                            <InputLabel :value="`${usePage().props.app.name} rank`" class="text-sm"/>
                                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                                {{ account.rank.toLocaleString('en-US') }}
                                            </p>
                                        </div>

                                        <div class="col-span-1">
                                            <InputLabel :value="`Joined ${usePage().props.app.name}`" class="text-sm"/>
                                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                                {{ dayjs(account.created_at).format('MMM D, YYYY') }}
                                            </p>
                                        </div>
                                    </div>

                                    <hr class="my-6 border border-beige-700 bg-beige-700">

                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="col-span-1">
                                            <InputLabel value="Total level" class="text-sm"/>
                                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                                {{ account.level }}
                                            </p>
                                        </div>

                                        <div class="col-span-1">
                                            <InputLabel value="Total XP" class="text-sm"/>
                                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                                {{ account.xp.toLocaleString('en-US') }}
                                            </p>
                                        </div>

                                        <div class="col-span-1">
                                            <InputLabel value="Rank" class="text-sm"/>
                                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                                {{ account.rank.toLocaleString('en-US') }}
                                            </p>
                                        </div>

                                        <div class="col-span-1">
                                            <InputLabel value="Account created" class="text-sm"/>
                                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                                {{ dayjs(account.created_at).format('MMM D, YYYY') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <ul class="-mb-px flex flex-wrap gap-2 text-center text-sm font-medium mt-4"
                                id="default-tab"
                                data-tabs-toggle="#default-tab-content"
                                role="tablist">
                                <li role="presentation">
                                    <button
                                        class="inline-block rounded-t-lg p-2 !text-black active bg-beige-300 !border-t !border-b !border-b-beige-300 !border-x !border-beige-700 dark:border-gray-700 dark:bg-gray-800 dark:text-blue-500"
                                        id="inventory-tab"
                                        data-tabs-target="#inventory"
                                        type="button"
                                        role="tab"
                                        aria-controls="inventory"
                                        aria-selected="false">
                                        Inventory
                                    </button>
                                </li>
                                <li role="presentation">
                                    <button
                                        class="inline-block rounded-t-lg p-2 !text-black active bg-beige-300 !border-t !border-b !border-b-beige-300 !border-x !border-beige-700 dark:border-gray-700 dark:bg-gray-800 dark:text-blue-500"
                                        id="looting-bag-tab"
                                        data-tabs-target="#looting-bag"
                                        type="button"
                                        role="tab"
                                        aria-controls="looting-bag"
                                        aria-selected="false">
                                        Looting bag
                                    </button>
                                </li>
                            </ul>

                            <div id="default-tab-content">
                                <div
                                    class="hidden rounded-r-lg rounded-b-lg border shadow bg-beige-300 border-beige-700 dark:bg-gray-800"
                                    id="inventory"
                                    role="tabpanel"
                                    aria-labelledby="inventory-tab">

                                    <Inventory :accountProp="account"/>
                                </div>

                                <div
                                    class="hidden rounded-r-lg rounded-b-lg border shadow bg-beige-300 border-beige-700 dark:bg-gray-800"
                                    id="looting-bag"
                                    role="tabpanel"
                                    aria-labelledby="looting-bag-tab">

                                    <LootingBag :accountProp="account"/>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-2">
                            <div class="mx-auto mt-6 grid grid-cols-3 gap-2">
                                <div class="col-span-2">
                                    <h3 class="header-chatbox-sword">
                                        Skills
                                    </h3>

                                    <ul class="mt-4 grid grid-cols-6 gap-1">
                                        <li>
                                            <Link :href="route('accounts.index')"
                                                  data-tooltip-target="total-tooltip-bottom"
                                                  data-tooltip-placement="bottom"
                                                  type="button"
                                                  class="flex items-center justify-center gap-2 rounded-lg border p-1 shadow bg-beige-300 border-beige-700 dark:border-gray-700 dark:bg-gray-800">
                                                <img src="/images/skill/total.webp"
                                                     class="h-6 w-6 object-contain"/>
                                                <span class="text-xs font-semibold capitalize">
                                                    {{ account.level }}
                                                </span>
                                            </Link>

                                            <div id="total-tooltip-bottom"
                                                 role="tooltip"
                                                 class="invisible absolute z-10 inline-block rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white opacity-0 shadow-sm tooltip dark:bg-gray-700">
                                                <p>
                                                    Total level
                                                </p>
                                                <p>
                                                    {{ account.xp.toLocaleString('en-US') }}
                                                </p>
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>
                                        </li>
                                        <li v-for="skill in account.skills" :key="skill.name">
                                            <Link :href="route('hiscores.skills.index', skill.slug)"
                                                  :data-tooltip-target="`${skill.slug}-tooltip-bottom`"
                                                  data-tooltip-placement="bottom"
                                                  type="button"
                                                  class="flex items-center justify-center gap-2 rounded-lg border p-1 shadow bg-beige-300 border-beige-700 dark:border-gray-700 dark:bg-gray-800">
                                                <img :src="`/images/skill/${skill.slug}.webp`"
                                                     class="h-6 w-6 object-contain"/>
                                                <span class="text-xs font-semibold capitalize">
                                                    {{ skill.level }}
                                                </span>
                                            </Link>

                                            <div :id="`${skill.slug}-tooltip-bottom`"
                                                 role="tooltip"
                                                 class="invisible absolute z-10 inline-block rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white opacity-0 shadow-sm tooltip dark:bg-gray-700">
                                                <p>
                                                    {{ skill.name }}
                                                </p>
                                                <p>
                                                    {{ skill.xp.toLocaleString('en-US') }}
                                                </p>
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-span-1">
                                    <h3 class="header-chatbox-sword">
                                        Quests
                                    </h3>

                                    <div class="mt-4 rounded-lg border p-2 shadow bg-beige-300 border-beige-700 dark:border-gray-700 dark:bg-gray-800">
                                        <Quests :accountProp="account" />
                                    </div>
                                </div>
                            </div>

                            <h3 class="mt-4 header-chatbox-sword">
                                Collection Log
                            </h3>

                            <div class="mt-4">
                                <CollectionLog :accountProp="account"/>
                            </div>

                            <h3 class="mt-4 header-chatbox-sword">
                                Bank
                            </h3>

                            <div class="mt-4">
                                <Bank :accountProp="account"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
