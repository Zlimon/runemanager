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
                <div class="float-end">
                    <div @click="deleteAccountAuthStatus(authStatus)" class="btn btn-danger">Delete</div>
                </div>

                <h1 class="text-center header-chatbox-sword">{{ authStatus.username }}</h1>

                <div class="d-flex justify-content-center">
                    <div class="col-12 col-md-5">
                        <div class="row">
                            <div class="col-4 col-form-label">Status</div>
                            <div class="col-6">
                                <span class="form-control-plaintext text-light">{{ authStatus.status }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4 col-form-label">Code</div>
                            <div class="col-6">
                                <pre class="form-control-plaintext text-light" style="font-size: .90rem;">{{ authStatus.code }}</pre>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="account_type" class="col-4 col-form-label">Account type</label>
                            <div class="col-7">
                                 <span class="form-control-plaintext text-light">
                                    <img v-if="accountType !== 'normal'"
                                         :src="'/images/' + accountType + '.png'"
                                         :alt="accountType + ' icon'"
                                         :title="'You have currently picked ' + accountType + ' as account type for this account'">
                                    {{ accountType }}
                                </span>

                                <div class="input-group mb-3">
                                    <select v-model="accountType" class="form-select">
                                        <option v-for="accountType in accountTypes" :value="accountType">
                                            {{ accountType }}
                                        </option>
                                    </select>
                                    <div @click="updateAccountType(authStatus)" class="btn btn btn-primary">Switch</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h2 class="mt-2">How to authenticate:</h2>

                <ol>
                    <li>Make sure you have the RuneManager plugin enabled</li>
                    <li>Enter your RuneManager user credentials in the RuneManager plugin configurations</li>
                    <li>Log in on your Old School RuneScape account</li>
                    <li>Type in chat:<br>
                        <span style="font-family: monospace; font-size: 1.25rem;">!auth {{ authStatus.code }}</span>
                    </li>
<!--                    <li>You should get the response:<br>-->
<!--                        <strong>Attempting to authenticate account {{ authStatus.username }} to-->
<!--                            user {{ authStatus.user.name }}</strong>-->
<!--                    </li>-->
                    <li>And then:<br>
                        <strong>Account successfully authenticated!</strong>
                    </li>
                </ol>

                <h2>Other responses mean:</h2>

                <ul>
                    <li>
                        <em>Not a supported account type. Valid account types: normal, ironman, hardcore ironman, ultimate
                            ironman</em><br>
                        <span>This means you are attempting to authenticate the account on an unsupported game mode such as DMM, Leagues etc.</span><br>
                        <span>To fix this you have to log in to a normal world</span>
                    </li>
                    <li>
                        <em>This account has no pending status</em><br>
                        <span>This means this account has (or has not successfully) already been authenticated to RuneManager</span>
                    </li>
                    <li>
                        <em>This account is registered as &lt;account type&gt;, not &lt;account type&gt;</em><br>
                        <span>This means you are attempting to authenticate the account with a different account type than the registered account type</span><br>
                        <span>To fix this you can update the account type above</span>
                    </li>
                    <li>
                        <em>Invalid code</em><br>
                        <span>This means you have most likely written the code wrong. Try again</span>
                    </li>
                    <li>
                        <em>Could not fetch player data from hiscores</em><br>
                        <span>This means RuneManager was not able to fetch player hiscores from Old School RuneScape. This is most likely an error on the Old School hiscore server</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "PageAccountAuthIndex",

    props: {
        authStatus: {required: true},
        accountTypes: {required: true},
    },

    methods: {
        updateAccountType(accountAuthStatus) {
            axios
                .post('/api/account/auth/' + accountAuthStatus.id + '/update', {
                    _method: 'patch',
                    account_type: this.accountType
                })
                .then(() => {
                    this.errors = null;

                    this.doSuccess('Successfully updated account type for account "' + accountAuthStatus.username + '".');
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.accountType = this.authStatus.account_type;

                    this.errors = error.response.data.errors;
                    this.doError(error.response.data.message, error.response.data.errors);
                });
        },

        deleteAccountAuthStatus(accountAuthStatus) {
            axios
                .post('/api/account/auth/' + accountAuthStatus.id + '/destroy', {
                    _method: 'delete',
                })
                .then(() => {
                    this.errors = null;

                    this.doSuccess('Successfully deleted authentication process for account "' + accountAuthStatus.username + '".');

                    window.location.href = '/account/auth/create';
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
            accountType: this.authStatus.account_type,

            errors: null,
        }
    },
}
</script>

<style scoped>

</style>
