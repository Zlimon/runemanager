<template>
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="p-4 bg-admin-dark">
                <div class="text-center">
                    <img :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + (user.icon_id ? user.icon_id : 0) + '.png'"
                         class="pixel icon"
                         alt="Profile icon"
                         style="width: 10rem; height: 10rem;">
                    <h1>{{ user.name }}</h1>
                </div>

                <div class="row d-flex justify-content-center">
                    <div class="col-12 col-md-8">
                        <div class="row mb-3">
                            <label for="name" class="col-sm-3 col-form-label">Username</label>
                            <div class="col-sm-9">
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
                        </div>

                        <div class="row mb-3">
                            <label for="name" class="col-sm-3 col-form-label">E-mail</label>
                            <div class="col-sm-9">
                                <input v-model="fields.email"
                                       v-bind:class="{ 'is-invalid' : this.errors && this.errors.email !== undefined }"
                                       type="email"
                                       id="email"
                                       name="email"
                                       class="form-control"
                                       required>
                                <div v-if="this.errors && this.errors.email !== undefined">
                                    <small v-for="error in this.errors.email" class="text-danger">{{ error }}<br></small>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="name" class="col-sm-3 col-form-label">Status</label>
                            <div class="col-sm-9">
                                <div class="form-check">
                                    <input v-model="fields.private"
                                           type="radio"
                                           id="private"
                                           name="private"
                                           class="form-check-input"
                                           value="1">
                                    <label for="private" class="form-check-label">
                                        Private
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input v-model="fields.private"
                                           type="radio"
                                           id="public"
                                           name="private"
                                           class="form-check-input"
                                           value="0">
                                    <label for="public" class="form-check-label">
                                        Public
                                    </label>
                                </div>
                                <div v-if="this.errors && this.errors.private !== undefined">
                                    <small v-for="error in this.errors.private" class="text-danger">{{ error }}<br></small>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="name" class="col-sm-3 col-form-label">Profile icon ID</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <img :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + (user.icon_id ? user.icon_id : 0) + '.png'"
                                             class="pixel hiscore-icon"
                                             alt="Current profile icon">
                                    </span>
                                    <input v-model="fields.icon_id"
                                           v-bind:class="{ 'is-invalid' : this.errors && this.errors.icon_id !== undefined }"
                                           type="number"
                                           id="icon_id"
                                           name="icon_id"
                                           class="form-control">
                                </div>
                                <div v-if="this.errors && this.errors.icon_id !== undefined">
                                    <small v-for="error in this.errors.icon_id" class="text-danger">{{ error }}<br></small>
                                </div>
                                <div class="form-text">
                                    Type in the ID of an in-game item you wish to display as the profile icon for this user.
                                    <br>
                                    Search item icons
                                    <a href="https://www.osrsbox.com/tools/item-search/" class="btn-link" target="_blank" rel="noopener noreferrer">
                                        here
                                    </a>
                                </div>
                            </div>
                        </div>

<!--                        @if (sizeof(user.account))-->
<!--                            <h5 class="text-center">Transfer ownership of OSRS accounts:</h5>-->

<!--                            @foreach (user.account as $key => $account)-->
<!--                                <div class="row mb-3">-->
<!--                                    <label for="account[{{ $account->id }}]"-->
<!--                                           class="col-sm-3 col-form-label">-->
<!--                                        {{ $account->username }}-->
<!--                                    </label>-->
<!--                                    <div class="col-sm-9">-->
<!--                                        <input type="hidden"-->
<!--                                               id="account[{{ $account->id }}]"-->
<!--                                               name="accountId[{{ $account->id }}]"-->
<!--                                               value="{{ $account->id }}">-->

<!--                                        <input type="text"-->
<!--                                               id="account[{{ $account->id }}]"-->
<!--                                               name="account[{{ $account->id }}]"-->
<!--                                               class="form-control @error('account[{{ $account->id }}]') is-invalid @enderror"-->
<!--                                               placeholder="Username or ID of new owner">-->

<!--                                        @error('account[{{ $account->id }}]')-->
<!--                                            <span class="invalid-feedback" role="alert">-->
<!--                                                <strong>{{ $message }}</strong>-->
<!--                                            </span>-->
<!--                                        @enderror-->
<!--                                    </div>-->
<!--                                </div>-->

<!--                                @php $key++ @endphp-->
<!--                            @endforeach-->
<!--                        @endif-->

                        <div @click="updateUser"
                             class="btn btn-success d-block">
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
    name: "PageAdminUserEdit",

    props: {
        user: {required: true},
    },

    methods: {
        updateUser() {
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

    mounted() {
        this.fields = this.user;
    },
}
</script>

<style scoped>

</style>
