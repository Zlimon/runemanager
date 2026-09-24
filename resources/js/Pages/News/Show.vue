<script setup>
import { Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Cards/Card.vue';
import NewsCard from '@/Components/Cards/NewsCard.vue';
import Badge from '@/Components/Display/Badge.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import DangerButton from '@/Components/Buttons/DangerButton.vue';

const props = defineProps({
    post: { type: Object, required: true },
    recent: { type: Array, default: () => [] },
    can_manage: { type: Boolean, default: false },
});

const dt = (iso) => dayjs(iso).format('MMMM D, YYYY');

const destroy = () => {
    if (!confirm(`Delete "${props.post.title}"? This cannot be undone.`)) {
        return;
    }

    router.delete(route('news.destroy', props.post.slug));
};
</script>

<template>
    <AppLayout :title="post.title">
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <Link :href="route('news.index')" class="link link-hover text-sm text-base-content/70">
                    &larr; All news
                </Link>

                <article class="mt-4">
                    <img :src="post.image_url" :alt="post.title"
                         class="h-56 w-full rounded object-cover sm:h-72">

                    <div class="mt-6 flex items-start justify-between gap-3">
                        <h1 class="text-3xl font-extrabold">{{ post.title }}</h1>
                        <Badge v-if="!post.is_published" variant="warning" class="shrink-0">
                            {{ post.published_at ? 'Scheduled' : 'Draft' }}
                        </Badge>
                    </div>

                    <p class="mt-2 text-sm text-base-content/60">
                        <template v-if="post.published_at">{{ dt(post.published_at) }}</template>
                        <template v-else>Not published yet</template>
                        <template v-if="post.created_by?.name"> · by {{ post.created_by.name }}</template>
                        <template v-if="post.announcement_id"> · announced in-game</template>
                    </p>

                    <div v-if="can_manage" class="mt-4 flex gap-2">
                        <Link :href="route('news.edit', post.slug)">
                            <SecondaryButton>Edit</SecondaryButton>
                        </Link>
                        <DangerButton type="button" @click="destroy">Delete</DangerButton>
                    </div>

                    <Card padding="p-6" class="mt-6">
                        <!-- The body is sanitised server-side on save against the
                             `news` purifier allowlist, which is why rendering it
                             as HTML here is safe. Never render an unsanitised
                             body through this. -->
                        <div class="article-body" v-html="post.body" />
                    </Card>
                </article>

                <section v-if="recent.length" class="mt-12">
                    <h2 class="text-xl font-bold">More news</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <NewsCard v-for="item in recent" :key="item.id" :post="item" compact />
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
