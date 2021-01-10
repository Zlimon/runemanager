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
                    <img :alt="meta.npc + ' icon'"
                         :src="'/images/npc/' + meta.npc + '.png'"
                         class="d-none d-md-inline pixel icon"
                         style="width: 7.5rem; height: 7.5rem;">

                    <div class="col">
                        <h1 class="text-left">{{ meta.alias }}</h1>

                        <span>Total Kills: <strong>{{ meta.total_kills }}</strong></span>
                        <br>
                        <span>Average Kills: <strong>{{ meta.average_total_kills }}</strong></span>
                    </div>
                </div>

                <table>
                    <tr>
                        <th>Rank</th>
                        <th>Account</th>
                        <th>Kill Count</th>
                        <th>Collection Log</th>
                        <th>Obtained</th>
                    </tr>
                    <tr v-for="(hiscore, index) in hiscores">
                        <td>{{ index + 1 }}</td>
                        <td><a :href="'/account/' + hiscore.account.username">{{ hiscore.account.username }}</a>
                        </td>
                        <td>{{ hiscore.kill_count }}</td>
                        <td>
                            <a :data-target="$idRef(index)" class="btn background-world-map" data-toggle="modal">
                                <img :title="'Click here to see collection log for ' + meta.alias"
                                     alt="Collection log item icon"
                                     src="https://www.osrsbox.com/osrsbox-db/items-icons/22711.png">
                            </a>
                        </td>
                        <td>
                            <div v-if="(hiscore.obtained !== null ? hiscore.obtained : 0) === total">
                        <span class="runescape-success">
                            {{ (hiscore.obtained !== null ? hiscore.obtained : 0) }} / {{ total }}
                        </span>
                            </div>
                            <div v-else-if="hiscore.obtained > 0">
                                <span class="runescape-progress">{{ hiscore.obtained }} / {{ total }}</span>
                            </div>
                            <div v-else>
                        <span class="runescape-danger">
                            {{ (hiscore.obtained !== null ? hiscore.obtained : 0) }} / {{ total }}
                        </span>
                            </div>
                        </td>
                        <div :id="$id(index)" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content bg-dark">
                                    <div class="modal-body background-dialog-iron-rivets text-light">
                                        <button class="btn btn-lg button-window-close float-right"
                                                data-dismiss="modal"></button>
                                        <h1>{{ hiscore.account.username }}</h1>
                                        <div class="d-flex flex-row flex-wrap justify-content-center">
                                            <div v-for="(count, item) in hiscore.log"
                                                 class="collection-log-item rounded background-world-map bg-dark p-4">
                                                <div v-if="count === 1">
                                                    <img :alt="item + ' item icon'"
                                                         :src="'/images/npc/' + meta.npc + '/' + item + '.png'"
                                                         :title="item.replaceAll('_', ' ') | capitalize"
                                                         class="pixel hiscore-icon">
                                                </div>
                                                <div v-else-if="count > 0">
                                                    <img :alt="item + ' item icon'"
                                                         :src="'/images/npc/' + meta.npc + '/' + item + '.png'"
                                                         :title="item.replaceAll('_', ' ') | capitalize"
                                                         class="pixel hiscore-icon">
                                                    <span class="collection-log-item-counter runescape-progress">
                                                {{ count }}
                                            </span>
                                                </div>
                                                <div v-else>
                                                    <img :alt="item + ' item icon'"
                                                         :src="'/images/npc/' + meta.npc + '/' + item + '.png'"
                                                         :title="item.replaceAll('_', ' ') | capitalize"
                                                         class="pixel hiscore-icon faded">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        npc: {type: String, required: true}
    },

    data() {
        return {
            loading: true,
            errored: false,
            hiscores: {},
            meta: {},
            total: 0
        }
    },

    mounted() {
        axios
            .get('/api/hiscore/npc/' + this.npc)
            .then((response) => {
                this.hiscores = response.data.data;
                this.meta = response.data.meta;
                this.total = Object.keys(response.data.data[0].log).length
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
