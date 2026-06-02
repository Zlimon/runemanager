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

const lootingBagLoading = ref(true);
const lootingBag = ref(null);

const getLootingBag = () => {
    lootingBagLoading.value = true;

    axios.get(route('api.accounts.looting-bag.show', props.account))
        .then((response) => {
            lootingBag.value = response.data.looting_bag;
        })
        .catch((error) => {
            console.error(error);
        })
        .finally(() => {
            lootingBagLoading.value = false;
        });
};

onMounted(getLootingBag);
</script>

<template>
    <div v-if="!lootingBagLoading">
        <ul v-if="lootingBag !== null"
            class="m-2 grid grid-cols-4 gap-2 p-4">
            <li v-for="(slotItem, slot) in lootingBag.items" :key="slot">
                <ItemSlot :item="slotItem" />
            </li>
        </ul>
        <div v-else class="flex h-96 items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">No looting bag found for this account</p>
        </div>
    </div>
    <Loader :component="true" :loading="lootingBagLoading" />
</template>
