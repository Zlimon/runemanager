<template>
    <div class="p-4 bg-admin-dark">
        <h1>Reserve an OSRS account</h1>
        <p>If you want to reserve an Old School RuneScape account for future use / prevent it being claimed, you can do it here.</p>
        <p>To link the account to an user, visit the account page and fill the "Transfer ownership of this account" form.</p>

        <div class="p-4 bg-admin-info">
            <div class="mb-3">
                <label for="account_username" class="form-label">Old School RuneScape username</label>
                <input v-model="fields.account_username"
                       v-bind:class="{ 'is-invalid' : this.errors && this.errors.name !== undefined }"
                       type="text"
                       id="account_username"
                       name="account_username"
                       class="form-control"
                       required>
                <div v-if="this.errors && this.errors.account_username !== undefined">
                    <small v-for="error in this.errors.account_username" class="text-danger">{{ error }}<br></small>
                </div>
            </div>

            <div class="mb-3">
                <label for="user_name" class="form-label">RuneManager username or ID</label>
                <input v-model="fields.user_name"
                       v-bind:class="{ 'is-invalid' : this.errors && this.errors.name !== undefined }"
                       type="text"
                       id="user_name"
                       name="user_name"
                       class="form-control"
                       required>
                <div v-if="this.errors && this.errors.user_name !== undefined">
                    <small v-for="error in this.errors.user_name" class="text-danger">{{ error }}<br></small>
                </div>
            </div>

            <div @click="createAccount"
                 class="btn btn-primary d-block">
                Reserve
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "PageAdminAccountCreate",

    methods: {
        createAccount() {
            axios
                .post('/api/admin/account/store', this.fields)
                .then((response) => {
                    this.errors = null;
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.errors = error.response.data.errors;
                });
        },
    },

    data() {
        return {
            fields: {},

            errors: null,
        }
    },
}
</script>

<style scoped>

</style>
