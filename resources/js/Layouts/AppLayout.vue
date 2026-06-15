<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import HiscoresMenu from '@/Components/HiscoresMenu.vue';
import NavDropdown from '@/Components/NavDropdown.vue';
import NavDropdownLink from '@/Components/NavDropdownLink.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';

defineProps({
    title: String,
});

const page = usePage();

const mobileMenuOpen = ref(false);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title"/>

        <Banner/>

        <div class="min-h-screen pack-bg">
            <nav class="bg-beige-600 dark:bg-gray-900">
                <div class="mx-auto flex max-w-screen-xl flex-wrap items-center justify-between p-4">
                    <Link :href="route('dashboard')" class="flex items-center space-x-3 rtl:space-x-reverse">
                        <img v-if="page.props.instance?.logo_url" :src="page.props.instance.logo_url"
                             :alt="page.props.app.name" class="h-12 w-auto object-contain md:h-16">
                        <span v-else
                              class="self-center whitespace-nowrap text-2xl text-6xl font-bold dark:text-white md:text-7xl"
                              style="font-family: 'runescape-smooth', sans-serif">
                            {{ page.props.app.name }}
                        </span>
                    </Link>
                    <button type="button"
                            class="btn btn-ghost btn-circle md:hidden"
                            :aria-expanded="mobileMenuOpen"
                            @click="mobileMenuOpen = !mobileMenuOpen">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 17 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M1 1h15M1 7h15M1 13h15"/>
                        </svg>
                    </button>
                    <div class="w-full items-center justify-between font-medium md:order-1 md:flex md:w-auto"
                         :class="mobileMenuOpen ? 'flex' : 'hidden'">
                        <ul class="mt-4 flex flex-col rounded-lg border border-gray-100 p-4 bg-beige-100 rtl:space-x-reverse dark:border-gray-700 dark:bg-gray-800 md:space-x-8 md:bg-beige-600 md:mt-0 md:flex-row md:border-0 md:p-0 md:dark:bg-gray-900">
                            <li>
                                <ResponsiveNavLink :href="route('dashboard')"
                                                   :active="route().current('dashboard')">
                                    Home
                                </ResponsiveNavLink>
                            </li>
                            <li>
                                <HiscoresMenu :active="route().current('hiscores.*')" />
                            </li>
                            <li>
                                <NavDropdown label="Accounts"
                                             :active="route().current('accounts.*') || route().current('map.*') || route().current('group-bank.*')">
                                    <NavDropdownLink :href="route('accounts.index')" :active="route().current('accounts.*')">
                                        All Accounts
                                    </NavDropdownLink>
                                    <NavDropdownLink :href="route('map.index')" :active="route().current('map.*')">
                                        Live Map
                                    </NavDropdownLink>
                                    <NavDropdownLink v-if="page.props.instance?.mode === 'group'"
                                                     :href="route('group-bank.index')" :active="route().current('group-bank.*')">
                                        Group Bank
                                    </NavDropdownLink>
                                </NavDropdown>
                            </li>
                            <li>
                                <NavDropdown label="Community"
                                             :active="route().current('feed.*') || route().current('calendar.*') || route().current('announcements.*')">
                                    <NavDropdownLink :href="route('feed.index')" :active="route().current('feed.*')">
                                        Live Feed
                                    </NavDropdownLink>
                                    <NavDropdownLink :href="route('calendar.index')" :active="route().current('calendar.*')">
                                        Calendar
                                    </NavDropdownLink>
                                    <NavDropdownLink :href="route('announcements.index')" :active="route().current('announcements.*')">
                                        Announcements
                                    </NavDropdownLink>
                                </NavDropdown>
                            </li>
                            <li v-if="page.props.is_admin">
                                <ResponsiveNavLink :href="route('admin.dashboard')"
                                                   :active="route().current('admin.*')">
                                    Admin
                                </ResponsiveNavLink>
                            </li>
                            <li>
                                <NavDropdown :label="page.props.auth?.user?.name ?? 'Account'"
                                             :active="route().current('themes.*') || route().current('api-tokens.*')">
                                    <NavDropdownLink :href="route('themes.index')" :active="route().current('themes.*')">
                                        Appearance
                                    </NavDropdownLink>
                                    <NavDropdownLink v-if="$page.props.jetstream.hasApiFeatures"
                                                     :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                        API Tokens
                                    </NavDropdownLink>
                                    <button type="button" @click="logout"
                                            class="block w-full rounded px-3 py-2 text-left text-base-content hover:bg-base-300">
                                        Log out
                                    </button>
                                </NavDropdown>
                            </li>
                            <li class="flex items-center">
                                <DarkModeToggle />
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>



            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-base-100 shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header"/>
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot/>
            </main>
        </div>
    </div>
</template>
