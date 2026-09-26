<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import NavDropdownLink from '@/Layouts/Partials/NavDropdownLink.vue';
import NavSubmenu from '@/Layouts/Partials/NavSubmenu.vue';

/*
 * Quick appearance switching from the account nav dropdown, so changing pack
 * does not mean a trip to Settings → Appearance.
 *
 * The list is the viewer's own installs (`my_packs`), which always includes
 * whatever they currently have selected even if somebody else installed it —
 * see ResourcePack::navListFor(). Installing a new pack still happens on the
 * Appearance page, which this links through to.
 */
const page = usePage();

const packs = computed(() => page.props.my_packs ?? []);
const preference = computed(() => page.props.pack_preference);

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
    <NavSubmenu v-if="preference" label="Resource pack">
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

        <!-- The untextured interface. Worth its place here rather than only on
             the Appearance page: it is the one appearance that offers the
             light/dark toggle, so it is how a member reaches dark mode under a
             pack that forbids it. -->
        <button type="button" :class="itemClass(hasNoPack)" @click="choose('none')">
            No resource pack
        </button>

        <NavDropdownLink :href="route('themes.index')" :active="route().current('themes.*')">
            Browse packs…
        </NavDropdownLink>
    </NavSubmenu>
</template>
