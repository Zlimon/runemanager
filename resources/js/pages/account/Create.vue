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
                <h1 class="text-center header-chatbox-sword">Link account</h1>

                <div class="d-flex justify-content-center">
                    <div class="col-12 col-md-5">
                        <div class="row mb-3">
                            <label for="account_username" class="col-sm-3 col-form-label">Username</label>
                            <div class="col-sm-9">
                                <input v-model="fields.account_username"
                                       v-bind:class="{ 'is-invalid' : this.errors && this.errors.account_username !== undefined }"
                                       type="text"
                                       id="account_username"
                                       name="account_username"
                                       class="form-control"
                                       required>
                                <div v-if="this.errors && this.errors.account_username !== undefined">
                                    <small v-for="error in this.errors.account_username" class="text-danger">{{ error }}<br></small>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="account_type" class="col-sm-3 col-form-label">Account type</label>
                            <div class="col-sm-9" style="margin-top: .5rem;">
                                <div v-for="accountType in accountTypes">
                                    <div v-if="accountType === 'normal'" class="form-check" style="margin-left: .4rem;">
                                        <input v-model="fields.account_type"
                                               type="radio"
                                               :id="accountType"
                                               name="account_type"
                                               class="form-check-input"
                                               :value="accountType"
                                               checked>
                                        <label :for="accountType" class="form-check-label">
                                            {{ accountType }}
                                        </label>
                                    </div>
                                    <div v-else class="icon-radio">
                                        <label :for="accountType" class="form-check-label">
                                            <input v-model="fields.account_type"
                                                   type="radio"
                                                   :id="accountType"
                                                   name="account_type"
                                                   class="form-check-input"
                                                   :value="accountType">
                                            <img :src="'/images/' + accountType + '.png'"
                                                 :alt="accountType + ' icon'"
                                                 :title="'Click here to select ' + accountType + ' as account type for this account'">
                                            <span>{{ accountType }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div v-if="this.errors && this.errors.account_type !== undefined">
                                    <small v-for="error in this.errors.account_type" class="text-danger">{{ error }}<br></small>
                                </div>
                            </div>
                        </div>

                        <div @click="createAccountAuthStatus" class="btn btn-primary d-block">
                            Update
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "PageAccountCreate",

    props: {
        accountTypes: {required: true},
    },

    methods: {
        createAccountAuthStatus() {
            axios
                .post('/api/authenticate/create', this.fields)
                .then(() => {
                    this.errors = null;

                    this.doSuccess('Successfully started authentication process for account "' + this.fields.account_username + '".');

                    window.location.href = '/authenticate';
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.errors = error.response.data.errors;
                    this.doError(error.response.data.message, error.response.data.errors);
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
