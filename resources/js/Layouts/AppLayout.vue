<script setup>
import {onMounted, ref} from 'vue';
import {Head, Link, router, usePage} from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {initFlowbite} from "flowbite";

const props = defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

let selectedHiscore = ref(null);

let skills = usePage().props.skills;
let bosses = usePage().props.bosses;
let clues = usePage().props.clues;

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};

onMounted(() => {
    initFlowbite();

    // if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    if (usePage().props.dark_mode === true) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark')
    }
})
</script>

<template>
    <div>
        <Head :title="title"/>

        <Banner/>

        <div class="min-h-screen bg-beige-300 dark:bg-gray-800">
            <nav @mouseleave="selectedHiscore = null"
                 class="border-gray-200 bg-beige-600 dark:border-gray-600 dark:bg-gray-900">
                <div class="mx-auto flex max-w-screen-xl flex-wrap items-center justify-between p-4">
                    <Link :href="route('dashboard')" class="flex items-center space-x-3 rtl:space-x-reverse">
                        <span class="self-center whitespace-nowrap text-2xl text-6xl font-bold dark:text-white md:text-7xl"
                              style="font-family: 'runescape-smooth', sans-serif">
                            {{ usePage().props.app.name }}
                        </span>
                    </Link>
                    <button data-collapse-toggle="mega-menu-full" type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg p-2 text-sm text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 md:hidden dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                            aria-controls="mega-menu-full" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 17 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M1 1h15M1 7h15M1 13h15"/>
                        </svg>
                    </button>
                    <div id="mega-menu-full"
                         class="hidden w-full items-center justify-between font-medium md:order-1 md:flex md:w-auto">
                        <ul class="mt-4 flex flex-col rounded-lg border border-gray-100 p-4 bg-beige-100 rtl:space-x-reverse dark:border-gray-700 dark:bg-gray-800 md:space-x-8 md:bg-beige-600 md:mt-0 md:flex-row md:border-0 md:p-0 md:dark:bg-gray-900">
                            <li>
                                <ResponsiveNavLink :href="route('dashboard')"
                                                   :active="route().current('dashboard')">
                                    Home
                                </ResponsiveNavLink>
                            </li>
                            <li>
                                <ResponsiveNavLink :active="route().current('hiscores.*')"
                                                   as="button"
                                                   id="hiscores-menu-dropdown-button"
                                                   data-collapse-toggle="hiscores-menu-dropdown">
                                    <div class="flex items-center">
                                        Hiscores
                                        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true"
                                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="m1 1 4 4 4-4"/>
                                        </svg>
                                    </div>
                                </ResponsiveNavLink>
                            </li>
                            <li>
                                <ResponsiveNavLink :href="route('accounts.index')"
                                                   :active="route().current('accounts.*')">
                                    Accounts
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
                        </ul>
                    </div>
                </div>
                <div id="hiscores-menu-dropdown"
                     class="hidden border-y border-gray-200 shadow-sm bg-beige-100 dark:border-gray-600 dark:bg-gray-800">
                    <div class="mx-auto flex max-w-screen-md justify-between px-4 py-5 text-gray-900 dark:text-white md:px-6">
                        <div @mouseover="selectedHiscore = 'skill'"
                             class="h-20 w-20 rounded-full p-2 ring-2 ring-beige-600 dark:ring-gray-500 bg-[url('/images/skill/total.webp')] bg-center bg-no-repeat bg-[length:40px_40px]"
                             :class="{ '!bg-beige-200': selectedHiscore === 'skill' || usePage().props.recordTypeProp === 'skill' }" />
                        <div @mouseover="selectedHiscore = 'boss'"
                             class="h-20 w-20 rounded-full p-2 ring-2 ring-beige-600 dark:ring-gray-500 bg-[url('/images/boss/boss.png')] bg-center bg-no-repeat bg-[length:40px_40px]"
                             :class="{ '!bg-beige-200': selectedHiscore === 'boss' || usePage().props.recordTypeProp === 'boss' }" />
                        <div @mouseover="selectedHiscore = 'clue'"
                             class="h-20 w-20 rounded-full p-2 ring-2 ring-beige-600 dark:ring-gray-500 bg-[url('/images/clue/clue.png')] bg-center bg-no-repeat bg-[length:40px_40px]"
                             :class="{ '!bg-beige-200': selectedHiscore === 'clue' || usePage().props.recordTypeProp === 'clue' }" />
                    </div>

                    <div v-if="selectedHiscore === 'skill'"
                         class="mx-auto grid max-w-screen-xl grid-cols-4 place-items-center gap-y-2 px-4 pb-5 md:px-6">
                        <Link v-for="skill in skills" :key="skill"
                              :href="route('hiscores.skills.index', skill.slug)"
                              class="flex items-center justify-center rounded-lg p-2 space-x-2 w-[50%] hover:bg-beige-400 sm:justify-start lg:p-4 hover:dark:bg-gray-800"
                              :class="{ 'bg-beige-400 border border-beige-700 shadow dark:border-gray-700 dark:bg-gray-800': usePage().props.skillSlugProp === skill.slug }">
                            <img :src="`/images/skill/${skill.slug}.webp`"
                                 class="h-8 w-8 object-contain"/>
                            <div class="hidden font-semibold capitalize sm:block">{{ skill.name }}</div>
                        </Link>
                    </div>

                    <div v-if="selectedHiscore === 'boss'"
                         class="mx-auto grid max-w-screen-xl grid-cols-4 place-items-center gap-y-2 px-4 pb-5 md:px-6">
                        <Link v-for="boss in bosses" :key="boss"
                              :href="route('hiscores.bosses.index', boss.slug)"
                              class="flex items-center justify-center rounded-lg p-2 space-x-2 w-[75%] hover:bg-beige-400 sm:justify-start lg:p-4 hover:dark:bg-gray-800"
                              :class="{ 'bg-beige-400 border border-beige-700 shadow dark:border-gray-700 dark:bg-gray-800': usePage().props.collectionSlugProp === boss.slug }">
                            <img :src="`/images/boss/${boss.slug}.png`"
                                 class="h-8 w-8 object-contain"/>
                            <div class="hidden font-semibold capitalize sm:block">{{ boss.name }}</div>
                        </Link>
                    </div>

                    <div v-if="selectedHiscore === 'clue'"
                         class="mx-auto grid max-w-screen-xl grid-cols-4 place-items-center gap-y-2 px-4 pb-5 md:px-6">
                        <Link v-for="clue in clues" :key="clue"
                              :href="route('hiscores.clues.index', clue.slug)"
                              class="flex items-center justify-center rounded-lg p-2 space-x-2 w-[75%] hover:bg-beige-400 sm:justify-start lg:p-4 hover:dark:bg-gray-800"
                              :class="{ 'bg-beige-400 border border-beige-700 shadow dark:border-gray-700 dark:bg-gray-800': usePage().props.collectionSlugProp === clue.slug }">
                            <img :src="`/images/clue/${clue.slug}.png`"
                                 class="h-8 w-8 object-contain"/>
                            <div class="hidden font-semibold capitalize sm:block">{{ clue.name }}</div>
                        </Link>
                    </div>
                </div>
            </nav>


            <!--            <nav class="border-b border-gray-100 bg-white">-->
            <!--                &lt;!&ndash; Primary Navigation Menu &ndash;&gt;-->
            <!--                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">-->
            <!--                    <div class="flex h-16 justify-between">-->
            <!--                        <div class="flex">-->
            <!--                            &lt;!&ndash; Logo &ndash;&gt;-->
            <!--                            <div class="flex shrink-0 items-center">-->
            <!--                                <Link :href="route('dashboard')">-->
            <!--                                    <ApplicationMark class="block h-9 w-auto" />-->
            <!--                                </Link>-->
            <!--                            </div>-->

            <!--                            &lt;!&ndash; Navigation Links &ndash;&gt;-->
            <!--                            <div class="hidden space-x-8 sm:ms-10 sm:-my-px sm:flex">-->
            <!--                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">-->
            <!--                                    Dashboard-->
            <!--                                </NavLink>-->
            <!--                            </div>-->
            <!--                        </div>-->

            <!--                        <div class="hidden sm:ms-6 sm:flex sm:items-center">-->
            <!--                            <div class="relative ms-3">-->
            <!--                                &lt;!&ndash; Teams Dropdown &ndash;&gt;-->
            <!--                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">-->
            <!--                                    <template #trigger>-->
            <!--                                        <span class="inline-flex rounded-md">-->
            <!--                                            <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:bg-gray-50 focus:outline-none active:bg-gray-50">-->
            <!--                                                {{ $page.props.auth.user.current_team.name }}-->

            <!--                                                <svg class="h-4 w-4 ms-2 -me-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">-->
            <!--                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />-->
            <!--                                                </svg>-->
            <!--                                            </button>-->
            <!--                                        </span>-->
            <!--                                    </template>-->

            <!--                                    <template #content>-->
            <!--                                        <div class="w-60">-->
            <!--                                            &lt;!&ndash; Team Management &ndash;&gt;-->
            <!--                                            <div class="block px-4 py-2 text-xs text-gray-400">-->
            <!--                                                Manage Team-->
            <!--                                            </div>-->

            <!--                                            &lt;!&ndash; Team Settings &ndash;&gt;-->
            <!--                                            <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">-->
            <!--                                                Team Settings-->
            <!--                                            </DropdownLink>-->

            <!--                                            <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">-->
            <!--                                                Create New Team-->
            <!--                                            </DropdownLink>-->

            <!--                                            &lt;!&ndash; Team Switcher &ndash;&gt;-->
            <!--                                            <template v-if="$page.props.auth.user.all_teams.length > 1">-->
            <!--                                                <div class="border-t border-gray-200" />-->

            <!--                                                <div class="block px-4 py-2 text-xs text-gray-400">-->
            <!--                                                    Switch Teams-->
            <!--                                                </div>-->

            <!--                                                <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">-->
            <!--                                                    <form @submit.prevent="switchToTeam(team)">-->
            <!--                                                        <DropdownLink as="button">-->
            <!--                                                            <div class="flex items-center">-->
            <!--                                                                <svg v-if="team.id == $page.props.auth.user.current_team_id" class="h-5 w-5 text-green-400 me-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">-->
            <!--                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />-->
            <!--                                                                </svg>-->

            <!--                                                                <div>{{ team.name }}</div>-->
            <!--                                                            </div>-->
            <!--                                                        </DropdownLink>-->
            <!--                                                    </form>-->
            <!--                                                </template>-->
            <!--                                            </template>-->
            <!--                                        </div>-->
            <!--                                    </template>-->
            <!--                                </Dropdown>-->
            <!--                            </div>-->

            <!--                            &lt;!&ndash; Settings Dropdown &ndash;&gt;-->
            <!--                            <div class="relative ms-3">-->
            <!--                                <Dropdown align="right" width="48">-->
            <!--                                    <template #trigger>-->
            <!--                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex rounded-full border-2 border-transparent text-sm transition focus:border-gray-300 focus:outline-none">-->
            <!--                                            <img class="h-8 w-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">-->
            <!--                                        </button>-->

            <!--                                        <span v-else class="inline-flex rounded-md">-->
            <!--                                            <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:bg-gray-50 focus:outline-none active:bg-gray-50">-->
            <!--                                                {{ $page.props.auth.user.name }}-->

            <!--                                                <svg class="h-4 w-4 ms-2 -me-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">-->
            <!--                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />-->
            <!--                                                </svg>-->
            <!--                                            </button>-->
            <!--                                        </span>-->
            <!--                                    </template>-->

            <!--                                    <template #content>-->
            <!--                                        &lt;!&ndash; Account Management &ndash;&gt;-->
            <!--                                        <div class="block px-4 py-2 text-xs text-gray-400">-->
            <!--                                            Manage Account-->
            <!--                                        </div>-->

            <!--                                        <DropdownLink :href="route('profile.show')">-->
            <!--                                            Profile-->
            <!--                                        </DropdownLink>-->

            <!--                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">-->
            <!--                                            API Tokens-->
            <!--                                        </DropdownLink>-->

            <!--                                        <div class="border-t border-gray-200" />-->

            <!--                                        &lt;!&ndash; Authentication &ndash;&gt;-->
            <!--                                        <form @submit.prevent="logout">-->
            <!--                                            <DropdownLink as="button">-->
            <!--                                                Log Out-->
            <!--                                            </DropdownLink>-->
            <!--                                        </form>-->
            <!--                                    </template>-->
            <!--                                </Dropdown>-->
            <!--                            </div>-->
            <!--                        </div>-->

            <!--                        &lt;!&ndash; Hamburger &ndash;&gt;-->
            <!--                        <div class="flex items-center -me-2 sm:hidden">-->
            <!--                            <button class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none" @click="showingNavigationDropdown = ! showingNavigationDropdown">-->
            <!--                                <svg-->
            <!--                                    class="h-6 w-6"-->
            <!--                                    stroke="currentColor"-->
            <!--                                    fill="none"-->
            <!--                                    viewBox="0 0 24 24"-->
            <!--                                >-->
            <!--                                    <path-->
            <!--                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"-->
            <!--                                        stroke-linecap="round"-->
            <!--                                        stroke-linejoin="round"-->
            <!--                                        stroke-width="2"-->
            <!--                                        d="M4 6h16M4 12h16M4 18h16"-->
            <!--                                    />-->
            <!--                                    <path-->
            <!--                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"-->
            <!--                                        stroke-linecap="round"-->
            <!--                                        stroke-linejoin="round"-->
            <!--                                        stroke-width="2"-->
            <!--                                        d="M6 18L18 6M6 6l12 12"-->
            <!--                                    />-->
            <!--                                </svg>-->
            <!--                            </button>-->
            <!--                        </div>-->
            <!--                    </div>-->
            <!--                </div>-->

            <!--                &lt;!&ndash; Responsive Navigation Menu &ndash;&gt;-->
            <!--                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">-->
            <!--                    <div class="pt-2 pb-3 space-y-1">-->
            <!--                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">-->
            <!--                            Dashboard-->
            <!--                        </ResponsiveNavLink>-->
            <!--                    </div>-->

            <!--                    &lt;!&ndash; Responsive Settings Options &ndash;&gt;-->
            <!--                    <div class="border-t border-gray-200 pt-4 pb-1">-->
            <!--                        <div class="flex items-center px-4">-->
            <!--                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">-->
            <!--                                <img class="h-10 w-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">-->
            <!--                            </div>-->

            <!--                            <div>-->
            <!--                                <div class="text-base font-medium text-gray-800">-->
            <!--                                    {{ $page.props.auth.user.name }}-->
            <!--                                </div>-->
            <!--                                <div class="text-sm font-medium text-gray-500">-->
            <!--                                    {{ $page.props.auth.user.email }}-->
            <!--                                </div>-->
            <!--                            </div>-->
            <!--                        </div>-->

            <!--                        <div class="mt-3 space-y-1">-->
            <!--                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">-->
            <!--                                Profile-->
            <!--                            </ResponsiveNavLink>-->

            <!--                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">-->
            <!--                                API Tokens-->
            <!--                            </ResponsiveNavLink>-->

            <!--                            &lt;!&ndash; Authentication &ndash;&gt;-->
            <!--                            <form method="POST" @submit.prevent="logout">-->
            <!--                                <ResponsiveNavLink as="button">-->
            <!--                                    Log Out-->
            <!--                                </ResponsiveNavLink>-->
            <!--                            </form>-->

            <!--                            &lt;!&ndash; Team Management &ndash;&gt;-->
            <!--                            <template v-if="$page.props.jetstream.hasTeamFeatures">-->
            <!--                                <div class="border-t border-gray-200" />-->

            <!--                                <div class="block px-4 py-2 text-xs text-gray-400">-->
            <!--                                    Manage Team-->
            <!--                                </div>-->

            <!--                                &lt;!&ndash; Team Settings &ndash;&gt;-->
            <!--                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)" :active="route().current('teams.show')">-->
            <!--                                    Team Settings-->
            <!--                                </ResponsiveNavLink>-->

            <!--                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')">-->
            <!--                                    Create New Team-->
            <!--                                </ResponsiveNavLink>-->

            <!--                                &lt;!&ndash; Team Switcher &ndash;&gt;-->
            <!--                                <template v-if="$page.props.auth.user.all_teams.length > 1">-->
            <!--                                    <div class="border-t border-gray-200" />-->

            <!--                                    <div class="block px-4 py-2 text-xs text-gray-400">-->
            <!--                                        Switch Teams-->
            <!--                                    </div>-->

            <!--                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">-->
            <!--                                        <form @submit.prevent="switchToTeam(team)">-->
            <!--                                            <ResponsiveNavLink as="button">-->
            <!--                                                <div class="flex items-center">-->
            <!--                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id" class="h-5 w-5 text-green-400 me-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">-->
            <!--                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />-->
            <!--                                                    </svg>-->
            <!--                                                    <div>{{ team.name }}</div>-->
            <!--                                                </div>-->
            <!--                                            </ResponsiveNavLink>-->
            <!--                                        </form>-->
            <!--                                    </template>-->
            <!--                                </template>-->
            <!--                            </template>-->
            <!--                        </div>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--            </nav>-->

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
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
