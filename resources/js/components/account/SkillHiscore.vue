<template>
    <div>
        <div v-if="errored" class="text-center py-5">
            <img src="/images/ignore.png"
                 class="pixel icon"
                 alt="Sad face">
            <h1>Sorry, no hiscores were found</h1>
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
                <div class="d-flex flex-wrap justify-content-around">
                    <div v-for="(hiscore, name) in hiscores">
                        <div class="button-combat-style-narrow text-center button-small">
                            <a :href="'/hiscore/skill/' + name">
                                <img :alt="name + ' skill icon'"
                                     :src="'/storage/resource-pack/skill/' + name + '.png'"
                                     :title="'Click here to visit ' + name + ' hiscores'"
                                     class="">
                                <span>{{ hiscore.level }}</span>
                            </a>
                        </div>
                    </div>
                    <div class="button-combat-style-narrow text-center button-small">
                        <a :href="'/hiscore/skill/total'">
                            <img :alt="'Total level icon'"
                                 :src="'/storage/resource-pack/skill/total.png'"
                                 :title="'Click here to visit total level hiscores'"
                                 class="">
                            <span>{{ data.level }}</span>
                        </a>
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
        fetchAccountSkillHiscores() {
            axios
                .get('/api/account/' + this.account.username + '/skill')
                .then((response) => {
                    this.data = response.data.data;
                    this.hiscores = response.data.meta.skill_hiscores;
                    this.$emit('load', response.data.data)
                    this.errored = false
                })
                .catch(error => {
                    console.log(error)
                    this.errored = true
                })
                .finally(() => this.loading = false)
        }
    },

    watch: {
        account(account) {
            this.account = account;
            this.fetchAccountSkillHiscores();
        }
    },

    data() {
        return {
            loading: true,
            errored: false,
            data: {},
            hiscores: {}
        }
    },

    mounted() {
        this.fetchAccountSkillHiscores();
    },

    filters: {
        capitalize: function (value) {
            if (!value) return ''
            value = value.toString()
            return value.charAt(0).toUpperCase() + value.slice(1)
        }
    }
}
</script>
