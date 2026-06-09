<script setup>
import { onBeforeUnmount, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import dayjs from "dayjs";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import ItemSlot from "@/Components/Game/ItemSlot.vue";

const props = defineProps({
    groupName: { type: String, default: null },
    groupBank: { type: Object, default: null },
});

// SPEC §5.2 — any member's plugin push updates the shared bank; reload the data
// live when the server broadcasts a change.
onMounted(() => {
    window.Echo.channel("group-bank").listen(".GroupBankUpdated", () => {
        router.reload({ only: ["groupBank"] });
    });
});

onBeforeUnmount(() => {
    window.Echo.leave("group-bank");
});
</script>

<template>
    <AppLayout title="Group Bank">
        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="flex items-baseline justify-between">
                    <h1 class="header-chatbox-sword text-2xl font-bold">
                        {{ groupName ? `${groupName} — Group Bank` : 'Group Bank' }}
                    </h1>
                    <span v-if="groupBank?.updated_at" class="text-xs text-base-content/60">
                        Updated {{ dayjs(groupBank.updated_at).format('MMM D, YYYY h:mm A') }}
                    </span>
                </div>

                <Card class="mt-6" padding="p-4">
                    <ul v-if="groupBank && groupBank.items.length"
                        class="grid grid-cols-6 gap-2 sm:grid-cols-8 md:grid-cols-10">
                        <li v-for="(slotItem, slot) in groupBank.items" :key="slot">
                            <ItemSlot :icon="slotItem.item?.icon"
                                      :name="slotItem.item?.name"
                                      :quantity="slotItem.quantity"
                                      :examine="slotItem.item?.examine"
                                      :highalch="slotItem.item?.highalch"
                                      :lowalch="slotItem.item?.lowalch" />
                        </li>
                    </ul>
                    <div v-else class="flex h-64 items-center justify-center text-base-content/60">
                        The group bank hasn't been synced yet — open it in-game with the plugin active.
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
