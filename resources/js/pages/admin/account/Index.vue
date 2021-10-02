<template>
    <div>
        <div class="text-center">
            <h1>Search for accounts</h1>
        </div>

        <div class="row d-flex justify-content-center mb-3">
            <div class="col-12 col-md-4">
                <div class="row">
                    <div class="input-group">
                        <input v-model="fields.search"
                               v-bind:class="{ 'is-invalid' : this.errors && this.errors.search !== undefined }"
                               @input="searchUser"
                               @change="searchUser"
                               type="text"
                               id="search"
                               name="search"
                               class="form-control"
                               placeholder=""
                               autofocus required>
                        <div @click="searchUser" class="btn btn-primary">Search</div>
                        <div v-if="this.errors && this.errors.search !== undefined">
                            <small v-for="error in this.errors.search" class="text-danger">{{ error }}<br></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>Account ID</th>
                        <th>Username</th>
                        <th class="d-none d-md-table-cell">Rank</th>
                        <th class="d-none d-md-table-cell">Level</th>
                        <th class="d-none d-md-table-cell">XP</th>
                        <th>Linked user</th>
                        <th class="d-none d-md-table-cell">Registered</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="account in loadedAccounts">
                        <th scope="row">{{ account.id }}</th>
                        <td>
                            <img v-if="account.account_type !== 'normal'"
                                 :src="'/images/' + account.account_type + '.png'"
                                 :alt="account.account_type + ' icon'">
                            {{ account.username }}
                        </td>
                        <td class="d-none d-md-table-cell">{{ account.rank }}</td>
                        <td class="d-none d-md-table-cell">{{ account.level }}</td>
                        <td class="d-none d-md-table-cell">{{ account.xp }}</td>
                        <td>
                            <a v-if="account.user_id !== null" :href="'/admin/user/' + account.user_id + '/show'" class="link-primary">
                                <div class="d-flex align-items-center">
                                    <div class="d-none d-md-table-cell">
                                        <img :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + account.user.icon_id + '.png'"
                                             class="pixel"
                                             alt="Profile icon"
                                             width="35">
                                    </div>
                                    <span>{{ account.user.name }}</span>
                                    <img v-if="account.user.private === 1"
                                         :src="'/storage/resource-pack/bank/placeholders_lock.png'"
                                         class="pixel"
                                         alt="Padlock icon"
                                         title="User is private"
                                         width="20">
                                </div>
                            </a>
                        </td>
                        <td class="d-none d-md-table-cell">{{ account.created_at | moment('ddd. Do MMM HH:mm') }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <a :href="'/admin/account/' + account.username + '/show'" class="btn btn-success d-block">Show</a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
require('moment');

export default {
    name: "PageAdminAccountIndex",

    props: {
        accounts: {required: true},
    },

    methods: {
        searchUser() {
            if (this.fields.search !== '') {
                axios
                    .post('/api/admin/account/search', this.fields)
                    .then(response => {
                        this.errors = null;

                        this.loadedAccounts = response.data;
                    })
                    .catch(error => {
                        console.error(error.response.data);

                        this.errors = error.response.data.errors;
                    });
            } else {
                this.loadedAccounts = this.preLoadedAccounts;
            }
        },
    },

    data() {
        return {
            preLoadedAccounts: this.accounts,
            loadedAccounts: this.accounts,

            fields: {
                search: '',
            },

            errors: null,
        }
    }
}
</script>

<style scoped>

</style>
