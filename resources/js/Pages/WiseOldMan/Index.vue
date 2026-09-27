<script setup>
import { reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import Card from '@/Components/Cards/Card.vue';
import Checkbox from '@/Components/Form/Checkbox.vue';
import Badge from '@/Components/Display/Badge.vue';

/*
 * Settings → Wise Old Man. Each of the user's accounts with its WOM link
 * status. We only ever read WOM for accounts it already tracks; asking WOM to
 * keep one updated (which creates a public profile when there is none) is the
 * owner's explicit, revocable choice.
 */
const props = defineProps({
    // [{ id, username, account_type, wom: WomAccountView::summary() | null }]
    accounts: { type: Array, default: () => [] },
});

const busy = ref(null);
const failure = ref(null);

// Local copy of each account's two choices, so a toggle shows at once and can
// be put back when the save fails (a 429 from the throttle, say).
const choices = reactive({});
watch(() => props.accounts, (accounts) => {
    accounts.forEach((account) => {
        choices[account.id] = {
            tracking_opt_in: account.wom?.tracking_opt_in ?? false,
            show_on_progress: account.wom?.show_on_progress ?? true,
        };
    });
}, { immediate: true, deep: true });

const hours = (value) => `${Number(value ?? 0).toLocaleString('en-US', { maximumFractionDigits: 1 })} h`;

const save = (account, field, value) => {
    const previous = choices[account.id][field];
    const revert = () => {
        choices[account.id][field] = previous;
        failure.value = `Could not save the change for ${account.username}. Please try again in a minute.`;
    };

    choices[account.id][field] = value;
    failure.value = null;
    busy.value = account.id;

    router.put(route('wise-old-man.update', { account: account.username }), { [field]: value }, {
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

const toggleProgress = (account, value) => save(account, 'show_on_progress', value);

const toggleTracking = (account, value, event) => {
    if (value && !account.wom?.linked
        && !confirm(`Ask Wise Old Man to track ${account.username}? This creates a public profile for it on wiseoldman.net.`)) {
        event.target.checked = false;
        return;
    }
    save(account, 'tracking_opt_in', value);
};
</script>

<template>
    <SettingsLayout title="Wise Old Man">
        <p class="mb-6 text-sm text-base-content/70">
            <a href="https://wiseoldman.net" target="_blank" rel="noopener" class="link">Wise Old Man</a>
            records XP, EHP and boss kills over time. We link your accounts to profiles Wise Old Man already
            tracks and never create one without your say-so.
        </p>

        <div v-if="failure" role="alert" class="alert alert-error mb-4 text-sm">{{ failure }}</div>

        <Card v-if="!accounts.length" padding="p-6" class="text-center text-sm text-base-content/60">
            You have no accounts yet. Log in with the RuneLite plugin to add one.
        </Card>

        <ul v-else class="space-y-4">
            <li v-for="account in accounts" :key="account.id">
                <Card padding="p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <img v-if="account.account_type && account.account_type !== 'normal'"
                                 :src="`/images/${account.account_type}.png`" class="h-5 w-5 object-contain" alt="">
                            <span class="truncate text-lg font-semibold">{{ account.username }}</span>
                            <Badge v-if="account.wom?.linked" variant="success" size="sm">Linked</Badge>
                            <Badge v-else-if="account.wom?.not_found" variant="warning" size="sm">Not on Wise Old Man</Badge>
                            <Badge v-else variant="ghost" size="sm">Not checked yet</Badge>
                        </div>
                        <a v-if="account.wom?.url" :href="account.wom.url" target="_blank" rel="noopener"
                           class="link link-hover text-sm">
                            View profile
                        </a>
                    </div>

                    <!-- Linked -->
                    <template v-if="account.wom?.linked">
                        <p class="mt-2 text-sm text-base-content/70">
                            {{ hours(account.wom.ehp) }} EHP · {{ hours(account.wom.ehb) }} EHB
                            <span v-if="account.wom.status && account.wom.status !== 'active'">
                                · Wise Old Man marks it <strong>{{ account.wom.status }}</strong>
                            </span>
                            <span v-if="account.wom.synced_at" class="text-base-content/50">
                                · synced {{ dayjs(account.wom.synced_at).format('MMM D, HH:mm') }}
                            </span>
                        </p>
                    </template>

                    <!-- WOM does not know the name -->
                    <p v-else-if="account.wom?.not_found" class="mt-2 text-sm text-base-content/70">
                        Wise Old Man does not track {{ account.username }} yet, so there is no history to show.
                    </p>
                    <p v-else class="mt-2 text-sm text-base-content/70">
                        We have not looked this account up on Wise Old Man yet. It is checked on the next daily sync.
                        Once it has been checked (tomorrow at the latest), you can choose whether Wise Old Man keeps it updated.
                    </p>

                    <div v-if="account.wom?.linked || account.wom?.not_found" class="mt-3 space-y-2">
                        <label class="flex cursor-pointer items-start gap-2 text-sm">
                            <input type="checkbox" class="checkbox checkbox-sm mt-0.5"
                                   :checked="choices[account.id].tracking_opt_in" :disabled="busy === account.id"
                                   @change="(event) => toggleTracking(account, event.target.checked, event)">
                            <span>
                                Keep this account updated on Wise Old Man
                                <span class="block text-xs text-base-content/60">
                                    Lets us ask Wise Old Man to refresh the profile from the official hiscores.
                                    <strong v-if="!account.wom.linked">
                                        This creates a public profile for {{ account.username }} on wiseoldman.net
                                        that anyone can look up.
                                    </strong>
                                    You can turn this off at any time; an existing profile stays on Wise Old Man.
                                </span>
                            </span>
                        </label>

                        <label v-if="account.wom.linked" class="flex cursor-pointer items-center gap-2 text-sm">
                            <Checkbox :checked="choices[account.id].show_on_progress" :disabled="busy === account.id"
                                      @update:checked="(value) => toggleProgress(account, value)" />
                            Show on the Progress page
                        </label>
                    </div>
                </Card>
            </li>
        </ul>
    </SettingsLayout>
</template>
