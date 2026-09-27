<script setup>
import { computed, ref } from 'vue';
import { Deferred, Link, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Cards/Card.vue';
import NewsCard from '@/Components/Cards/NewsCard.vue';
import FeedEventItem from '@/Components/Game/Feed/FeedEventItem.vue';
import { visibleFeedEvents } from '@/feed';
import { useResourcePackIcon } from '@/composables/useResourcePackIcon';
import { useEventColor } from '@/composables/useEventColor';

const { packIcon } = useResourcePackIcon();
const { eventHex } = useEventColor();

const props = defineProps({
    stats: { type: Object, default: () => ({ accounts: 0, members: 0, online: 0 }) },
    news: { type: Array, default: () => [] },
    announcements: { type: Array, default: () => [] },
    upcoming: { type: Array, default: () => [] },
    topAccounts: { type: Array, default: () => [] },
    feed: { type: Array, default: () => [] },
    // Deferred, and only sent while Wise Old Man is on.
    topGainers: { type: Array, default: null },
});

const page = usePage();
const instanceName = computed(() => page.props.instance?.name || page.props.app?.name || 'RuneManager');
const description = computed(() => page.props.instance?.description
    || 'Track, share, and compete on your Old School RuneScape progress.');
const bannerStyle = computed(() => page.props.instance?.banner_url
    ? `background-image: url('${page.props.instance.banner_url}')`
    : '');

const dt = (iso) => dayjs(iso).format('MMM D, YYYY h:mm A');
const eventDate = (iso) => dayjs(iso).format('ddd, MMM D · h:mm A');

// Everything is stored; this Live Feed surface applies the admin's display
// filters (milestone level-ups, minimum loot value).
const feedEvents = ref([...props.feed]);
const visibleFeed = computed(() => visibleFeedEvents(feedEvents.value, page.props.feed_level_milestones ?? [], page.props.feed_loot_min_value ?? 0));
const removeEvent = (id) => {
    feedEvents.value = feedEvents.value.filter((e) => e.id !== id);
};
</script>

<template>
    <AppLayout title="Home">
        <section class="bg-center bg-cover bg-no-repeat bg-neutral bg-blend-multiply"
                 :style="bannerStyle">
            <div class="px-4 mx-auto max-w-screen-xl text-center py-24 lg:py-32">
                <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-neutral-content md:text-5xl lg:text-6xl">
                    Welcome to {{ instanceName }}
                </h1>
                <p class="mb-8 whitespace-pre-line text-lg font-normal text-neutral-content/80 lg:text-xl sm:px-16 lg:px-48">
                    {{ description }}
                </p>
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

            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Main column: announcements + recent activity -->
                <div class="space-y-8 lg:col-span-2">
                    <!-- Latest news -->
                    <div v-if="news.length">
                        <div class="flex items-baseline justify-between">
                            <h2 class="text-xl font-bold">Latest news</h2>
                            <Link :href="route('news.index')" class="link link-hover text-sm text-base-content/70">
                                View all
                            </Link>
                        </div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <NewsCard v-for="post in news" :key="post.id" :post="post" />
                        </div>
                    </div>

                    <!-- Announcements -->
                    <div v-if="announcements.length">
                        <div class="flex items-baseline justify-between">
                            <h2 class="text-xl font-bold">Announcements</h2>
                            <Link :href="route('announcements.index')" class="link link-hover text-sm text-base-content/70">
                                View all
                            </Link>
                        </div>
                        <ul class="mt-4 space-y-2">
                            <li v-for="announcement in announcements" :key="announcement.id">
                                <Card padding="p-4">
                                    <h3 class="text-base font-semibold">{{ announcement.title }}</h3>
                                    <p class="mt-1 whitespace-pre-line text-sm text-base-content/80">
                                        {{ announcement.body }}
                                    </p>
                                    <p class="mt-2 text-xs text-base-content/60">
                                        {{ dt(announcement.created_at) }} · by {{ announcement.created_by.name }}
                                    </p>
                                </Card>
                            </li>
                        </ul>
                    </div>

                    <!-- Recent activity -->
                    <div>
                        <div class="flex items-baseline justify-between">
                            <h2 class="text-xl font-bold">Recent activity</h2>
                            <Link :href="route('feed.index')" class="link link-hover text-sm text-base-content/70">
                                Live Feed
                            </Link>
                        </div>

                        <ul v-if="visibleFeed.length" class="mt-4 space-y-2">
                            <li v-for="event in visibleFeed" :key="event.id">
                                <FeedEventItem :event="event" @deleted="removeEvent" />
                            </li>
                        </ul>
                        <Card v-else padding="p-8" class="mt-4 text-center text-base-content/60">
                            No activity yet — notable events will appear here as accounts sync.
                        </Card>
                    </div>
                </div>

                <!-- Side widgets -->
                <div class="space-y-8">
                    <!-- Upcoming events -->
                    <div>
                        <div class="flex items-baseline justify-between">
                            <h2 class="text-xl font-bold">Upcoming events</h2>
                            <Link :href="route('calendar.index')" class="link link-hover text-sm text-base-content/70">
                                Calendar
                            </Link>
                        </div>
                        <ul v-if="upcoming.length" class="mt-4 space-y-2">
                            <li v-for="event in upcoming" :key="event.id">
                                <Card padding="p-3">
                                    <div class="flex items-start gap-2">
                                        <span class="mt-1.5 inline-block h-2.5 w-2.5 shrink-0 rounded-full"
                                              :style="{ backgroundColor: eventHex(event) }"></span>
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

                    <!-- This week's top XP gainers (Wise Old Man) -->
                    <Deferred v-if="page.props.wom_enabled" data="topGainers">
                        <template #fallback>
                            <div class="h-40 animate-pulse rounded bg-base-300/40"></div>
                        </template>

                        <div v-if="topGainers?.length">
                            <div class="flex items-baseline justify-between">
                                <h2 class="text-xl font-bold">Top gainers this week</h2>
                                <Link :href="route('progress.index')" class="link link-hover text-sm text-base-content/70">
                                    Progress
                                </Link>
                            </div>
                            <Card padding="p-2" class="mt-4">
                                <ol class="divide-y divide-base-content/10">
                                    <li v-for="(gainer, index) in topGainers" :key="gainer.account_id">
                                        <Link :href="route('accounts.show', { account: gainer.username })"
                                              class="pack-row flex items-center gap-3 rounded px-2 py-2 hover:bg-base-300/40">
                                            <span class="w-5 text-center text-sm font-bold text-base-content/50">{{ index + 1 }}</span>
                                            <img v-if="gainer.account_type && gainer.account_type !== 'normal'"
                                                 :src="`/images/${gainer.account_type}.png`"
                                                 class="h-5 w-5 object-contain" alt="">
                                            <span class="min-w-0 flex-1 truncate font-semibold">{{ gainer.username }}</span>
                                            <span class="text-sm text-success">+{{ Math.round(gainer.gained).toLocaleString('en-US') }} xp</span>
                                        </Link>
                                    </li>
                                </ol>
                            </Card>
                        </div>
                    </Deferred>

                    <!-- Top accounts -->
                    <div v-if="topAccounts.length">
                        <div class="flex items-baseline justify-between">
                            <h2 class="text-xl font-bold">Top accounts</h2>
                            <Link :href="route('hiscores.overall.index')" class="link link-hover text-sm text-base-content/70">
                                Hiscores
                            </Link>
                        </div>
                        <Card padding="p-2" class="mt-4">
                            <ol class="divide-y divide-base-content/10">
                                <li v-for="(account, index) in topAccounts" :key="account.id">
                                    <Link :href="route('accounts.show', { account: account.username })"
                                          class="pack-row flex items-center gap-3 rounded px-2 py-2 hover:bg-base-300/40">
                                        <span class="w-5 text-center text-sm font-bold text-base-content/50">{{ index + 1 }}</span>
                                        <img v-if="account.account_type && account.account_type !== 'normal'"
                                             :src="`/images/${account.account_type}.png`"
                                             class="h-5 w-5 object-contain" alt="">
                                        <span class="min-w-0 flex-1 truncate font-semibold">{{ account.username }}</span>
                                        <span class="flex items-center gap-1 text-sm text-base-content/70">
                                            <img class="h-5 w-5 object-contain" alt="" v-bind="packIcon('skill', 'overall')">
                                            {{ account.level }}
                                        </span>
                                    </Link>
                                </li>
                            </ol>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
