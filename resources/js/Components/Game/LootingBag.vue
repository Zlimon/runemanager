<script setup>
import ItemSlot from "@/Components/Game/ItemSlot.vue";

defineProps({
    lootingBag: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <ul v-if="lootingBag"
        class="m-2 grid grid-cols-4 gap-2 p-4">
        <li v-for="(slotItem, slot) in lootingBag.items" :key="slot">
            <ItemSlot :icon="slotItem.item?.icon" :name="slotItem.item?.name" :quantity="slotItem.quantity"
                      :examine="slotItem.item?.examine" :highalch="slotItem.item?.highalch" :lowalch="slotItem.item?.lowalch" />
        </li>
    </ul>
    <div v-else class="flex h-96 items-center justify-center">
        <p class="text-gray-700 dark:text-gray-200">No looting bag found for this account</p>
    </div>
</template>
