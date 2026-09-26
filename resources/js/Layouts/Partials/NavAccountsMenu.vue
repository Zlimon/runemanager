<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import NavDropdownLink from '@/Layouts/Partials/NavDropdownLink.vue';
import NavSubmenu from '@/Layouts/Partials/NavSubmenu.vue';

/*
 * Links to the viewer's own OSRS accounts, from the account nav dropdown.
 *
 * Behind a submenu rather than listed inline: a member may have claimed a dozen
 * accounts, and that many rows pushed the dropdown's own actions off the bottom
 * of the panel. The same reason NavPackSwitcher sits behind one.
 */
const page = usePage();

const accounts = computed(() => page.props.my_accounts ?? []);

/*
 * Marks the collapsed row while the viewer is looking at one of them.
 *
 * A function rather than a computed on purpose: `route().current()` reads
 * Ziggy's globals and the current URL, neither of which is reactive, so a
 * computed would cache the answer from whichever page first evaluated it and
 * keep it across every Inertia visit. Called from the template, it is
 * re-evaluated on each render, the same way the links below are.
 */
const viewingOwnAccount = () => accounts.value.some(
    (account) => route().current('accounts.show', { account: account.username }),
);
</script>

<template>
    <NavSubmenu v-if="accounts.length" label="My accounts" :active="viewingOwnAccount()">
        <NavDropdownLink v-for="account in accounts" :key="account.username"
                         :href="route('accounts.show', { account: account.username })"
                         :active="route().current('accounts.show', { account: account.username })">
            <span class="flex items-center gap-2">
                <!-- Account-type marks have no pack equivalent, so they stay as
                     plain images from public/images. -->
                <img v-if="account.account_type && account.account_type !== 'normal'"
                     :src="`/images/${account.account_type}.png`"
                     class="h-4 w-4 object-contain" alt="">
                {{ account.username }}
            </span>
        </NavDropdownLink>
    </NavSubmenu>
</template>
