<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useResourcePackIcon } from '@/composables/useResourcePackIcon';

defineProps({
    active: Boolean,
});

const page = usePage();
const { packIcon } = useResourcePackIcon();

// Category metadata drives the tab strip, the route, and the per-item icon so the
// three lists share one template. Each `icon`/`tab` is an <img> attrs object:
// skills resolve through packIcon (active pack → vanilla); bosses and clues have
// no pack equivalent so they point at /images — boss slugs are underscore from
// the hiscores API but the files use dashes, and clue tiers map to
// {tier}-treasure-trails.png.
const bossIcon = (slug) => ({ src: `/images/boss/${slug.replaceAll('_', '-')}.png` });
const clueIcon = (slug) => ({ src: `/images/clue/${slug.replace('clue_scrolls_', '')}-treasure-trails.png` });

const categories = [
    { key: 'skill', label: 'Skills', route: 'hiscores.skills.index', icon: (slug) => packIcon('skill', slug), tab: packIcon('tab', 'stats') },
    { key: 'boss', label: 'Bosses', route: 'hiscores.bosses.index', icon: bossIcon, tab: { src: '/images/boss/bosses.png' } },
    { key: 'clue', label: 'Clues', route: 'hiscores.clues.index', icon: clueIcon, tab: { src: '/images/clue/clues.png' } },
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
             class="dropdown-content z-50 mt-2 w-[32rem] max-w-[90vw] rounded border border-base-300 bg-base-200 p-4 shadow-xl resource-pack-dialog">
            <div class="mb-3 grid grid-cols-2 gap-1">
                <Link :href="route('hiscores.overall.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <img v-bind="packIcon('skill', 'overall')" class="h-5 w-5 object-contain" alt="">
                    Overall
                </Link>

                <Link :href="route('hiscores.loot.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <img v-bind="packIcon('tab', 'inventory')" class="h-5 w-5 object-contain" alt="">
                    Loot
                </Link>

                <Link :href="route('hiscores.diaries.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <img v-bind="packIcon('quests_tab', 'green_achievement_diaries_icon')" class="h-5 w-5 object-contain" alt="">
                    Achievements Diaries
                </Link>

                <Link :href="route('hiscores.collection-log.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <img v-bind="packIcon('quests_tab', 'combat_achievements_collections_logged')" class="h-5 w-5 object-contain" alt="">
                    Collection Log
                </Link>

                <Link :href="route('hiscores.combat-achievements.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <img v-bind="packIcon('quests_tab', 'combat_achievements_icon')" class="h-5 w-5 object-contain" alt="">
                    Combat Achievements
                </Link>
            </div>

            <div role="tablist" class="tabs tabs-boxed mb-3">
                <a v-for="category in categories" :key="category.key"
                   role="tab" class="tab gap-1.5"
                   :class="{ 'tab-active': activeKey === category.key }"
                   @click="activeKey = category.key">
                    <img v-bind="category.tab" class="h-4 w-4 object-contain" alt="">
                    {{ category.label }}
                </a>
            </div>

            <div class="grid max-h-80 grid-cols-2 gap-1 overflow-y-auto sm:grid-cols-3">
                <Link v-for="item in items" :key="item.slug"
                      :href="route(activeCategory.route, item.slug)"
                      class="flex items-center gap-2 rounded p-2 hover:bg-base-300">
                    <img v-bind="activeCategory.icon(item.slug)" class="h-6 w-6 object-contain" :alt="item.name">
                    <span class="truncate text-sm capitalize text-base-content">{{ item.name }}</span>
                </Link>
            </div>
        </div>
    </div>
</template>
