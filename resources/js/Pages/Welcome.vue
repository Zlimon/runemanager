<script setup>
import {Head, Link} from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome"/>
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

    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-2">
            <div class="col-span-1 grid grid-cols-2 bg-red-500">
                <div class="col-span-1"
                     style="background: url('/images/scenery_1.png'); background-size: cover; background-position: center; height: 15rem;"/>
                <div class="col-span-1">
                    <h2 class="text-4xl text-center header-chatbox-sword">
                        About
                    </h2>
                    <div class="m-4 text-start">
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        </p>
                    </div>
                </div>

                <div class="col-span-1">
                    <h2 class="text-4xl text-center header-chatbox-sword">
                        Requirements
                    </h2>
                    <div class="m-4 text-end">
                        <ul>
                            <li>105+ Combat</li>
                            <li>85+ Magic</li>
                            <li>85+ Ranged</li>
                        </ul>
                    </div>
                </div>
                <div class="col-span-1"
                     style="background: url('/images/scenery_4.png'); background-size: cover; background-position: center; height: 15rem;"/>
            </div>

            <div class="col-span-1 bg-blue-500">
                Calendar
            </div>
        </div>
    </div>
</template>
