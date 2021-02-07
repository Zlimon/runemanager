<template>
    <div>
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
                <div class="background-dialog-iron-rivets p-2 mb-2">
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
                                    class="hiscore-icon faded">
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

        fetchBank() {
            axios
                .get('/api/account/' + this.account.username + '/bank')
                .then((response) => {
                    this.bank = response.data.data;
                })
                .catch(error => {
                    console.log(error)
                    this.errored = true
                })
                .finally(() => this.loading = false)
        }
    },

    data() {
        return {
            loading: true,
            errored: false,
            bank: [],
        }
    },

    mounted() {
        this.fetchBank();
    },

    created() {
        window.Echo.channel('account-bank')
            .listen('AccountBank', (e) => {
                if (this.checkAccount(e.account)) {
                    this.fetchBank();
                }
            });
    },
}
</script>
