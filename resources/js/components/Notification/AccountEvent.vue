<template>
    <div>
        <div v-if="errored" class="text-center py-5">
            <div class="text-center">
                <h3>Nothing interesting happens.</h3>
            </div>
        </div>

        <div v-else>
            <div v-if="loading">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>

            <div v-else>
                <div v-if="broadcastsData.data.length === 0">
                    <div class="text-center">
                        <h3>Nothing interesting happens.</h3>
                    </div>
                </div>

                <advanced-laravel-vue-paginate :data="broadcastsData" previousText="&lt;" nextText="&gt;" @paginateTo="getBroadcasts"/>

                <broadcast :broadcasts="broadcastsData.data"></broadcast>
            </div>
        </div>
    </div>
</template>

<script>
import broadcasts from './Broadcast.vue'

export default {
    props: {
        account: {required: true},
    },

    methods: {
        getBroadcasts(page = 1) {
            axios
                .get('/api/broadcast/account/' + this.account.username + '/event?page=' + page)
                .then((response) => {
                    this.broadcastsData = response.data;
                })
                .catch(error => {
                    console.log(error)
                    this.errored = true
                })
                .finally(() => this.loading = false)
        },

        checkAccount(accountId) {
            return this.account.id === accountId;
        },
    },

    components: {
        'broadcasts': broadcasts,
    },

    data() {
        return {
            loading: true,
            errored: false,
            broadcastsData: {}
        }
    },

    mounted() {
        this.getBroadcasts();
    },

    created() {
        window.Echo.channel('account')
            .listen('AccountEvent', (e) => {
                if (this.checkAccount(e.broadcast.log.account_id)) {
                    this.broadcastsData.data.unshift(e.broadcast);
                }
            });
    },
}
</script>
