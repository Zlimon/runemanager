<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    rosterRequired: { type: Boolean, default: false },
    rosterAccounts: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    account_id: '',
    terms: false,
});

// Clan/group registration is closed until the roster has at least one
// unclaimed account for the new user to select.
const registrationClosed = props.rosterRequired && props.rosterAccounts.length === 0;

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="registrationClosed"
             class="rounded p-4 text-center text-sm pack-bg-card resource-pack-border">
            Registration is currently closed — there are no available accounts to claim.
            Ask an admin to add you to the roster, then register here.
            <div class="mt-3">
                <Link :href="route('login')" class="link link-hover">Back to login</Link>
            </div>
        </div>

        <form v-else @submit.prevent="submit">
            <div v-if="rosterRequired">
                <InputLabel for="account_id" value="Your account" />
                <select
                    id="account_id"
                    v-model="form.account_id"
                    class="select select-bordered mt-1 block w-full"
                    required
                >
                    <option value="" disabled>Select the account you own</option>
                    <option v-for="account in rosterAccounts" :key="account.id" :value="account.id">
                        {{ account.username }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.account_id" />
            </div>

            <div :class="{ 'mt-4': rosterRequired }">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ms-2">
                            I agree to the <a target="_blank" :href="route('terms.show')" class="link link-hover">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" class="link link-hover">Privacy Policy</a>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')" class="link link-hover text-sm text-base-content/70">
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
