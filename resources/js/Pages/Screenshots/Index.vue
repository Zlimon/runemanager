<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import Card from '@/Components/Cards/Card.vue';
import Badge from '@/Components/Display/Badge.vue';
import Checkbox from '@/Components/Form/Checkbox.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import ImageLightbox from '@/Components/Modals/ImageLightbox.vue';
import ConfirmationModal from '@/Components/Modals/ConfirmationModal.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import DangerButton from '@/Components/Buttons/DangerButton.vue';
import PaginationItem from '@/Components/Tables/PaginationItem.vue';

/*
 * Settings → Screenshots. Every screenshot the plugin attached to the viewer's
 * feed events, newest first. A hidden screenshot stays on its event but only
 * the owner and admins see it; deleting one removes the picture and keeps the
 * event. Hide, show and delete work on one card or on a checked selection.
 */
const props = defineProps({
    // Paginated: { data: [{ id, account: { id, username }, type, title, occurred_at, url, hidden }], links, meta? }
    screenshots: { type: Object, required: true },
    // [{ id, username }]
    accounts: { type: Array, default: () => [] },
    // ['loot_drop', …] or [{ value, label }]
    types: { type: Array, default: () => [] },
    // { account, type }
    filters: { type: Object, default: () => ({}) },
});

const RELOAD = ['screenshots', 'filters', 'jetstream'];

const items = computed(() => props.screenshots.data ?? []);
const pagination = computed(() => props.screenshots.meta ?? props.screenshots);
const pageLinks = computed(() => pagination.value.links ?? []);

const typeOptions = computed(() => props.types.map((type) => (typeof type === 'string'
    ? { value: type, label: type.replaceAll('_', ' ').replace(/^./, (c) => c.toUpperCase()) }
    : type)));

// Filters
const accountFilter = ref(String(props.filters.account ?? ''));
const typeFilter = ref(String(props.filters.type ?? ''));

const applyFilters = () => {
    router.get(route('screenshots.index'), {
        ...(accountFilter.value ? { account: accountFilter.value } : {}),
        ...(typeFilter.value ? { type: typeFilter.value } : {}),
    }, { only: RELOAD, preserveState: true, preserveScroll: true, replace: true });
};

watch([accountFilter, typeFilter], applyFilters);

// Selection only spans the current page; it is dropped whenever the page's
// items change (filter, pagination, a finished action).
const selected = ref([]);
watch(items, () => { selected.value = []; });

const isSelected = (id) => selected.value.includes(id);
const select = (id, on) => {
    selected.value = on ? [...selected.value, id] : selected.value.filter((other) => other !== id);
};
const allSelected = computed(() => items.value.length > 0 && selected.value.length === items.value.length);
const selectAll = (on) => { selected.value = on ? items.value.map((item) => item.id) : []; };

// Actions
const busy = ref(false);
const failure = ref(null);

// A 403 (none of the ids are yours any more) or a 429 from the throttle shows
// inline instead of Inertia's error modal.
const fail = () => {
    failure.value = 'That did not work. Reload the page, or try again in a minute.';
};

const options = {
    only: RELOAD,
    preserveScroll: true,
    preserveState: true,
    onStart: () => {
        busy.value = true;
        failure.value = null;
    },
    onError: fail,
    onNetworkError: fail,
    onHttpException: () => {
        fail();
        return false;
    },
    onFinish: () => { busy.value = false; },
};

const setHidden = (ids, hidden) => {
    router.patch(route('screenshots.update'), { ids, hidden }, options);
};

const confirmingDelete = ref(null);

const destroy = () => {
    const ids = confirmingDelete.value;
    confirmingDelete.value = null;
    router.delete(route('screenshots.destroy'), {
        ...options,
        data: { ids },
        onSuccess: () => {
            // Deleting the last card on a later page leaves it empty; step back.
            const previous = pageLinks.value[0]?.url;
            if (!items.value.length && previous) {
                router.visit(previous, { only: RELOAD, preserveScroll: true, preserveState: true });
            }
        },
    });
};

const deleteCount = computed(() => confirmingDelete.value?.length ?? 0);
</script>

