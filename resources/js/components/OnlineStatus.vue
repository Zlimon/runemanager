<template>
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
    },

    data() {
        return {
            online: this.account.online
        }
    },

    created() {
        window.Echo.channel('account-online')
            .listen('AccountOnline', (e) => {
                if (this.checkAccount(e.account[0])) {
                    this.online = e.account[1];
                }
            });
    },
}
</script>
