<template>
    <div class="row">
        <div class="col-12 col-md-4">
            <div class="p-4 bg-admin-dark">
                <h1>Reserve an OSRS account</h1>
                <p>If you want to reserve an Old School RuneScape account for future use / prevent it being claimed, you can do it here.</p>
                <p>To link the account to an user, visit the account page and fill the "Transfer ownership of this account" form.</p>

                <div class="p-4 bg-admin-info">
                    <div class="mb-3">
                        <label for="username" class="form-label">Old School RuneScape username</label>
                        <input v-model="fields.username"
                               v-bind:class="{ 'is-invalid' : this.errors && this.errors.name !== undefined }"
                               type="text"
                               id="username"
                               name="username"
                               class="form-control"
                               required>
                        <div v-if="this.errors && this.errors.username !== undefined">
                            <small v-for="error in this.errors.username" class="text-danger">{{ error }}<br></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">RuneManager username or ID</label>
                        <input v-model="fields.name"
                               v-bind:class="{ 'is-invalid' : this.errors && this.errors.name !== undefined }"
                               type="text"
                               id="name"
                               name="name"
                               class="form-control"
                               required>
                        <div v-if="this.errors && this.errors.name !== undefined">
                            <small v-for="error in this.errors.name" class="text-danger">{{ error }}<br></small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
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
                .put('/api/admin/user/' + this.user.id + '/update', this.fields)
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
