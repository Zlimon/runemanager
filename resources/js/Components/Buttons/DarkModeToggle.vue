<script setup>
import { onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

/*
 * Light/dark switch, usable anywhere — including the auth screens, which don't
 * mount AppLayout. The Blade root sets the correct theme on first paint; this
 * keeps the <html> class + DaisyUI data-theme in sync across SPA navigations,
 * and persists the choice (account for users, cookie for guests).
 *
 * Only the untextured "no resource pack" interface supports both modes, so the
 * button hides itself when the server says the viewer can't toggle — Default
 * Vanilla ships one fixed scheme. The theme name itself comes from the server
 * rather than being re-derived here, so the precedence rules live in one place
 * (App\Support\Instance::themeFor).
 */
const page = usePage();

const apply = () => {
    document.documentElement.classList.toggle('dark', page.props.dark_mode === true);
    document.documentElement.dataset.theme = page.props.theme;
};

const toggle = () => {
    router.put(route('dark-mode.update'), { dark_mode: !page.props.dark_mode }, {
        preserveScroll: true,
        preserveState: true,
    });
};

onMounted(apply);
watch(() => [page.props.dark_mode, page.props.theme], apply);
</script>

<template>
    <button v-if="page.props.can_toggle_dark_mode"
            type="button"
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
