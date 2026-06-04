<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
});

const classes = computed(() => {
    return props.active
        ? 'block py-2 px-3 rounded text-accent font-semibold md:bg-transparent md:p-0'
        : 'block py-2 px-3 rounded text-base-content hover:bg-base-200 md:hover:bg-transparent md:hover:text-accent md:p-0';
});
</script>

<template>
    <div>
        <button v-if="as == 'button'" :class="classes" class="w-full text-start">
            <slot />
        </button>

        <Link v-else :href="href" :class="classes">
            <slot />
        </Link>
    </div>
</template>
