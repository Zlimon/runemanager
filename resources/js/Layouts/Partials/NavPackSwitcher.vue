<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import NavDropdownLink from '@/Layouts/Partials/NavDropdownLink.vue';

/*
 * Quick appearance switching from the account nav dropdown, so changing pack
 * does not mean a trip to Settings → Appearance.
 *
 * One row that flies out to the left on hover, rather than the packs listed
 * inline: the dropdown already carries the viewer's accounts and their account
 * actions, and a list that grows with every pack they install would crowd both.
 * Opening leftwards because this dropdown is the last in the nav and already
 * sits against the right edge of the window.
 *
 * The list is the viewer's own installs (`my_packs`), which always includes
 * whatever they currently have selected even if somebody else installed it —
 * see ResourcePack::navListFor(). Installing a new pack still happens on the
 * Appearance page, which this links through to.
 */
const page = usePage();

const packs = computed(() => page.props.my_packs ?? []);
const preference = computed(() => page.props.pack_preference);

const open = ref(false);

// Mark against the viewer's stored *preference*, not the pack in effect: a
// member who picked the pack that happens to also be the instance default is
// in a different state from one who is simply following that default, and
// only the preference distinguishes them.
const isActivePack = (id) => preference.value?.disabled !== true && preference.value?.id === id;
const followsDefault = computed(
    () => preference.value?.disabled !== true && preference.value?.id === null,
);
const hasNoPack = computed(() => preference.value?.disabled === true);

/*
 * A pack's stylesheet is a server-rendered <link> in app.blade.php, so the new
 * one only appears on a full document load — an Inertia visit would swap the
 * markup and leave the old pack's CSS behind. Same reason GuestPackPicker and
 * the Appearance page reload after saving.
 */
const choose = (value) => {
    router.put(route('user.resource-pack.update'), { resource_pack_id: value }, {
        preserveScroll: true,
        onSuccess: () => window.location.reload(),
    });
};

const itemClass = (active) => [
    'nav-menu-link block w-full rounded px-3 py-2 text-left text-base-content hover:bg-base-300',
    active ? 'is-active bg-base-300 font-semibold' : '',
];
</script>

<template>
    <!-- `focusin`/`focusout` rather than hover alone, so the submenu is reachable
         by keyboard and on touch, where there is no hover at all. -->
    <div v-if="preference" class="relative"
         @mouseenter="open = true"
         @mouseleave="open = false"
         @focusin="open = true"
         @focusout="open = false">
        <button type="button"
                class="nav-menu-link flex w-full items-center justify-between gap-2 rounded px-3 py-2 text-left text-base-content hover:bg-base-300"
                :class="{ 'is-active bg-base-300': open }"
                aria-haspopup="true" :aria-expanded="open"
                @click="open = !open">
            <!-- No current-pack label beside it: the panel is only as wide as
                 the account names above, so a second piece of text wraps the
                 row onto two lines. The flyout marks what is active instead. -->
            <span class="whitespace-nowrap">Resource pack</span>
            <svg class="h-2.5 w-2.5 shrink-0 text-base-content/60" aria-hidden="true"
                 fill="none" viewBox="0 0 6 10" xmlns="http://www.w3.org/2000/svg">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="M5 1 1 5l4 4" />
            </svg>
        </button>

        <!-- The wrapper's `pr-4` clears the parent panel's own padding and doubles
             as an invisible bridge, so the submenu does not close while the
             pointer travels to it. -->
        <div v-show="open" class="absolute right-full top-0 z-50 pr-4">
            <div class="w-52 space-y-1 rounded border border-base-300 bg-base-200 p-3 text-base-content shadow-xl resource-pack-dialog">
                <button v-for="pack in packs" :key="pack.id" type="button"
                        :class="itemClass(isActivePack(pack.id))"
                        @click="choose(pack.id)">
                    <span class="flex items-center gap-2">
                        <img v-if="pack.icon_url" :src="pack.icon_url" class="h-4 w-4 object-contain" alt="">
                        {{ pack.alias }}
                    </span>
                </button>

                <button type="button" :class="itemClass(followsDefault)" @click="choose(null)">
                    Instance default
                </button>

                <!-- The untextured interface. Worth its place here rather than
                     only on the Appearance page: it is the one appearance that
                     offers the light/dark toggle, so it is how a member reaches
                     dark mode under a pack that forbids it. -->
                <button type="button" :class="itemClass(hasNoPack)" @click="choose('none')">
                    No resource pack
                </button>

                <NavDropdownLink :href="route('themes.index')" :active="route().current('themes.*')">
                    Browse packs…
                </NavDropdownLink>
            </div>
        </div>
    </div>
</template>

