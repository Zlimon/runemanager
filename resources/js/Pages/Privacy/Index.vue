<script setup>
import { reactive, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import Card from '@/Components/Cards/Card.vue';
import Checkbox from '@/Components/Form/Checkbox.vue';
import Badge from '@/Components/Display/Badge.vue';
import ConfirmationModal from '@/Components/Modals/ConfirmationModal.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';

/*
 * Settings → Privacy. Every per-account switch that decides what other members
 * and visitors see, one card per account: event locations on the map, whether
 * new screenshots arrive hidden, and (when the instance uses Wise Old Man) the
 * Progress page and WOM tracking. The owner always sees their own data.
 */
const props = defineProps({
    // [{ id, username, account_type, show_events_on_map, hide_new_screenshots,
    //    positioned_event_count, screenshot_count, hidden_screenshot_count,
    //    wom: null | { status: 'linked'|'not_found'|'unchecked', show_on_progress, tracking_opt_in,
//                  synced_at, sync_available_at } }]
    accounts: { type: Array, default: () => [] },
    wom_enabled: { type: Boolean, default: false },
});

const busy = ref(null);
const failure = ref(null);

// Local copy of each account's switches, so a toggle shows at once and can be
// put back when the save fails (a 429 from the throttle, say).
const choices = reactive({});
watch(() => props.accounts, (accounts) => {
    accounts.forEach((account) => {
        choices[account.id] = {
            show_events_on_map: account.show_events_on_map,
            hide_new_screenshots: account.hide_new_screenshots,
            show_on_progress: account.wom?.show_on_progress ?? true,
            tracking_opt_in: account.wom?.tracking_opt_in ?? false,
        };
    });
}, { immediate: true, deep: true });

const save = (account, field, value) => {
    const previous = choices[account.id][field];
    const revert = () => {
        choices[account.id][field] = previous;
        failure.value = `Could not save the change for ${account.username}. Please try again in a minute.`;
    };

    choices[account.id][field] = value;
    failure.value = null;
    busy.value = account.id;

    router.put(route('privacy.update', { account: account.username }), { [field]: value }, {
        preserveScroll: true,
        onError: revert,
        onNetworkError: revert,
        onHttpException: () => {
            revert();
            return false;
        },
        onFinish: () => { busy.value = null; },
    });
};

// Opting an account WOM does not know into tracking creates a public profile,
// so that one choice is confirmed first.
const confirmingTracking = ref(null);

const toggleTracking = (account, value, event) => {
    if (value && account.wom?.status !== 'linked') {
        event.target.checked = false;
        confirmingTracking.value = account;
        return;
    }
    save(account, 'tracking_opt_in', value);
};

const confirmTracking = () => {
    save(confirmingTracking.value, 'tracking_opt_in', true);
    confirmingTracking.value = null;
};

// A manual Wise Old Man refresh; the server allows one per account a day and
// says when the next one is due (sync_available_at).
const syncing = ref(null);

const syncWom = (account) => {
    syncing.value = account.id;
    router.post(route('privacy.wom-sync', { account: account.username }), {}, {
        preserveScroll: true,
        onFinish: () => { syncing.value = null; },
    });
};

const count = (value, one, many) => `${value.toLocaleString('en-US')} ${value === 1 ? one : many}`;
</script>

<template>
    <SettingsLayout title="Privacy">
        <p class="mb-6 text-sm text-base-content/70">
            Choose what other members and visitors see of each of your accounts. You always see everything
            of your own, and you can change these at any time.
        </p>

        <div v-if="failure" role="alert" class="alert alert-error mb-4 text-sm">{{ failure }}</div>

        <Card v-if="!accounts.length" padding="p-6" class="text-center text-sm text-base-content/60">
            You have no accounts yet. Log in with the RuneLite plugin to add one.
        </Card>

        <ul v-else class="space-y-4">
            <li v-for="account in accounts" :key="account.id">
                <Card padding="p-4">
                    <div class="flex min-w-0 items-center gap-2">
                        <img v-if="account.account_type && account.account_type !== 'normal'"
                             :src="`/images/${account.account_type}.png`" class="h-5 w-5 object-contain" alt="">
                        <span class="truncate text-lg font-semibold">{{ account.username }}</span>
                    </div>

                    <div class="mt-3 space-y-4">
                        <!-- Map -->
                        <div>
                            <label class="flex cursor-pointer items-start gap-2 text-sm">
                                <Checkbox class="checkbox-sm mt-0.5" :checked="choices[account.id].show_events_on_map"
                                          :disabled="busy === account.id"
                                          @update:checked="(value) => save(account, 'show_events_on_map', value)" />
                                <span>
                                    Show where my events happened
                                    <span class="block text-xs text-base-content/60">
                                        The plugin records where your level-ups, drops and other feed events happened.
                                        When this is on, others see that on the
                                        <Link :href="route('map.index')" class="link">Live Map</Link>, in the feed and
                                        in Recent Events.
                                    </span>
                                </span>
                            </label>
                            <p class="mt-1 pl-7 text-xs text-base-content/50">
                                {{ count(account.positioned_event_count, 'event', 'events') }} with a location
                                <template v-if="account.positioned_event_count">
                                    ·
                                    <Link :href="route('map.index', { events: account.id })" class="link link-hover">
                                        View on map
                                    </Link>
                                </template>
                            </p>
                        </div>

                        <!-- Screenshots -->
                        <div>
                            <label class="flex cursor-pointer items-start gap-2 text-sm">
                                <Checkbox class="checkbox-sm mt-0.5" :checked="choices[account.id].hide_new_screenshots"
                                          :disabled="busy === account.id"
                                          @update:checked="(value) => save(account, 'hide_new_screenshots', value)" />
                                <span>
                                    Hide new screenshots
                                    <span class="block text-xs text-base-content/60">
                                        New screenshots from the plugin arrive hidden: only you and the site's admins
                                        see them until you show them. Screenshots you already have keep their setting.
                                    </span>
                                </span>
                            </label>
                            <p class="mt-1 pl-7 text-xs text-base-content/50">
                                {{ count(account.screenshot_count, 'screenshot', 'screenshots') }}
                                <template v-if="account.hidden_screenshot_count">
                                    ({{ account.hidden_screenshot_count.toLocaleString('en-US') }} hidden)
                                </template>
                                <template v-if="account.screenshot_count">
                                    ·
                                    <Link :href="route('screenshots.index', { account: account.id })"
                                          class="link link-hover">
                                        Manage screenshots
                                    </Link>
                                </template>
                            </p>
                        </div>

                        <!-- Wise Old Man -->
                        <div v-if="wom_enabled">
                            <p class="flex items-center gap-2 text-sm font-semibold">
                                Wise Old Man
                                <Badge v-if="account.wom?.status === 'linked'" variant="success" size="sm">Linked</Badge>
                                <Badge v-else-if="account.wom?.status === 'not_found'" variant="warning" size="sm">
                                    Not on Wise Old Man
                                </Badge>
                                <Badge v-else variant="ghost" size="sm">Not checked yet</Badge>
                            </p>

                            <p v-if="!account.wom || account.wom.status === 'unchecked'"
                               class="mt-1 text-xs text-base-content/60">
                                We have not looked this account up on
                                <a href="https://wiseoldman.net" target="_blank" rel="noopener" class="link">Wise Old Man</a>
                                yet. It is checked on the next daily sync (or sync it now below), after which you can
                                choose here whether Wise Old Man keeps it updated.
                            </p>

                            <div v-else class="mt-2 space-y-2">
                                <label class="flex cursor-pointer items-start gap-2 text-sm">
                                    <input type="checkbox" class="checkbox checkbox-sm mt-0.5"
                                           :checked="choices[account.id].tracking_opt_in" :disabled="busy === account.id"
                                           @change="(event) => toggleTracking(account, event.target.checked, event)">
                                    <span>
                                        Keep this account updated on Wise Old Man
                                        <span class="block text-xs text-base-content/60">
                                            Lets us ask Wise Old Man to refresh the profile from the official hiscores.
                                            <strong v-if="account.wom.status !== 'linked'">
                                                This creates a public profile for {{ account.username }} on
                                                wiseoldman.net that anyone can look up.
                                            </strong>
                                            You can turn this off at any time; an existing profile stays on Wise Old Man.
                                        </span>
                                    </span>
                                </label>

                                <label v-if="account.wom.status === 'linked'"
                                       class="flex cursor-pointer items-start gap-2 text-sm">
                                    <Checkbox class="checkbox-sm mt-0.5" :checked="choices[account.id].show_on_progress"
                                              :disabled="busy === account.id"
                                              @update:checked="(value) => save(account, 'show_on_progress', value)" />
                                    <span>
                                        Show on the Progress page
                                        <span class="block text-xs text-base-content/60">
                                            Lists this account's Wise Old Man gains on
                                            <Link :href="route('progress.index')" class="link">Progress</Link>.
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <SecondaryButton class="btn-sm"
                                                 :disabled="syncing === account.id || !!account.wom?.sync_available_at"
                                                 @click="syncWom(account)">
                                    Sync now
                                </SecondaryButton>
                                <span class="text-xs text-base-content/50">
                                    <template v-if="account.wom?.sync_available_at">
                                        You can sync again {{ dayjs(account.wom.sync_available_at).fromNow() }}.
                                    </template>
                                    <template v-else>Once a day per account.</template>
                                    <template v-if="account.wom?.synced_at">
                                        Last updated {{ dayjs(account.wom.synced_at).fromNow() }}.
                                    </template>
                                </span>
                            </div>
                        </div>
                    </div>
                </Card>
            </li>
        </ul>

        <ConfirmationModal :show="confirmingTracking !== null" @close="confirmingTracking = null">
            <template #title>Track on Wise Old Man?</template>
            <template #content>
                Ask Wise Old Man to track {{ confirmingTracking?.username }}? This creates a public profile for it
                on wiseoldman.net.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingTracking = null">Cancel</SecondaryButton>
                <PrimaryButton class="ms-3" @click="confirmTracking">Track</PrimaryButton>
            </template>
        </ConfirmationModal>
    </SettingsLayout>
</template>
