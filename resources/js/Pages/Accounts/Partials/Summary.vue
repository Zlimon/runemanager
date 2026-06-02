<script setup>
import { usePage } from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";
import Freshness from "@/Components/Freshness.vue";
import dayjs from "dayjs";

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
    freshness: {
        type: Object,
        default: () => ({ stale_after_minutes: 60 }),
    },
});

const page = usePage();
</script>

<template>
    <div class="flex flex-col justify-between">
        <div class="grid grid-cols-2 gap-6">
            <div class="col-span-1">
                <InputLabel :value="`${page.props.app.name} rank`" class="text-sm"/>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ account.rank.toLocaleString('en-US') }}
                </p>
            </div>

            <div class="col-span-1">
                <InputLabel :value="`Joined ${page.props.app.name}`" class="text-sm"/>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ dayjs(account.created_at).format('MMM D, YYYY') }}
                </p>
            </div>
        </div>

        <hr class="my-6 border border-beige-700 bg-beige-700">

        <div class="mb-2 flex items-baseline justify-between">
            <InputLabel class="text-sm" value="Hiscores" />
            <Freshness :updated-at="freshness.hiscores"
                       :stale-after-minutes="freshness.stale_after_minutes ?? 60" />
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div class="col-span-1">
                <InputLabel class="text-sm" value="Total level"/>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ account.level }}
                </p>
            </div>

            <div class="col-span-1">
                <InputLabel class="text-sm" value="Total XP"/>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ account.xp.toLocaleString('en-US') }}
                </p>
            </div>

            <div class="col-span-1">
                <InputLabel class="text-sm" value="Rank"/>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ account.rank.toLocaleString('en-US') }}
                </p>
            </div>

            <div class="col-span-1">
                <InputLabel class="text-sm" value="Account created"/>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ dayjs(account.created_at).format('MMM D, YYYY') }}
                </p>
            </div>
        </div>
    </div>
</template>
