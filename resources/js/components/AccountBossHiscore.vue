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
                        <th>Kill Count</th>
                        <th>Hiscore Rank</th>
                        <th>Collection Log</th>
                        <th>Obtained</th>
                    </tr>
                    <tr v-for="(hiscore, name) in meta.bossHiscores">
                        <td>
                            <a :href="'/hiscore/boss/' + name">
                                <img :alt="hiscore.alias + ' boss icon'"
                                     :src="'/images/boss/' + name.replace(/ /g, '_') + '.png'"
                                     :title="'Click here to visit ' + hiscore.alias + ' hiscores'"
                                     class="pixel hiscore-icon">
                                <span class="d-none d-md-inline">{{ hiscore.alias }}</span>
                            </a>
                        </td>
                        <td>{{ hiscore.kill_count }}</td>
                        <td>{{ hiscore.rank }}</td>
                        <td>
                            <a :data-target="$idRef(name.replace(/ /g, '_'))" class="btn background-world-map"
                               data-toggle="modal">
                                <img :title="'Click here to see collection log for ' + hiscore.alias"
                                     alt="Collection log item icon"
                                     src="https://www.osrsbox.com/osrsbox-db/items-icons/22711.png">
                            </a>
                        </td>
                        <td>
                            <div v-if="(hiscore.obtained !== null ? hiscore.obtained : 0) === hiscore.total">
                        <span class="runescape-success">
                            {{ (hiscore.obtained !== null ? hiscore.obtained : 0) }} / {{ hiscore.total }}
                        </span>
                            </div>
                            <div v-else-if="hiscore.obtained > 0">
                                <span class="runescape-progress">{{ hiscore.obtained }} / {{ hiscore.total }}</span>
                            </div>
                            <div v-else>
                        <span class="runescape-danger">
                            {{ (hiscore.obtained !== null ? hiscore.obtained : 0) }} / {{ hiscore.total }}
                        </span>
                            </div>
                        </td>
                        <div :id="$id(name.replace(/ /g, '_'))" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content bg-dark">
                                    <div class="modal-body background-dialog-iron-rivets text-light">
                                        <button class="btn btn-lg button-window-close float-right"
                                                data-dismiss="modal"></button>
                                        <h1>{{ data.username }}</h1>
                                        <div class="d-flex flex-row flex-wrap justify-content-center">
                                            <div v-for="(count, item) in hiscore.log"
                                                 class="collection-log-item background-world-map bg-dark p-4">
                                                <div v-if="count === 1">
                                                    <img :alt="item + ' item icon'"
                                                         :src="'/images/boss/' + name.replace(/ /g, '_') + '/' + item + '.png'"
                                                         :title="item.replaceAll('_', ' ') | capitalize"
                                                         class="pixel hiscore-icon">
                                                </div>
                                                <div v-else-if="count > 0">
                                                    <img :alt="item + ' item icon'"
                                                         :src="'/images/boss/' + name.replace(/ /g, '_') + '/' + item + '.png'"
                                                         :title="item.replaceAll('_', ' ') | capitalize"
                                                         class="pixel hiscore-icon">
                                                    <span class="collection-log-item-counter runescape-progress">
                                                {{ count }}
                                            </span>
                                                </div>
                                                <div v-else>
                                                    <img :alt="item + ' item icon'"
                                                         :src="'/images/boss/' + name.replace(/ /g, '_') + '/' + item + '.png'"
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
        account: {type: String, required: true},
    },

    data() {
        return {
            loading: true,
            errored: false,
            data: {},
            meta: {}
        }
    },

    mounted() {
        axios
            .get('/api/account/' + this.account + '/boss')
            .then((response) => {
                this.data = response.data.data;
                this.meta = response.data.meta;
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
        },
    }
}
</script>