<template>
    <SettingsLayout title="Screenshots">
        <p class="mb-6 text-sm text-base-content/70">
            Screenshots the RuneLite plugin attached to your feed events. A hidden screenshot is only shown to you
            and the site's admins; the event itself stays in the feed. Deleting a screenshot keeps its event.
            To have new ones arrive hidden, see
            <Link :href="route('privacy.index')" class="link">Privacy</Link>.
        </p>

        <div class="mb-4 flex flex-wrap items-end gap-3">
            <div class="w-full sm:w-48">
                <InputLabel for="screenshot-account" value="Account" />
                <SelectInput id="screenshot-account" v-model="accountFilter" class="select-sm mt-1">
                    <option value="">All accounts</option>
                    <option v-for="account in accounts" :key="account.id" :value="String(account.id)">
                        {{ account.username }}
                    </option>
                </SelectInput>
            </div>
            <div class="w-full sm:w-48">
                <InputLabel for="screenshot-type" value="Type" />
                <SelectInput id="screenshot-type" v-model="typeFilter" class="select-sm mt-1">
                    <option value="">All types</option>
                    <option v-for="type in typeOptions" :key="type.value" :value="type.value">{{ type.label }}</option>
                </SelectInput>
            </div>
        </div>

        <div v-if="failure" role="alert" class="alert alert-error mb-4 text-sm">{{ failure }}</div>

        <!-- Bulk actions -->
        <div v-if="items.length" class="mb-4 flex flex-wrap items-center gap-2 text-sm">
            <label class="flex cursor-pointer items-center gap-2">
                <Checkbox class="checkbox-sm" :checked="allSelected" @update:checked="selectAll" />
                Select all
            </label>
            <template v-if="selected.length">
                <span class="text-base-content/60">· {{ selected.length }} selected</span>
                <SecondaryButton class="btn-sm" :disabled="busy" @click="setHidden(selected, true)">
                    Hide selected
                </SecondaryButton>
                <SecondaryButton class="btn-sm" :disabled="busy" @click="setHidden(selected, false)">
                    Show selected
                </SecondaryButton>
                <DangerButton class="btn-sm" :disabled="busy" @click="confirmingDelete = [...selected]">
                    Delete selected
                </DangerButton>
            </template>
        </div>

        <Card v-if="!items.length" padding="p-6" class="text-center text-sm text-base-content/60">
            <template v-if="filters.account || filters.type">No screenshots match these filters.</template>
            <template v-else>
                <p>You have no screenshots yet.</p>
                <p class="mt-2">
                    Turn on <strong>Attach screenshots to feed</strong> in the RuneManager plugin settings in RuneLite
                    to add a screenshot to your drops, level-ups and other feed events. They are shown with the event
                    unless you hide them here, or turn on <em>Hide new screenshots</em> under
                    <Link :href="route('privacy.index')" class="link">Privacy</Link>.
                </p>
            </template>
        </Card>

        <ul v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <li v-for="shot in items" :key="shot.id">
                <Card padding="p-0" frame="dialog" class="overflow-hidden">
                    <div class="relative p-2">
                        <ImageLightbox :src="shot.url" :alt="shot.title" thumb-class="h-36 w-full object-cover"
                                       class="!w-full" />
                        <Checkbox class="checkbox-sm absolute left-4 top-4 bg-base-100"
                                  :aria-label="`Select ${shot.title}`"
                                  :checked="isSelected(shot.id)" @update:checked="(on) => select(shot.id, on)" />
                        <Badge v-if="shot.hidden" size="sm" class="pointer-events-none absolute right-4 top-4">
                            Hidden
                        </Badge>
                    </div>

                    <div class="px-4 pb-4">
                        <p class="truncate text-sm font-medium" :title="shot.title">{{ shot.title }}</p>
                        <p class="mt-0.5 text-xs text-base-content/50">
                            {{ shot.account.username }} ·
                            <span :title="dayjs(shot.occurred_at).format('MMM D, YYYY h:mm A')">
                                {{ dayjs(shot.occurred_at).format('MMM D, YYYY') }}
                            </span>
                        </p>
                        <div class="mt-3 flex gap-2">
                            <SecondaryButton class="btn-xs" :disabled="busy" @click="setHidden([shot.id], !shot.hidden)">
                                {{ shot.hidden ? 'Show' : 'Hide' }}
                            </SecondaryButton>
                            <DangerButton class="btn-xs" :disabled="busy" @click="confirmingDelete = [shot.id]">
                                Delete
                            </DangerButton>
                        </div>
                    </div>
                </Card>
            </li>
        </ul>

        <nav v-if="pagination.last_page > 1" class="mt-8 flex justify-center" aria-label="Pagination">
            <div class="join border border-base-300">
                <PaginationItem v-for="(link, index) in pageLinks" :key="index"
                                :href="link.url"
                                :only="link.url ? RELOAD : undefined"
                                :active="link.active"
                                :disabled="!link.url">
                    <span v-html="link.label" />
                </PaginationItem>
            </div>
        </nav>

        <ConfirmationModal :show="confirmingDelete !== null" @close="confirmingDelete = null">
            <template #title>Delete {{ deleteCount === 1 ? 'screenshot' : `${deleteCount} screenshots` }}</template>
            <template #content>
                {{ deleteCount === 1 ? 'This screenshot is' : 'These screenshots are' }} removed for good.
                The feed {{ deleteCount === 1 ? 'event stays' : 'events stay' }}, without a picture.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDelete = null">Cancel</SecondaryButton>
                <DangerButton class="ms-3" @click="destroy">Delete</DangerButton>
            </template>
        </ConfirmationModal>
    </SettingsLayout>
</template>
