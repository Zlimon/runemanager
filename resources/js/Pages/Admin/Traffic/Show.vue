<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import Badge from '@/Components/Display/Badge.vue';
import Card from '@/Components/Cards/Card.vue';
import ChoiceChips from '@/Components/Tables/ChoiceChips.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import StatTile from '@/Components/Display/StatTile.vue';
import RequestsChart from './Partials/RequestsChart.vue';
import RequestsFeed from './Partials/RequestsFeed.vue';
import RoutesTable from './Partials/RoutesTable.vue';
import { flagLabels, flagVariant, formatBytes, formatMs, formatPercent, formatWhen } from './Partials/format.js';

/*
 * One user's plugin traffic: volume over time, the routes they hit, the
 * flags they raised, every account hash / username / IP they pushed from,
 * and their full request log.
 */
const props = defineProps({
    user: { type: Object, required: true },
    window: { type: String, required: true },
    windows: { type: Array, required: true },
    flags: { type: Array, required: true },
    flag: { type: String, default: null },
    summary: { type: Object, required: true },
    timeline: { type: Array, required: true },
    routes: { type: Array, required: true },
    flagCounts: { type: Object, required: true },
    identities: { type: Object, required: true },
    // Resource collection: { data, links, meta }.
    requests: { type: Object, required: true },
});

const windowLabels = { '1h': 'Last hour', '24h': 'Last 24 hours', '7d': 'Last 7 days' };
const windowOptions = props.windows.map((value) => ({ value, label: windowLabels[value] ?? value }));

const form = ref({ window: props.window, flag: props.flag ?? '' });

watch(form, () => {
    const query = Object.fromEntries(Object.entries(form.value).filter(([, value]) => value !== ''));
    router.get(route('admin.traffic.user', props.user.id), query, { preserveState: true, preserveScroll: true, replace: true });
}, { deep: true });

const hasUserPage = route().has('admin.users.show');
</script>

<template>
    <SettingsLayout :title="`Plugin traffic · ${user.name}`">
        <template #actions>
            <ChoiceChips v-model="form.window" :options="windowOptions" label="Time window" />
        </template>

        <div class="space-y-8">
            <p class="text-sm text-base-content/70">
                Successful live pings (heartbeat, position, vitals, status) are not logged; their failures and flags are.
                Floods of rejected requests are sampled per IP, so those counts are minimums.
            </p>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-base-content/70">
                <span>{{ user.email }}</span>
                <span>{{ user.tokens }} active {{ user.tokens === 1 ? 'token' : 'tokens' }}</span>
                <Link v-if="hasUserPage" :href="route('admin.users.show', user.id)" class="link">Manage user</Link>
                <Link :href="route('admin.traffic', { window })" class="link">All traffic</Link>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <StatTile label="Requests" :value="summary.requests" />
                <StatTile label="Error rate" :value="formatPercent(summary.error_rate)" />
                <StatTile label="Throttled" :value="summary.throttled" />
                <StatTile label="Flagged" :value="summary.flagged" />
                <StatTile label="Data sent" :value="formatBytes(summary.bytes)" />
                <StatTile label="Avg response" :value="formatMs(summary.avg_ms)" />
                <StatTile label="Account hashes" :value="identities.hashes.length" />
                <StatTile label="IPs" :value="identities.ips.length" />
            </div>

            <Card>
                <h2 class="mb-3 text-lg font-semibold">Requests over time</h2>
                <RequestsChart :buckets="timeline" />
            </Card>

            <div class="grid gap-4 lg:grid-cols-2">
                <Card padding="p-4">
                    <h2 class="mb-3 text-lg font-semibold">Flags raised</h2>
                    <p v-if="Object.keys(flagCounts).length === 0" class="text-sm text-base-content/70">None in this window.</p>
                    <div v-else class="flex flex-wrap gap-2">
                        <Badge v-for="(count, key) in flagCounts" :key="key" :variant="flagVariant(key)">
                            {{ flagLabels[key] ?? key }} · {{ count.toLocaleString() }}
                        </Badge>
                    </div>
                </Card>

                <Card padding="p-4">
                    <h2 class="mb-3 text-lg font-semibold">IP addresses</h2>
                    <p v-if="identities.ips.length === 0" class="text-sm text-base-content/70">None in this window.</p>
                    <ul v-else class="space-y-1 text-sm">
                        <li v-for="ip in identities.ips" :key="ip.ip" class="flex justify-between gap-2">
                            <code>{{ ip.ip }}</code>
                            <span class="text-base-content/70">
                                {{ ip.requests.toLocaleString() }} · {{ formatWhen(ip.last_seen) }}
                            </span>
                        </li>
                    </ul>
                </Card>
            </div>

            <section>
                <h2 class="mb-3 text-lg font-semibold">Account hashes and usernames</h2>
                <Card v-if="identities.hashes.length === 0" padding="p-4">
                    <p class="text-sm text-base-content/70">No account-scoped pushes in this window.</p>
                </Card>
                <div v-else class="grid gap-3 md:grid-cols-2">
                    <Card v-for="hash in identities.hashes" :key="hash.account_hash" padding="p-4">
                        <code class="block truncate text-xs" :title="hash.account_hash">{{ hash.account_hash }}</code>
                        <div class="my-2 flex flex-wrap gap-1">
                            <Badge v-for="name in hash.usernames" :key="name" size="sm">{{ name }}</Badge>
                            <Badge v-for="type in hash.account_types" :key="type" size="sm" variant="info">{{ type }}</Badge>
                        </div>
                        <p class="text-xs text-base-content/70">
                            {{ hash.requests.toLocaleString() }} requests · last {{ formatWhen(hash.last_seen) }}
                        </p>
                    </Card>
                </div>
            </section>

            <section>
                <h2 class="mb-3 text-lg font-semibold">Routes</h2>
                <RoutesTable :routes="routes" />
            </section>

            <section>
                <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                    <h2 class="text-lg font-semibold">Requests</h2>
                    <SelectInput v-model="form.flag" class="w-auto" aria-label="Filter by flag">
                        <option value="">Every request</option>
                        <option v-for="key in flags" :key="key" :value="key">{{ flagLabels[key] ?? key }}</option>
                    </SelectInput>
                </div>
                <RequestsFeed :requests="requests" :only="['requests', 'flag']" :show-user="false" />
            </section>
        </div>
    </SettingsLayout>
</template>
