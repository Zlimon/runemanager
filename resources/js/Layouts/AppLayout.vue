<script setup>
import { onMounted, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import HiscoresMenu from '@/Components/HiscoresMenu.vue';

defineProps({
    title: String,
});

const page = usePage();

const mobileMenuOpen = ref(false);

const logout = () => {
    router.post(route('logout'));
};

// Persist the user's light/dark preference. Only reachable when no pack is in
// effect (the toggle is hidden otherwise — a pack controls dark mode itself).
const toggleDarkMode = () => {
    router.put(route('user.dark-mode.update'), { dark_mode: !page.props.dark_mode }, {
        preserveScroll: true,
    });
};

// The resource pack CSS itself is included by the Blade root via a server-rendered
// <link> tag (see resources/views/app.blade.php). Vue flips Tailwind's `dark` class
// (so `dark:` variants compile) and sets DaisyUI's `data-theme` so the matching base
// theme — runemanager / runemanager-dark — drives the semantic colour tokens.
const applyDarkMode = () => {
    const dark = page.props.dark_mode === true;
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.dataset.theme = dark ? 'runemanager-dark' : 'runemanager';
};

onMounted(applyDarkMode);
watch(() => page.props.dark_mode, applyDarkMode);
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
                                <ResponsiveNavLink :href="route('accounts.index')"
                                                   :active="route().current('accounts.*')">
                                    Accounts
                                </ResponsiveNavLink>
                            </li>
                            <li>
                                <ResponsiveNavLink :href="route('map.index')"
                                                   :active="route().current('map.*')">
                                    Live Map
                                </ResponsiveNavLink>
                            </li>
                            <li v-if="page.props.instance?.mode === 'group'">
                                <ResponsiveNavLink :href="route('group-bank.index')"
                                                   :active="route().current('group-bank.*')">
                                    Group Bank
                                </ResponsiveNavLink>
                            </li>
                            <li>
                                <ResponsiveNavLink :href="route('feed.index')"
                                                   :active="route().current('feed.*')">
                                    Live Feed
                                </ResponsiveNavLink>
                            </li>
                            <li>
                                <ResponsiveNavLink :href="route('calendar.index')"
                                                   :active="route().current('calendar.*')">
                                    Calendar
                                </ResponsiveNavLink>
                            </li>
                            <li>
                                <ResponsiveNavLink :href="route('announcements.index')"
                                                   :active="route().current('announcements.*')">
                                    Announcements
                                </ResponsiveNavLink>
                            </li>
                            <li v-if="page.props.is_admin">
                                <ResponsiveNavLink :href="route('admin.dashboard')"
                                                   :active="route().current('admin.*')">
                                    Admin
                                </ResponsiveNavLink>
                            </li>
                            <li>
                                <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures"
                                                   :href="route('api-tokens.index')"
                                                   :active="route().current('api-tokens.index')">
                                    API Tokens
                                </ResponsiveNavLink>
                            </li>
                            <li>
                                <form method="POST" @submit.prevent="logout">
                                    <ResponsiveNavLink as="button">
                                        Log out
                                    </ResponsiveNavLink>
                                </form>
                            </li>
                            <li v-if="page.props.can_toggle_dark_mode" class="flex items-center">
                                <button type="button"
                                        class="btn btn-ghost btn-circle btn-sm"
                                        :aria-label="page.props.dark_mode ? 'Switch to light mode' : 'Switch to dark mode'"
                                        @click="toggleDarkMode">
                                    <svg v-if="!page.props.dark_mode" class="h-5 w-5" fill="none" stroke="currentColor"
                                         stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>
                                    </svg>
                                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor"
                                         stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="4"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                                    </svg>
                                </button>
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
