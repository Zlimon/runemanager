<template>
    <div>
        <div class="d-flex flex-row flex-wrap justify-content-around">
            <div v-for="account in group.account">
                <div @click='loadAccount(account)' class="btn button-rectangle background-world-map mb-2">
                    <div class="row align-items-center mb-2">
                        <div class="col-4">
                            <img
                                :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + account.user.id + '.png'"
                                class="pixel icon"
                                alt="Profile icon"
                                title="Click here to visit this account">
                        </div>

                        <div class="col">
                            <div class="text-left">
                                <span>
                                    <img v-if="account.account_type !== 'normal'"
                                         :src="'/images/' + account.account_type + '.png'"
                                         :alt="account.account_type + ' icon'">
                                    {{ account.username }}
                                </span>
                                <br>
                                <span class="font-small">
                                    <img class="pixel"
                                         :src="'/images/skill/total.png'"
                                         alt="Total level icon">
                                    {{ account.level }}
                                </span>
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
    name: "Account",

    props: {
        group: {required: true},
    },

    methods: {
        loadAccount(account) {
            this.$emit('clicked', account)
        }
    },
}
</script>

<style scoped>

</style>
