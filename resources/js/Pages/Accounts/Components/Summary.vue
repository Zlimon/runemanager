<script setup>
import {ref} from "vue";
import {Link, useForm, usePage} from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";
import dayjs from "dayjs";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);
</script>

<template>
    <div class="flex flex-col justify-between">
        <div class="grid grid-cols-2 gap-6">
            <div class="col-span-1">
                <InputLabel :value="`${usePage().props.app.name} rank`" class="text-sm"/>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ account.rank.toLocaleString('en-US') }}
                </p>
            </div>

            <div class="col-span-1">
                <InputLabel :value="`Joined ${usePage().props.app.name}`" class="text-sm"/>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ dayjs(account.created_at).format('MMM D, YYYY') }}
                </p>
            </div>
        </div>

        <hr class="my-6 border border-beige-700 bg-beige-700">

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
