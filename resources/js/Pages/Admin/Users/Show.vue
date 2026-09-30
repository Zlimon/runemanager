<script setup>
import { computed, ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Display/Badge.vue';
import Card from '@/Components/Cards/Card.vue';
import ConfirmationModal from '@/Components/Modals/ConfirmationModal.vue';
import DangerButton from '@/Components/Buttons/DangerButton.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import InputError from '@/Components/Form/InputError.vue';
import SuspensionPanel from './Partials/SuspensionPanel.vue';
import TokensPanel from './Partials/TokensPanel.vue';
import { formatDate } from './Partials/formatDate.js';

const props = defineProps({
    user: { type: Object, required: true },
    accounts: { type: Array, required: true },
    tokens: { type: Array, required: true },
    roleOptions: { type: Array, required: true },
    isSelf: { type: Boolean, default: false },
    isLastActiveOwner: { type: Boolean, default: false },
});

// Why this admin may not suspend, demote or delete the user, if they may not.
const locked = computed(() => {
    if (props.isSelf) {
        return 'This is your own account.';
    }
    if (props.isLastActiveOwner) {
        return 'This is the last active owner; promote another user first.';
    }
    return null;
});

const currentRole = props.roleOptions.find((role) => props.user.roles.includes(role)) ?? props.roleOptions.at(-1);
const roleForm = useForm({ role: currentRole });

const updateRole = () => {
    roleForm.put(route('admin.users.role', props.user.id), { preserveScroll: true });
};

const confirmingDelete = ref(false);
const deleting = ref(false);

const destroy = () => {
    deleting.value = true;
    router.delete(route('admin.users.destroy', props.user.id), {
        onFinish: () => {
            deleting.value = false;
            confirmingDelete.value = false;
        },
    });
};

const hasTrafficPage = route().has('admin.traffic.user');

// A refusal from the last-owner guard: `user` from suspend/role, `password`
// from delete (the key Jetstream's own delete form reads).
const page = usePage();
const refusal = computed(() => page.props.errors?.user ?? page.props.errors?.password ?? null);
</script>

<template>
    <AdminLayout :title="user.name">
        <template #actions>
            <Link :href="route('admin.users')" class="btn btn-ghost btn-sm">All users</Link>
            <Link v-if="hasTrafficPage" :href="route('admin.traffic.user', user.id)" class="btn btn-outline btn-sm">
                Plugin traffic
            </Link>
        </template>

        <div v-if="refusal" role="alert" class="alert alert-error mb-6">{{ refusal }}</div>

        <div class="grid gap-6 xl:grid-cols-2">
            <Card>
                <div class="flex items-start gap-4">
                    <img :src="user.profile_photo_url" :alt="user.name" class="size-14 rounded-full object-cover">
                    <div class="min-w-0 space-y-1">
                        <p class="text-lg font-semibold">{{ user.name }}</p>
                        <p class="break-all text-sm text-base-content/70">{{ user.email }}</p>
                        <div class="flex flex-wrap gap-1">
                            <Badge v-for="role in user.roles" :key="role" size="sm"
                                   :variant="role === 'Owner' ? 'primary' : 'neutral'">
                                {{ role }}
                            </Badge>
                            <Badge size="sm" :variant="user.suspension ? 'error' : 'success'">
                                {{ user.suspension ? 'Suspended' : 'Active' }}
                            </Badge>
                        </div>
                    </div>
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
                    <dt class="text-base-content/60">Joined</dt>
                    <dd>{{ formatDate(user.created_at) }}</dd>
                    <dt class="text-base-content/60">Email verified</dt>
                    <dd>{{ formatDate(user.email_verified_at, 'No') }}</dd>
                    <dt class="text-base-content/60">Two-factor</dt>
                    <dd>{{ user.two_factor_enabled ? 'Enabled' : 'Off' }}</dd>
                </dl>
            </Card>

            <SuspensionPanel :user="user" :locked="locked" />

            <Card>
                <h2 class="mb-3 text-lg font-semibold">Role</h2>
                <form class="flex items-end gap-3" @submit.prevent="updateRole">
                    <SelectInput v-model="roleForm.role" class="w-48" :disabled="isSelf || isLastActiveOwner"
                                 :error="roleForm.errors.role !== undefined" aria-label="Role">
                        <option v-for="role in roleOptions" :key="role" :value="role">{{ role }}</option>
                    </SelectInput>
                    <PrimaryButton :disabled="isSelf || isLastActiveOwner || roleForm.processing || !roleForm.isDirty">Save</PrimaryButton>
                </form>
                <InputError :messages="roleForm.errors.role ?? roleForm.errors.user" />
                <p v-if="isSelf" class="mt-2 text-sm text-base-content/60">You cannot change your own role.</p>
                <p v-else-if="isLastActiveOwner" class="mt-2 text-sm text-base-content/60">
                    The last active owner cannot be demoted.
                </p>
            </Card>

            <Card padding="p-0">
                <h2 class="p-4 text-lg font-semibold">OSRS accounts</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="account in accounts" :key="account.id">
                            <td>
                                <Link :href="route('accounts.show', { account: account.username })" class="link link-hover font-medium">
                                    {{ account.username }}
                                </Link>
                            </td>
                            <td class="text-base-content/70">{{ account.account_type?.replace(/_/g, ' ') }}</td>
                        </tr>
                        <tr v-if="accounts.length === 0">
                            <td colspan="2" class="text-center text-base-content/50">No accounts claimed.</td>
                        </tr>
                    </tbody>
                </table>
            </Card>

            <div class="xl:col-span-2">
                <TokensPanel :user-id="user.id" :tokens="tokens" />
            </div>

            <Card class="xl:col-span-2">
                <h2 class="mb-2 text-lg font-semibold">Delete user</h2>
                <p class="mb-3 text-sm text-base-content/70">
                    Deletes the website user, their tokens and screenshots. Their OSRS accounts stay
                    and go back to the roster unclaimed.
                </p>
                <p v-if="locked" class="mb-3 text-sm text-base-content/60">{{ locked }}</p>
                <DangerButton type="button" :disabled="locked !== null" @click="confirmingDelete = true">
                    Delete user
                </DangerButton>
            </Card>
        </div>

        <ConfirmationModal :show="confirmingDelete" @close="confirmingDelete = false">
            <template #title>Delete {{ user.name }}</template>
            <template #content>
                This permanently deletes the user. It cannot be undone.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDelete = false">Cancel</SecondaryButton>
                <DangerButton :class="{ 'opacity-25': deleting }" :disabled="deleting" @click="destroy">
                    Delete
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AdminLayout>
</template>
