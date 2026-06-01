<script setup>
import {ref, onMounted} from "vue";
import Loader from "@/Components/Loader.vue";
import ItemSlot from "@/Components/RuneManager/ItemSlot.vue";

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

    axios.get(route('api.accounts.looting-bag.show', account.value))
        .then((response) => {
            inventory.value = response.data.looting_bag;
        }).catch(error => {
        console.error(error)
    }).finally(() => {
        inventoryLoading.value = false;
    });
};
</script>

<template>
    <div v-if="!inventoryLoading">
        <ul v-if="inventory !== null"
            class="m-2 grid grid-cols-4 gap-2 p-4">
            <li v-for="(item, slot) in inventory.items">
                <ItemSlot :itemProp="item"/>
            </li>
        </ul>
        <div v-else class="flex h-96 items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">No inventory found for this account</p>
        </div>
    </div>
    <Loader :component="true" :loading="inventoryLoading"></Loader>
</template>
