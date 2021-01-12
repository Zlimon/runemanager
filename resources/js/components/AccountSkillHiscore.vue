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
                    <img :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + data.user.icon_id + '.png'"
                         class="d-none d-md-inline pixel icon float-left"
                         alt="Profile icon"
                         style="width: 7.5rem; height: 7.5rem;">

                    <div class="col">
                        <h1 class="text-left">{{ data.username }}</h1>

                        <span>Rank: <strong>{{ data.rank }}</strong></span>
                        <br>
                        <span>Total XP: <strong>{{ data.xp }}</strong></span>
                        <br>
                        <span>Total Level: <strong>{{ data.level }}</strong></span>
                        <br>
                        <span>Joined: <strong>{{ data.joined }}</strong></span>
                    </div>
                </div>

                <table>
                    <tr>
                        <th></th>
                        <th>Level</th>
                        <th>XP</th>
                        <th>Hiscore Rank</th>
                    </tr>
                    <tr v-for="(hiscore, name) in hiscores">
                        <td>
                            <a :href="'/hiscore/skill/' + name">
                                <img :alt="name + ' skill icon'"
                                     :src="'/images/skill/' + name + '.png'"
                                     :title="'Click here to visit ' + name + ' hiscores'"
                                     class="pixel hiscore-icon">
                                <span class="d-none d-md-inline">{{ name | capitalize }}</span>
                            </a>
                        </td>
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
