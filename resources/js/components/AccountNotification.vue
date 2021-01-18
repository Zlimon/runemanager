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
                <div v-if="notificationsData.data.length === 0">
                    <div class="text-center">
                        <h3>Nothing interesting happens.</h3>
                    </div>
                </div>

                <advanced-laravel-vue-paginate :data="notificationsData" previousText="&lt;" nextText="&gt;" @paginateTo="getNotifications"/>

                <notifications :notifications="notificationsData.data"></notifications>
            </div>
        </div>
    </div>
</template>

<script>
import notifications from './Notification.vue'

export default {
    props: {
        account: {required: true},
    },

    methods: {
        getNotifications(page = 1) {
            axios
                .get('/api/notification/account/' + this.account.username + '?page=' + page)
                .then((response) => {
                    this.notificationsData = response.data;
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
        'notifications': notifications,
    },

    data() {
        return {
            loading: true,
            errored: false,
            notificationsData: {}
        }
    },

    mounted() {
        this.getNotifications();
    },

    created() {
        window.Echo.channel('account-all')
            .listen('AccountAll', (e) => {
                if (this.checkAccount(e.notification.log.account_id)) {
                    this.notificationsData.data.unshift(e.notification);
                }
            });
    },
}
</script>
