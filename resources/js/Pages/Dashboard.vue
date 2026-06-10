<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

defineProps({
    announcements: { type: Array, default: () => [] },
});

const page = usePage();
const instanceName = computed(() => page.props.instance?.name || page.props.app?.name || 'RuneManager');
const description = computed(() => page.props.instance?.description
    || 'Track, share, and compete on your Old School RuneScape progress.');
const bannerStyle = computed(() => page.props.instance?.banner_url
    ? `background-image: url('${page.props.instance.banner_url}')`
    : '');

const dt = (iso) => dayjs(iso).format('MMM D, YYYY h:mm A');
</script>

<template>
    <AppLayout title="Dashboard">
<!--        <template #header>-->
<!--            <h2 class="font-semibold text-xl text-gray-800 leading-tight">-->
<!--                Dashboard-->
<!--            </h2>-->
<!--        </template>-->

        <section class="bg-center bg-cover bg-no-repeat bg-[url('/images/background_4.png')] bg-gray-700 bg-blend-multiply"
                 :style="bannerStyle">
            <div class="px-4 mx-auto max-w-screen-xl text-center py-24 lg:py-40">
                <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-white md:text-5xl lg:text-6xl">
                    Welcome to {{ instanceName }}
                </h1>
                <p class="mb-8 whitespace-pre-line text-lg font-normal text-gray-300 lg:text-xl sm:px-16 lg:px-48">
                    {{ description }}
                </p>
            </div>
        </section>

        <div v-if="announcements.length" class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="flex items-baseline justify-between">
                    <h2 class="header-chatbox-sword text-xl font-bold">Announcements</h2>
                    <Link :href="route('announcements.index')" class="link link-hover text-sm text-base-content/70">
                        View all
                    </Link>
                </div>

                <ul class="mt-4 space-y-4">
                    <li v-for="announcement in announcements" :key="announcement.id">
                        <Card padding="p-4">
                            <h3 class="text-base font-semibold">{{ announcement.title }}</h3>
                            <p class="mt-2 whitespace-pre-line text-sm text-base-content/80">
                                {{ announcement.body }}
                            </p>
                            <p class="mt-2 text-xs text-base-content/60">
                                {{ dt(announcement.created_at) }} · by {{ announcement.created_by.name }}
                            </p>
                        </Card>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
