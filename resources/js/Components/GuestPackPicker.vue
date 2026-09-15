<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

/*
 * Resource-pack choice for logged-out visitors.
 *
 * Members set this on the Appearance page, which is auth-gated; guests have no
 * account row to store `disable_resource_pack` on, so their choice lives in a
 * cookie (see App\Support\Instance::RESOURCE_PACK_COOKIE). Without this control
 * a guest could never leave Default Vanilla — and Default Vanilla is the one
 * appearance with no light/dark toggle, so they'd be locked out of both.
 *
 * Only the two states that matter publicly are offered; picking a specific
 * installed pack stays a member feature.
 */
const page = usePage();

const isGuest = computed(() => !page.props.auth?.user);
const noPack = computed(() => page.props.no_pack === true);

/*
 * The pack stylesheet is a server-rendered <link>, so swapping it needs a full
 * page load — same reason the Appearance page reloads after saving.
 */
const choose = (value) => {
    router.put(route('user.resource-pack.update'), { resource_pack_id: value }, {
        preserveScroll: true,
        onSuccess: () => window.location.reload(),
    });
};
</script>

<template>
    <div v-if="isGuest" class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-ghost btn-circle btn-sm"
             aria-label="Change appearance">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <circle cx="13.5" cy="6.5" r="1.5" />
                <circle cx="17.5" cy="10.5" r="1.5" />
                <circle cx="8.5" cy="7.5" r="1.5" />
                <circle cx="6.5" cy="12.5" r="1.5" />
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 2a10 10 0 0 0 0 20 2 2 0 0 0 2-2 2 2 0 0 1 2-2h2a4 4 0 0 0 4-4 10 10 0 0 0-10-10Z" />
            </svg>
        </div>

        <ul tabindex="0"
            class="dropdown-content menu z-50 mt-2 w-56 rounded border border-base-300 bg-base-200 p-2 shadow-xl">
            <li class="menu-title text-xs">Appearance</li>
            <li>
                <button type="button" :class="{ 'active font-semibold': !noPack }"
                        @click="choose(null)">
                    Default Vanilla
                </button>
            </li>
            <li>
                <button type="button" :class="{ 'active font-semibold': noPack }"
                        @click="choose('none')">
                    No resource pack
                </button>
            </li>
        </ul>
    </div>
</template>
