<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import Card from '@/Components/Card.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';

defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
    anonymized: { type: Boolean, default: false },
    stats: { type: Object, default: () => ({ accounts: 0, members: 0, online: 0 }) },
    upcoming: { type: Array, default: () => [] },
    topAccounts: { type: Array, default: () => [] },
});

const page = usePage();
const instanceName = computed(() => page.props.instance?.name || page.props.app?.name || 'RuneManager');
const description = computed(() => page.props.instance?.description
    || 'Track, share, and compete on your Old School RuneScape progress.');
const bannerStyle = computed(() => page.props.instance?.banner_url
    ? `background-image: url('${page.props.instance.banner_url}')`
    : '');

const eventDate = (iso) => dayjs(iso).format('ddd, MMM D · h:mm A');
</script>

<template>
    <Head title="Home" />

    <div class="min-h-screen pack-bg text-base-content">
        <!-- Guest top bar -->
        <header class="bg-beige-600 dark:bg-gray-900">
            <div class="mx-auto flex max-w-screen-xl items-center justify-between p-4">
                <Link :href="route('home')" class="flex items-center space-x-3">
                    <img v-if="page.props.instance?.logo_url" :src="page.props.instance.logo_url"
                         :alt="instanceName" class="h-12 w-auto object-contain md:h-16">
                    <span v-else class="self-center whitespace-nowrap text-4xl font-bold dark:text-white md:text-5xl"
                          style="font-family: 'runescape-smooth', sans-serif">
                        {{ instanceName }}
                    </span>
                </Link>
                <div class="flex items-center gap-2">
                    <DarkModeToggle />
                    <Link v-if="canLogin" :href="route('login')" class="btn btn-sm">Log in</Link>
                    <Link v-if="canRegister" :href="route('register')" class="btn btn-sm btn-neutral">Register</Link>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="bg-center bg-cover bg-no-repeat bg-[url('/images/background_4.png')] bg-gray-700 bg-blend-multiply"
                 :style="bannerStyle">
            <div class="px-4 mx-auto max-w-screen-xl text-center py-24 lg:py-40">
                <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-white md:text-5xl lg:text-6xl">
                    Welcome to {{ instanceName }}
                </h1>
                <p class="mb-8 whitespace-pre-line text-lg font-normal text-gray-300 lg:text-xl sm:px-16 lg:px-48">
                    {{ description }}
                </p>
                <div v-if="canLogin || canRegister"
                     class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                    <Link v-if="canRegister" :href="route('register')" class="btn btn-neutral">
                        Get started
                    </Link>
                    <Link v-if="canLogin" :href="route('login')" class="btn">
                        Log in
                    </Link>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-screen-xl px-4 py-10 sm:px-6 lg:px-8">
            <!-- Headline counts -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card padding="p-5" class="text-center">
                    <p class="text-3xl font-extrabold">{{ stats.accounts }}</p>
                    <p class="mt-1 text-sm text-base-content/60">Accounts</p>
                </Card>
                <Card padding="p-5" class="text-center">
                    <p class="text-3xl font-extrabold">{{ stats.members }}</p>
                    <p class="mt-1 text-sm text-base-content/60">Members</p>
                </Card>
                <Card padding="p-5" class="text-center">
                    <p class="flex items-center justify-center gap-2 text-3xl font-extrabold">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-success"></span>
                        {{ stats.online }}
                    </p>
                    <p class="mt-1 text-sm text-base-content/60">Online now</p>
                </Card>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2">
                <!-- Top accounts -->
                <div v-if="topAccounts.length">
                    <h2 class="text-xl font-bold">Top accounts</h2>
                    <Card padding="p-2" class="mt-4">
                        <ol class="divide-y divide-base-content/10">
                            <li v-for="(account, index) in topAccounts" :key="index">
                                <component :is="anonymized ? 'div' : Link"
                                           :href="anonymized ? undefined : route('accounts.show', { account: account.username })"
                                           class="flex items-center gap-3 rounded px-2 py-2"
                                           :class="anonymized ? '' : 'hover:bg-base-300/40'">
                                    <span class="w-5 text-center text-sm font-bold text-base-content/50">{{ index + 1 }}</span>
                                    <img v-if="account.account_type && account.account_type !== 'normal'"
                                         :src="`/images/${account.account_type}.png`"
                                         class="h-5 w-5 object-contain" alt="">
                                    <span class="min-w-0 flex-1 truncate font-semibold">{{ account.username }}</span>
                                    <span class="flex items-center gap-1 text-sm text-base-content/70">
                                        <img class="h-5 w-5 object-contain" src="/images/skill/overall.png" alt="">
                                        {{ account.level }}
                                    </span>
                                </component>
                            </li>
                        </ol>
                    </Card>
                </div>

                <!-- Upcoming events -->
                <div>
                    <h2 class="text-xl font-bold">Upcoming events</h2>
                    <ul v-if="upcoming.length" class="mt-4 space-y-2">
                        <li v-for="event in upcoming" :key="event.id">
                            <Card padding="p-3">
                                <div class="flex items-start gap-2">
                                    <span class="mt-1.5 inline-block h-2.5 w-2.5 shrink-0 rounded-full"
                                          :style="{ backgroundColor: event.color || '#DC8850' }"></span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold">{{ event.title }}</p>
                                        <p class="text-xs text-base-content/60">{{ eventDate(event.starts_at) }}</p>
                                    </div>
                                </div>
                            </Card>
                        </li>
                    </ul>
                    <Card v-else padding="p-4" class="mt-4 text-center text-sm text-base-content/60">
                        Nothing scheduled.
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>
