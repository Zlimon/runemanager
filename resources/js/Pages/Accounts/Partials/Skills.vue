<script setup>
import { computed, ref } from "vue";
import { Link } from '@inertiajs/vue3';
import TabbedCard from "@/Components/TabbedCard.vue";
import { useResourcePackIcon } from '@/composables/useResourcePackIcon';

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
});

const { packIcon } = useResourcePackIcon();
const activeTab = ref('skills');
const activeKey = ref(null);

/*
 * Icons resolve to <img> attrs: skills via packIcon (active pack → vanilla);
 * bosses and clues have no pack equivalent so they point straight at /images.
 * The /images/{boss,clue}/ files use dash-separated slugs (e.g. `abyssal-sire`),
 * while the OSRS hiscores API ships underscore slugs, so boss converts on the
 * way out; clue tiers map to `{tier}-treasure-trails.png`.
 */
const bossIcon = (slug) => ({ src: `/images/boss/${slug.replace(/_/g, '-')}.png` });
const clueIcon = (slug) => ({ src: `/images/clue/${slug.replace(/^clue_scrolls_/, '')}-treasure-trails.png` });

// Skills includes a synthetic "total" tile prepended to the real skill list so
// the user sees their overall first. Total uses account.{level,xp} from the
// denormalised columns rather than entries['skills']['overall'].
const skillCells = computed(() => [
    {
        href: route('accounts.index'),
        slug: 'overall',
        label: props.account.level,
        tooltip: { name: 'Total level', value: props.account.xp.toLocaleString('en-US') },
        icon: packIcon('skill', 'overall'),
    },
    ...props.account.skills.map((skill) => ({
        href: route('hiscores.skills.index', skill.slug),
        slug: skill.slug,
        label: skill.level,
        tooltip: { name: skill.name, value: skill.xp.toLocaleString('en-US') },
        icon: packIcon('skill', skill.slug),
    })),
]);

const bossCells = computed(() => (props.account.bosses ?? []).map((boss) => ({
    href: route('hiscores.bosses.index', boss.slug),
    slug: boss.slug,
    label: boss.score > 0 ? boss.score.toLocaleString('en-US') : '—',
    tooltip: {
        name: boss.name,
        value: `Rank ${boss.rank > 0 ? boss.rank.toLocaleString('en-US') : '—'}`,
    },
    icon: bossIcon(boss.slug),
})));

const clueCells = computed(() => (props.account.clues ?? []).map((clue) => ({
    href: route('hiscores.clues.index', clue.slug),
    slug: clue.slug,
    label: clue.score > 0 ? clue.score.toLocaleString('en-US') : '—',
    tooltip: {
        name: `${clue.name} clues`,
        value: `Rank ${clue.rank > 0 ? clue.rank.toLocaleString('en-US') : '—'}`,
    },
    icon: clueIcon(clue.slug),
})));

const cells = computed(() => {
    if (activeTab.value === 'bosses') {
        return bossCells.value;
    }
    if (activeTab.value === 'clues') {
        return clueCells.value;
    }
    return skillCells.value;
});

const tabs = [
    { key: 'skills', label: 'Skills', icon: packIcon('tab', 'stats') },
    { key: 'bosses', label: 'Bosses', icon: { src: '/images/boss/bosses.png' } },
    { key: 'clues', label: 'Clues', icon: { src: '/images/clue/clues.png' } },
];
</script>

<template>
    <!-- Sub-tabs for switching between the hiscore categories; same chrome as
         Show.vue's Inventory/Looting Bag via the shared TabbedCard. -->
    <TabbedCard :tabs="tabs" v-model="activeTab">
        <!-- Fixed 72px-wide cells so the pack's tile_half_left/right.png pair
             (36px each) join seam-to-seam instead of stretching. flex-wrap
             keeps the row count flexible across screen widths. -->
        <ul class="flex flex-wrap justify-center gap-1"
            :class="activeTab === 'bosses' ? 'max-h-96 overflow-y-auto' : ''">
            <li v-for="cell in cells" :key="cell.slug" class="relative w-[72px]">
                <!-- `box` provides the no-pack bg + border + rounded; pack CSS
                     resets bg to transparent and paints the stats-tile texture
                     in its place. -->
                <Link :href="cell.href"
                      class="flex h-9 items-center justify-center gap-1 box resource-pack-box px-1"
                      @mouseleave="activeKey = null"
                      @mouseover="activeKey = cell.slug">
                    <img v-bind="cell.icon" class="h-5 w-5 object-contain" :alt="cell.tooltip.name">
                    <span class="text-xs font-semibold capitalize text-gray-900 dark:text-white">
                        {{ cell.label }}
                    </span>
                </Link>

                <div v-if="activeKey === cell.slug" class="box-tooltip">
                    <p>{{ cell.tooltip.name }}</p>
                    <p>{{ cell.tooltip.value }}</p>
                </div>
            </li>
        </ul>
    </TabbedCard>
</template>
