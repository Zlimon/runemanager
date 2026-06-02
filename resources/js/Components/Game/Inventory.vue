<script setup>
import { ref, onMounted } from "vue";
import Loader from "@/Components/Loader.vue";
import ItemSlot from "@/Components/Game/ItemSlot.vue";

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
});

const inventoryLoading = ref(true);
const inventory = ref(null);

const getInventory = () => {
    inventoryLoading.value = true;

    axios.get(route('api.accounts.inventory.show', props.account))
        .then((response) => {
            inventory.value = response.data.inventory;
        })
        .catch((error) => {
            console.error(error);
        })
        .finally(() => {
            inventoryLoading.value = false;
        });
};

onMounted(getInventory);
</script>

<template>
    <div v-if="!inventoryLoading">
        <ul v-if="inventory !== null"
            class="m-2 grid grid-cols-4 gap-2 p-4">
            <li v-for="(slotItem, slot) in inventory.items" :key="slot">
                <ItemSlot :item="slotItem" />
            </li>
        </ul>
        <div v-else class="flex h-96 items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">No inventory found for this account</p>
        </div>
    </div>
    <Loader :component="true" :loading="inventoryLoading" />
</template>
