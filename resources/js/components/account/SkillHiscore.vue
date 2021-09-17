<template>
    <div>
        <div v-if="errored" class="text-center py-5">
            <img :src="'/images/ignore.png'"
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

            <div v-else class="d-flex flex-row flex-wrap">
                <div v-for="(hiscore, name) in hiscores">
                    <div class="btn bg-dark text-light button-small button-combat-style-narrow">
                        <a :href="'/hiscore/skill/' + name">
                            <img :src="'/storage/resource-pack/skill/' + name + '.png'"
                                 :alt="name + ' skill icon'"
                                 :title="'Click here to visit ' + name + ' hiscores'">
                            <span>{{ hiscore.level }}</span>
                        </a>
                    </div>
                </div>
                <div class="btn bg-dark text-light button-small button-combat-style-narrow">
                    <a :href="'/hiscore/skill/total'">
                        <img :src="'/storage/resource-pack/skill/total.png'"
                             alt="Total level icon"
                             title="Click here to visit total level hiscores">
                        <span>{{ data.level }}</span>
                    </a>
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
}
</script>
