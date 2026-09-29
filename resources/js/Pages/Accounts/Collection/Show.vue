<script setup>
import { computed } from "vue";
import { Deferred } from "@inertiajs/vue3";
import AccountLayout from "@/Layouts/AccountLayout.vue";
import Loader from "@/Components/Display/Loader.vue";
import Freshness from "@/Pages/Accounts/Partials/Freshness.vue";
import CollectionLog from "@/Pages/Accounts/Collection/Partials/CollectionLog.vue";
import Loot from "@/Pages/Accounts/Collection/Partials/Loot.vue";

const props = defineProps({
    account: { type: Object, required: true },
    position: { type: Object, default: null },
    vitals: { type: Object, default: null },
    location: { type: String, default: null },
    collectionLog: { type: Object, default: null },
    recentLoot: { type: Array, default: () => [] },
    freshness: { type: Object, default: () => ({ stale_after_minutes: 60 }) },
});

// DataUpdated type ⇒ the props this tab reloads (freshness is added by the layout).
const RELOAD_PROPS = {
    loot: ['recentLoot'],
};

const staleAfter = computed(() => props.freshness.stale_after_minutes ?? 60);
</script>

<template>
    <AccountLayout :account="account" :position="position" :vitals="vitals" :location="location"
                   :reload-props="RELOAD_PROPS">
        <h3>Collection Log</h3>
        <div class="mt-2">
            <Deferred data="collectionLog">
                <template #fallback>
                    <div class="flex h-96 items-center justify-center">
                        <Loader :component="true" :loading="true" />
                    </div>
                </template>

                <CollectionLog :collection-log="collectionLog" />
            </Deferred>
        </div>

        <div class="mt-6 flex items-baseline justify-between">
            <h3>Recent Loot</h3>
            <Freshness :updated-at="freshness.loot" :stale-after-minutes="staleAfter" />
        </div>
        <div class="mt-2">
            <Loot :entries="recentLoot" />
        </div>
    </AccountLayout>
</template>
