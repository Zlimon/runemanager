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
                <h1 class="text-center header-chatbox-sword">{{ user.name }}</h1>

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

                        <div class="row mb-3">
                            <div class="col-sm-3 col-form-label">or pick a random icon</div>
                            <div class="col-sm-9">
                                <div class="d-flex">
                                    <div v-for="icon in randomIcons">
                                        <img @click="fields.icon_id = icon"
                                             :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + icon + '.png'"
                                             class="p-2 me-2 background-world-map"
                                             alt="Random item icon"
                                             title="Click here to pick this as your user icon">
                                    </div>
                                </div>
                            </div>
                        </div>

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
    name: "PageEdit",

    props: {
        user: {required: true},
        randomIcons: {required: false},
    },

    methods: {
        updateUser() {
            axios
                .put('/api/user/update', this.fields)
                .then(() => {
                    this.errors = null;

                    this.doSuccess('Successfully updated user "' + this.user.name + '".');
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

    mounted() {
        this.fields = this.user;
    },
}
</script>

<style scoped>

</style>
