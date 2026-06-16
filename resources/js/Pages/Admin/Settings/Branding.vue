<script setup>
import { useForm } from "@inertiajs/vue3";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import Card from "@/Components/Card.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    branding: { type: Object, default: () => ({}) },
});

const form = useForm({
    logo: null,
    banner: null,
    remove_logo: false,
    remove_banner: false,
});

const submit = () => {
    form.post(route('admin.branding.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => form.reset('logo', 'banner', 'remove_logo', 'remove_banner'),
    });
};
</script>

<template>
    <SettingsLayout title="Branding">
        <Card>
            <form class="space-y-6" @submit.prevent="submit">
                <div>
                    <InputLabel value="Logo" />
                    <img v-if="props.branding.logo_url" :src="props.branding.logo_url" alt="Current logo"
                         class="mt-1 h-12 object-contain">
                    <input type="file" accept="image/*"
                           class="file-input file-input-bordered mt-1 block w-full"
                           @input="form.logo = $event.target.files[0]">
                    <label v-if="props.branding.logo_url" class="mt-1 flex items-center gap-2 text-xs text-base-content/70">
                        <input type="checkbox" v-model="form.remove_logo" class="checkbox checkbox-xs"> Remove current logo
                    </label>
                    <InputError v-if="form.errors.logo" :messages="form.errors.logo" />
                </div>

                <div>
                    <InputLabel value="Banner" />
                    <img v-if="props.branding.banner_url" :src="props.branding.banner_url" alt="Current banner"
                         class="mt-1 h-24 w-full rounded object-cover">
                    <input type="file" accept="image/*"
                           class="file-input file-input-bordered mt-1 block w-full"
                           @input="form.banner = $event.target.files[0]">
                    <label v-if="props.branding.banner_url" class="mt-1 flex items-center gap-2 text-xs text-base-content/70">
                        <input type="checkbox" v-model="form.remove_banner" class="checkbox checkbox-xs"> Remove current banner
                    </label>
                    <InputError v-if="form.errors.banner" :messages="form.errors.banner" />
                </div>

                <div class="flex justify-end">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Save branding
                    </PrimaryButton>
                </div>
            </form>
        </Card>
    </SettingsLayout>
</template>
