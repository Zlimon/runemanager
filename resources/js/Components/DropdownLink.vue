<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String,
    as: String,
});
</script>

<!-- `nav-menu-link` is app.css's hook for repainting the hovered/focused row
     from the pack palette; the DaisyUI classes are what renders with no pack. -->
<template>
    <div>
        <button v-if="as == 'button'" type="submit" class="nav-menu-link block w-full px-4 py-2 text-start text-sm leading-5 text-base-content hover:bg-base-200 focus:outline-none focus:bg-base-200 transition duration-150 ease-in-out">
            <slot />
        </button>

        <a v-else-if="as =='a'" :href="href" class="nav-menu-link block px-4 py-2 text-sm leading-5 text-base-content hover:bg-base-200 focus:outline-none focus:bg-base-200 transition duration-150 ease-in-out">
            <slot />
        </a>

        <Link v-else :href="href" class="nav-menu-link block px-4 py-2 text-sm leading-5 text-base-content hover:bg-base-200 focus:outline-none focus:bg-base-200 transition duration-150 ease-in-out">
            <slot />
        </Link>
    </div>
</template>
