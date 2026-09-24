<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Cards/Card.vue';
import Checkbox from '@/Components/Form/Checkbox.vue';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import ArticleEditor from '@/Pages/News/Partials/ArticleEditor.vue';

const props = defineProps({
    // Null when writing a new article.
    post: { type: Object, default: null },
});

const isEditing = computed(() => props.post !== null);

const form = useForm({
    title: props.post?.title ?? '',
    excerpt: props.post?.excerpt ?? '',
    body: props.post?.body ?? '',
    image: null,
    remove_image: false,
    published: props.post ? props.post.published_at !== null : false,
    // The datetime-local input wants "YYYY-MM-DDTHH:mm", not an ISO string.
    published_at: props.post?.published_at
        ? dayjs(props.post.published_at).format('YYYY-MM-DDTHH:mm')
        : '',
    announce: props.post ? props.post.announcement_id !== null : false,
});

const imagePreview = ref(props.post?.image_url ?? null);

const pickImage = (event) => {
    const file = event.target.files?.[0] ?? null;
    form.image = file;
    form.remove_image = false;

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => { imagePreview.value = e.target.result; };
        reader.readAsDataURL(file);
    }
};

const clearImage = () => {
    form.image = null;
    form.remove_image = true;
    imagePreview.value = null;
};

const submit = () => {
    // forceFormData, because the header image makes this multipart. Inertia
    // spoofs the method for an update, which is why the route is a POST.
    const options = { forceFormData: true, preserveScroll: true };

    if (isEditing.value) {
        form.post(route('news.update', props.post.slug), options);

        return;
    }

    form.post(route('news.store'), options);
};
</script>

<template>
    <AppLayout :title="isEditing ? 'Edit article' : 'Write article'">
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="flex items-baseline justify-between">
                    <h1 class="header-chatbox-sword text-2xl font-bold">
                        {{ isEditing ? 'Edit article' : 'Write article' }}
                    </h1>
                    <Link :href="isEditing ? route('news.show', post.slug) : route('news.index')"
                          class="link link-hover text-sm text-base-content/70">
                        Cancel
                    </Link>
                </div>

                <form class="mt-6 space-y-6" @submit.prevent="submit">
                    <Card padding="p-6" class="space-y-4">
                        <div>
                            <InputLabel for="news-title" value="Title" />
                            <TextInput id="news-title" v-model="form.title" type="text"
                                       class="mt-1" :error="form.errors.title !== undefined" />
                            <InputError :messages="form.errors.title" />
                        </div>

                        <div>
                            <InputLabel for="news-excerpt" value="Summary" />
                            <textarea id="news-excerpt" v-model="form.excerpt" rows="2"
                                      class="textarea textarea-bordered mt-1 block w-full"
                                      :class="{ 'textarea-error': form.errors.excerpt }" />
                            <p class="mt-1 text-xs text-base-content/60">
                                Shown in the news listings and on the dashboard, and used as the
                                in-game announcement text. {{ form.excerpt.length }}/300
                            </p>
                            <InputError :messages="form.errors.excerpt" />
                        </div>

                        <div>
                            <InputLabel value="Header image (optional)" />
                            <p class="mt-1 text-xs text-base-content/60">
                                Leave empty to use the default news image.
                            </p>
                            <div class="mt-2 flex items-start gap-4">
                                <img v-if="imagePreview" :src="imagePreview" alt=""
                                     class="h-24 w-40 rounded border border-base-300 object-cover">
                                <div class="space-y-2">
                                    <input type="file" accept="image/*"
                                           class="file-input file-input-bordered file-input-sm w-full max-w-xs"
                                           @change="pickImage">
                                    <SecondaryButton v-if="imagePreview" type="button" class="btn-xs"
                                                     @click="clearImage">
                                        Use default image
                                    </SecondaryButton>
                                </div>
                            </div>
                            <InputError :messages="form.errors.image" />
                        </div>
                    </Card>

                    <Card padding="p-6">
                        <InputLabel value="Article" />
                        <div class="mt-1">
                            <ArticleEditor v-model="form.body" :error="form.errors.body !== undefined" />
                        </div>
                        <InputError :messages="form.errors.body" />
                    </Card>

                    <Card padding="p-6" class="space-y-4">
                        <label class="flex items-start gap-3">
                            <Checkbox v-model:checked="form.published" class="mt-1" />
                            <span>
                                <span class="font-semibold">Publish</span>
                                <span class="block text-xs text-base-content/60">
                                    Leave unticked to keep this as a draft only news managers can see.
                                </span>
                            </span>
                        </label>

                        <div v-if="form.published" class="ps-8">
                            <InputLabel for="news-published-at" value="Publish date (optional)" />
                            <TextInput id="news-published-at" v-model="form.published_at"
                                       type="datetime-local" class="mt-1 max-w-xs"
                                       :error="form.errors.published_at !== undefined" />
                            <p class="mt-1 text-xs text-base-content/60">
                                Leave empty to publish immediately. A future date schedules the post.
                            </p>
                            <InputError :messages="form.errors.published_at" />
                        </div>

                        <label class="flex items-start gap-3">
                            <Checkbox v-model:checked="form.announce" class="mt-1" />
                            <span>
                                <span class="font-semibold">Alert players in-game</span>
                                <span class="block text-xs text-base-content/60">
                                    Raises an announcement the RuneLite plugin shows in-game, using the
                                    summary above. Only sent once the article is actually published, so a
                                    draft or scheduled post never alerts anyone early.
                                </span>
                            </span>
                        </label>
                    </Card>

                    <div class="flex items-center justify-end gap-3">
                        <PrimaryButton :class="{ 'opacity-25': form.processing }"
                                       :disabled="form.processing">
                            {{ form.published ? 'Publish' : 'Save draft' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
