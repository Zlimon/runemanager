<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    active: Boolean,
});

const page = usePage();

// Category metadata drives the tab strip, the route, and the icon path so the
// three lists share one template instead of three near-identical blocks. Icon
// paths mirror the bundled /images conventions: skills are plain png, boss
// slugs are underscore from the hiscores API but the files use dashes, and clue
// tiers map to {tier}-treasure-trails.png.
const categories = [
    { key: 'skill', label: 'Skills', route: 'hiscores.skills.index', icon: (slug) => `/images/skill/${slug}.png` },
    { key: 'boss', label: 'Bosses', route: 'hiscores.bosses.index', icon: (slug) => `/images/boss/${slug.replaceAll('_', '-')}.png` },
    { key: 'clue', label: 'Clues', route: 'hiscores.clues.index', icon: (slug) => `/images/clue/${slug.replace('clue_scrolls_', '')}-treasure-trails.png` },
];

const lists = computed(() => ({
    skill: page.props.skills ?? [],
    boss: page.props.bosses ?? [],
    clue: page.props.clues ?? [],
}));

const activeKey = ref('skill');
const activeCategory = computed(() => categories.find((c) => c.key === activeKey.value));
const items = computed(() => lists.value[activeKey.value]);
</script>

<template>
    <div class="dropdown">
        <div tabindex="0" role="button"
             class="flex items-center gap-1 py-2 px-3 rounded cursor-pointer md:p-0"
             :class="active ? 'text-accent font-semibold' : 'text-base-content hover:text-accent'">
            Hiscores
            <svg class="h-2.5 w-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                 fill="none" viewBox="0 0 10 6">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="m1 1 4 4 4-4" />
            </svg>
        </div>

        <div tabindex="0"
             class="dropdown-content z-50 mt-2 w-[32rem] max-w-[90vw] rounded border border-base-300 bg-base-200 p-3 shadow-xl resource-pack-dialog">
            <div class="mb-3 grid grid-cols-2 gap-1">
                <Link :href="route('hiscores.overall.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <img src="/images/skill/overall.png" class="h-5 w-5 object-contain" alt=""
                         onerror="this.style.display='none'">
                    Overall
                </Link>

                <Link :href="route('hiscores.loot.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <svg class="h-5 w-5 text-accent" viewBox="0 0 24 24" fill="currentColor"
                         xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="12" cy="6" rx="8" ry="3" />
                        <path d="M4 6v4c0 1.66 3.58 3 8 3s8-1.34 8-3V6c0 1.66-3.58 3-8 3S4 7.66 4 6Z" />
                        <path d="M4 12v4c0 1.66 3.58 3 8 3s8-1.34 8-3v-4c0 1.66-3.58 3-8 3s-8-1.34-8-3Z" opacity="0.7" />
                    </svg>
                    Loot
                </Link>

                <Link :href="route('hiscores.diaries.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <svg class="h-5 w-5 text-secondary" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 5a2 2 0 0 1 2-2h12v18H6a2 2 0 0 1-2-2V5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6M9 11h6" />
                    </svg>
                    Diaries
                </Link>

                <Link :href="route('hiscores.collection-log.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <svg class="h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 4h11l5 5v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L15 10" />
                    </svg>
                    Collection Log
                </Link>
            </div>

            <div role="tablist" class="tabs tabs-boxed mb-3">
                <a v-for="category in categories" :key="category.key"
                   role="tab" class="tab"
                   :class="{ 'tab-active': activeKey === category.key }"
                   @click="activeKey = category.key">
                    {{ category.label }}
                </a>
            </div>

            <div class="grid max-h-80 grid-cols-2 gap-1 overflow-y-auto sm:grid-cols-3">
                <Link v-for="item in items" :key="item.slug"
                      :href="route(activeCategory.route, item.slug)"
                      class="flex items-center gap-2 rounded p-2 hover:bg-base-300">
                    <img :src="activeCategory.icon(item.slug)" class="h-6 w-6 object-contain" :alt="item.name">
                    <span class="truncate text-sm capitalize text-base-content">{{ item.name }}</span>
                </Link>
            </div>
        </div>
    </div>
</template>
