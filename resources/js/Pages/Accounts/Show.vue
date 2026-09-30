<script setup>
import { computed, ref, watch } from "vue";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";
import AccountLayout from "@/Layouts/AccountLayout.vue";
import TabbedCard from "@/Components/Cards/TabbedCard.vue";
import Card from "@/Components/Cards/Card.vue";
import Quests from "@/Pages/Accounts/Partials/Quests.vue";
import Diaries from "@/Pages/Accounts/Partials/Diaries.vue";
import CombatAchievements from "@/Pages/Accounts/Partials/CombatAchievements.vue";
import AchievementGallery from "@/Pages/Accounts/Partials/AchievementGallery.vue";
import RecentEvents from "@/Pages/Accounts/Partials/RecentEvents.vue";
import Freshness from "@/Pages/Accounts/Partials/Freshness.vue";
import Equipment from "@/Pages/Accounts/Partials/Equipment.vue";
import Avatar from "@/Pages/Accounts/Partials/Avatar.vue";
import Summary from "@/Pages/Accounts/Partials/Summary.vue";
import Skills from "@/Pages/Accounts/Partials/Skills.vue";

/*
 * The Adventurer's Log — the account's landing tab, after the old RuneScape
 * one: the character panel on the left; Total Level, Recent Events and the
 * quest journal on the right.
 */
const props = defineProps({
    account: { type: Object, required: true },
    position: { type: Object, default: null },
    vitals: { type: Object, default: null },
    location: { type: String, default: null },
    quests: { type: Object, default: null },
    diaries: { type: Object, default: () => ({}) },
    combatAchievements: { type: Object, default: null },
    avatar: { type: Object, default: null },
    pinnedFeed: { type: Array, default: () => [] },
    // { data, next_cursor } — the first page; RecentEvents fetches the rest.
    recentEvents: { type: Object, default: () => ({ data: [], next_cursor: null }) },
    // { position, total } among this instance's accounts by total XP, or null
    // while the account has no XP to rank on.
    instanceRank: { type: Object, default: null },
    freshness: { type: Object, default: () => ({ stale_after_minutes: 60 }) },
    // WomAccountView::summary() — only sent while Wise Old Man is on.
    wom: { type: Object, default: null },
});

// DataUpdated type ⇒ the props this tab reloads (freshness is added by the layout).
const RELOAD_PROPS = {
    equipment: ['account'],
    avatar: ['avatar'],
    diaries: ['diaries'],
    combat_achievements: ['combatAchievements'],
};

const { packIcon } = useResourcePackIcon();

// Lets the gallery's unpin flip the matching Recent Events row.
const recentEventsList = ref(null);

const activeJournalTab = ref('quests');
const journalTabs = [
    { key: 'quests', label: 'Quests', icon: packIcon('tab', 'quests') },
    { key: 'diaries', label: 'Diaries', icon: packIcon('tab', 'quests_green_achievement_diaries') },
    { key: 'combat-achievements', label: 'Combat', icon: packIcon('tab', 'combat_achievements') },
];

const staleAfter = computed(() => props.freshness.stale_after_minutes ?? 60);

const number = (value) => Number(value ?? 0).toLocaleString('en-US');

// When the avatar includes an opponent, give it the whole column and tuck the
// equipment away (toggleable above the model) so the fight has room to breathe.
const hasNpc = computed(() => !!props.avatar?.npc_obj_url);
const showEquipment = ref(true);
watch(hasNpc, (npc) => { showEquipment.value = !npc; }, { immediate: true });
</script>

<template>
    <AccountLayout :account="account" :position="position" :vitals="vitals" :location="location"
                   :reload-props="RELOAD_PROPS">
        <!-- Owner-curated achievement gallery -->
        <div class="mb-4 flex justify-center">
            <AchievementGallery :account="account" :events="pinnedFeed"
                                @unpinned="(id) => recentEventsList?.setPinned(id, false)" />
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Character panel -->
            <div>
                <Card frame="dialog" padding="p-4">
                    <div class="mb-2 flex items-baseline justify-between gap-2">
                        <h3 class="truncate">{{ account.username }}</h3>
                        <button type="button"
                                class="shrink-0 text-xs text-base-content/60 hover:text-base-content"
                                @click="showEquipment = !showEquipment">
                            {{ showEquipment ? 'Hide equipment' : 'Show equipment' }}
                        </button>
                    </div>

                    <!-- Side by side when there's no opponent and equipment is shown. -->
                    <div v-if="!hasNpc && showEquipment" class="grid grid-cols-2 gap-4">
                        <Avatar :avatar="avatar" />
                        <Equipment :account="account" />
                    </div>

                    <!-- Otherwise the model fills the column; equipment stacks above. -->
                    <div v-else>
                        <Equipment v-if="showEquipment" :account="account" class="mb-3" />
                        <Avatar :avatar="avatar" :expanded="true" />
                    </div>

                    <hr class="my-4 border border-base-300 bg-base-300">

                    <Summary :account="account" :instance-rank="instanceRank" :wom="wom" />
                </Card>
            </div>

            <div class="flex flex-col gap-6 lg:col-span-2">
                <!-- Total Level -->
                <section>
                    <div class="mb-2 flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                        <h3>Total Level: {{ number(account.level) }}</h3>
                        <div class="flex flex-wrap items-baseline gap-x-4 text-xs text-base-content/70">
                            <span>XP <span class="font-semibold text-base-content">{{ number(account.xp) }}</span></span>
                            <span>Rank <span class="font-semibold text-base-content">{{ account.rank > 0 ? number(account.rank) : '—' }}</span></span>
                            <Freshness :updated-at="freshness.hiscores" :stale-after-minutes="staleAfter" />
                        </div>
                    </div>
                    <Skills :account="account" />
                </section>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-5">
                    <RecentEvents ref="recentEventsList" class="md:col-span-3" :account="account" :events="recentEvents" />

                    <TabbedCard class="md:col-span-2" :tabs="journalTabs" v-model="activeJournalTab"
                                tab-class="flex-1 min-w-0 justify-center px-2">
                        <template #quests>
                            <Quests :quests="quests" :freshness="freshness.quests" :stale-after="staleAfter" />
                        </template>
                        <template #diaries>
                            <Diaries :diaries="diaries" :freshness="freshness.diaries" :stale-after="staleAfter" />
                        </template>
                        <template #combat-achievements>
                            <CombatAchievements :combat-achievements="combatAchievements"
                                                :freshness="freshness.combat_achievements" :stale-after="staleAfter" />
                        </template>
                    </TabbedCard>
                </div>
            </div>
        </div>
    </AccountLayout>
</template>
