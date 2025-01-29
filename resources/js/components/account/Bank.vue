<template>
    <div>
        <div v-if="typeof user.user !== 'undefined' && account.user_id === user.user.id" class="text-center">
            <form>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="bankDisplayToggle" @change="updateDisplayBank()" :checked="display">
                    <label class="custom-control-label" for="bankDisplayToggle">Display bank</label>
                </div>
            </form>
        </div>

        <div v-if="errored" class="text-center py-5">
            <img src="/images/ignore.png"
                 class="pixel icon"
                 alt="Sad face">
            <h3>Sorry, no bank were found</h3>
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
                <div class="background-dialog-iron-rivets p-1 mb-1 pl-2" style="max-height: 40rem; overflow: scroll; overflow-x: hidden;">
                    <h3 class="text-center">Total value: {{ total.toLocaleString() }} gp</h3>
                    <div class="d-flex flex-row flex-wrap" style="max-width: 25rem; margin: 0 auto;">
                        <div v-for="(item, index) in bank" class="bank-item p-1">
                            <div v-if="item.quantity === 1">
                                <img
                                    :alt="item.name + ' item icon'"
                                    :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + item.id + '.png'"
                                    :title="item.name + ' x ' + item.quantity"
                                    class="hiscore-icon">
                            </div>
                            <div v-else-if="item.quantity > 0">
                                <img
                                    :alt="item.name + ' item icon'"
                                    :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + item.id + '.png'"
                                    :title="item.name + ' x ' + item.quantity"
                                    class="hiscore-icon">
                                <span class="collection-log-item-counter runescape-progress"
                                      style="left: 0; font-weight: normal;">
                                    {{ item.quantity }}
                                </span>
                            </div>
                            <div v-else>
                                <img
                                    :alt="item.name + ' item icon'"
                                    :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + item.id + '.png'"
                                    :title="item.name + ' x ' + item.quantity"
                                    class="hiscore-icon opacity-25">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        account: {required: true},
    },

    methods: {
        checkAccount(accountId) {
            return this.account.id === accountId;
        },

        fetchAccountBank() {
            axios
                .get('/api/account/' + this.account.username + '/bank')
                .then((response) => {
                    this.bank = response.data.data;
                    this.total = response.data.total;
                    this.display = response.data.display !== 0;
                    this.errored = false
                })
                .catch(error => {
                    console.log(error)
                    this.errored = true
                })
                .finally(() => this.loading = false)
        },

        updateDisplayBank() {
            axios
                .post('/api/account/' + this.account.username + '/bank', {
                    _method: 'patch',
                })
                .then((response) => {
                    console.log(response.data); // TODO local notification
                })
                .catch(error => {
                    console.log(error)
                });
        }
    },

    watch: {
        account(account) {
            this.account = account;
            this.fetchAccountBank();
        }
    },

    data() {
        return {
            loading: true,
            errored: false,
            user: {},
            bank: [],
            total: 0,
            display: false,
        }
    },

    mounted() {
        axios
            .get('/api/user')
            .then(response => {
                this.user = response.data;
            })
            .catch(error => {
                console.log(error)
            });

        this.fetchAccountBank();
    },

    created() {
        window.Echo.channel('account-bank')
            .listen('AccountBank', (e) => {
                if (this.checkAccount(e.account)) {
                    this.fetchAccountBank();
                }
            });
    },
}
</script>
