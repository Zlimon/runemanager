<script setup>
import { computed, ref } from "vue";
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const activeTab = ref('skills');
const activeKey = ref(null);

/*
 * Asset URL helpers — prefer the active pack's icon, fall back to the local
 * /images/{kind}/{slug}.{ext} bundle when the pack doesn't ship one (e.g.
 * Sailing on packs predating Nov 2025) or no pack is active. ?v={version}
 * busts the browser cache when the pack is re-installed.
 *
 * The local /images/{boss,clue}/ files use dash-separated slugs (e.g.
 * `abyssal-sire.png`), while the OSRS hiscores API ships underscore slugs
 * (`abyssal_sire`), so the boss path converts on the way out.
 */
const packIcon = (kind, slug) => {
    const pack = page.props.pack;
    return pack?.name
        ? `/resource-packs/${pack.name}/${kind}/${slug}.png?v=${pack.version ?? ''}`
        : null;
};

const skillIconSrc = (slug) =>
    packIcon('skill', slug) ?? `/images/skill/${slug}.webp`;

const bossIconSrc = (slug) =>
    packIcon('boss', slug) ?? `/images/boss/${slug.replace(/_/g, '-')}.png`;

// Clue tier slugs from the hiscore API are `clue_scrolls_{tier}`; the bundled
// /images/clue/ files use `{tier}-treasure-trails.png`.
const clueIconSrc = (slug) => {
    const fromPack = packIcon('clue', slug);
    if (fromPack) {
        return fromPack;
    }
    const tier = slug.replace(/^clue_scrolls_/, '');
    return `/images/clue/${tier}-treasure-trails.png`;
};

const onIconError = (event, fallback) => {
    event.target.onerror = null;
    event.target.src = fallback;
};

// Skills includes a synthetic "total" tile prepended to the real skill list so
// the user sees their overall first. Total uses account.{level,xp} from the
// denormalised columns rather than entries['skills']['overall'].
const skillCells = computed(() => [
    {
        href: route('accounts.index'),
        slug: 'total',
        label: props.account.level,
        tooltip: { name: 'Total level', value: props.account.xp.toLocaleString('en-US') },
        iconSrc: () => skillIconSrc('total'),
        fallback: '/images/skill/total.webp',
    },
    ...props.account.skills.map((skill) => ({
        href: route('hiscores.skills.index', skill.slug),
        slug: skill.slug,
        label: skill.level,
        tooltip: { name: skill.name, value: skill.xp.toLocaleString('en-US') },
        iconSrc: () => skillIconSrc(skill.slug),
        fallback: `/images/skill/${skill.slug}.webp`,
    })),
]);

const bossCells = computed(() => (props.account.bosses ?? []).map((boss) => ({
    href: route('hiscores.bosses.index', boss.slug),
    slug: boss.slug,
    label: boss.score > 0 ? boss.score.toLocaleString('en-US') : '—',
    tooltip: { name: boss.name, value: `Rank ${boss.rank > 0 ? boss.rank.toLocaleString('en-US') : '—'}` },
    iconSrc: () => bossIconSrc(boss.slug),
    fallback: '/images/boss/boss.png',
})));

const clueCells = computed(() => (props.account.clues ?? []).map((clue) => ({
    href: route('hiscores.clues.index', clue.slug),
    slug: clue.slug,
    label: clue.score > 0 ? clue.score.toLocaleString('en-US') : '—',
    tooltip: { name: `${clue.name} clues`, value: `Rank ${clue.rank > 0 ? clue.rank.toLocaleString('en-US') : '—'}` },
    iconSrc: () => clueIconSrc(clue.slug),
    fallback: '/images/clue/clue.png',
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
    { key: 'skills', label: 'Skills' },
    { key: 'bosses', label: 'Bosses' },
    { key: 'clues', label: 'Clues' },
];
</script>

<template>
    <div>
        <!-- Sub-tab strip for switching between the hiscore categories. -->
        <div class="tabs tabs-lifted mt-4" role="tablist">
            <a v-for="tab in tabs" :key="tab.key"
               class="tab resource-pack-tab"
               :class="{ 'tab-active !bg-base-200 is-active': activeTab === tab.key }"
               role="tab"
               @click="activeTab = tab.key">
                {{ tab.label }}
            </a>
        </div>

        <ul class="mt-2 grid grid-cols-7 gap-1"
            :class="activeTab === 'bosses' ? 'max-h-96 overflow-y-auto' : ''">
            <li v-for="cell in cells" :key="cell.slug" class="relative">
                <Link :href="cell.href"
                      class="flex h-9 items-center justify-center gap-1 box !bg-base-200 resource-pack-box px-1"
                      @mouseleave="activeKey = null"
                      @mouseover="activeKey = cell.slug">
                    <img :src="cell.iconSrc()"
                         class="h-5 w-5 object-contain"
                         :alt="cell.tooltip.name"
                         @error="onIconError($event, cell.fallback)">
                    <span class="text-xs font-semibold capitalize">{{ cell.label }}</span>
                </Link>

                <div v-if="activeKey === cell.slug" class="box-tooltip">
                    <p>{{ cell.tooltip.name }}</p>
                    <p>{{ cell.tooltip.value }}</p>
                </div>
            </li>
        </ul>
    </div>
</template>
