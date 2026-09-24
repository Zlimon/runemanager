<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import Card from '@/Components/Cards/Card.vue';
import Badge from '@/Components/Display/Badge.vue';

/*
 * One news post in a listing — the news index and the dashboard digest both use
 * this, so a change to how a post reads happens in one place.
 *
 * `compact` drops the header image and shortens the excerpt, for the narrow
 * dashboard column where a full card would crowd out everything else.
 */
const props = defineProps({
    post: { type: Object, required: true },
    compact: { type: Boolean, default: false },
});

const publishedLabel = computed(() => {
    if (!props.post.published_at) {
        return 'Draft';
    }

    const at = dayjs(props.post.published_at);

    return at.isAfter(dayjs())
        ? `Scheduled for ${at.format('MMM D, YYYY')}`
        : at.format('MMM D, YYYY');
});
</script>

<template>
    <Card :padding="compact ? 'p-4' : 'p-0'" class="h-full overflow-hidden">
        <Link :href="route('news.show', post.slug)" class="group block h-full">
            <img v-if="!compact"
                 :src="post.image_url"
                 :alt="post.title"
                 class="h-40 w-full object-cover"
                 loading="lazy">

            <div :class="compact ? '' : 'p-4'">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="text-base font-semibold group-hover:text-primary"
                        :class="compact ? 'line-clamp-2' : ''">
                        {{ post.title }}
                    </h3>
                    <!-- A draft or scheduled post is only ever sent to a news
                         manager, so the badge doubles as the reminder that this
                         row is not public yet. -->
                    <Badge v-if="!post.is_published" variant="warning" size="sm" class="shrink-0">
                        {{ post.published_at ? 'Scheduled' : 'Draft' }}
                    </Badge>
                </div>

                <p class="mt-2 text-sm text-base-content/70"
                   :class="compact ? 'line-clamp-2' : 'line-clamp-3'">
                    {{ post.excerpt }}
                </p>

                <p class="mt-2 text-xs text-base-content/60">
                    {{ publishedLabel }}
                    <template v-if="post.created_by?.name"> · by {{ post.created_by.name }}</template>
                </p>
            </div>
        </Link>
    </Card>
</template>
