<script setup>
import ItemSlot from "@/Components/Game/ItemSlot.vue";

defineProps({
    inventory: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <ul v-if="inventory"
        class="m-2 grid grid-cols-4 gap-2 p-4">
        <li v-for="(slotItem, slot) in inventory.items" :key="slot">
            <ItemSlot :item="slotItem" />
        </li>
    </ul>
    <div v-else class="flex h-96 items-center justify-center">
        <p class="text-gray-500 dark:text-gray-400">No inventory found for this account</p>
    </div>
</template>
