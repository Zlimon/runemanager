<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useResourcePackIcon } from '@/composables/useResourcePackIcon';

defineProps({
    active: Boolean,
});

const page = usePage();
const { packIcon, skillIcon, onIconError } = useResourcePackIcon();

// Category metadata drives the tab strip, the route, and the per-item icon path
// so the three lists share one template instead of three near-identical blocks.
// Skills prefer the active pack's icon (bundled fallback via onIconError); bosses
// and clues have no pack icons, so they use the local /images files — boss slugs
// are underscore from the hiscores API but the files use dashes, and clue tiers
// map to {tier}-treasure-trails.png. tabIcon/tabFallback drive the category tab
// strip, mirroring the Skills/Bosses/Clues tabs on the account profile.
const localSkill = (slug) => `/images/skill/${slug}.png`;
const localBoss = (slug) => `/images/boss/${slug.replaceAll('_', '-')}.png`;
const localClue = (slug) => `/images/clue/${slug.replace('clue_scrolls_', '')}-treasure-trails.png`;

const categories = [
    { key: 'skill', label: 'Skills', route: 'hiscores.skills.index', icon: (slug) => skillIcon(slug) ?? localSkill(slug), fallback: localSkill, tabIcon: packIcon('tab', 'stats') ?? '/images/skill/overall.png', tabFallback: '/images/skill/overall.png' },
    { key: 'boss', label: 'Bosses', route: 'hiscores.bosses.index', icon: localBoss, fallback: localBoss, tabIcon: '/images/boss/bosses.png', tabFallback: '/images/boss/bosses.png' },
    { key: 'clue', label: 'Clues', route: 'hiscores.clues.index', icon: localClue, fallback: localClue, tabIcon: '/images/clue/clues.png', tabFallback: '/images/clue/clues.png' },
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
                    <img :src="skillIcon('overall') ?? '/images/skill/overall.png'"
                         class="h-5 w-5 object-contain" alt=""
                         @error="onIconError($event, '/images/skill/overall.png')">
                    Overall
                </Link>

                <Link :href="route('hiscores.loot.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <img :src="packIcon('tab', 'inventory')" class="h-5 w-5 object-contain" alt=""
                         @error="onIconError($event, null)">
                    Loot
                </Link>

                <Link :href="route('hiscores.diaries.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <img :src="packIcon('quests_tab', 'green_achievement_diaries_icon') ?? '/images/journal/diaries.png'"
                         class="h-5 w-5 object-contain" alt=""
                         @error="onIconError($event, '/images/journal/diaries.png')">
                    Achievements Diaries
                </Link>

                <Link :href="route('hiscores.collection-log.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <img :src="packIcon('quests_tab', 'combat_achievements_collections_logged') ?? '/images/journal/diaries.png'"
                         class="h-5 w-5 object-contain" alt=""
                         @error="onIconError($event, '/images/journal/diaries.png')">
                    Collection Log
                </Link>

                <Link :href="route('hiscores.combat-achievements.index')"
                      class="flex items-center gap-2 rounded p-2 font-semibold hover:bg-base-300">
                    <img :src="packIcon('quests_tab', 'combat_achievements_icon')"
                         class="h-5 w-5 object-contain" alt=""
                         @error="onIconError($event, null)">
                    Combat Achievements
                </Link>
            </div>

            <div role="tablist" class="tabs tabs-boxed mb-3">
                <a v-for="category in categories" :key="category.key"
                   role="tab" class="tab gap-1.5"
                   :class="{ 'tab-active': activeKey === category.key }"
                   @click="activeKey = category.key">
                    <img :src="category.tabIcon" class="h-4 w-4 object-contain" alt=""
                         @error="onIconError($event, category.tabFallback)">
                    {{ category.label }}
                </a>
            </div>

            <div class="grid max-h-80 grid-cols-2 gap-1 overflow-y-auto sm:grid-cols-3">
                <Link v-for="item in items" :key="item.slug"
                      :href="route(activeCategory.route, item.slug)"
                      class="flex items-center gap-2 rounded p-2 hover:bg-base-300">
                    <img :src="activeCategory.icon(item.slug)" class="h-6 w-6 object-contain" :alt="item.name"
                         @error="onIconError($event, activeCategory.fallback(item.slug))">
                    <span class="truncate text-sm capitalize text-base-content">{{ item.name }}</span>
                </Link>
            </div>
        </div>
    </div>
</template>
