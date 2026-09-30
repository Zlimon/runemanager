<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import Card from '@/Components/Cards/Card.vue';
import DangerButton from '@/Components/Buttons/DangerButton.vue';
import DialogModal from '@/Components/Modals/DialogModal.vue';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import { formatDate } from './formatDate.js';

/*
 * The user's suspension state, with the suspend dialog (a reason is required)
 * and the button that lifts it. `locked` carries why this admin may not
 * suspend the user (themselves, or the last active owner).
 */
const props = defineProps({
    user: { type: Object, required: true },
    locked: { type: String, default: null },
});

const confirming = ref(false);
const form = useForm({ reason: '' });

const suspend = () => {
    form.post(route('admin.users.suspend', props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirming.value = false;
            form.reset();
        },
    });
};

const unsuspend = () => {
    router.delete(route('admin.users.unsuspend', props.user.id), { preserveScroll: true });
};
</script>

<template>
    <Card>
        <h2 class="mb-3 text-lg font-semibold">Suspension</h2>

        <div v-if="user.suspension" class="space-y-3">
            <div role="alert" class="alert alert-error">
                <div>
                    <p class="font-medium">Suspended {{ formatDate(user.suspension.at) }}
                        <span v-if="user.suspension.by">by {{ user.suspension.by }}</span></p>
                    <p class="mt-1 whitespace-pre-line text-sm">{{ user.suspension.reason }}</p>
                </div>
            </div>
            <PrimaryButton type="button" @click="unsuspend">Lift suspension</PrimaryButton>
        </div>

        <div v-else class="space-y-3">
            <p class="text-sm text-base-content/70">
                Suspending signs the user out everywhere, revokes their plugin tokens and blocks
                them from logging in on the website or the plugin until the suspension is lifted.
            </p>
            <p v-if="locked" class="text-sm text-base-content/60">{{ locked }}</p>
            <DangerButton type="button" :disabled="locked !== null" @click="confirming = true">
                Suspend user
            </DangerButton>
        </div>

        <DialogModal :show="confirming" @close="confirming = false">
            <template #title>Suspend {{ user.name }}</template>
            <template #content>
                <InputLabel for="suspension-reason" value="Reason (shown to administrators)" />
                <textarea id="suspension-reason" v-model="form.reason" rows="3" maxlength="500"
                          class="textarea textarea-bordered mt-1 block w-full"
                          :class="{ 'textarea-error': form.errors.reason }" />
                <InputError :messages="form.errors.reason" />
                <InputError :messages="form.errors.user" />
            </template>
            <template #footer>
                <SecondaryButton @click="confirming = false">Cancel</SecondaryButton>
                <DangerButton class="ms-3" :class="{ 'opacity-25': form.processing }"
                              :disabled="form.processing" @click="suspend">
                    Suspend
                </DangerButton>
            </template>
        </DialogModal>
    </Card>
</template>
