<template>
    <div class="row">
        <div class="col-12 col-md-3 mb-2">
            <div class="bg-admin-dark p-4">
                <h1>Skills</h1>

                <div class="p-4 mb-4 bg-admin-info">
                    <accountskillhiscore :account="account"></accountskillhiscore>
                </div>

                <h1>Bosses</h1>

                <div class="p-4 bg-admin-info">
                    <accountbosshiscore :account="account"></accountbosshiscore>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="bg-admin-dark p-4">
                <h5>Transfer ownership of this account</h5>

                <label v-if="loadedAccount.user_id !== null" for="name" class="col-form-label">From {{ loadedAccount.user.name }} to:</label>
                <label v-else for="name" class="col-form-label"><em>Currently not linked to any user</em></label>
                <div class="row">
                    <div class="col-sm-9">
                        <div class="input-group">
                            <input v-model="fields.name"
                                   v-bind:class="{ 'is-invalid' : this.errors && this.errors.name !== undefined }"
                                   type="text"
                                   id="name"
                                   name="name"
                                   class="form-control"
                                   required>
                            <div @click="transferAccountOwnership" class="btn btn-primary">Search</div>
                        </div>
                        <div v-if="this.errors && this.errors.name !== undefined">
                            <small v-for="error in this.errors.name" class="text-danger">{{ error }}<br></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "PageAdminAccountShow",

    props: {
        account: {required: true},
    },

    methods: {
        transferAccountOwnership() {
            axios
                .put('/api/admin/account/' + this.account.username + '/update', this.fields)
                .then((response) => {
                    this.errors = null;

                    this.loadedAccount.user.name = response.data.name;
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.errors = error.response.data.errors;
                });
        },
    },

    data() {
        return {
            loadedAccount: this.account,

            fields: {},

            errors: null,
        }
    },
}
</script>

<style scoped>

</style>
