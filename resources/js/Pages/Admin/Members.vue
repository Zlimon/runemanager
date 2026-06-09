<script setup>
import { computed } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    mode: { type: String, required: true },
    manageable: { type: Boolean, default: false },
    accounts: { type: Array, default: () => [] },
});

const form = useForm({ username: '' });

const addMember = () => {
    form.post(route('admin.members.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const removeMember = (account) => {
    if (!confirm(`Remove ${account.username}? This deletes their account data.`)) {
        return;
    }
    router.delete(route('admin.members.destroy', account.id), { preserveScroll: true });
};

const intro = computed(() => props.mode === 'clan'
    ? 'Members are synced from your in-game clan by the owner\'s plugin. You can prune stale entries here.'
    : 'Add the in-game usernames of your group members. Each becomes an account they claim by logging in through the plugin.');
</script>

<template>
    <AppLayout title="Members">
        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                <h1 class="header-chatbox-sword text-2xl font-bold">Members</h1>
                <p class="text-sm text-base-content/60">{{ intro }}</p>

                <Card v-if="manageable">
                    <form class="flex items-end gap-3" @submit.prevent="addMember">
                        <div class="flex-1">
                            <InputLabel for="username" value="In-game username" />
                            <TextInput id="username"
                                       v-model="form.username"
                                       type="text"
                                       class="mt-1 block w-full"
                                       autocomplete="off"
                                       :error="form.errors.username !== undefined" />
                            <InputError v-if="form.errors.username" :messages="form.errors.username" />
                        </div>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Add
                        </PrimaryButton>
                    </form>
                </Card>

                <Card padding="p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Claimed by</th>
                                <th v-if="mode === 'clan'">Rank</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="account in accounts" :key="account.id">
                                <td class="font-medium">{{ account.username }}</td>
                                <td>
                                    <span v-if="account.claimed_by">{{ account.claimed_by }}</span>
                                    <span v-else class="text-base-content/50">Unclaimed</span>
                                </td>
                                <td v-if="mode === 'clan'" class="text-base-content/70">
                                    {{ account.clan_title ?? '—' }}
                                </td>
                                <td class="text-right">
                                    <DangerButton type="button" class="btn-xs" @click="removeMember(account)">
                                        Remove
                                    </DangerButton>
                                </td>
                            </tr>
                            <tr v-if="accounts.length === 0">
                                <td colspan="4" class="text-center text-base-content/50">
                                    No members yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
