<script setup>
import { router } from '@inertiajs/vue3';
import Card from '@/Components/Cards/Card.vue';
import DangerButton from '@/Components/Buttons/DangerButton.vue';
import { formatDate } from './formatDate.js';

/*
 * The user's Sanctum tokens (the plugin's logins), each revocable, plus a
 * revoke-all. A revoked plugin has to sign in again.
 */
const props = defineProps({
    userId: { type: Number, required: true },
    tokens: { type: Array, required: true },
});

const revoke = (token = null) => {
    const message = token
        ? `Revoke the token "${token.name}"?`
        : 'Revoke every token of this user?';
    if (!confirm(message)) {
        return;
    }
    router.delete(route('admin.users.tokens.destroy', token ? [props.userId, token.id] : [props.userId]), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Card padding="p-0">
        <div class="flex items-center justify-between p-4">
            <h2 class="text-lg font-semibold">API tokens</h2>
            <DangerButton v-if="tokens.length > 1" type="button" class="btn-sm" @click="revoke()">
                Revoke all
            </DangerButton>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Last used</th>
                    <th>Created</th>
                    <th class="text-right" />
                </tr>
            </thead>
            <tbody>
                <tr v-for="token in tokens" :key="token.id">
                    <td class="font-medium">{{ token.name }}</td>
                    <td class="whitespace-nowrap text-base-content/70">{{ formatDate(token.last_used_at, 'Never') }}</td>
                    <td class="whitespace-nowrap text-base-content/70">{{ formatDate(token.created_at) }}</td>
                    <td class="text-right">
                        <DangerButton type="button" class="btn-xs" @click="revoke(token)">Revoke</DangerButton>
                    </td>
                </tr>
                <tr v-if="tokens.length === 0">
                    <td colspan="4" class="text-center text-base-content/50">No tokens.</td>
                </tr>
            </tbody>
        </table>
    </Card>
</template>
