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

            <div v-else class="d-flex flex-row flex-wrap justify-content-between">
                <div v-for="(hiscore, name) in hiscores">
                    <div class="btn bg-dark text-light button-small button-combat-style-narrow">
                        <div v-if="!showCollectionLog">
                            <a :href="'/hiscore/boss/' + name">
                                <img :src="'/images/boss/' + name + '.png'"
                                     class="hiscore-icon-small"
                                     :alt="name + ' boss icon'"
                                     :title="'Click here to visit ' + name + ' hiscores'">
                                <span>{{ hiscore.kill_count }}</span>
                            </a>
                        </div>

                        <div v-else>
                            <div :data-bs-target="$idRef(name)" data-bs-toggle="modal" style="cursor: pointer;">
                                <span>
                                    <img :src="'/images/boss/' + name + '.png'"
                                         class="hiscore-icon-small"
                                         :alt="name + ' boss icon'"
                                         :title="'Click here to visit ' + name + ' hiscores'">
                                    <span v-if="(hiscore.obtained !== null ? hiscore.obtained : 0) === hiscore.total"
                                          class="runescape-success">
                                        {{ (hiscore.obtained !== null ? hiscore.obtained : 0) }}/{{ hiscore.total }}
                                    </span>
                                    <span v-else-if="hiscore.obtained > 0" class="runescape-progress">
                                        {{ hiscore.obtained }}/{{ hiscore.total }}
                                    </span>
                                    <span v-else class="runescape-danger">
                                        {{ (hiscore.obtained !== null ? hiscore.obtained : 0) }}/{{ hiscore.total }}
                                    </span>
                                </span>
                            </div>

                            <!--Modal-->
                            <div :id="$id(name)" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content bg-dark">
                                        <div class="modal-body background-dialog-iron-rivets text-light">
                                            <button type="button"
                                                    class="btn-close float-end button-window-close"
                                                    data-bs-dismiss="modal">
                                            </button>
                                            <h1>{{ data.username }}</h1>

                                            <div class="d-flex flex-row flex-wrap justify-content-center">
                                                <div v-for="(count, item) in hiscore.log"
                                                     class="p-4 bg-dark collection-log-item background-world-map ">
                                                    <div v-if="count === 1">
                                                        <img :src="'/images/boss/' + name + '/' + item + '.png'"
                                                             class="pixel hiscore-icon"
                                                             :alt="item + ' item icon'"
                                                             :title="item | capitalize"
                                                             loading="lazy">
                                                    </div>
                                                    <div v-else-if="count > 0">
                                                        <img :src="'/images/boss/' + name + '/' + item + '.png'"
                                                             class="pixel hiscore-icon"
                                                             :alt="item + ' item icon'"
                                                             :title="item | capitalize"
                                                             loading="lazy">
                                                        <span class="collection-log-item-counter runescape-progress">
                                                            {{ count }}
                                                        </span>
                                                    </div>
                                                    <div v-else>
                                                        <img :src="'/images/boss/' + name + '/' + item + '.png'"
                                                             class="pixel hiscore-icon faded"
                                                             :alt="item + ' item icon'"
                                                             :title="item | capitalize"
                                                             loading="lazy">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-on:click="toggleCollectionLog"
                     class="btn bg-dark text-light button-small button-combat-style-narrow" style="cursor: pointer;">
                    <img src="https://www.osrsbox.com/osrsbox-db/items-icons/22711.png"
                         class="hiscore-icon-small"
                         alt="Collection log item icon"
                         title="Click here to switch between collection log mode">
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
        toggleCollectionLog() {
            this.showCollectionLog = this.showCollectionLog === false;
        },

        fetchAccountBossHiscores() {
            axios
                .get('/api/account/' + this.account.username + '/boss')
                .then((response) => {
                    this.data = response.data.data;
                    this.hiscores = response.data.meta.boss_hiscores;
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
            this.fetchAccountBossHiscores();
        }
    },

    data() {
        return {
            loading: true,
            errored: false,
            data: {},
            hiscores: {},
            showCollectionLog: false
        }
    },

    mounted() {
        this.fetchAccountBossHiscores();
    },

    filters: {
        capitalize: function (value) {
            if (!value) return ''
            value = value.toString()
            return (value.charAt(0).toUpperCase() + value.slice(1)).replaceAll('_', ' ')
        },
    }
}
</script>
