<template>
    <div>
        <div class="text-center">
            <h1>Search for users</h1>
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
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Linked OSRS account</th>
                        <th class="d-none d-md-table-cell">Registered</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="user in loadedUsers">
                        <th scope="row">{{ user.id }}</th>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="d-none d-md-table-cell">
                                    <img :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + user.icon_id + '.png'"
                                         class="pixel"
                                         alt="Profile icon"
                                         width="35">
                                </div>
                                <span>{{ user.name }}</span>
                                <img v-if="user.private === 1"
                                     :src="'/storage/resource-pack/bank/placeholders_lock.png'"
                                     class="pixel"
                                     alt="Padlock icon"
                                     title="User is private"
                                     width="20">
                            </div>
                        </td>
                        <td>{{ user.email }}</td>
                        <td>
                            <span v-for="account in user.account">
                                <a :href="'/admin/account/' + account.username + '/show'" class="link-primary">
                                    <img v-if="account.account_type !== 'normal'"
                                         :src="'/images/' + account.account_type + '.png'"
                                         :alt="account.account_type + ' icon'">
                                    <span>{{ account.id }} - {{ account.username }}</span>
                                </a>
                                <br>
                            </span>
                        </td>
                        <td class="d-none d-md-table-cell">{{ user.created_at | moment('ddd. Do MMM HH:mm') }}</td>
                        <td>
                            <div class="d-flex justify-content-between">
                                <a :href="'/admin/user/' + user.id + '/show'" class="btn btn-success">Show</a>
                                <a :href="'/admin/user/' + user.id + '/edit'" class="btn btn-primary">Edit</a>
                                <a :href="'/admin/user/' + user.id + '/ban'" class="btn btn-danger">Ban</a>
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
    name: "PageAdminUserIndex",

    props: {
        users: {required: true},
    },

    methods: {
        searchUser() {
            if (this.fields.search !== '') {
                axios
                    .post('/api/admin/user/search', this.fields)
                    .then(response => {
                        this.errors = null;

                        this.loadedUsers = response.data;
                    })
                    .catch(error => {
                        console.error(error.response.data);

                        this.errors = error.response.data.errors;
                    });
            } else {
                this.loadedUsers = this.preLoadedUsers;
            }
        },
    },

    data() {
        return {
            preLoadedUsers: this.users,
            loadedUsers: this.users,

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
