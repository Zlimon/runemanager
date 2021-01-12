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
                <div class="d-flex flex-row">
                    <img :alt="meta.skill + ' icon'"
                         :src="'/images/skill/' + meta.skill + '.png'"
                         class="d-none d-md-inline pixel icon"
                         style="width: 7.5rem; height: 7.5rem;">

                    <div class="col">
                        <h1 class="text-left">{{ meta.skill }}</h1>

                        <span>Total XP: <strong>{{ meta.total_xp }}</strong></span>
                        <br>
                        <span>Average Level: <strong>{{ meta.average_total_level }}</strong></span>
                        <br>
                        <span>Maxed: <strong>{{ meta.total_max_level }}</strong></span>
                    </div>
                </div>

                <table>
                    <tr>
                        <th>Rank</th>
                        <th>Account</th>
                        <th>Level</th>
                        <th>XP</th>
                        <th>Hiscore Rank</th>
                    </tr>
                    <tr v-for="(hiscore, index) in hiscores">
                        <td>{{ index + 1 }}</td>
                        <td><a :href="'/account/' + hiscore.username">{{ hiscore.username }}</a></td>
                        <td>{{ hiscore.level }}</td>
                        <td>{{ hiscore.xp }}</td>
                        <td>{{ hiscore.rank }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        skill: {type: String, required: true}
    },

    data() {
        return {
            loading: true,
            errored: false,
            hiscores: {},
            meta: {}
        }
    },

    mounted() {
        axios
            .get('/api/hiscore/skill/' + this.skill)
            .then((response) => {
                this.hiscores = response.data.data;
                this.meta = response.data.meta;
            })
            .catch(error => {
                console.log(error)
                this.errored = true
            })
            .finally(() => this.loading = false)
    },
}
</script>
