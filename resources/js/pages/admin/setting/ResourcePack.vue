<template>
    <div class="row">
        <div class="col-12 col-md-4">
            <div class="bg-admin-dark p-4">
                <h3>Install new Resource Pack</h3>

                <div class="p-4 mb-4 bg-admin-info">
                    <div class="row">
                        <div class="input-group">
                            <span class="input-group-text">pack-</span>
                            <input
                                v-model="fields.search"
                                v-bind:class="{ 'is-invalid' : this.errors && this.errors.search !== undefined }"
                                type="text"
                                id="search"
                                name="search"
                                class="form-control"
                                placeholder=""
                                required>
                            <button @click="searchResourcePack" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>

                        <div v-if="this.errors && this.errors.search !== undefined">
                            <small v-for="error in this.errors.search" class="text-danger">{{ error }}<br></small>
                        </div>

                        <div class="text-dark">
                            Visit the
                            <a href="https://github.com/melkypie/resource-packs/wiki/Resource-packs-hub"
                               class="link-light"
                               target="_blank"
                               rel="noopener noreferrer">
                                Resource packs hub
                            </a>
                            to browse through available resource packs!
                        </div>
                    </div>
                </div>

                <h3>Downloaded Resource Packs</h3>

                <div class="p-4 mb-4 bg-admin-info">
                    <div style="max-height: 500px; overflow-y: auto; overflow-x: hidden;">
                        <div v-for="resourcePack in resourcePacks">
                            <div class="row">
                                <div class="col-9">
                                    <p>
                                        <span>
                                            {{ resourcePack.alias }}
                                            <span class="badge bg-dark">
                                                v.{{ resourcePack.version }}
                                            </span>
                                        </span>
                                        <br>
                                        <small class="text-dark">By {{ resourcePack.author }}</small>
                                    </p>
                                </div>

                                <div class="col-3">
                                    <div v-if="resourcePack.id !== currentResourcePack.id"
                                         @click="switchResourcePack(resourcePack)"
                                         class="btn btn-primary btn-block">
                                        Use
                                    </div>
                                    <div v-else
                                         @click="updateResourcePack(resourcePack)"
                                         class="btn btn-success btn-block">
                                        Update
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h3>Currently in use</h3>

                <div class="p-4 mb-4 bg-admin-info">
                    <div class="row mb-3">
                        <div class="col-7">
                            <img :src="'/storage/resource-pack/icon.png' + '?' + Math.random()"
                                 class="w-100"
                                 alt="Resource Pack icon"
                                 :title="currentResourcePack.alias + ' by ' + currentResourcePack.author">
                        </div>

                        <div class="col">
                            <h5>
                                {{ currentResourcePack.alias }}
                                <span class="badge bg-dark">
                                    v.{{ currentResourcePack.version }}
                                </span>
                            </h5>
                            <small class="text-dark">By {{ currentResourcePack.author }}</small>
                            <br>
                            <small class="text-dark">Last updated {{ currentResourcePack.updated_at }}</small>

                            <div @click="updateResourcePack(currentResourcePack)"
                                 class="btn btn-success btn-block">
                                Update
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-8">
            <div class="text-center h-100 w-100 border border-dark border-5">
                <fieldset>
                    <iframe :src="'http://runemanager.test' + '?' + Math.random()"
                            title="Preview of RuneManager">
                    </iframe>
                </fieldset>
            </div>
        </div>

        <div class="modal fade" ref="responseModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-admin-dark">
                    <div class="modal-header">
                        <h5 class="modal-title">Modal title</h5>
                        <button type="button" class="btn-close" @click="modal.hide()" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <pre>{{ artisanResponse }}</pre>
                    </div>
                    <div class="modal-footer">
                        <div v-if="artisanStatus === 0"
                             @click="switchResourcePack(downloadedResourcePack)"
                             class="btn btn-primary">
                            Switch to {{ downloadedResourcePack.alias }}
                        </div>
                        <div v-else-if="artisanStatus === 1"
                             @click="switchResourcePack(downloadedResourcePack)"
                             class="btn btn-primary">
                            Switch to {{ downloadedResourcePack.alias }}
                        </div>
                        <div v-else-if="artisanStatus === 3"
                             @click="searchResourcePack"
                             class="btn btn-primary">
                            Try again
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {Modal} from 'bootstrap'

export default {
    name: "ResourcePack",

    props: {
        resourcePacksProp: {required: true},
        currentResourcePackProp: {required: true},
    },

    methods: {
        searchResourcePack() {
            axios
                .post('/api/admin/settings/resource-pack', this.fields)
                .then(response => {
                    this.errors = null;

                    this.resourcePacks = response.data.resourcePacks;
                    this.artisanStatus = response.data.status;
                    this.artisanResponse = response.data.message;
                    this.downloadedResourcePack = response.data.resourcePack;
                    this.modal.show();
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.errors = error.response.data.errors;
                });
        },

        switchResourcePack(resourcePack) {
            this.modal.hide();

            axios
                .post('/api/admin/settings/resource-pack/' + resourcePack.id + '/switch', {
                    _method: 'patch',
                })
                .then(response => {
                    this.errors = null;

                    this.artisanResponse = response.data.message;
                    this.currentResourcePack = response.data.resourcePack;

                    this.toastSuccess('Successfully switched to ' + this.currentResourcePack.alias);
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.toastError(error.response.data.errors.message);
                    this.errors = error.response.data.errors;
                });
        },

        updateResourcePack(resourcePack) {
            axios
                .post('/api/admin/settings/resource-pack/' + resourcePack.id + '/update', {
                    _method: 'patch',
                })
                .then(response => {
                    this.errors = null;

                    this.artisanResponse = response.data.message;
                    this.currentResourcePack = response.data.resourcePack;

                    this.toastSuccess('Successfully updated ' + this.currentResourcePack.alias);
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.toastError(error.response.data.errors.message);
                    this.errors = error.response.data.errors;
                });
        },

        toastSuccess(successMessage) {
            this.$swal.fire({
                toast: true,
                icon: 'success',
                title: 'Success',
                text: successMessage,
                position: 'top-right',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            })
        },

        toastError(errorMessage) {
            this.$swal.fire({
                toast: true,
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                position: 'top-right',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            })
        },
    },

    data() {
        return {
            modal: null,

            resourcePacks: this.resourcePacksProp,
            currentResourcePack: this.currentResourcePackProp,

            fields: {
                search: '',
            },

            artisanStatus: null,
            artisanResponse: '',
            downloadedResourcePack: {},

            errored: false,
            errors: null,
        }
    },

    mounted() {
        this.modal = new Modal(this.$refs.responseModal)
    },
}
</script>

<style scoped>
fieldset {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
}

iframe {
    width: 100%;
    height: 100%;
}
</style>
