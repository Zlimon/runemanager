<template>
    <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
        <h1 class="text-center">Compare</h1>

        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <input v-model="accountOne.username"
                           type="text"
                           class="form-control"
                           id="account_one"
                           name="account_one"
                           placeholder="Type a player username"
                           autofocus required>
                </div>
            </div>

            <div class="col-6">
                <div class="form-group">
                    <input v-model="accountTwo.username"
                           type="text"
                           class="form-control"
                           id="account_two"
                           name="account_two"
                           placeholder="Type a player username"
                           autofocus required>
                </div>
            </div>
        </div>

        <div class="text-center">
            <div @click="fetchAccounts" class="btn button-combat-style-narrow">Compare</div>
        </div>

        <div v-if="accountOne.username !== '' && accountTwo.username !== ''">
            <div v-if="loading">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>

            <div v-else class="row justify-content-center">
                <div v-if="accountOne.hiscore !== undefined" class="col-auto">
                    <table class="table-responsive">
                        <tr>
                            <th class="d-none d-md-table-cell">Name</th>
                            <th>Rank</th>
                            <th>Level</th>
                            <th>XP</th>
                            <th></th>
                        </tr>

                        <tr class="bg-dark text-light background-dialog-panel">
                            <td class="d-none d-md-table-cell pl-2">Overall</td>
                            <td>{{ accountOne.user.rank }}</td>
                            <td>{{ accountOne.user.level }}</td>
                            <td></td>
                            <td>
                                <img :alt="'Total level icon'"
                                     :src="'/storage/resource-pack/skill/total.png'"
                                     :title="'Click here to visit total level hiscores'"
                                     class="">
                            </td>
                        </tr>

                        <tr v-for="(hiscore, name) in accountOne.hiscore"
                            class="bg-dark text-light background-dialog-panel">
                            <td class="d-none d-md-table-cell text-capitalize pl-2">{{ name }}</td>
                            <td class="pl-2 pl-md-0">{{ hiscore.rank }}</td>
                            <td>{{ hiscore.level }}</td>
                            <td>{{ hiscore.xp }}</td>
                            <td>
                                <img :alt="name + ' skill icon'"
                                     :src="'/storage/resource-pack/skill/' + name + '.png'"
                                     :title="'Click here to visit ' + name + ' hiscores'">
                            </td>
                        </tr>
                    </table>
                </div>
                <div v-else-if="this.errors && this.errors.accountOne !== undefined" class="col-5 text-center py-5">
                    <img src="/images/ignore.png"
                         class="pixel icon"
                         alt="Sad face">
                    <h3>{{ errors.accountOne }}</h3>
                </div>

                <div>
                    <table v-if="comparisons !== null && comparisons.length > 0"
                           class="table-responsive table-borderless">
                        <tr>
                            <th></th>
                        </tr>
                        <tr>
                            <td>
                                <img v-if="accountOne.user.rank < accountTwo.user.rank"
                                     alt=""
                                     :src="'/storage/resource-pack/other/list_sorting_arrow_ascending.png'"
                                     class="w-50 float-left"
                                     style="image-rendering: pixelated; transform: rotate(270deg);">
                                <img v-else
                                     alt=""
                                     :src="'/storage/resource-pack/other/list_sorting_arrow_ascending.png'"
                                     class="w-50 float-right"
                                     style="image-rendering: pixelated; transform: rotate(90deg);">
                            </td>
                        </tr>
                        <tr v-for="comparison in comparisons">
                            <td class="text-center">
                                <img v-if="comparison"
                                     alt=""
                                     :src="'/storage/resource-pack/other/list_sorting_arrow_ascending.png'"
                                     class="w-50 float-left"
                                     style="image-rendering: pixelated; transform: rotate(270deg);">
                                <img v-else-if="!comparison"
                                     alt=""
                                     :src="'/storage/resource-pack/other/list_sorting_arrow_ascending.png'"
                                     class="w-50 float-right"
                                     style="image-rendering: pixelated; transform: rotate(90deg);">
                            </td>
                        </tr>
                    </table>
                </div>

                <div v-if="accountTwo.hiscore !== undefined" class="col-auto">
                    <table class="table-responsive">
                        <tr>
                            <th></th>
                            <th class="d-none d-md-table-cell">Name</th>
                            <th>Rank</th>
                            <th>Level</th>
                            <th>XP</th>
                        </tr>

                        <tr class="bg-dark text-light background-dialog-panel">
                            <td>
                                <img :alt="'Total level icon'"
                                     :src="'/storage/resource-pack/skill/total.png'"
                                     :title="'Click here to visit total level hiscores'"
                                     class="pl-1">
                            </td>
                            <td class="d-none d-md-table-cell">Overall</td>
                            <td>{{ accountTwo.user.rank }}</td>
                            <td>{{ accountTwo.user.level }}</td>
                            <td></td>
                        </tr>

                        <tr v-for="(hiscore, name) in accountTwo.hiscore"
                            class="bg-dark text-light background-dialog-panel">
                            <td>
                                <img :alt="name + ' skill icon'"
                                     :src="'/storage/resource-pack/skill/' + name + '.png'"
                                     :title="'Click here to visit ' + name + ' hiscores'"
                                     class="pl-1">
                            </td>
                            <td class="d-none d-md-table-cell text-capitalize">{{ name }}</td>
                            <td>{{ hiscore.rank }}</td>
                            <td>{{ hiscore.level }}</td>
                            <td>{{ hiscore.xp }}</td>
                        </tr>
                    </table>
                </div>
                <div v-else-if="this.errors && this.errors.accountTwo !== undefined" class="col-5 text-center py-5">
                    <img src="/images/ignore.png"
                         class="pixel icon"
                         alt="Sad face">
                    <h3>{{ errors.accountTwo }}</h3>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "AccountCompare",

    methods: {
        fetchAccounts() {
            if (this.accountOne.username !== '' && this.accountTwo.username !== '') {
                this.loading = true;

                this.fetchAccountOneSkillHiscores(this.accountOne.username)
                this.fetchAccountTwoSkillHiscores(this.accountTwo.username)

                this.compareAccounts()
            }
        },

        fetchAccountOneSkillHiscores(account) {
            axios
                .get('/api/account/' + account + '/skill')
                .then((response) => {
                    this.errors.accountOne = null;

                    console.log(response.data)
                    this.accountOne.user = response.data.data;
                    this.accountOne.hiscore = response.data.meta.skill_hiscores;
                })
                .catch(error => {
                    console.error(error)

                    this.accountOne.hiscore = undefined;
                    this.comparisons = null;
                    this.errors.accountOne = 'Could not load account "' + account + '"';
                })
        },

        fetchAccountTwoSkillHiscores(account) {
            axios
                .get('/api/account/' + account + '/skill')
                .then((response) => {
                    this.errors.accountTwo = null;

                    console.log(response.data)
                    this.accountTwo.user = response.data.data;
                    this.accountTwo.hiscore = response.data.meta.skill_hiscores;
                })
                .catch(error => {
                    console.error(error)

                    this.accountTwo.hiscore = undefined;
                    this.comparisons = null;
                    this.errors.accountTwo = 'Could not load account "' + account + '"';
                })
        },

        compareAccounts() {
            axios
                .get('/api/hiscore/' + this.accountOne.username + '/' + this.accountTwo.username + '/compare')
                .then((response) => {
                    this.comparisons = response.data;
                })
                .catch(error => {
                    console.error(error)

                    this.comparisons = null;
                })
                .finally(() => this.loading = false)
        },
    },

    data() {
        return {
            accountOne: {
                username: 'Jern Zlimon',
                user: undefined,
                hiscore: undefined,
            },
            accountTwo: {
                username: 'Fletenios',
                user: undefined,
                hiscore: undefined,
            },

            comparisons: null,

            errored: false,
            errors: {},
            loading: false,
        }
    }
}
</script>

<style scoped>

</style>
