<script setup>
import {ref, onMounted} from "vue";
import Loader from "@/Components/Loader.vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);

onMounted(() => {
    getInventory();
});

let inventoryLoading = ref(true);
let inventory = ref(null);
const getInventory = () => {
    inventoryLoading.value = true;

    axios.get(route('api.accounts.inventory.show', account.value))
        .then((response) => {
            inventory.value = response.data.inventory;
        }).catch(error => {
        console.error(error)
    }).finally(() => {
        inventoryLoading.value = false;
    });
};

let activeItem = ref(null);
</script>

<template>
    <div v-if="!inventoryLoading">
        <ul v-if="inventory !== null"
            class="m-2 grid grid-cols-4 gap-2">
            <li v-for="(item, slot) in inventory.items">
                <div class="box h-14 w-14 hover:bg-base-200"
                     @mouseleave="activeItem = null"
                     @mouseover="item.item !== null ? activeItem = item.item?._id : null">
                    <div v-if="item.item !== null">
                        <span v-if="item.amount > 1"
                              class="absolute p-1 text-xs font-bold">
                            {{ item.amount }}
                        </span>
                        <div class="flex justify-center items-center h-14">
                            <img v-if="item.item.icon"
                                 :class="{ 'opacity-50': item.amount === 0 }"
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
                    <p>
                        {{ item.item.name }}
                    </p>
                    <p>
                        {{ item.item.examine }}
                    </p>
                    <p v-if="item.amount > 0 && item.item.highalch">
                        HA: {{ (item.item.highalch * item.amount).toLocaleString('en-US') }} gp
                        <span v-if="item.amount > 1">({{ item.item.highalch.toLocaleString('en-US') }} ea)</span>
                    </p>
                    <p v-if="item.amount > 0 && item.item.lowalch">
                        LA: {{ (item.item.lowalch * item.amount).toLocaleString('en-US') }} gp
                        <span v-if="item.amount > 1">({{ item.item.lowalch.toLocaleString('en-US') }} ea)</span>
                    </p>
                </div>
            </li>
        </ul>
        <div v-else class="flex h-96 items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">No inventory found for this account</p>
        </div>
    </div>
    <Loader :component="true" :loading="inventoryLoading"></Loader>
</template>
