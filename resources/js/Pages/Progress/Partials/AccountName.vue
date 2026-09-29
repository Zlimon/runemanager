<script setup>
import { Link, usePage } from '@inertiajs/vue3';

/*
 * A board row's account: linked to its profile for members; plain text for
 * guests on a public /progress page (profiles need a login), and for any row
 * whose account_id is absent — the anonymized rows a public board serves to
 * protect member privacy, whose masked username wouldn't resolve to a real
 * profile anyway.
 */
defineProps({
    username: { type: String, required: true },
    accountType: { type: String, default: null },
    accountId: { type: [Number, String], default: null },
});

const page = usePage();
</script>

<template>
    <span class="flex min-w-0 items-center gap-2">
        <img v-if="accountType && accountType !== 'normal'" :src="`/images/${accountType}.png`"
             class="h-5 w-5 shrink-0 object-contain" alt="">
        <Link v-if="page.props.auth?.user && accountId" :href="route('accounts.show', { account: username })"
              class="truncate font-semibold hover:underline">
            {{ username }}
        </Link>
        <span v-else class="truncate font-semibold">{{ username }}</span>
    </span>
</template>
