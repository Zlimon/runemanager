<script setup>
import { onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

/*
 * Light/dark switch, usable anywhere — including the auth screens, which don't
 * mount AppLayout. The Blade root sets the correct theme on first paint; this
 * keeps the <html> class + DaisyUI data-theme in sync across SPA navigations,
 * and persists the choice (account for users, cookie for guests).
 */
const page = usePage();

const apply = () => {
    const dark = page.props.dark_mode === true;
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.dataset.theme = dark ? 'runemanager-dark' : 'runemanager';
};

const toggle = () => {
    router.put(route('dark-mode.update'), { dark_mode: !page.props.dark_mode }, {
        preserveScroll: true,
        preserveState: true,
    });
};

onMounted(apply);
watch(() => page.props.dark_mode, apply);
</script>

<template>
    <button type="button"
            class="btn btn-ghost btn-circle btn-sm"
            :aria-label="page.props.dark_mode ? 'Switch to light mode' : 'Switch to dark mode'"
            @click="toggle">
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
</template>
