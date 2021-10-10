<template>
    <div class="row mb-3">
        <div class="col-3 d-none d-md-block">
            <div class="bg-dark background-dialog-panel p-3">
                <h2 class="text-center header-chatbox-sword">Notifications</h2>
                <announcementall></announcementall>
            </div>
        </div>

        <div class="col">
            <div class="bg-dark background-dialog-panel p-3">
                <h1 class="text-center">Compare</h1>

                <div class="row">
                    <div class="col-6">
                        <div class="input-group">
                            <input v-model="accountOne.username"
                                   v-bind:class="{ 'is-invalid' : this.errors && this.errors.accountOne !== undefined }"
                                   type="text"
                                   class="form-control"
                                   id="account_one"
                                   name="account_one"
                                   placeholder="Type a player username"
                                   required>
                            <span v-if="accountOne.account !== undefined && accountOne.account.account_type !== 'normal'" class="input-group-text">
                                <img :src="'/images/' + accountOne.account.account_type +'.png'"
                                     class="pixel"
                                     alt="Account type icon"
                                     style="width: 1rem;">
                            </span>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="input-group">
                            <span v-if="accountTwo.account !== undefined && accountTwo.account.account_type !== 'normal'" class="input-group-text">
                                <img :src="'/images/' + accountTwo.account.account_type +'.png'"
                                     class="pixel"
                                     alt="Account type icon"
                                     style="width: 1rem;">
                            </span>
                            <input v-model="accountTwo.username"
                                   v-bind:class="{ 'is-invalid' : this.errors && this.errors.accountTwo !== undefined }"
                                   type="text"
                                   class="form-control"
                                   id="account_two"
                                   name="account_two"
                                   placeholder="Type a player username"
                                   required>
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

                    <div v-else class="d-flex justify-content-center">
                        <div v-if="accountOne.hiscore !== undefined" class="col">
                            <table class="table-responsive">
                                <tr>
                                    <th class="d-none d-md-table-cell">Name</th>
                                    <th>Rank</th>
                                    <th>Level</th>
                                    <th>XP</th>
                                    <th></th>
                                </tr>

                                <tr class="bg-dark text-light background-dialog-panel">
                                    <td class="d-none d-md-table-cell ps-2">Overall</td>
                                    <td>{{ accountOne.account.rank }}</td>
                                    <td>{{ accountOne.account.level }}</td>
                                    <td>{{ accountOne.account.xp }}</td>
                                    <td>
                                        <img :src="'/storage/resource-pack/skill/total.png'"
                                             :alt="'Total level icon'"
                                             :title="'Click here to visit total level hiscores'">
                                    </td>
                                </tr>

                                <tr v-for="(hiscore, name) in accountOne.hiscore"
                                    class="bg-dark text-light background-dialog-panel">
                                    <td class="d-none d-md-table-cell text-capitalize ps-2">{{ name }}</td>
                                    <td class="ps-2 ps-md-0">{{ hiscore.rank }}</td>
                                    <td>{{ hiscore.level }}</td>
                                    <td>{{ hiscore.xp }}</td>
                                    <td>
                                        <img :src="'/storage/resource-pack/skill/' + name + '.png'"
                                             :alt="name + ' skill icon'"
                                             :title="'Click here to visit ' + name + ' hiscores'">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div v-else-if="this.errors && this.errors.accountOne !== undefined" class="col-5 text-center py-5">
                            <img :src="'/images/ignore.png'"
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
                                        <img v-if="accountOne.account.rank < accountTwo.account.rank"
                                             :src="'/storage/resource-pack/other/list_sorting_arrow_ascending.png'"
                                             class="w-50 float-start"
                                             alt=""
                                             style="image-rendering: pixelated; transform: rotate(270deg);">
                                        <img v-else
                                             :src="'/storage/resource-pack/other/list_sorting_arrow_ascending.png'"
                                             class="w-50 float-end"
                                             alt=""
                                             style="image-rendering: pixelated; transform: rotate(90deg);">
                                    </td>
                                </tr>
                                <tr v-for="comparison in comparisons">
                                    <td class="text-center">
                                        <img v-if="comparison"
                                             :src="'/storage/resource-pack/other/list_sorting_arrow_ascending.png'"
                                             class="w-50 float-start"
                                             alt=""
                                             style="image-rendering: pixelated; transform: rotate(270deg);">
                                        <img v-else-if="!comparison"
                                             :src="'/storage/resource-pack/other/list_sorting_arrow_ascending.png'"
                                             class="w-50 float-end"
                                             alt=""
                                             style="image-rendering: pixelated; transform: rotate(90deg);">
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div v-if="accountTwo.hiscore !== undefined" class="col">
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
                                        <img :src="'/storage/resource-pack/skill/total.png'"
                                             class="ps-1"
                                             :alt="'Total level icon'"
                                             :title="'Click here to visit total level hiscores'">
                                    </td>
                                    <td class="d-none d-md-table-cell">Overall</td>
                                    <td>{{ accountTwo.account.rank }}</td>
                                    <td>{{ accountTwo.account.level }}</td>
                                    <td>{{ accountTwo.account.xp }}</td>
                                </tr>

                                <tr v-for="(hiscore, name) in accountTwo.hiscore"
                                    class="bg-dark text-light background-dialog-panel">
                                    <td>
                                        <img :src="'/storage/resource-pack/skill/' + name + '.png'"
                                             class="ps-1"
                                             :alt="name + ' skill icon'"
                                             :title="'Click here to visit ' + name + ' hiscores'">
                                    </td>
                                    <td class="d-none d-md-table-cell text-capitalize">{{ name }}</td>
                                    <td>{{ hiscore.rank }}</td>
                                    <td>{{ hiscore.level }}</td>
                                    <td>{{ hiscore.xp }}</td>
                                </tr>
                            </table>
                        </div>
                        <div v-else-if="this.errors && this.errors.accountTwo !== undefined" class="col-5 text-center py-5">
                            <img :src="'/images/ignore.png'"
                                 class="pixel icon"
                                 alt="Sad face">
                            <h3>{{ errors.accountTwo }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "PageHiscoreCompare",

    props: {
        accountOneUsername: {required: false},
        accountTwoUsername: {required: false},
    },

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

                    this.errors.accountOne = undefined;

                    this.accountOne.account = response.data.data;
                    this.accountOne.hiscore = response.data.meta.skill_hiscores;

                    console.log(this.accountOne.account)
                    console.log(this.accountOne.hiscore)
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
                    this.errors.accountTwo = undefined;

                    this.accountTwo.account = response.data.data;
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
                username: this.accountOneUsername,
                account: undefined,
                hiscore: undefined,
            },
            accountTwo: {
                username: this.accountTwoUsername,
                account: undefined,
                hiscore: undefined,
            },

            comparisons: null,

            errors: {},
            loading: false,
        }
    }
}
</script>

<style scoped>

</style>
