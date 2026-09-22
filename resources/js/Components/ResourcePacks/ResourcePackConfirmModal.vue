<script setup>
import { computed, ref, watch } from 'vue';
import DialogModal from '@/Components/Modals/DialogModal.vue';
import Badge from '@/Components/Display/Badge.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';

/*
 * Confirms a resource-pack choice before it takes effect.
 *
 * Both actions it guards are immediate and unprompted: switching repaints the
 * whole interface and reloads the page, and installing POSTs to the hub as soon
 * as it is called. Since the card itself became the click target, that is a
 * large surface to trigger either by accident.
 *
 * An installed pack arrives with everything: author, version, tags, its
 * sampled palette and how many members have chosen it. A hub pack arrives with
 * only a name and an icon, so its author, version and tags are fetched from its
 * pack.properties when the dialog opens — once per pack, then served from cache.
 * The palette still cannot be shown for a hub pack: it is sampled from the
 * artwork inside the download, which does not exist until the pack is installed.
 */
const props = defineProps({
    show: { type: Boolean, default: false },
    // The pack being confirmed. Shape is toPickerArray(); hub packs carry only
    // alias/icon_url, so every richer field is rendered conditionally.
    pack: { type: Object, default: null },
    action: {
        type: String,
        default: 'switch',
        validator: (value) => ['switch', 'install'].includes(value),
    },
    // Whether the interface is currently dark, so the dialog can say whether
    // confirming will move it. Null when the caller does not know.
    currentlyDark: { type: Boolean, default: null },
    busy: { type: Boolean, default: false },
});

defineEmits(['confirm', 'close']);

const isInstall = computed(() => props.action === 'install');

// What the hub knows about an uninstalled pack. Null until fetched, and left
// null if the pack ships no pack.properties — the dialog then just shows less.
const hubDetails = ref(null);
const hubLoading = ref(false);

watch(() => [props.show, props.pack?.name], async ([show, name]) => {
    hubDetails.value = null;

    if (!show || !isInstall.value || !name) {
        return;
    }

    hubLoading.value = true;
    try {
        const { data } = await window.axios.get(route('user.resource-pack.hub'), { params: { name } });
        // Ignore a response for a pack the dialog has since moved on from.
        if (props.pack?.name === name) {
            hubDetails.value = data;
        }
    } catch {
        // Nothing to describe; the dialog stays usable without it.
    } finally {
        hubLoading.value = false;
    }
}, { immediate: true });

// One source for the descriptive fields, whichever kind of pack this is.
const details = computed(() => (isInstall.value ? hubDetails.value : props.pack) ?? {});

const title = computed(() => (isInstall.value
    ? `Install ${props.pack?.alias ?? 'this pack'}?`
    : `Switch to ${props.pack?.alias ?? 'this pack'}?`));

/*
 * Only worth saying when it is actually going to change — selecting a pack
 * clears the viewer's own light/dark choice and lets the pack's flag govern, so
 * a switch can move the entire interface between themes. That is the one
 * consequence the icon cannot hint at.
 */
const themeChange = computed(() => {
    if (isInstall.value || props.pack?.dark_mode === undefined || props.currentlyDark === null) {
        return null;
    }

    return props.pack.dark_mode === props.currentlyDark
        ? null
        : (props.pack.dark_mode ? 'This pack will switch the interface to dark mode.'
            : 'This pack will switch the interface to light mode.');
});

const palette = computed(() => {
    const background = props.pack?.background_color;
    const accent = props.pack?.accent_color;

    return background || accent ? { background, accent } : null;
});

const byline = computed(() => [details.value.author, details.value.version]
    .filter(Boolean)
    .join(' · '));

const usage = computed(() => {
    const count = props.pack?.users_count;

    if (isInstall.value || count === undefined || count === null) {
        return null;
    }

    if (count === 0) {
        return 'No members have chosen this yet';
    }

    return count === 1 ? 'Chosen by 1 member' : `Chosen by ${count} members`;
});
</script>

<template>
    <DialogModal :show="show" max-width="md" @close="$emit('close')">
        <template #title>{{ title }}</template>

        <template #content>
            <div class="flex items-start gap-4">
                <!-- The pack's own icon at full size. It is pixel art, so it is
                     rendered without smoothing rather than blurred up to fit. -->
                <img v-if="pack?.icon_url" :src="pack.icon_url" :alt="pack.alias"
                     class="h-24 w-24 shrink-0 rounded object-contain [image-rendering:pixelated]"
                     onerror="this.style.visibility='hidden'">

                <div class="min-w-0 space-y-2">
                    <p class="font-semibold text-base-content">{{ pack?.alias }}</p>
                    <p v-if="byline" class="text-xs text-base-content/60">{{ byline }}</p>
                    <p v-else-if="hubLoading" class="text-xs text-base-content/40">Loading details…</p>
                    <p v-if="usage" class="text-xs text-base-content/60">{{ usage }}</p>

                    <div v-if="details.tags?.length" class="flex flex-wrap gap-1">
                        <Badge v-for="tag in details.tags" :key="tag" size="xs">{{ tag }}</Badge>
                    </div>

                    <!-- The colours the interface will actually take, sampled from
                         the pack's own artwork at install. -->
                    <div v-if="palette" class="flex items-center gap-2">
                        <span class="text-xs text-base-content/60">Palette</span>
                        <span v-if="palette.background"
                              class="inline-block h-4 w-8 rounded border pack-accent-border"
                              :style="{ backgroundColor: palette.background }"
                              :title="palette.background" />
                        <span v-if="palette.accent"
                              class="inline-block h-4 w-8 rounded border pack-accent-border"
                              :style="{ backgroundColor: palette.accent }"
                              :title="palette.accent" />
                    </div>
                </div>
            </div>

            <p v-if="themeChange" class="mt-4 text-sm font-medium text-base-content">
                {{ themeChange }}
            </p>

            <p v-if="isInstall" class="mt-4 text-sm text-base-content/70">
                This downloads the pack from the RuneLite community hub and adds it to the
                packs you can choose from. Its palette is sampled from the pack's own
                artwork, so it can only be shown once the pack is installed.
            </p>
        </template>

        <template #footer>
            <SecondaryButton :disabled="busy" @click="$emit('close')">
                Cancel
            </SecondaryButton>
            <PrimaryButton class="ms-3" :disabled="busy" @click="$emit('confirm')">
                {{ isInstall ? 'Install' : 'Switch' }}
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
