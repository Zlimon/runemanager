<script setup>
import { computed, ref, watch } from "vue";
import ItemSlot from "@/Components/Game/ItemSlot.vue";
import TabbedCard from "@/Components/TabbedCard.vue";

const props = defineProps({
    bank: {
        type: Object,
        default: null,
    },
});

// 1×1 transparent placeholder for the first tab's marker (the "all items" tab
// has no representative item icon of its own).
const placeholderIcon =
    "iVBORw0KGgoAAAANSUhEUgAAACQAAAAgCAYAAAB6kdqOAAADd0lEQVR4Xu2Xv2sVQRDH3/0HqV5zzcEVBweCTQqLgM3rxMbCSgsLBbFQRFFEUQiiEDAQCBICKkHFXyghEoJKCAZ/BX9HBQURQURERERELMZ8bh1ub+/evTu9YOPCgklmdz4z853Zs9X6vxpdnuT3P1ueXD8R5HZ3KBe80QCKYeKwlThZGQDW2748gFrLky8Lsfx83pGPt2J5OhnKzHg50Kc7cQ6mu33t5cm3hwANyIf5SB5dCWXqeJkDTz7fi5Mzr2YimT0VyPigX2Jfe3ny9f4S0GK/vL8ZyYNLoVweKXNgMsqZF1dNNscOldnXWl5SpqRkiyvk3Vwkd8+Hcu6Y/1sPRWI1GWI/mwpleiyQ0QPt5oDQA9H+eBLLmxuRzJ8O5MyQXyJWEwRbyzuyry2dVQ0D2ZpwBZuF8hKtsSnv5Gggw3vasmFtXx2gbvMiFShlU03wbxySMbK1rmNHnwLRkVqyHRsrA+Xnhh2tApEpHFACBXo5HcnJw76sWV0MRADch83Bbe0qQHmYLJTpmO+PDYB2GL97O2syQAdl9eEl3Yg9JZ6bCJImoGyVgNAH0XOJWwIbyO4w7F9fM4DMmKw+UiDuu33W6AjwWkBETIpxoCVQILvDJo76SRn5mS4CcNcmVx+mbAQBNLrjnLZ+liGzjEY4iB44TL23rDcO7AyREdLPxQTBGeYMmhrc3rayau5VHWHDuYvDlcpmngVmBhGjCQ7u35rOjSIg1ZSKli7SILRsOosIlMxWBGKZslF3bWsGmToggy7Q3s19iTNEi0b4HUEM9KelViDuVGEP7a4ExDIX4EB1olkiQ2TRBqJEKmx0RPS0tQ3E3/8KyHZAa+NUgcggoicbmnrsKTO66wakGmK6071Hdv4BUJEDoPSlZ/LSwpSym72WulEgd1C6A9O1LwIiCBqFc1rqCkCm03Sy4kBnjQujc4S5pJpTe7fLioAqPB/mctUD+qEkpDb5Slz6BlLtkHIFstte7dWRDcSs4k46t+J7ZvSh7xIdxkEFtdvd/gxF5PosYJ//+DIltYE4T+f2AGLZnx3pBsj+DGU2paO/99bnSBuhBlCrpam2U65vHJnjcXTLonauzlT4aEyBeF7oTAZqJaDilY86BTF/d0Fs4bMp68KFxoDymctf5CX/G0ErOGagZsF7BbUsq9yhG9Ayw5jVlMNfYvqkTwc5XXUAAAAASUVORK5CYII=";

const activeTab = ref(null);

// Adapt the keyed bank.tabs object into TabbedCard's tab list, attaching the
// marker icon for each tab. The first tab uses the generic placeholder; the
// rest borrow their first item's icon as the tab marker.
const bankTabs = computed(() => {
    if (!props.bank) {
        return [];
    }
    return Object.entries(props.bank.tabs).map(([tab, items], index) => ({
        key: tab,
        icon: index >= 1 && items[0]?.item ? items[0].item.icon : null,
    }));
});

watch(bankTabs, (tabs) => {
    if (tabs.length && !activeTab.value) {
        activeTab.value = tabs[0].key;
    }
}, { immediate: true });

const activeTabItems = computed(() => {
    if (!props.bank || !activeTab.value) {
        return null;
    }
    return props.bank.tabs[activeTab.value] ?? null;
});
</script>

<template>
    <div v-if="bank">
        <TabbedCard :tabs="bankTabs" v-model="activeTab" tab-class="!h-12" body-class="p-2">
            <template #tab="{ tab }">
                <img :src="`data:image/jpeg;base64,${tab.icon ?? placeholderIcon}`"
                     class="object-contain"
                     loading="lazy">
            </template>

            <ul v-if="activeTabItems" class="grid grid-cols-8 gap-2 p-4">
                <li v-for="(slotItem, slot) in activeTabItems" :key="slot">
                    <ItemSlot :icon="slotItem.item?.icon" :name="slotItem.item?.name" :quantity="slotItem.quantity"
                              :examine="slotItem.item?.examine" :highalch="slotItem.item?.highalch" :lowalch="slotItem.item?.lowalch" />
                </li>
            </ul>
        </TabbedCard>
    </div>
    <div v-else class="flex h-96 items-center justify-center">
        <p class="text-gray-700 dark:text-gray-200">No bank found for this account</p>
    </div>
</template>
