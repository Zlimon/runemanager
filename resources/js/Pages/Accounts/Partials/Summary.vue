<script setup>
import { usePage } from '@inertiajs/vue3';
import InputLabel from "@/Components/Form/InputLabel.vue";
import dayjs from "dayjs";

defineProps({
    account: {
        type: Object,
        required: true,
    },
    // Rank among this instance's accounts by total XP. Null until the account
    // has hiscores to rank on.
    instanceRank: {
        type: Object,
        default: null,
    },
    // WomAccountView::summary() when Wise Old Man is on; null otherwise.
    wom: {
        type: Object,
        default: null,
    },
});

const hours = (value) => `${Number(value ?? 0).toLocaleString('en-US', { maximumFractionDigits: 0 })} h`;

const page = usePage();
</script>

<template>
    <div class="flex flex-col justify-between">
        <div class="grid grid-cols-2 gap-6">
            <div class="col-span-1">
                <InputLabel :value="`${page.props.app.name} rank`" class="text-sm"/>
                <p class="text-xl font-bold text-base-content">
                    <template v-if="instanceRank">
                        #{{ instanceRank.position.toLocaleString('en-US') }}
                        <span class="text-sm font-normal text-base-content/60">
                            of {{ instanceRank.total.toLocaleString('en-US') }}
                        </span>
                    </template>
                    <template v-else>Unranked</template>
                </p>
            </div>

            <div class="col-span-1">
                <InputLabel :value="`Joined ${page.props.app.name}`" class="text-sm"/>
                <p class="text-xl font-bold text-base-content">
                    {{ dayjs(account.created_at).format('MMM D, YYYY') }}
                </p>
            </div>
        </div>

        <!-- Wise Old Man efficiency, kept deliberately small (gains live on their own tab). -->
        <p v-if="wom?.linked" class="mt-4 text-xs text-base-content/60">
            EHP {{ hours(wom.ehp) }} · EHB {{ hours(wom.ehb) }} ·
            <a :href="wom.url" target="_blank" rel="noopener" class="link link-hover">Wise Old Man ↗</a>
        </p>
    </div>
</template>
