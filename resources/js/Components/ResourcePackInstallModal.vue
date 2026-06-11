<script setup>
import { onBeforeUnmount, ref } from "vue";
import Loader from "@/Components/Loader.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

/*
 * Centered modal shown while a resource pack downloads. Installing queues an
 * async job, so rather than reload straight away (which would flash the default
 * theme) we hold here under the loading rat and poll the status endpoint until
 * the assets land, then reload. A timeout shows a graceful fallback.
 *
 * Call `start({ name, alias })` via a template ref. Used by both the member
 * Appearance page and the admin pack hub — they differ only in the URLs.
 */
const props = defineProps({
    installUrl: { type: String, required: true },
    statusUrl: { type: String, required: true },
});

const POLL_INTERVAL = 2000;
const POLL_TIMEOUT = 90_000;

const pack = ref(null);
const timedOut = ref(false);
let pollTimer = null;
let deadline = 0;

const stop = () => {
    if (pollTimer) {
        clearTimeout(pollTimer);
        pollTimer = null;
    }
};

const poll = (name) => {
    pollTimer = setTimeout(() => {
        window.axios.get(props.statusUrl, { params: { name } })
            .then(({ data }) => {
                if (data.installed) {
                    window.location.reload();
                } else if (Date.now() > deadline) {
                    timedOut.value = true;
                } else {
                    poll(name);
                }
            })
            .catch(() => {
                if (Date.now() > deadline) {
                    timedOut.value = true;
                } else {
                    poll(name);
                }
            });
    }, POLL_INTERVAL);
};

const start = (target) => {
    if (pack.value) {
        return;
    }
    pack.value = target;
    timedOut.value = false;

    window.axios.post(props.installUrl, { name: target.name })
        .then(({ data }) => {
            if (data.installed) {
                window.location.reload();
                return;
            }
            deadline = Date.now() + POLL_TIMEOUT;
            poll(target.name);
        })
        .catch(() => { timedOut.value = true; });
};

const dismiss = () => {
    stop();
    pack.value = null;
    timedOut.value = false;
};

defineExpose({ start });
onBeforeUnmount(stop);
</script>

<template>
    <div v-if="pack" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-sm rounded-lg p-8 text-center shadow-xl pack-bg-card resource-pack-border">
            <Loader v-if="!timedOut" bare :loading="true">
                <h3 class="text-lg font-semibold">Installing {{ pack.alias }}…</h3>
                <p class="text-sm text-base-content/70">
                    Downloading the pack — this usually takes a few seconds.
                </p>
            </Loader>
            <template v-else>
                <h3 class="text-lg font-semibold">Still working on it</h3>
                <p class="mt-1 text-sm text-base-content/70">
                    {{ pack.alias }} is taking longer than usual to download. It'll appear automatically
                    once it's ready — no need to install again.
                </p>
                <PrimaryButton type="button" class="mt-4" @click="dismiss">
                    Got it
                </PrimaryButton>
            </template>
        </div>
    </div>
</template>
