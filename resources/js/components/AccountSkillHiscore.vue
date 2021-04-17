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
                            <img :alt="'Total level skill icon'"
                                 :src="'/images/skill/total.png'"
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
        account: {type: String, required: true},
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
        axios
            .get('/api/account/' + this.account + '/skill')
            .then((response) => {
                this.data = response.data.data;
                this.hiscores = response.data.meta.skillHiscores;
                this.$emit('load', response.data.data)
            })
            .catch(error => {
                console.log(error)
                this.errored = true
            })
            .finally(() => this.loading = false)
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
