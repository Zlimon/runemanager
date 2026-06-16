<script setup>
import ApiTokenManager from '@/Pages/API/Partials/ApiTokenManager.vue';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

defineProps({
    tokens: Array,
    availablePermissions: Array,
    defaultPermissions: Array,
});
</script>

<template>
    <SettingsLayout title="API tokens">
        <ApiTokenManager
            :tokens="tokens"
            :available-permissions="availablePermissions"
            :default-permissions="defaultPermissions"
        />
    </SettingsLayout>
</template>
