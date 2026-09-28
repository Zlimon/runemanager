<script setup>
import { computed, ref } from "vue";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";
import AccountLayout from "@/Layouts/AccountLayout.vue";
import TabbedCard from "@/Components/Cards/TabbedCard.vue";
import Card from "@/Components/Cards/Card.vue";
import Freshness from "@/Pages/Accounts/Partials/Freshness.vue";
import Equipment from "@/Pages/Accounts/Partials/Equipment.vue";
import Inventory from "@/Pages/Accounts/Items/Partials/Inventory.vue";
import LootingBag from "@/Pages/Accounts/Items/Partials/LootingBag.vue";
import Bank from "@/Pages/Accounts/Items/Partials/Bank.vue";

const props = defineProps({
    account: { type: Object, required: true },
    position: { type: Object, default: null },
    vitals: { type: Object, default: null },
    location: { type: String, default: null },
    inventory: { type: Object, default: null },
    lootingBag: { type: Object, default: null },
    bank: { type: Object, default: null },
    freshness: { type: Object, default: () => ({ stale_after_minutes: 60 }) },
});

// DataUpdated type ⇒ the props this tab reloads (freshness is added by the layout).
const RELOAD_PROPS = {
    inventory: ['inventory'],
    looting_bag: ['lootingBag'],
    bank: ['bank'],
    equipment: ['account'],
};

const { packIcon } = useResourcePackIcon();

const activeTab = ref('inventory');
const inventoryTabs = [
    { key: 'inventory', label: 'Inventory', icon: packIcon('tab', 'inventory') },
    { key: 'looting-bag', label: 'Looting bag', icon: packIcon('bank', 'deposit_looting_bag') },
];

const staleAfter = computed(() => props.freshness.stale_after_minutes ?? 60);
</script>

<template>
    <AccountLayout :account="account" :position="position" :vitals="vitals" :location="location"
                   :reload-props="RELOAD_PROPS">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="flex flex-col gap-4">
                <Card padding="p-4">
                    <div class="mb-2 flex items-baseline justify-between">
                        <h3>Equipment</h3>
                        <Freshness :updated-at="freshness.equipment" :stale-after-minutes="staleAfter" />
                    </div>
                    <Equipment :account="account" />
                </Card>

                <TabbedCard :tabs="inventoryTabs" v-model="activeTab">
                    <template #inventory>
                        <div class="flex justify-end">
                            <Freshness :updated-at="freshness.inventory" :stale-after-minutes="staleAfter" />
                        </div>
                        <Inventory :inventory="inventory" />
                    </template>
                    <template #looting-bag>
                        <div class="flex justify-end">
                            <Freshness :updated-at="freshness.looting_bag" :stale-after-minutes="staleAfter" />
                        </div>
                        <LootingBag :looting-bag="lootingBag" />
                    </template>
                </TabbedCard>
            </div>

            <div class="lg:col-span-2">
                <div class="flex items-baseline justify-between">
                    <h3>Bank</h3>
                    <Freshness :updated-at="freshness.bank" :stale-after-minutes="staleAfter" />
                </div>
                <div class="mt-2">
                    <Bank :bank="bank" />
                </div>
            </div>
        </div>
    </AccountLayout>
</template>
