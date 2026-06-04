<script setup>
import { Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

defineProps({
    announcements: { type: Array, default: () => [] },
});

const dt = (iso) => dayjs(iso).format('MMM D, YYYY h:mm A');
</script>

<template>
    <AppLayout title="Dashboard">
<!--        <template #header>-->
<!--            <h2 class="font-semibold text-xl text-gray-800 leading-tight">-->
<!--                Dashboard-->
<!--            </h2>-->
<!--        </template>-->

        <section class="bg-center bg-no-repeat bg-[url('/images/background_4.png')] bg-gray-700 bg-blend-multiply">
            <div class="px-4 mx-auto max-w-screen-xl text-center py-24 lg:py-56">
                <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-white md:text-5xl lg:text-6xl">
                    Welcome to -app name-
                </h1>
                <p class="mb-8 text-lg font-normal text-gray-300 lg:text-xl sm:px-16 lg:px-48">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                </p>
                <div class="flex flex-col space-y-4 sm:flex-row sm:justify-center sm:space-y-0">
                    <a href="#"
                       class="inline-flex justify-center items-center py-3 px-5 text-base font-medium text-center text-white rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900">
                        Login
                        <svg class="w-3.5 h-3.5 ms-2 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M1 5h12m0 0L9 1m4 4L9 9"/>
                        </svg>
                    </a>
                    <a href="#"
                       class="inline-flex justify-center hover:text-gray-900 items-center py-3 px-5 sm:ms-4 text-base font-medium text-center text-white rounded-lg border border-white hover:bg-gray-100 focus:ring-4 focus:ring-gray-400">
                        Register
                    </a>
                </div>
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
